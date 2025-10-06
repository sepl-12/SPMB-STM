<?php

namespace App\Filament\Resources\ApplicantResource\Pages;

use App\Filament\Resources\ApplicantResource;
use App\Models\Applicant;
use App\Models\FormField;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tab;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ViewApplicant extends ViewRecord
{
    protected static string $resource = ApplicantResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        $this->record->loadMissing([
            'wave',
            'latestSubmission.submissionFiles',
            'payments',
        ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Applicant Profile')
                    ->tabs([
                        Tab::make('Ringkasan')
                            ->schema([
                                Section::make('Informasi Utama')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('registration_number')->label('No. Pendaftaran'),
                                                TextEntry::make('registered_datetime')->label('Tgl Daftar')->dateTime('d M Y H:i'),
                                                TextEntry::make('applicant_full_name')->label('Nama Lengkap'),
                                                TextEntry::make('chosen_major_name')->label('Jurusan'),
                                                TextEntry::make('wave.wave_name')->label('Gelombang'),
                                                TextEntry::make('payment_status')
                                                    ->label('Status Bayar')
                                                    ->badge()
                                                    ->color(fn (?string $state) => match ($state) {
                                                        'paid' => 'success',
                                                        'failed' => 'danger',
                                                        'unpaid', 'pending' => 'warning',
                                                        'refunded' => 'gray',
                                                        default => 'info',
                                                    })
                                                    ->formatStateUsing(fn (?string $state) => match ($state) {
                                                        'paid' => 'Paid',
                                                        'failed' => 'Failed',
                                                        'unpaid' => 'Unpaid',
                                                        'refunded' => 'Refunded',
                                                        default => ucfirst((string) $state),
                                                    }),
                                            ]),
                                    ]),
                                Section::make('Kontak')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('applicant_phone_number')->label('Nomor Telepon'),
                                                TextEntry::make('applicant_email_address')->label('Email'),
                                            ]),
                                    ]),
                            ]),
                        Tab::make('Jawaban Form')
                            ->schema([
                                Section::make('Ringkasan Jawaban')
                                    ->schema([
                                        KeyValueEntry::make('latest_answers')
                                            ->label('')
                                            ->state(fn (Applicant $record) => $this->getAnswersWithLabels($record))
                                            ->visible(fn (Applicant $record) => ! empty($this->getAnswersWithLabels($record))),
                                        TextEntry::make('no_answers_message')
                                            ->label('')
                                            ->state('Belum ada jawaban formulir yang tersimpan.')
                                            ->visible(fn (Applicant $record) => empty($this->getAnswersWithLabels($record)))
                                            ->color('gray'),
                                    ]),
                            ]),
                        Tab::make('Berkas')
                            ->schema([
                                Section::make('Lampiran')
                                    ->schema([
                                        RepeatableEntry::make('latest_files')
                                            ->label('')
                                            ->state(fn (Applicant $record) => $this->getLatestFilesState($record)->values()->all())
                                            ->visible(fn (Applicant $record) => $this->getLatestFilesState($record)->isNotEmpty())
                                            ->schema([
                                                Grid::make()
                                                    ->schema([
                                                        TextEntry::make('original_file_name')->label('Nama File')->wrap(),
                                                        TextEntry::make('mime_type_name')->label('Tipe')->badge()->color('gray'),
                                                        TextEntry::make('file_size')
                                                            ->label('Ukuran')
                                                            ->state(fn (array $state) => $state['file_size'])
                                                            ->color('gray'),
                                                        TextEntry::make('download_url')
                                                            ->label('Unduh')
                                                            ->url(fn (array $state) => $state['download_url'] ?? null, shouldOpenInNewTab: true)
                                                            ->badge()
                                                            ->color('primary')
                                                            ->formatStateUsing(fn () => 'Download'),
                                                    ]),
                                            ]),
                                        TextEntry::make('no_files_message')
                                            ->label('')
                                            ->state('Belum ada berkas yang diunggah.')
                                            ->visible(fn (Applicant $record) => $this->getLatestFilesState($record)->isEmpty())
                                            ->color('gray'),
                                    ]),
                            ]),
                        Tab::make('Pembayaran')
                            ->schema([
                                Section::make('Riwayat Pembayaran')
                                    ->schema([
                                        RepeatableEntry::make('payments')
                                            ->label('')
                                            ->state(fn (Applicant $record) => $this->getPaymentsState($record)->values()->all())
                                            ->visible(fn (Applicant $record) => $this->getPaymentsState($record)->isNotEmpty())
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextEntry::make('merchant_order_code')->label('Order Code'),
                                                        TextEntry::make('payment_status_name')->label('Status')->badge()->color(fn (array $state) => match ($state['payment_status_name']) {
                                                            'PAID', 'paid', 'success' => 'success',
                                                            'FAILED', 'failed' => 'danger',
                                                            'PENDING', 'pending' => 'warning',
                                                            default => 'gray',
                                                        }),
                                                        TextEntry::make('payment_method_name')->label('Metode'),
                                                        TextEntry::make('paid_amount_total')->label('Jumlah')->money('IDR'),
                                                        TextEntry::make('status_updated_datetime')->label('Diupdate')->dateTime('d M Y H:i'),
                                                    ]),
                                            ]),
                                        TextEntry::make('no_payments_message')
                                            ->label('')
                                            ->state('Belum ada transaksi pembayaran.')
                                            ->visible(fn (Applicant $record) => $this->getPaymentsState($record)->isEmpty())
                                            ->color('gray'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    protected function getAnswersWithLabels(Applicant $record): array
    {
        $answers = $record->getLatestSubmissionAnswers();

        if ($answers === [] || $answers === null) {
            return [];
        }

        $fields = FormField::query()
            ->whereIn('field_key', array_keys($answers))
            ->where('is_archived', false)
            ->pluck('field_label', 'field_key');

        return collect($answers)
            ->mapWithKeys(function ($value, $key) use ($fields) {
                $label = $fields[$key] ?? $key;
                $formatted = ApplicantResource::formatAnswerValue($value);

                return $formatted === null ? [] : [$label => $formatted];
            })
            ->all();
    }

    protected function getLatestFilesState(Applicant $record): Collection
    {
        $submission = $record->latestSubmission;

        if (! $submission) {
            return collect();
        }

        return $submission->submissionFiles
            ->sortByDesc('uploaded_datetime')
            ->map(function ($file) {
                $downloadUrl = null;

                if ($file->stored_disk_name && $file->stored_file_path) {
                    try {
                        $downloadUrl = Storage::disk($file->stored_disk_name)->url($file->stored_file_path);
                    } catch (\Throwable $exception) {
                        $downloadUrl = null;
                    }
                }

                return [
                    'original_file_name' => $file->original_file_name,
                    'mime_type_name' => $file->mime_type_name,
                    'file_size' => $this->formatFileSize($file->file_size_bytes),
                    'download_url' => $downloadUrl,
                ];
            });
    }

    protected function getPaymentsState(Applicant $record): Collection
    {
        return $record->payments
            ->sortByDesc('status_updated_datetime')
            ->map(fn ($payment) => [
                'merchant_order_code' => $payment->merchant_order_code,
                'payment_status_name' => $payment->payment_status_name,
                'payment_method_name' => $payment->payment_method_name,
                'paid_amount_total' => $payment->paid_amount_total,
                'status_updated_datetime' => $payment->status_updated_datetime,
            ]);
    }

    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return number_format($size, $unit === 0 ? 0 : 2) . ' ' . $units[$unit];
    }
}
