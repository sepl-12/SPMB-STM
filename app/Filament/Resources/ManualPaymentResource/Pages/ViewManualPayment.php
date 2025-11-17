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
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ViewManualPayment extends ViewRecord
{
    protected static string $resource = ManualPaymentResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Split::make([
                    // Bagian Kiri: Bukti Pembayaran (Gambar Besar)
                    Components\Section::make('Bukti Pembayaran')
                        ->schema([
                            Components\ViewEntry::make('proof_image_path')
                                ->label('')
                                ->view('filament.infolists.entries.payment-proof-image')
                                ->columnSpanFull(),

                            Components\Actions::make([
                                Components\Actions\Action::make('view_fullsize')
                                    ->label('Lihat Ukuran Penuh')
                                    ->icon('heroicon-o-magnifying-glass-plus')
                                    ->color('primary')
                                    ->url(fn ($record) => $record->getProofImageUrl())
                                    ->openUrlInNewTab(),
                            ])->fullWidth(),

                            Components\TextEntry::make('upload_datetime')
                                ->label('Waktu Upload')
                                ->icon('heroicon-o-clock')
                                ->dateTime('d F Y, H:i')
                                ->badge()
                                ->color('info'),
                        ])
                        ->columnSpan(['lg' => 2]),

                    // Bagian Kanan: Informasi Detail
                    Components\Group::make()
                        ->schema([
                            // Section: Informasi Pendaftar
                            Components\Section::make('Informasi Pendaftar')
                                ->icon('heroicon-o-user')
                                ->schema([
                                    Components\TextEntry::make('applicant.registration_number')
                                        ->label('No. Pendaftaran')
                                        ->icon('heroicon-o-hashtag')
                                        ->weight(FontWeight::Bold)
                                        ->copyable()
                                        ->badge()
                                        ->color('primary'),

                                    Components\TextEntry::make('applicant.applicant_full_name')
                                        ->label('Nama Lengkap')
                                        ->icon('heroicon-o-user-circle')
                                        ->weight(FontWeight::SemiBold)
                                        ->size('lg'),

                                    Components\TextEntry::make('applicant.applicant_email_address')
                                        ->label('Email')
                                        ->icon('heroicon-o-envelope')
                                        ->copyable(),

                                    Components\TextEntry::make('applicant.wave.wave_name')
                                        ->label('Gelombang')
                                        ->icon('heroicon-o-calendar-days')
                                        ->badge()
                                        ->color('warning'),
                                ]),

                            // Section: Detail Pembayaran
                            Components\Section::make('Detail Pembayaran')
                                ->icon('heroicon-o-banknotes')
                                ->schema([
                                    Components\TextEntry::make('paid_amount')
                                        ->label('Jumlah Dibayar')
                                        ->money('IDR')
                                        ->icon('heroicon-o-currency-dollar')
                                        ->weight(FontWeight::Bold)
                                        ->size('lg')
                                        ->color('success'),

                                    Components\TextEntry::make('payment.paid_amount_total')
                                        ->label('Yang Harus Dibayar')
                                        ->money('IDR')
                                        ->icon('heroicon-o-receipt-percent'),

                                    Components\TextEntry::make('payment_notes')
                                        ->label('Catatan dari User')
                                        ->icon('heroicon-o-chat-bubble-left-right')
                                        ->placeholder('Tidak ada catatan')
                                        ->columnSpanFull(),
                                ]),

                            // Section: Status Approval
                            Components\Section::make('Status Approval')
                                ->icon('heroicon-o-shield-check')
                                ->schema([
                                    Components\TextEntry::make('approval_status')
                                        ->label('Status')
                                        ->badge()
                                        ->formatStateUsing(fn ($record) => $record->getStatusLabel())
                                        ->color(fn ($record) => $record->getStatusBadgeColor())
                                        ->size('lg')
                                        ->weight(FontWeight::Bold),

                                    Components\TextEntry::make('approvedBy.name')
                                        ->label('Disetujui/Ditolak Oleh')
                                        ->icon('heroicon-o-user')
                                        ->placeholder('-')
                                        ->visible(fn ($record) => $record->approved_by !== null),

                                    Components\TextEntry::make('approved_at')
                                        ->label('Tanggal Approval')
                                        ->icon('heroicon-o-clock')
                                        ->dateTime('d F Y, H:i')
                                        ->visible(fn ($record) => $record->approved_at !== null),

                                    Components\TextEntry::make('rejection_reason')
                                        ->label('Alasan Penolakan')
                                        ->icon('heroicon-o-exclamation-circle')
                                        ->columnSpanFull()
                                        ->color('danger')
                                        ->visible(fn ($record) => $record->isRejected()),
                                ]),
                        ])
                        ->columnSpan(['lg' => 1]),
                ])
                ->from('lg')
                ->columnSpanFull(),
            ]);
    }

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
