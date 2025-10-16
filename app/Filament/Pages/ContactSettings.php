<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ContactSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Kontak & Sosial Media';

    protected static ?string $title = 'Pengaturan Kontak & Sosial Media';

    protected static string $view = 'filament.pages.contact-settings';

    // Form data properties
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getSettingsData());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Kontak')
                    ->description('Data kontak yang akan ditampilkan di website')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextInput::make('contact_email')
                            ->label('Email Kontak')
                            ->email()
                            ->required()
                            ->maxLength(150)
                            ->placeholder('info@sekolah.com')
                            ->helperText('Email yang akan ditampilkan di website dan untuk dihubungi calon siswa')
                            ->columnSpanFull(),

                        TextInput::make('contact_whatsapp')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->placeholder('628123456789')
                            ->helperText('Format: 628xxx (tanpa spasi, tanda +, atau karakter lain)')
                            ->rule('regex:/^62[0-9]{9,13}$/'),

                        TextInput::make('contact_phone')
                            ->label('Nomor Telepon Kantor')
                            ->tel()
                            ->maxLength(30)
                            ->placeholder('(021) 12345678')
                            ->helperText('Nomor telepon kantor sekolah'),

                        Textarea::make('contact_address')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->rows(3)
                            ->placeholder('Jl. Pendidikan No. 1, Jakarta Selatan, DKI Jakarta 12345')
                            ->helperText('Alamat lengkap sekolah yang akan ditampilkan di website')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Sosial Media')
                    ->description('Link akun sosial media sekolah')
                    ->icon('heroicon-o-share')
                    ->schema([
                        TextInput::make('social_facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://facebook.com/sekolahkita')
                            ->helperText('URL lengkap halaman Facebook sekolah')
                            ->prefix('https://'),

                        TextInput::make('social_instagram_handle')
                            ->label('Instagram Handle')
                            ->maxLength(100)
                            ->placeholder('@sekolahkita')
                            ->helperText('Username Instagram (dengan atau tanpa @)')
                            ->prefix('@'),

                        TextInput::make('social_twitter_handle')
                            ->label('Twitter/X Handle')
                            ->maxLength(100)
                            ->placeholder('@sekolahkita')
                            ->helperText('Username Twitter/X (dengan atau tanpa @)')
                            ->prefix('@'),

                        TextInput::make('social_youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://youtube.com/@sekolahkita')
                            ->helperText('URL lengkap channel YouTube sekolah')
                            ->prefix('https://'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getSettingsData(): array
    {
        return [
            'contact_email' => AppSetting::get('contact_email', ''),
            'contact_whatsapp' => AppSetting::get('contact_whatsapp', ''),
            'contact_phone' => AppSetting::get('contact_phone', ''),
            'contact_address' => AppSetting::get('contact_address', ''),
            'social_facebook_url' => AppSetting::get('social_facebook_url', ''),
            'social_instagram_handle' => AppSetting::get('social_instagram_handle', ''),
            'social_twitter_handle' => AppSetting::get('social_twitter_handle', ''),
            'social_youtube_url' => AppSetting::get('social_youtube_url', ''),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Save each setting
        foreach ($data as $key => $value) {
            AppSetting::set($key, $value ?? '');
        }

        // Clear cache after save
        AppSetting::clearCache();

        Notification::make()
            ->success()
            ->title('Berhasil Disimpan')
            ->body('Pengaturan kontak dan sosial media telah diperbarui.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save')
                ->color('primary'),
        ];
    }
}
