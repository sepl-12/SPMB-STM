<?php

namespace App\Filament\Resources\ApplicantResource\Pages;

use App\Filament\Infolists\Components\FileViewerEntry;
use App\Filament\Resources\ApplicantResource;
use App\Mail\ApplicantRegistered;
use App\Mail\ExamCardReady;
use App\Mail\PaymentConfirmed;
use App\Models\Applicant;
use App\Models\FormField;
use App\Services\Email\EmailServiceInterface;
use Filament\Actions\Action as HeaderAction;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontFamily;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
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
            'latestPayment',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::make('sendEmail')
                ->label('Kirim Email')
                ->icon('heroicon-o-envelope')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Kirim Email ke Pendaftar')
                ->modalDescription('Pilih jenis email yang akan dikirim ke ' . $this->record->applicant_full_name)
                ->modalSubmitActionLabel('Kirim Email')
                ->form([
                    \Filament\Forms\Components\Select::make('email_type')
                        ->label('Jenis Email')
                        ->options([
                            'registration' => ' Email Pendaftaran Berhasil',
                            'payment' => 'Email Pembayaran Berhasil',
                            'exam_card' => ' Email Kartu Ujian',
                        ])
                        ->required()
                        ->default('registration')
                        ->native(false),

                    \Filament\Forms\Components\TextInput::make('custom_email')
                        ->label('Email Tujuan (Opsional)')
                        ->email()
                        ->placeholder($this->record->applicant_email_address)
                        ->helperText('Kosongkan untuk menggunakan email pendaftar'),
                ])
                ->action(function (array $data) {
                    $emailType = $data['email_type'];
                    $recipient = $data['custom_email'] ?? $this->record->applicant_email_address;

                    if (!$recipient || $recipient === '-') {
                        Notification::make()
                            ->danger()
                            ->title('Email Tidak Valid')
                            ->body('Pendaftar tidak memiliki email yang valid.')
                            ->send();
                        return;
                    }

                    try {
                        match ($emailType) {
                            'registration' => app(EmailServiceInterface::class)->send($recipient, new ApplicantRegistered($this->record)),
                            'payment' => app(EmailServiceInterface::class)->send($recipient, new PaymentConfirmed($this->record->latestPayment)),
                            'exam_card' => app(EmailServiceInterface::class)->send($recipient, new ExamCardReady($this->record)),
                        };

                        Notification::make()
                            ->success()
                            ->title('Email Terkirim!')
                            ->body("Email {$emailType} berhasil dikirim ke {$recipient}")
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Gagal Mengirim Email')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

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
                                                TextEntry::make('latestPayment.payment_status_name')
                                                    ->label('Status Bayar')
                                                    ->badge()
                                                    ->formatStateUsing(fn($state) => $state?->label() ?? 'Belum Bayar')
                                                    ->color(fn($state): string => $state?->color() ?? 'warning')
                                                    ->placeholder('Belum Bayar'),
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
                            ->badge(fn(Applicant $record) => $this->getFormAnswersCount($record) ?: null)
                            ->schema([
                                Section::make('Ringkasan Jawaban')
                                    ->description('Semua jawaban yang telah diisi oleh pendaftar')
                                    ->headerActions([
                                        \Filament\Infolists\Components\Actions\Action::make('expand_all')
                                            ->label('Expand Semua')
                                            ->icon('heroicon-o-arrows-pointing-out')
                                            ->color('gray')
                                            ->hidden(fn(Applicant $record) => empty($this->getFormAnswerGroups($record))),
                                    ])
                                    ->schema(function (Applicant $record) {
                                        $answerGroups = $this->getFormAnswerGroups($record);

                                        if (empty($answerGroups)) {
                                            return [
                                                TextEntry::make('no_answers_message')
                                                    ->label('')
                                                    ->state('Belum ada jawaban formulir yang tersimpan.')
                                                    ->color('gray')
                                                    ->icon('heroicon-o-information-circle'),
                                            ];
                                        }

                                        $sections = [];

                                        foreach ($answerGroups as $group) {
                                            /** @var \Illuminate\Support\Collection $fields */
                                            $fields = $group['fields'];

                                            if ($fields->isEmpty()) {
                                                continue;
                                            }

                                            $fieldSections = $fields->map(function (array $field) use ($record) {
                                                $meta = $this->getAnswerDisplayMeta($field['label'], $field['value']);
                                                $fieldKey = 'answer_value_' . md5(($field['field_key'] ?? $field['label']));

                                                // Check if field is an image type
                                                if ($field['field']?->field_type === 'image') {
                                                    $rawValue = $field['raw_value'];

                                                    // Handle different image value formats
                                                    if (is_string($rawValue) && !empty($rawValue)) {
                                                        // Single image path
                                                        return ImageEntry::make($fieldKey)
                                                            ->label($field['label'])
                                                            ->state($rawValue)
                                                            ->disk('public')
                                                            ->height(200)
                                                            ->width('auto')
                                                            ->extraAttributes([
                                                                'class' => 'rounded-lg'
                                                            ]);
                                                    } elseif (is_array($rawValue)) {
                                                        // Handle array of images (if format is [['url' => '...', 'name' => '...']])
                                                        $imageUrls = collect($rawValue)
                                                            ->map(fn($file) => is_array($file) ? ($file['url'] ?? $file['path'] ?? null) : $file)
                                                            ->filter()
                                                            ->toArray();

                                                        if (!empty($imageUrls)) {
                                                            return ImageEntry::make($fieldKey)
                                                                ->label($field['label'])
                                                                ->state($imageUrls)
                                                                ->disk('public')
                                                                ->height(200)
                                                                ->width('auto')
                                                                ->extraAttributes([
                                                                    'class' => 'rounded-lg'
                                                                ]);
                                                        }
                                                    }
                                                }

                                                // Check if field is a file/document type
                                                if ($field['field']?->field_type === 'file' && $field['field']?->id) {
                                                    $fileData = $this->getFileDataForField($record, $field['field']->id);

                                                    if ($fileData) {
                                                        return FileViewerEntry::make($fieldKey)
                                                            ->label($field['label'])
                                                            ->fileName($fileData['original_file_name'])
                                                            ->fileSize($fileData['file_size'])
                                                            ->mimeType($fileData['mime_type_name'])
                                                            ->downloadUrl($fileData['download_url'])
                                                            ->previewUrl($fileData['preview_url']);
                                                    }
                                                }

                                                // Default to TextEntry for non-image or if image not found
                                                return TextEntry::make($fieldKey)
                                                    ->label($field['label'])
                                                    ->weight('bold')
                                                    ->fontFamily(FontFamily::Mono)
                                                    ->size(TextEntrySize::Medium)
                                                    ->state($field['value'])
                                                    ->copyable()
                                                    ->copyMessage('Tersalin!')
                                                    ->copyMessageDuration(1500)
                                                    ->placeholder('(belum diisi)')
                                                    ->formatStateUsing(function ($state) {
                                                        if ($state === null || $state === '') {
                                                            return null;
                                                        }

                                                        if (is_string($state) && strlen($state) > 500) {
                                                            return substr($state, 0, 500) . '... (klik copy untuk lihat lengkap)';
                                                        }

                                                        return $state;
                                                    })
                                                    ->color('blue')
                                                    ->extraAttributes([
                                                        'class' => 'break-words'
                                                    ]);
                                            })->all();

                                            if (empty($fieldSections)) {
                                                continue;
                                            }

                                            $sections[] = Section::make($group['title'])
                                                ->description($group['description'])
                                                ->schema($fieldSections)
                                                ->icon('heroicon-o-rectangle-group')
                                                ->collapsible()
                                                ->collapsed(false)
                                                ->compact()
                                                ->columns(2);
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
                                            ->state(fn(Applicant $record) => $this->getPaymentsState($record)->values()->all())
                                            ->visible(fn(Applicant $record) => $this->getPaymentsState($record)->isNotEmpty())
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextEntry::make('merchant_order_code')->label('Order Code'),
                                                        TextEntry::make('payment_status_name')
                                                            ->label('Status')
                                                            ->badge()
                                                            ->formatStateUsing(fn($state) => $state?->label() ?? ucfirst($state))
                                                            ->color(fn($state): string => $state?->color() ?? 'gray'),
                                                        TextEntry::make('payment_method_name')->label('Metode')
                                                            ->formatStateUsing(fn($state) => $state?->label() ?? ucfirst($state)),
                                                        TextEntry::make('paid_amount_total')->label('Jumlah')->money('IDR'),
                                                        TextEntry::make('status_updated_datetime')->label('Diupdate')->dateTime('d M Y H:i'),
                                                    ]),
                                            ]),
                                        TextEntry::make('no_payments_message')
                                            ->label('')
                                            ->state('Belum ada transaksi pembayaran.')
                                            ->visible(fn(Applicant $record) => $this->getPaymentsState($record)->isEmpty())
                                            ->color('gray'),
                                    ]),
                            ]),
                    ])->columnSpan('full'),
            ]);
    }

    protected function getFormAnswersCount(Applicant $record): int
    {
        return collect($this->getFormAnswerGroups($record))
            ->sum(fn(array $group) => $group['fields']->count());
    }

    protected function getAnswersWithLabels(Applicant $record): array
    {
        return collect($this->getFormAnswerGroups($record))
            ->flatMap(function (array $group) {
                return $group['fields']->mapWithKeys(fn(array $field) => [$field['label'] => $field['value']]);
            })
            ->all();
    }

    protected function getFormAnswerGroups(Applicant $record): array
    {
        $answers = $record->getLatestSubmissionAnswers();

        if ($answers === [] || $answers === null) {
            return [];
        }

        $fields = FormField::query()
            ->with('formStep')
            ->whereIn('field_key', array_keys($answers))
            ->where('is_archived', false)
            ->get();

        $sortedFields = $fields->sortBy([
            fn(FormField $field) => $field->formStep?->step_order_number ?? PHP_INT_MAX,
            fn(FormField $field) => $field->field_order_number,
        ]);

        $groups = $sortedFields
            ->groupBy(fn(FormField $field) => $field->form_step_id ?? 'ungrouped')
            ->map(function (Collection $fields) use ($answers) {
                $step = $fields->first()->formStep;
                $items = $fields->map(function (FormField $field) use ($answers) {
                    $rawValue = $answers[$field->field_key] ?? null;
                    $formatted = $this->formatAnswerValueForDisplay($rawValue, $field);

                    if ($formatted === null || $formatted === '') {
                        return null;
                    }

                    return [
                        'label' => $field->field_label,
                        'value' => $formatted,
                        'raw_value' => $rawValue,
                        'field_key' => $field->field_key,
                        'field' => $field,
                    ];
                })->filter()->values();

                return [
                    'title' => $step?->step_title ?? 'Bagian Formulir Lainnya',
                    'description' => $step?->step_description,
                    'order' => $step?->step_order_number ?? PHP_INT_MAX,
                    'fields' => $items,
                ];
            })
            ->filter(fn(array $group) => $group['fields']->isNotEmpty())
            ->sortBy('order')
            ->values()
            ->all();

        $definedFieldKeys = $fields->pluck('field_key');

        $leftover = collect($answers)
            ->except($definedFieldKeys->all())
            ->filter(function ($value) {
                if (is_array($value)) {
                    return ! empty(array_filter($value));
                }

                return $value !== null && $value !== '';
            });

        if ($leftover->isNotEmpty()) {
            $additionalFields = $leftover
                ->map(function ($value, $key) {
                    $formatted = $this->formatAnswerValueForDisplay($value, null);

                    if ($formatted === null || $formatted === '') {
                        return null;
                    }

                    return [
                        'label' => $key,
                        'value' => $formatted,
                        'raw_value' => $value,
                        'field_key' => $key,
                        'field' => null,
                    ];
                })
                ->filter()
                ->values();

            if ($additionalFields->isNotEmpty()) {
                $groups[] = [
                    'title' => 'Jawaban Tanpa Grup',
                    'description' => 'Bidang formulir yang tidak lagi tersedia.',
                    'order' => PHP_INT_MAX,
                    'fields' => $additionalFields,
                ];
            }
        }

        return $groups;
    }

    protected function getAnswerDisplayMeta(string $label, mixed $value): array
    {
        $icon = 'heroicon-o-document-text';
        $iconColor = 'gray';

        if (is_string($value)) {
            $lowerLabel = strtolower($label);

            if (filter_var($value, FILTER_VALIDATE_URL)) {
                $icon = 'heroicon-o-link';
                $iconColor = 'info';
            } elseif (str_contains($value, '@')) {
                $icon = 'heroicon-o-envelope';
                $iconColor = 'warning';
            } elseif (str_contains($lowerLabel, 'foto') || str_contains($lowerLabel, 'gambar')) {
                $icon = 'heroicon-o-photo';
                $iconColor = 'success';
            } elseif (str_contains($lowerLabel, 'file') || str_contains($lowerLabel, 'dokumen')) {
                $icon = 'heroicon-o-document';
                $iconColor = 'primary';
            }
        }

        return [
            'icon' => $icon,
            'color' => $iconColor,
        ];
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
                            // Use signed URL with UUID for security
                            $downloadUrl = $file->getSignedDownloadUrl(24);
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
            ->map(fn($payment) => [
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

    protected function getFileDataForField(Applicant $record, int $formFieldId): ?array
    {
        $submission = $record->latestSubmission;

        if (!$submission) {
            return null;
        }

        $file = $submission->submissionFiles
            ->where('form_field_id', $formFieldId)
            ->first();

        if (!$file) {
            return null;
        }

        $downloadUrl = null;
        $previewUrl = null;

        if ($file->stored_disk_name && $file->stored_file_path) {
            try {
                $disk = Storage::disk($file->stored_disk_name);
                if ($disk->exists($file->stored_file_path)) {
                    // Use signed URLs with UUID (secure, expiring in 24 hours)
                    $downloadUrl = $file->getSignedDownloadUrl(24);
                    $previewUrl = $file->getSignedPreviewUrl(24);
                }
            } catch (\Throwable $exception) {
                $downloadUrl = null;
                $previewUrl = null;
            }
        }

        return [
            'original_file_name' => $file->original_file_name,
            'mime_type_name' => $file->mime_type_name,
            'file_size' => $this->formatFileSize($file->file_size_bytes),
            'download_url' => $downloadUrl,
            'preview_url' => $previewUrl,
        ];
    }
}
