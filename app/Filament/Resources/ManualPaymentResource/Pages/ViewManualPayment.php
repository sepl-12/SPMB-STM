<?php

namespace App\Filament\Resources\ManualPaymentResource\Pages;

use App\Enum\PaymentStatus;
use App\Filament\Resources\ManualPaymentResource;
use App\Mail\ManualPaymentApproved;
use App\Mail\ManualPaymentRejected;
use App\Models\ManualPayment;
use App\Services\GmailMailableSender;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ViewManualPayment extends ViewRecord
{
    protected static string $resource = ManualPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Setujui Pembayaran')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Setujui Pembayaran')
                ->modalDescription('Apakah Anda yakin ingin menyetujui pembayaran ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Setujui')
                ->action(function () {
                    $this->approvePayment();
                })
                ->visible(fn() => $this->record->isPending()),

            Actions\Action::make('reject')
                ->label('Tolak Pembayaran')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->minLength(10)
                        ->maxLength(500)
                        ->placeholder('Contoh: Jumlah pembayaran tidak sesuai, bukti transfer tidak jelas, dll.')
                        ->rows(4)
                        ->helperText('Minimal 10 karakter'),
                ])
                ->action(function (array $data) {
                    $this->rejectPayment($data['rejection_reason']);
                })
                ->visible(fn() => $this->record->isPending()),

            Actions\Action::make('back')
                ->label('Kembali')
                ->url(ManualPaymentResource::getUrl('index'))
                ->color('gray'),
        ];
    }

    protected function approvePayment(): void
    {
        try {
            DB::transaction(function () {
                // Update manual payment status
                $this->record->update([
                    'approval_status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                // Update payment status to settlement
                $this->record->payment->update([
                    'payment_status_name' => PaymentStatus::SETTLEMENT,
                    'status_updated_datetime' => now(),
                ]);

                Log::info('Manual payment approved', [
                    'manual_payment_id' => $this->record->id,
                    'payment_id' => $this->record->payment_id,
                    'approved_by' => auth()->id(),
                ]);
            });

            // Send email notification to applicant via Gmail API
            try {
                $applicantEmail = $this->record->applicant->applicant_email_address;
                if ($applicantEmail) {
                    app(GmailMailableSender::class)->send(
                        $applicantEmail,
                        new ManualPaymentApproved($this->record)
                    );
                    Log::info('Manual payment approval email sent', [
                        'manual_payment_id' => $this->record->id,
                        'email' => $applicantEmail,
                    ]);
                }
            } catch (\Exception $emailError) {
                Log::warning('Failed to send approval email', [
                    'manual_payment_id' => $this->record->id,
                    'error' => $emailError->getMessage(),
                ]);
            }

            Notification::make()
                ->success()
                ->title('Pembayaran Disetujui')
                ->body("Pembayaran dari {$this->record->applicant->applicant_full_name} telah disetujui.")
                ->send();

            // Redirect back to list
            $this->redirect(ManualPaymentResource::getUrl('index'));
        } catch (\Exception $e) {
            Log::error('Manual payment approval failed', [
                'manual_payment_id' => $this->record->id,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->danger()
                ->title('Gagal Menyetujui')
                ->body('Terjadi kesalahan saat menyetujui pembayaran.')
                ->send();
        }
    }

    protected function rejectPayment(string $reason): void
    {
        try {
            $this->record->update([
                'approval_status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            Log::info('Manual payment rejected', [
                'manual_payment_id' => $this->record->id,
                'payment_id' => $this->record->payment_id,
                'rejected_by' => auth()->id(),
                'reason' => $reason,
            ]);

            // Send email notification to applicant via Gmail API
            try {
                $applicantEmail = $this->record->applicant->applicant_email_address;
                if ($applicantEmail) {
                    app(GmailMailableSender::class)->send(
                        $applicantEmail,
                        new ManualPaymentRejected($this->record)
                    );
                    Log::info('Manual payment rejection email sent', [
                        'manual_payment_id' => $this->record->id,
                        'email' => $applicantEmail,
                    ]);
                }
            } catch (\Exception $emailError) {
                Log::warning('Failed to send rejection email', [
                    'manual_payment_id' => $this->record->id,
                    'error' => $emailError->getMessage(),
                ]);
            }

            Notification::make()
                ->warning()
                ->title('Pembayaran Ditolak')
                ->body("Pembayaran dari {$this->record->applicant->applicant_full_name} telah ditolak dengan alasan: {$reason}")
                ->send();

            // Redirect back to list
            $this->redirect(ManualPaymentResource::getUrl('index'));
        } catch (\Exception $e) {
            Log::error('Manual payment rejection failed', [
                'manual_payment_id' => $this->record->id,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->danger()
                ->title('Gagal Menolak')
                ->body('Terjadi kesalahan saat menolak pembayaran.')
                ->send();
        }
    }
}
