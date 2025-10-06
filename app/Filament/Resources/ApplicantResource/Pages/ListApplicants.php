<?php

namespace App\Filament\Resources\ApplicantResource\Pages;

use App\Filament\Resources\ApplicantResource;
use App\Models\ExportTemplate;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListApplicants extends ListRecords
{
    protected static string $resource = ApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('quickExport')
                ->label('Export Rekap Cepat')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn () => ExportTemplate::query()->where('is_default', true)->exists())
                ->action(function () {
                    Notification::make()
                        ->title('Export default dijadwalkan')
                        ->body('Hubungkan ke layanan ekspor untuk menghasilkan file secara otomatis.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
