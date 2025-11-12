<?php

namespace App\Filament\Resources;

use App\Enum\PaymentStatus;
use App\Filament\Resources\ManualPaymentResource\Pages;
use App\Mail\ManualPaymentApproved;
use App\Mail\ManualPaymentRejected;
use App\Models\ManualPayment;
use App\Services\GmailMailableSender;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ManualPaymentResource extends Resource
{
    protected static ?string $model = ManualPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?string $navigationLabel = 'Approval Pembayaran Manual';

    protected static ?string $modelLabel = 'Pembayaran Manual';

    protected static ?string $pluralModelLabel = 'Pembayaran Manual';

    protected static ?string $navigationGroup = 'Pembayaran';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pendaftar')
                    ->schema([
                        Forms\Components\TextInput::make('applicant.registration_number')
                            ->label('Nomor Pendaftaran')
                            ->disabled(),
                        Forms\Components\TextInput::make('applicant.applicant_full_name')
                            ->label('Nama Lengkap')
                            ->disabled(),
                        Forms\Components\TextInput::make('applicant.wave.wave_name')
                            ->label('Gelombang')
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Detail Pembayaran')
                    ->schema([
                        Forms\Components\TextInput::make('paid_amount')
                            ->label('Jumlah Dibayar')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('payment.paid_amount_total')
                            ->label('Jumlah yang Harus Dibayar')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('upload_datetime')
                            ->label('Waktu Upload')
                            ->disabled(),
                        Forms\Components\Textarea::make('payment_notes')
                            ->label('Catatan dari User')
                            ->rows(2)
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Bukti Pembayaran')
                    ->schema([
                        Forms\Components\FileUpload::make('proof_image_path')
                            ->label('Bukti Transfer')
                            ->image()
                            ->imagePreviewHeight('400')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Status Approval')
                    ->schema([
                        Forms\Components\Select::make('approval_status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu Verifikasi',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->disabled(),
                        Forms\Components\TextInput::make('approvedBy.name')
                            ->label('Disetujui/Ditolak Oleh')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Tanggal Approval')
                            ->disabled(),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn($record) => $record?->isRejected()),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('applicant.registration_number')
                    ->label('No. Pendaftaran')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('applicant.applicant_full_name')
                    ->label('Nama Pendaftar')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\ImageColumn::make('proof_image_path')
                    ->label('Bukti')
                    ->getStateUsing(fn($record) => $record->getProofImageUrl())
                    ->size(60),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('upload_datetime')
                    ->label('Waktu Upload')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('approval_status')
                    ->label('Status')
                    ->formatStateUsing(fn($record) => $record->getStatusLabel())
                    ->color(fn($record) => $record->getStatusBadgeColor())
                    ->sortable(),

                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->label('Disetujui Oleh')
                    ->default('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Tanggal Approval')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('upload_datetime', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('approval_status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Verifikasi',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\Filter::make('created_today')
                    ->label('Upload Hari Ini')
                    ->query(fn(Builder $query) => $query->whereDate('upload_datetime', today())),

                Tables\Filters\Filter::make('created_this_week')
                    ->label('Upload Minggu Ini')
                    ->query(fn(Builder $query) => $query->whereBetween('upload_datetime', [now()->startOfWeek(), now()->endOfWeek()])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail'),

                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pembayaran')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui pembayaran ini?')
                    ->action(function (ManualPayment $record) {
                        static::approvePayment($record);
                    })
                    ->visible(fn(ManualPayment $record) => $record->isPending()),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->minLength(10)
                            ->maxLength(500)
                            ->placeholder('Contoh: Jumlah pembayaran tidak sesuai, bukti tidak jelas, dll.')
                            ->rows(4),
                    ])
                    ->action(function (ManualPayment $record, array $data) {
                        static::rejectPayment($record, $data['rejection_reason']);
                    })
                    ->visible(fn(ManualPayment $record) => $record->isPending()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Setujui yang Dipilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->isPending()) {
                                    static::approvePayment($record);
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManualPayments::route('/'),
            'view' => Pages\ViewManualPayment::route('/{record}'),
        ];
    }

    /**
     * Approve manual payment
     */
    protected static function approvePayment(ManualPayment $record): void
    {
        try {
            \DB::transaction(function () use ($record) {
                // Update manual payment status
                $record->update([
                    'approval_status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                // Update payment status to settlement
                $record->payment->update([
                    'payment_status_name' => PaymentStatus::SETTLEMENT,
                    'status_updated_datetime' => now(),
                ]);

                Log::info('Manual payment approved', [
                    'manual_payment_id' => $record->id,
                    'payment_id' => $record->payment_id,
                    'approved_by' => auth()->id(),
                ]);
            });

            // Send email notification to applicant via Gmail API
            try {
                $applicantEmail = $record->applicant->applicant_email_address;
                if ($applicantEmail) {
                    app(GmailMailableSender::class)->send(
                        $applicantEmail,
                        new ManualPaymentApproved($record)
                    );
                    Log::info('Manual payment approval email sent', [
                        'manual_payment_id' => $record->id,
                        'email' => $applicantEmail,
                    ]);
                }
            } catch (\Exception $emailError) {
                Log::warning('Failed to send approval email', [
                    'manual_payment_id' => $record->id,
                    'error' => $emailError->getMessage(),
                ]);
            }

            Notification::make()
                ->success()
                ->title('Pembayaran Disetujui')
                ->body("Pembayaran dari {$record->applicant->applicant_full_name} telah disetujui.")
                ->send();
        } catch (\Exception $e) {
            Log::error('Manual payment approval failed', [
                'manual_payment_id' => $record->id,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->danger()
                ->title('Gagal Menyetujui')
                ->body('Terjadi kesalahan saat menyetujui pembayaran.')
                ->send();
        }
    }

    /**
     * Reject manual payment
     */
    protected static function rejectPayment(ManualPayment $record, string $reason): void
    {
        try {
            $record->update([
                'approval_status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            Log::info('Manual payment rejected', [
                'manual_payment_id' => $record->id,
                'payment_id' => $record->payment_id,
                'rejected_by' => auth()->id(),
                'reason' => $reason,
            ]);

            // Send email notification to applicant via Gmail API
            try {
                $applicantEmail = $record->applicant->applicant_email_address;
                if ($applicantEmail) {
                    app(GmailMailableSender::class)->send(
                        $applicantEmail,
                        new ManualPaymentRejected($record)
                    );
                    Log::info('Manual payment rejection email sent', [
                        'manual_payment_id' => $record->id,
                        'email' => $applicantEmail,
                    ]);
                }
            } catch (\Exception $emailError) {
                Log::warning('Failed to send rejection email', [
                    'manual_payment_id' => $record->id,
                    'error' => $emailError->getMessage(),
                ]);
            }

            Notification::make()
                ->warning()
                ->title('Pembayaran Ditolak')
                ->body("Pembayaran dari {$record->applicant->applicant_full_name} telah ditolak.")
                ->send();
        } catch (\Exception $e) {
            Log::error('Manual payment rejection failed', [
                'manual_payment_id' => $record->id,
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
