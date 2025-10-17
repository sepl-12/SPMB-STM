<?php

namespace App\Mail;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamCardReady extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Applicant $applicant,
        public ?string $pdfPath = null
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kartu Ujian - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.exam-card-ready',
            with: [
                'applicant' => $this->applicant,
                'registrationNumber' => $this->applicant->registration_number,
                'name' => $this->applicant->getLatestSubmissionAnswers()['name'] ?? 'Calon Peserta',
                'wave' => $this->applicant->wave,
                'examDate' => $this->applicant->wave->end_datetime?->addDays(7)->format('d F Y'),
                'examLocation' => setting('contact_address', 'SMK Muhammadiyah 1 Sangatta Utara'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            return [
                Attachment::fromPath($this->pdfPath)
                    ->as('Kartu-Ujian-' . $this->applicant->registration_number . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
