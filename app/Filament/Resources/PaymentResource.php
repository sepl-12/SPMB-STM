<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicantResource;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
                    ->color('info')
                    ->toggleable(),
                BadgeColumn::make('payment_status_name')
                    ->label('Status')
                    ->colors([
                        'success' => ['PAID', 'paid', 'success'],
                        'danger' => ['FAILED', 'failed', 'canceled'],
                        'warning' => ['PENDING', 'pending'],
                        'gray' => ['REFUNDED', 'refunded'],
                    ])
                    ->formatStateUsing(fn (?string $state) => match(strtoupper($state ?? '')) {
                        'PAID', 'SUCCESS' => 'Lunas',
                        'PENDING' => 'Menunggu',
                        'FAILED', 'CANCELED' => 'Gagal',
                        'REFUNDED' => 'Dikembalikan',
                        default => ucfirst(strtolower((string) $state))
                    })
                    ->sortable(),
                TextColumn::make('paid_amount_total')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status_updated_datetime')
                    ->label('Terakhir Update')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_status_name')
                    ->label('Status')
                    ->options(fn () => Payment::query()
                        ->select('payment_status_name')
                        ->distinct()
                        ->orderBy('payment_status_name')
                        ->pluck('payment_status_name', 'payment_status_name')
                        ->filter()
                        ->mapWithKeys(fn ($label, $value) => [$value => ucfirst(strtolower($label))])
                        ->all()),
                SelectFilter::make('payment_method_name')
                    ->label('Metode')
                    ->options(fn () => Payment::query()
                        ->select('payment_method_name')
                        ->distinct()
                        ->orderBy('payment_method_name')
                        ->pluck('payment_method_name', 'payment_method_name')
                        ->filter()
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
                    }),
            ])
            ->bulkActions([]);
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
        return false;
    }
}
