<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\ApplicantResource;
use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reconcile')
                ->label('Rekonsiliasi Status')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->getRecord();

                    Notification::make()
                        ->title('Rekonsiliasi dijadwalkan')
                        ->body('Integrasikan webhook/gateway untuk memperbarui status pembayaran ' . $record->merchant_order_code . '.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function mount($record): void
    {
        parent::mount($record);

        $this->record->loadMissing(['applicant']);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detail Pembayaran')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('merchant_order_code')->label('Order Code')->copyable(),
                                TextEntry::make('payment_status_name')->label('Status')->badge()->color(fn (?string $state) => match (strtolower((string) $state)) {
                                    'paid', 'success' => 'success',
                                    'failed', 'canceled' => 'danger',
                                    'pending' => 'warning',
                                    'refunded' => 'gray',
                                    default => 'info',
                                })->formatStateUsing(fn (?string $state) => ucfirst(strtolower((string) $state))),
                                TextEntry::make('payment_method_name')->label('Metode')->badge()->color('info'),
                                TextEntry::make('payment_gateway_name')->label('Gateway')->badge()->color('primary'),
                                TextEntry::make('paid_amount_total')->label('Nominal')->money('IDR'),
                                TextEntry::make('status_updated_datetime')->label('Diupdate')->dateTime('d M Y H:i'),
                            ]),
                    ]),
                Section::make('Calon Siswa')
                    ->schema([
                        TextEntry::make('applicant.applicant_full_name')
                            ->label('Nama')
                            ->url(fn () => ApplicantResource::getUrl('view', ['record' => $this->record->applicant_id]), shouldOpenInNewTab: true),
                        TextEntry::make('applicant.registration_number')->label('No. Pendaftaran'),
                        TextEntry::make('applicant.chosen_major_name')->label('Jurusan'),
                    ])
                    ->collapsible(),
                Section::make('Payload Gateway')
                    ->schema([
                        TextEntry::make('gateway_payload_display')
                            ->label('Data Gateway')
                            ->getStateUsing(function ($record) {
                                if (empty($record->gateway_payload_json)) {
                                    return null;
                                }
                                return json_encode($record->gateway_payload_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                            })
                            ->formatStateUsing(fn (?string $state): string => $state ?? 'Tidak ada data')
                            ->extraAttributes(['class' => 'font-mono text-xs'])
                            ->html()
                            ->formatStateUsing(function (?string $state): string {
                                if (!$state || $state === 'Tidak ada data') {
                                    return '<span class="text-gray-500">Tidak ada data</span>';
                                }
                                return '<pre class="bg-black p-4 rounded-lg overflow-x-auto text-xs">' . htmlspecialchars($state) . '</pre>';
                            })
                            ->visible(fn () => !empty($this->record->gateway_payload_json)),
                        TextEntry::make('no_payload')
                            ->label('')
                            ->state('Belum ada payload yang tersimpan.')
                            ->color('gray')
                            ->visible(fn () => empty($this->record->gateway_payload_json)),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
