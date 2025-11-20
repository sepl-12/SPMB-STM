<?php

namespace App\Filament\Resources;

use App\Enum\PaymentStatus;
use App\Enum\PaymentMethod;
use App\Filament\Resources\ApplicantResource;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'PPDB';

    protected static ?string $navigationLabel = 'Pembayaran';

    protected static ?string $slug = 'payments';
    
    protected static ?string $modelLabel = 'Pembayaran';
    
    protected static ?string $pluralModelLabel = 'Pembayaran';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('applicant'))
            ->defaultSort('status_updated_datetime', 'desc')
            ->deferLoading(true)
            ->columns([
                TextColumn::make('merchant_order_code')
                    ->label('Kode Order')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('applicant.applicant_full_name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Payment $record) => $record->applicant?->registration_number)
                    ->url(fn (Payment $record) => $record->applicant_id ? ApplicantResource::getUrl('view', ['record' => $record->applicant_id]) : null, shouldOpenInNewTab: true),
                TextColumn::make('payment_method_name')
                    ->label('Metode Bayar')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? ucfirst($state))
                    ->color('info')
                    ->toggleable(),
                BadgeColumn::make('payment_status_name')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state instanceof PaymentStatus ? $state->label() : ucfirst($state))
                    ->color(fn ($state): string => $state instanceof PaymentStatus ? $state->color() : 'gray')
                    ->sortable(),
                TextColumn::make('paid_amount_total')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status_updated_datetime')
                    ->label('Terakhir Update')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->label('Dihapus Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('-')
                    ->badge()
                    ->color('danger'),
                TextColumn::make('deletedBy.name')
                    ->label('Dihapus Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
                TextColumn::make('deletion_reason')
                    ->label('Alasan Hapus')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record?->deletion_reason)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Status Penghapusan')
                    ->placeholder('Hanya Aktif')
                    ->trueLabel('Hanya Dihapus')
                    ->falseLabel('Hanya Aktif')
                    ->default(false),
                SelectFilter::make('payment_status_name')
                    ->label('Status')
                    ->options(fn () => collect(PaymentStatus::cases())
                        ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
                        ->all()),
                SelectFilter::make('payment_method_name')
                    ->label('Metode')
                    ->options(fn () => collect(PaymentMethod::cases())
                        ->mapWithKeys(fn ($method) => [$method->value => $method->label()])
                        ->all()),
                Filter::make('status_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('status_updated_datetime', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('status_updated_datetime', '<=', $date));
                    })
                    ->indicateUsing(fn (array $data) => match (true) {
                        filled($data['from'] ?? null) && filled($data['until'] ?? null) => 'Diupdate: ' . $data['from'] . ' → ' . $data['until'],
                        filled($data['from'] ?? null) => 'Diupdate ≥ ' . $data['from'],
                        filled($data['until'] ?? null) => 'Diupdate ≤ ' . $data['until'],
                        default => null,
                    }),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('reconcile')
                    ->label('Rekonsiliasi Status')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (Payment $record) {
                        Notification::make()
                            ->title('Rekonsiliasi dijadwalkan')
                            ->body('Integrasikan webhook/gateway untuk memperbarui status pembayaran ' . $record->merchant_order_code . '.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Payment $record) => $record->deleted_at === null),

                DeleteAction::make()
                    ->label('Hapus')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Pembayaran')
                    ->modalDescription('Data akan di-soft delete dan bisa di-restore. Payment yang sudah settlement tidak dapat dihapus.')
                    ->form([
                        Forms\Components\Textarea::make('deletion_reason')
                            ->label('Alasan Penghapusan')
                            ->required()
                            ->placeholder('Jelaskan mengapa payment ini dihapus...')
                            ->rows(3)
                            ->helperText('Alasan ini akan tercatat dalam audit log'),
                    ])
                    ->before(function (Payment $record, array $data) {
                        // Validasi menggunakan service
                        $service = app(\App\Services\Payment\PaymentDeletionService::class);
                        $validation = $service->canDelete($record);

                        if (!$validation['can_delete']) {
                            Notification::make()
                                ->danger()
                                ->title('Tidak Dapat Menghapus')
                                ->body(implode("\n", $validation['errors']))
                                ->persistent()
                                ->send();

                            // Cancel deletion
                            throw new \Filament\Support\Exceptions\Halt();
                        }

                        // Set metadata
                        $record->deleted_by = auth()->id();
                        $record->deletion_reason = $data['deletion_reason'];
                        $record->save();
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Payment Dihapus')
                            ->body('Data pembayaran berhasil dihapus (soft delete)')
                    )
                    ->visible(fn (Payment $record) => $record->deleted_at === null),

                RestoreAction::make()
                    ->label('Pulihkan')
                    ->color('success')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->modalHeading('Pulihkan Payment')
                    ->modalDescription('Payment akan dikembalikan ke status aktif')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Payment Dipulihkan')
                            ->body('Data pembayaran berhasil dipulihkan')
                    )
                    ->visible(fn (Payment $record) => $record->deleted_at !== null),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('delete_bulk')
                    ->label('Hapus Terpilih')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Multiple Payments')
                    ->modalDescription('Payment yang sudah settlement akan dilewati secara otomatis')
                    ->form([
                        Forms\Components\Textarea::make('deletion_reason')
                            ->label('Alasan Penghapusan Massal')
                            ->required()
                            ->placeholder('Jelaskan mengapa payment-payment ini dihapus...')
                            ->rows(3),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $service = app(\App\Services\Payment\PaymentDeletionService::class);
                        $deleted = 0;
                        $failed = 0;
                        $errors = [];

                        foreach ($records as $payment) {
                            try {
                                $service->delete($payment, $data['deletion_reason']);
                                $deleted++;
                            } catch (\Exception $e) {
                                $failed++;
                                $errors[] = "{$payment->merchant_order_code}: {$e->getMessage()}";
                            }
                        }

                        $message = "Berhasil hapus {$deleted} payment";
                        if ($failed > 0) {
                            $message .= ", Gagal: {$failed}";
                        }

                        Notification::make()
                            ->success()
                            ->title($message)
                            ->body($failed > 0 ? implode("\n", array_slice($errors, 0, 3)) : null)
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\RestoreBulkAction::make()
                    ->label('Pulihkan Terpilih')
                    ->successNotificationTitle('Payment berhasil dipulihkan')
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        // Gunakan policy untuk authorization
        return auth()->user()->can('delete', $record);
    }

    public static function getEloquentQuery(): Builder
    {
        // Tampilkan juga yang sudah di-soft delete
        return parent::getEloquentQuery()->withTrashed();
    }
}
