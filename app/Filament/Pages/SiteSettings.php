<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

    public function mount(): void
    {
        $this->form->fill($this->getSettingsData());
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
        return [
            // Hero
            'hero_title' => AppSetting::get('hero_title', ''),
            'hero_subtitle' => AppSetting::get('hero_subtitle', ''),
            'hero_image' => AppSetting::get('hero_image', ''),

            // CTA
            'cta_button_label' => AppSetting::get('cta_button_label', ''),
            'cta_button_url' => AppSetting::get('cta_button_url', ''),

            // Content
            'requirements_text' => AppSetting::get('requirements_text', ''),
            'faq_items' => json_decode(AppSetting::get('faq_items', '[]'), true) ?: [],
            'timeline_items' => json_decode(AppSetting::get('timeline_items', '[]'), true) ?: [],

            // Contact
            'contact_email' => AppSetting::get('contact_email', ''),
            'contact_whatsapp' => AppSetting::get('contact_whatsapp', ''),
            'contact_phone' => AppSetting::get('contact_phone', ''),
            'contact_address' => AppSetting::get('contact_address', ''),

            // Social
            'social_facebook_url' => AppSetting::get('social_facebook_url', ''),
            'social_instagram_handle' => AppSetting::get('social_instagram_handle', ''),
            'social_twitter_handle' => AppSetting::get('social_twitter_handle', ''),
            'social_youtube_url' => AppSetting::get('social_youtube_url', ''),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            // Save each setting
            foreach ($data as $key => $value) {
                // Handle JSON fields
                if (in_array($key, ['faq_items', 'timeline_items'])) {
                    AppSetting::set($key, json_encode(array_values($value ?? [])));
                } else {
                    AppSetting::set($key, $value ?? '');
                }
            }

            // Clear cache after save
            AppSetting::clearCache();

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
