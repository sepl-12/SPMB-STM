<?php

namespace App\Mail;

use App\Models\ManualPayment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ManualPaymentApproved extends Mailable
{

    /**
     * Create a new message instance.
     */
    public function __construct(
        public ManualPayment $manualPayment
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pembayaran Manual Disetujui - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.manual-payment-approved',
            with: [
                'manualPayment' => $this->manualPayment,
                'applicant' => $this->manualPayment->applicant,
                'registrationNumber' => $this->manualPayment->applicant->registration_number,
                'name' => $this->manualPayment->applicant->applicant_full_name,
                'amount' => number_format($this->manualPayment->paid_amount, 0, ',', '.'),
                'approvalDate' => $this->manualPayment->approved_at?->format('d F Y, H:i'),
                'approvedBy' => $this->manualPayment->approvedBy?->name ?? 'Admin',
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
        return [];
    }
}
