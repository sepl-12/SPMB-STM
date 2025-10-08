<?php

namespace App\Filament\Resources\ApplicantResource\Pages;

use App\Filament\Resources\ApplicantResource;
use App\Models\Applicant;
use App\Models\FormField;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Nben\FilamentRecordNav\Actions\NextRecordAction;
use Nben\FilamentRecordNav\Actions\PreviousRecordAction;
use Nben\FilamentRecordNav\Concerns\WithRecordNavigation;

class ViewApplicant extends ViewRecord
{

    use WithRecordNavigation;

    protected static string $resource = ApplicantResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        $this->record->loadMissing([
            'wave',
            'latestSubmission.submissionFiles',
            'payments',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            PreviousRecordAction::make(),
            NextRecordAction::make()
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Applicant Profile')
                    ->tabs([
                        Tab::make('Ringkasan')
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Section::make('Informasi Utama')
                                    ->icon('heroicon-o-identification')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('registration_number')->label('No. Pendaftaran'),
                                                TextEntry::make('registered_datetime')->label('Tgl Daftar')->dateTime('d M Y H:i'),
                                                TextEntry::make('applicant_full_name')->label('Nama Lengkap'),
                                                TextEntry::make('chosen_major_name')->label('Jurusan'),
                                                TextEntry::make('wave.wave_name')->label('Gelombang'),
                                                TextEntry::make('payment_status')
                                                    ->label('Status Bayar')
                                                    ->badge()
                                                    ->color(fn (?string $state) => match ($state) {
                                                        'paid' => 'success',
                                                        'failed' => 'danger',
                                                        'unpaid', 'pending' => 'warning',
                                                        'refunded' => 'gray',
                                                        default => 'info',
                                                    })
                                                    ->formatStateUsing(fn (?string $state) => match ($state) {
                                                        'paid' => 'Paid',
                                                        'failed' => 'Failed',
                                                        'unpaid' => 'Unpaid',
                                                        'refunded' => 'Refunded',
                                                        default => ucfirst((string) $state),
                                                    }),
                                            ]),
                                    ]),
                                Section::make('Kontak')
                                    ->icon('heroicon-o-phone')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('applicant_phone_number')->label('Nomor Telepon'),
                                                TextEntry::make('applicant_email_address')->label('Email'),
                                            ]),
                                    ]),
                            ]),
                        Tab::make('Jawaban Form')
                            ->icon('heroicon-o-document-text')
                            ->badge(fn (Applicant $record) => count($this->getAnswersWithLabels($record)) ?: null)
                            ->schema([
                                Section::make('Ringkasan Jawaban')
                                    ->description('Semua jawaban yang telah diisi oleh pendaftar')
                                    ->headerActions([
                                        \Filament\Infolists\Components\Actions\Action::make('expand_all')
                                            ->label('Expand Semua')
                                            ->icon('heroicon-o-arrows-pointing-out')
                                            ->color('gray')
                                            ->hidden(fn (Applicant $record) => empty($this->getAnswersWithLabels($record))),
                                    ])
                                    ->schema(function (Applicant $record) {
                                        $answersWithLabels = $this->getAnswersWithLabels($record);
                                        
                                        if (empty($answersWithLabels)) {
                                            return [
                                                TextEntry::make('no_answers_message')
                                                    ->label('')
                                                    ->state('Belum ada jawaban formulir yang tersimpan.')
                                                    ->color('gray')
                                                    ->icon('heroicon-o-information-circle'),
                                            ];
                                        }

                                        $sections = [];
                                        
                                        foreach ($answersWithLabels as $label => $value) {
                                            // Determine icon based on value
                                            $icon = 'heroicon-o-document-text';
                                            $iconColor = 'gray';
                                            
                                            if (is_string($value)) {
                                                if (filter_var($value, FILTER_VALIDATE_URL)) {
                                                    $icon = 'heroicon-o-link';
                                                    $iconColor = 'info';
                                                } elseif (str_contains($value, '@')) {
                                                    $icon = 'heroicon-o-envelope';
                                                    $iconColor = 'warning';
                                                } elseif (str_contains(strtolower($label), 'foto') || str_contains(strtolower($label), 'gambar')) {
                                                    $icon = 'heroicon-o-photo';
                                                    $iconColor = 'success';
                                                } elseif (str_contains(strtolower($label), 'file') || str_contains(strtolower($label), 'dokumen')) {
                                                    $icon = 'heroicon-o-document';
                                                    $iconColor = 'primary';
                                                }
                                            }
                                            
                                            $sections[] = Section::make()
                                                ->heading(fn () => new HtmlString(
                                                    '<div class="flex items-center gap-3">' .
                                                    '<span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/20">' .
                                                    '<svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">' .
                                                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>' .
                                                    '</svg>' .
                                                    '</span>' .
                                                    '<div>' .
                                                    '<span class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Pertanyaan</span>' .
                                                    '<span class="block text-sm font-semibold text-gray-950 dark:text-white mt-0.5">' . e($label) . '</span>' .
                                                    '</div>' .
                                                    '</div>'
                                                ))
                                                ->schema([
                                                    TextEntry::make('answer_value_' . md5($label))
                                                        ->label('')
                                                        ->state($value)
                                                        ->copyable()
                                                        ->copyMessage('Tersalin!')
                                                        ->copyMessageDuration(1500)
                                                        ->placeholder('(belum diisi)')
                                                        ->icon($icon)
                                                        ->iconColor($iconColor)
                                                        ->formatStateUsing(function ($state) {
                                                            if ($state === null || $state === '') {
                                                                return null;
                                                            }
                                                            
                                                            if (is_string($state) && strlen($state) > 500) {
                                                                return substr($state, 0, 500) . '... (klik copy untuk lihat lengkap)';
                                                            }
                                                            
                                                            return $state;
                                                        })
                                                        ->color('success')
                                                        ->weight('medium')
                                                        ->size('md')
                                                        ->badge()
                                                        ->extraAttributes([
                                                            'class' => 'break-words'
                                                        ]),
                                                ])
                                                ->icon($icon)
                                                ->iconColor($iconColor)
                                                ->collapsible()
                                                ->collapsed(false)
                                                ->compact();
                                        }

                                        return $sections;
                                    })
                                    ->columnSpan('full'),
                            ]),
                        Tab::make('Pembayaran')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Section::make('Riwayat Pembayaran')
                                    ->icon('heroicon-o-clock')
                                    ->description('Histori transaksi pembayaran pendaftar')
                                    ->schema([
                                        RepeatableEntry::make('payments')
                                            ->label('')
                                            ->state(fn (Applicant $record) => $this->getPaymentsState($record)->values()->all())
                                            ->visible(fn (Applicant $record) => $this->getPaymentsState($record)->isNotEmpty())
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextEntry::make('merchant_order_code')->label('Order Code'),
                                                        TextEntry::make('payment_status_name')
                                                            ->label('Status')
                                                            ->badge()
                                                            ->color(fn (?string $state): string => match ($state) {
                                                                'PAID', 'paid', 'success' => 'success',
                                                                'FAILED', 'failed' => 'danger',
                                                                'PENDING', 'pending' => 'warning',
                                                                default => 'gray',
                                                            }),
                                                        TextEntry::make('payment_method_name')->label('Metode'),
                                                        TextEntry::make('paid_amount_total')->label('Jumlah')->money('IDR'),
                                                        TextEntry::make('status_updated_datetime')->label('Diupdate')->dateTime('d M Y H:i'),
                                                    ]),
                                            ]),
                                        TextEntry::make('no_payments_message')
                                            ->label('')
                                            ->state('Belum ada transaksi pembayaran.')
                                            ->visible(fn (Applicant $record) => $this->getPaymentsState($record)->isEmpty())
                                            ->color('gray'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    protected function getAnswersWithLabels(Applicant $record): array
    {
        $answers = $record->getLatestSubmissionAnswers();

        if ($answers === [] || $answers === null) {
            return [];
        }

        $fields = FormField::query()
            ->whereIn('field_key', array_keys($answers))
            ->where('is_archived', false)
            ->get()
            ->keyBy('field_key');

        return collect($answers)
            ->mapWithKeys(function ($value, $key) use ($fields) {
                $field = $fields[$key] ?? null;
                $label = $field?->field_label ?? $key;
                $formatted = $this->formatAnswerValueForDisplay($value, $field);

                return $formatted === null ? [] : [$label => $formatted];
            })
            ->all();
    }

    protected function formatAnswerValueForDisplay($value, ?FormField $field): mixed
    {
        // Handle null
        if ($value === null) {
            return null;
        }

        // Handle file uploads
        if (is_array($value) && isset($value[0]['url'])) {
            $files = collect($value)->map(fn($file) => $file['name'] ?? $file['url'])->join(', ');
            return $files;
        }

        // Handle array/checkbox
        if (is_array($value)) {
            return implode(', ', $value);
        }

        // Handle date fields
        if ($field && $field->field_type === 'date' && is_string($value)) {
            try {
                return \Carbon\Carbon::parse($value)->format('d M Y');
            } catch (\Throwable $e) {
                return $value;
            }
        }

        // Handle long URLs
        if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        return $value;
    }

    protected function getLatestFilesState(Applicant $record): Collection
    {
        $submission = $record->latestSubmission;

        if (! $submission) {
            return collect();
        }

        return $submission->submissionFiles
            ->sortByDesc('uploaded_datetime')
            ->map(function ($file) {
                $downloadUrl = null;

                if ($file->stored_disk_name && $file->stored_file_path) {
                    try {
                        $disk = Storage::disk($file->stored_disk_name);
                        if ($disk->exists($file->stored_file_path)) {
                            // @phpstan-ignore-next-line
                            $downloadUrl = $disk->url($file->stored_file_path);
                        }
                    } catch (\Throwable $exception) {
                        $downloadUrl = null;
                    }
                }

                return [
                    'original_file_name' => $file->original_file_name,
                    'mime_type_name' => $file->mime_type_name,
                    'file_size' => $this->formatFileSize($file->file_size_bytes),
                    'download_url' => $downloadUrl,
                ];
            });
    }

    protected function getPaymentsState(Applicant $record): Collection
    {
        return $record->payments
            ->sortByDesc('status_updated_datetime')
            ->map(fn ($payment) => [
                'merchant_order_code' => $payment->merchant_order_code,
                'payment_status_name' => $payment->payment_status_name,
                'payment_method_name' => $payment->payment_method_name,
                'paid_amount_total' => $payment->paid_amount_total,
                'status_updated_datetime' => $payment->status_updated_datetime,
            ]);
    }

    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return number_format($size, $unit === 0 ? 0 : 2) . ' ' . $units[$unit];
    }
}
