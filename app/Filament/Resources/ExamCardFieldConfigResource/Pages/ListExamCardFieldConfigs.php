<?php

namespace App\Filament\Resources\ExamCardFieldConfigResource\Pages;

use App\Filament\Resources\ExamCardFieldConfigResource;
use App\Models\ExamCardFieldConfig;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListExamCardFieldConfigs extends ListRecords
{
    protected static string $resource = ExamCardFieldConfigResource::class;

    // Tambahkan listener untuk Livewire events
    protected $listeners = ['openPreviewInNewTab' => 'handleOpenPreview'];

    public function handleOpenPreview($url)
    {
        // This will be called from Alpine.js
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview_dummy')
                ->label('Preview (Dummy)')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(route('exam-card.preview'))
                ->openUrlInNewTab()
                ->tooltip('Preview cepat dengan data dummy'),

            Actions\Action::make('preview_real')
                ->label('Preview (Data Asli)')
                ->icon('heroicon-o-photo')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\Select::make('applicant_id')
                        ->label('Pilih Peserta')
                        ->searchable()
                        ->options(function () {
                            return \App\Models\Applicant::query()
                                ->whereHas('latestSubmission')
                                ->orderBy('created_at', 'desc')
                                ->limit(100)
                                ->get()
                                ->mapWithKeys(function ($applicant) {
                                    $label = $applicant->registration_number . ' - ' . $applicant->applicant_full_name;
                                    if ($applicant->applicant_email_address) {
                                        $label .= ' (' . $applicant->applicant_email_address . ')';
                                    }
                                    return [$applicant->id => $label];
                                });
                        })
                        ->required()
                        ->helperText('Pilih peserta yang sudah submit form untuk melihat preview dengan data asli (termasuk foto)')
                        ->searchPrompt('Ketik nomor pendaftaran atau nama peserta...')
                        ->noSearchResultsMessage('Tidak ada peserta ditemukan.')
                        ->preload(),
                ])
                ->action(function (array $data) {
                    $url = route('exam-card.preview', ['applicant_id' => $data['applicant_id']]);

                    // Use JavaScript to open in new tab
                    $this->js("window.open('$url', '_blank');");

                    Notification::make()
                        ->title('Preview dibuka')
                        ->body('Kartu tes dibuka di tab baru.')
                        ->success()
                        ->send();
                })
                ->modalSubmitActionLabel('Buka Preview')
                ->modalHeading('Preview dengan Data Asli')
                ->modalDescription('Pilih peserta untuk melihat preview kartu tes dengan data dan foto asli')
                ->modalWidth('md')
                ->tooltip('Preview dengan data peserta asli (termasuk foto)'),

            Actions\Action::make('export')
                ->label('Export Konfigurasi')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $configs = ExamCardFieldConfig::orderBy('order')->get();
                    $data = $configs->toArray();

                    $filename = 'exam-card-config-' . now()->format('Y-m-d-His') . '.json';
                    $json = json_encode($data, JSON_PRETTY_PRINT);

                    return response()->streamDownload(function () use ($json) {
                        echo $json;
                    }, $filename, [
                        'Content-Type' => 'application/json',
                    ]);
                })
                ->tooltip('Download konfigurasi field dalam format JSON'),

            Actions\Action::make('reset')
                ->label('Reset ke Default')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Reset Konfigurasi ke Default')
                ->modalDescription('Apakah Anda yakin ingin mereset semua konfigurasi ke default? Ini akan menghapus semua perubahan yang telah Anda buat.')
                ->modalSubmitActionLabel('Ya, Reset')
                ->action(function () {
                    // Truncate table and reseed
                    ExamCardFieldConfig::truncate();
                    Artisan::call('db:seed', ['--class' => 'ExamCardFieldConfigSeeder']);

                    Notification::make()
                        ->title('Konfigurasi berhasil direset')
                        ->success()
                        ->body('Semua konfigurasi telah dikembalikan ke default.')
                        ->send();

                    // Refresh the page
                    return redirect()->route('filament.admin.resources.exam-card-field-configs.index');
                })
                ->tooltip('Kembalikan semua konfigurasi ke pengaturan default'),

            Actions\CreateAction::make(),
        ];
    }
}
