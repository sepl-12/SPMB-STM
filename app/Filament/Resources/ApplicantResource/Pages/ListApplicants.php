<?php

namespace App\Filament\Resources\ApplicantResource\Pages;

use App\Exports\ApplicantsExport;
use App\Filament\Resources\ApplicantResource;
use App\Models\Applicant;
use App\Models\ExportTemplate;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

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
                    $template = ExportTemplate::query()
                        ->where('is_default', true)
                        ->first();

                    if (!$template) {
                        Notification::make()
                            ->title('Template default tidak ditemukan')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Get all applicants (or apply filters if needed)
                    $applicants = Applicant::query()
                        ->with(['wave', 'answers'])
                        ->get();

                    if ($applicants->isEmpty()) {
                        Notification::make()
                            ->title('Tidak ada data untuk diekspor')
                            ->warning()
                            ->send();
                        return;
                    }

                    $filename = 'rekap_pendaftar_' . now()->format('YmdHis') . '.xlsx';

                    try {
                        return Excel::download(
                            new ApplicantsExport($template, $applicants),
                            $filename
                        );
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Ekspor gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
