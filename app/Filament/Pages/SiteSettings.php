<?php

namespace App\Filament\Pages;

use App\Settings\PaymentSettings;
use App\Settings\SettingsRepositoryInterface;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Pengaturan Website';

    protected static ?string $title = 'Pengaturan Website';

    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    protected ?SettingsRepositoryInterface $settingsRepo = null;

    public function mount(): void
    {
        $this->form->fill($this->getSettingsData());
    }

    protected function settings(): SettingsRepositoryInterface
    {
        return $this->settingsRepo ??= app(SettingsRepositoryInterface::class);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Hero Section
                Section::make('Hero Halaman Utama')
                    ->description('Atur teks dan gambar hero pada halaman utama')
                    ->icon('heroicon-o-photo')
                    ->collapsible()
                    ->schema([
                        TextInput::make('hero_title')
                            ->label('Judul Hero')
                            ->required()
                            ->maxLength(120)
                            ->placeholder('Penerimaan Peserta Didik Baru Online 2025/2026')
                            ->columnSpanFull(),

                        Textarea::make('hero_subtitle')
                            ->label('Subjudul Hero')
                            ->rows(2)
                            ->placeholder('Membuka pendaftaran siswa baru...')
                            ->columnSpanFull(),

                        FileUpload::make('hero_image')
                            ->label('Gambar Hero')
                            ->image()
                            ->disk('public')
                            ->directory('hero')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->helperText('Unggah gambar dengan rasio 16:9. Max 5MB')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // CTA Section
                Section::make('Call To Action')
                    ->description('Tombol ajakan bertindak')
                    ->icon('heroicon-o-cursor-arrow-rays')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('cta_button_label')
                            ->label('Label Tombol')
                            ->maxLength(50)
                            ->placeholder('Daftar Sekarang'),
                    ])
                    ->columns(1),

                // Requirements Section
                Section::make('Syarat Pendaftaran')
                    ->description('Syarat dan ketentuan pendaftaran')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        MarkdownEditor::make('requirements_text')
                            ->label('Syarat Pendaftaran')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                                'link',
                            ])
                            ->placeholder("1. Mengisi formulir\n2. Pas foto 3x4\n3. Fotokopi ijazah"),
                    ]),

                // FAQ Section
                Section::make('FAQ')
                    ->description('Pertanyaan yang sering diajukan')
                    ->icon('heroicon-o-question-mark-circle')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('faq_items')
                            ->label('Daftar FAQ')
                            ->schema([
                                TextInput::make('question')
                                    ->label('Pertanyaan')
                                    ->required()
                                    ->maxLength(150),
                                Textarea::make('answer')
                                    ->label('Jawaban')
                                    ->rows(3)
                                    ->required(),
                            ])
                            ->collapsed()
                            ->itemLabel(fn(array $state): ?string => $state['question'] ?? null)
                            ->columnSpanFull()
                            ->defaultItems(0),
                    ]),

                // Timeline Section
                Section::make('Timeline Pendaftaran')
                    ->description('Tahapan proses pendaftaran')
                    ->icon('heroicon-o-calendar-days')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('timeline_items')
                            ->label('Tahapan')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul Tahap')
                                    ->required()
                                    ->maxLength(120)
                                    ->placeholder('Contoh: Registrasi Online'),
                                Select::make('icon')
                                    ->label('Icon')
                                    ->options([
                                        'user-plus' => '👤 Pendaftaran',
                                        'document' => '📄 Dokumen',
                                        'check-circle' => '✓ Verifikasi',
                                        'currency' => '💰 Pembayaran',
                                    ])
                                    ->default('user-plus'),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(2)
                                    ->maxLength(250),
                            ])
                            ->collapsed()
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                            ->columns(2)
                            ->columnSpanFull()
                            ->defaultItems(0),
                    ]),

                // Contact Information
                Section::make('Informasi Kontak')
                    ->description('Data kontak yang akan ditampilkan di website')
                    ->icon('heroicon-o-phone')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('contact_email')
                            ->label('Email Kontak')
                            ->email()
                            ->required()
                            ->maxLength(150)
                            ->placeholder('info@sekolah.com')
                            ->helperText('Email yang akan ditampilkan di website'),

                        TextInput::make('contact_whatsapp')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->placeholder('628123456789')
                            ->helperText('Format: 628xxx (tanpa spasi)')
                            ->rule('regex:/^62[0-9]{9,13}$/'),

                        TextInput::make('contact_phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(30)
                            ->placeholder('(021) 12345678'),

                        Textarea::make('contact_address')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->rows(3)
                            ->placeholder('Jl. Pendidikan No. 1, Jakarta')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // Exam Settings Section
                Section::make('Pengaturan Ujian')
                    ->description('Atur jadwal dan lokasi ujian masuk')
                    ->icon('heroicon-o-academic-cap')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        DatePicker::make('exam_start_date')
                            ->label('Tanggal Mulai Ujian')
                            ->required()
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->helperText('Tanggal pertama pelaksanaan ujian')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Jika end date belum diisi atau lebih kecil dari start date
                                $endDate = $get('exam_end_date');
                                if (!$endDate || $endDate < $state) {
                                    $set('exam_end_date', $state);
                                }
                            }),

                        DatePicker::make('exam_end_date')
                            ->label('Tanggal Akhir Ujian')
                            ->required()
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->helperText('Tanggal terakhir pelaksanaan ujian')
                            ->minDate(fn($get) => $get('exam_start_date'))
                            ->reactive(),

                        TextInput::make('exam_location')
                            ->label('Lokasi Ujian')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('SMK Muhammadiyah 1 Sangatta Utara - Ruang Auditorium')
                            ->helperText('Lokasi lengkap pelaksanaan ujian')
                            ->columnSpanFull(),

                        // Preview
                        \Filament\Forms\Components\Placeholder::make('exam_preview')
                            ->label('Preview')
                            ->content(function ($get) {
                                $startDate = $get('exam_start_date');
                                $endDate = $get('exam_end_date');
                                $location = $get('exam_location');

                                if (!$startDate || !$endDate) {
                                    return '⏳ Pilih tanggal untuk melihat preview';
                                }

                                $start = \Carbon\Carbon::parse($startDate);
                                $end = \Carbon\Carbon::parse($endDate);

                                // Format range
                                if ($start->format('m Y') === $end->format('m Y')) {
                                    $dateRange = $start->format('d') . ' - ' . $end->format('d F Y');
                                } elseif ($start->format('Y') === $end->format('Y')) {
                                    $dateRange = $start->format('d F') . ' - ' . $end->format('d F Y');
                                } else {
                                    $dateRange = $start->format('d F Y') . ' - ' . $end->format('d F Y');
                                }

                                $preview = "📅 Tanggal Ujian: {$dateRange}";
                                if ($location) {
                                    $preview .= "\n📍 Lokasi: {$location}";
                                }

                                return new \Illuminate\Support\HtmlString('<div style="background: #f0f9ff; border-left: 4px solid #0284c7; padding: 12px; border-radius: 4px; font-family: monospace; white-space: pre-line; color: #0c4a6e;">' . $preview . '</div>');
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // Emergency Payment Section
                Section::make('Pembayaran Darurat')
                    ->description('Mode pembayaran manual saat Midtrans bermasalah')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->collapsible()
                    ->collapsed(fn() => !PaymentSettings::isEmergencyModeEnabled())
                    ->schema([
                        Toggle::make('emergency_payment_enabled')
                            ->label('Aktifkan Mode Pembayaran Darurat')
                            ->helperText('⚠️ Jika diaktifkan, user akan melakukan pembayaran manual via QRIS dan upload bukti transfer. Pastikan Anda siap melakukan approval.')
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                if ($state) {
                                    Notification::make()
                                        ->warning()
                                        ->title('Mode Darurat Diaktifkan')
                                        ->body('User akan melakukan pembayaran manual. Pastikan Anda memantau halaman approval pembayaran.')
                                        ->send();
                                }
                            }),

                        FileUpload::make('emergency_qris_image')
                            ->label('Upload QRIS Image')
                            ->image()
                            ->imageEditor()
                            ->maxSize(2048)
                            ->directory('qris')
                            ->disk('public')
                            ->visibility('public')
                            ->helperText('Format: JPG/PNG, Max 2MB. Pastikan QRIS masih aktif dan valid.')
                            ->visible(fn($get) => $get('emergency_payment_enabled'))
                            ->columnSpanFull(),

                        TextInput::make('emergency_payment_account_name')
                            ->label('Nama Penerima QRIS')
                            ->placeholder('Yayasan Pendidikan XYZ')
                            ->helperText('Nama yang muncul saat user scan QRIS')
                            ->visible(fn($get) => $get('emergency_payment_enabled')),

                        Textarea::make('emergency_payment_instructions')
                            ->label('Instruksi Pembayaran')
                            ->rows(6)
                            ->helperText('Instruksi yang akan ditampilkan ke user')
                            ->placeholder("1. Scan QRIS di bawah\n2. Bayar sesuai jumlah\n3. Upload bukti")
                            ->visible(fn($get) => $get('emergency_payment_enabled'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // Social Media
                Section::make('Sosial Media')
                    ->description('Link akun sosial media sekolah')
                    ->icon('heroicon-o-share')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('social_facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://facebook.com/sekolahkita')
                            ->prefix('https://'),

                        TextInput::make('social_instagram_handle')
                            ->label('Instagram Handle')
                            ->maxLength(100)
                            ->placeholder('@sekolahkita')
                            ->prefix('@'),

                        TextInput::make('social_twitter_handle')
                            ->label('Twitter/X Handle')
                            ->maxLength(100)
                            ->placeholder('@sekolahkita')
                            ->prefix('@'),

                        TextInput::make('social_youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://youtube.com/@sekolahkita')
                            ->prefix('https://'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getSettingsData(): array
    {
        $repo = $this->settings();

        return [
            'hero_title' => $repo->get('hero_title', ''),
            'hero_subtitle' => $repo->get('hero_subtitle', ''),
            'hero_image' => $repo->get('hero_image', ''),
            'cta_button_label' => $repo->get('cta_button_label', ''),
            'cta_button_url' => $repo->get('cta_button_url', ''),
            'requirements_text' => $repo->get('requirements_text', ''),
            'faq_items' => json_decode($repo->get('faq_items', '[]'), true) ?: [],
            'timeline_items' => json_decode($repo->get('timeline_items', '[]'), true) ?: [],
            'contact_email' => $repo->get('contact_email', ''),
            'contact_whatsapp' => $repo->get('contact_whatsapp', ''),
            'contact_phone' => $repo->get('contact_phone', ''),
            'contact_address' => $repo->get('contact_address', ''),

            // Exam Settings
            'exam_start_date' => $repo->get('exam_start_date', ''),
            'exam_end_date' => $repo->get('exam_end_date', ''),
            'exam_location' => $repo->get('exam_location', ''),

            // Emergency Payment Settings
            'emergency_payment_enabled' => PaymentSettings::isEmergencyModeEnabled(),
            'emergency_qris_image' => PaymentSettings::getQrisImagePath(),
            'emergency_payment_account_name' => PaymentSettings::getAccountName(),
            'emergency_payment_instructions' => PaymentSettings::getEmergencyInstructions(),

            'social_facebook_url' => $repo->get('social_facebook_url', ''),
            'social_instagram_handle' => $repo->get('social_instagram_handle', ''),
            'social_twitter_handle' => $repo->get('social_twitter_handle', ''),
            'social_youtube_url' => $repo->get('social_youtube_url', ''),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $repo = $this->settings();

            // Handle Emergency Payment Settings separately
            if (isset($data['emergency_payment_enabled'])) {
                PaymentSettings::setEmergencyMode((bool) $data['emergency_payment_enabled']);
            }

            if (isset($data['emergency_qris_image']) && !empty($data['emergency_qris_image'])) {
                PaymentSettings::setQrisImagePath($data['emergency_qris_image']);
            }

            if (isset($data['emergency_payment_account_name'])) {
                $repo->set('emergency_payment_account_name', $data['emergency_payment_account_name'] ?? '');
            }

            if (isset($data['emergency_payment_instructions'])) {
                $repo->set('emergency_payment_instructions', $data['emergency_payment_instructions'] ?? '');
            }

            // Handle other settings
            foreach ($data as $key => $value) {
                // Skip emergency payment settings (already handled)
                if (in_array($key, [
                    'emergency_payment_enabled',
                    'emergency_qris_image',
                    'emergency_payment_account_name',
                    'emergency_payment_instructions'
                ])) {
                    continue;
                }

                if (in_array($key, ['faq_items', 'timeline_items'])) {
                    $repo->set($key, json_encode(array_values($value ?? [])));
                } else {
                    $repo->set($key, $value ?? '');
                }
            }

            Notification::make()
                ->success()
                ->title('Berhasil Disimpan')
                ->body('Pengaturan website telah diperbarui.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Gagal Menyimpan')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->send();
        }
    }
}
