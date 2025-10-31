<?php

namespace App\Filament\Resources\ExamCardFieldConfigResource\Pages;

use App\Filament\Resources\ExamCardFieldConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExamCardFieldConfig extends EditRecord
{
    protected static string $resource = ExamCardFieldConfigResource::class;

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

                    \Filament\Notifications\Notification::make()
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

            Actions\DeleteAction::make(),
        ];
    }
}
