<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Mail\Markdown;
use App\Services\GmailApiService as GmailApiMailer;

class GmailMailableSender
{
    private readonly array $googleConfig;

    public function __construct(
        private GmailApiMailer $gmail,
        array $config = []
    ) {
        // Use injected config or fallback to config helper
        $this->googleConfig = !empty($config) ? $config : config('google', []);
    }

    /**
     * Kirim Mailable Laravel (modern) via Gmail API (tanpa SMTP).
     *
     * @param  string   $to         Alamat tujuan
     * @param  Mailable $mailable   Instance Mailable apa pun (modern)
     * @return string               Gmail Message ID
     */
    public function send(string $to, Mailable $mailable): string
    {
        // ---- 1) Ambil subject dari Envelope (modern) ----
        $envelope = method_exists($mailable, 'envelope') ? $mailable->envelope() : null;
        $subject  = $envelope?->subject ?? '(No Subject)';
        $subject  = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        // Alamat FROM = akun yang sudah OAuth (harus sama pemilik refresh token)
        $from = $this->googleConfig['sender_email'] ?? env('GOOGLE_SENDER');

        // ---- 2) Ambil konten dari Content (modern) ----
        $content = method_exists($mailable, 'content') ? $mailable->content() : null;

        // Data utk view/markdown
        $data = $content?->with ?? [];

        // Render HTML (prioritas: html langsung > view > markdown)
        $html = null;
        if (!empty($content?->html) && is_string($content->html)) {
            // Jika ada html string langsung
            $html = $this->normalizeHtml($content->html);
        } elseif (!empty($content?->view)) {
            $html = $this->renderView($content->view, $data);
        } elseif (!empty($content?->markdown)) {
            $html = $this->renderMarkdown($content->markdown, $data);
        } else {
            // Fallback: jika (sangat jarang) developer set $mailable->view/viewData manual
            $view     = property_exists($mailable, 'view') ? $mailable->view : null;
            $viewData = property_exists($mailable, 'viewData') ? $mailable->viewData : [];
            if ($view) {
                $html = $this->renderView($view, $viewData);
            }
        }

        if (!$html) {
            $html = '<p>(empty)</p>'; // fallback aman
        }

        // Buat versi text/plain sederhana (fallback anti-spam)
        $text = $this->toPlainText($html);

        // ---- 3) Ambil attachments() (modern) ----
        $attachments = [];
        if (method_exists($mailable, 'attachments')) {
            foreach ((array) $mailable->attachments() as $att) {
                // Upayakan akses properti umum Attachment (path/content/name/contentType)
                $path        = $att->path        ?? (property_exists($att, 'path') ? $att->path : null);
                $name        = $att->name        ?? (property_exists($att, 'name') ? $att->name : null);
                $contentType = $att->contentType ?? ($att->mime ?? null) ?? 'application/octet-stream';
                $contentRaw  = $att->content     ?? (property_exists($att, 'content') ? $att->content : null);

                if ($path && is_string($path) && @is_file($path)) {
                    $attachments[] = [
                        'filename'    => $name ?: basename($path),
                        'contentType' => $contentType,
                        'data'        => @file_get_contents($path),
                    ];
                } elseif (is_string($contentRaw)) {
                    // Lampiran dari memory string
                    $attachments[] = [
                        'filename'    => $name ?: 'attachment.bin',
                        'contentType' => $contentType,
                        'data'        => $contentRaw,
                    ];
                }
                // (Jika ada tipe disk/stream khusus, bisa ditambah handler lain di sini)
            }
        }

        // ---- 4) Rakit MIME: multipart/alternative (text+html), lalu mixed jika ada lampiran ----
        $boundaryMixed = '=_Mixed_' . Str::random(24);
        $boundaryAlt   = '=_Alt_'   . Str::random(24);

        $headers = [
            "From: {$from}",
            "To: {$to}",
            "Subject: {$subject}",
            "MIME-Version: 1.0",
        ];

        // Part alternative (text + html)
        $altBody =
            "--{$boundaryAlt}\r\n" .
            "Content-Type: text/plain; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: base64\r\n\r\n" .
            chunk_split(base64_encode($text)) . "\r\n" .
            "--{$boundaryAlt}\r\n" .
            "Content-Type: text/html; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: base64\r\n\r\n" .
            chunk_split(base64_encode($html)) . "\r\n" .
            "--{$boundaryAlt}--";

        if (empty($attachments)) {
            // Tanpa lampiran → langsung alternative sebagai body
            $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundaryAlt}\"";
            $mime = implode("\r\n", $headers) . "\r\n\r\n" . $altBody;
        } else {
            // Dengan lampiran → multipart/mixed yang berisi multipart/alternative + tiap lampiran
            $headers[] = "Content-Type: multipart/mixed; boundary=\"{$boundaryMixed}\"";

            $mixedBody =
                "--{$boundaryMixed}\r\n" .
                "Content-Type: multipart/alternative; boundary=\"{$boundaryAlt}\"\r\n\r\n" .
                $altBody . "\r\n";

            foreach ($attachments as $a) {
                $filename    = $this->encodeHeaderParam($a['filename']);
                $contentType = $a['contentType'] ?: 'application/octet-stream';
                $dataB64     = chunk_split(base64_encode($a['data'] ?? ''));

                $mixedBody .=
                    "--{$boundaryMixed}\r\n" .
                    "Content-Type: {$contentType}; name=\"{$filename}\"\r\n" .
                    "Content-Transfer-Encoding: base64\r\n" .
                    "Content-Disposition: attachment; filename=\"{$filename}\"\r\n\r\n" .
                    $dataB64 . "\r\n";
            }

            $mixedBody .= "--{$boundaryMixed}--";
            $mime = implode("\r\n", $headers) . "\r\n\r\n" . $mixedBody;
        }

        // ---- 5) Base64URL & kirim via Gmail API ----
        $raw = GmailApiMailer::b64url($mime);
        return $this->gmail->sendRaw($raw);
    }

    // ====== Helpers ======

    private function renderView(string $view, array $data = []): string
    {
        return App::make('view')->make($view, $data)->render();
    }

    private function renderMarkdown(string $markdownView, array $data = []): string
    {
        /** @var Markdown $md */
        $md = App::make(Markdown::class);
        // $markdownView adalah nama view markdown (mis: 'mail.welcome'), bukan teks markdown mentah
        return $md->render($markdownView, $data);
    }

    private function normalizeHtml(string $html): string
    {
        // Bisa ditambah sanitasi ringan bila perlu; saat ini langsung dipakai
        return $html;
    }

    private function toPlainText(string $html): string
    {
        // Fallback sederhana: strip tag & decode entities → batasi panjang garis
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
        // Rapikan newline berlebih
        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\r\n|\r|\n/", "\n", $text);
        $text = trim($text);
        return $text ?: '(no text)';
    }

    private function encodeHeaderParam(string $value): string
    {
        // RFC 2047: encode UTF-8 di header param (filename)
        // Banyak client cukup aman pakai plain UTF-8, tapi kita tetap aman:
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }
}
