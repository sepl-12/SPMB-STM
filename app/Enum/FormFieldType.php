<?php

namespace App\Enum;

enum FormFieldType: string
{
    case TEXT = 'text';
    case EMAIL = 'email';
    case TEXTAREA = 'textarea';
    case NUMBER = 'number';
    case SELECT = 'select';
    case RADIO = 'radio';
    case MULTI_SELECT = 'multi_select';
    case DATE = 'date';
    case FILE = 'file';
    case IMAGE = 'image';
    case BOOLEAN = 'boolean';
    case SIGNATURE = 'signature';

    /**
     * Get display label for the field type
     */
    public function label(): string
    {
        return match ($this) {
            self::TEXT => '📝 Teks - Input teks pendek',
            self::EMAIL => '📧 Email - Input email dengan validasi',
            self::TEXTAREA => '📄 Textarea - Input teks panjang',
            self::NUMBER => '🔢 Angka - Input numerik',
            self::SELECT => '📋 Select - Pilihan tunggal',
            self::RADIO => '🔘 Radio - Pilihan tunggal (radio button)',
            self::MULTI_SELECT => '☑️ Multi Select - Pilihan ganda',
            self::DATE => '📅 Tanggal - Pemilih tanggal',
            self::FILE => '📎 File - Upload file',
            self::IMAGE => '🖼️ Gambar - Upload gambar',
            self::BOOLEAN => '✅ Ya/Tidak - Toggle on/off',
            self::SIGNATURE => '✍️ Tanda Tangan - Coret tanda tangan digital',
        };
    }

    /**
     * Get short display name for the field type
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::TEXT => 'Teks',
            self::EMAIL => 'Email',
            self::TEXTAREA => 'Textarea',
            self::NUMBER => 'Angka',
            self::SELECT => 'Select',
            self::RADIO => 'Radio',
            self::MULTI_SELECT => 'Multi Select',
            self::DATE => 'Tanggal',
            self::FILE => 'File',
            self::IMAGE => 'Gambar',
            self::BOOLEAN => 'Ya/Tidak',
            self::SIGNATURE => 'Tanda Tangan',
        };
    }

    /**
     * Get badge color for the field type
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::TEXT, self::EMAIL, self::TEXTAREA, self::NUMBER => 'primary',
            self::SELECT, self::RADIO, self::MULTI_SELECT => 'warning',
            self::DATE => 'success',
            self::FILE, self::IMAGE => 'danger',
            self::BOOLEAN => 'info',
            self::SIGNATURE => 'secondary',
        };
    }

    /**
     * Check if field type requires options
     */
    public function requiresOptions(): bool
    {
        return in_array($this, [self::SELECT, self::RADIO, self::MULTI_SELECT]);
    }

    /**
     * Check if field type is for file upload
     */
    public function isFileUpload(): bool
    {
        return in_array($this, [self::FILE, self::IMAGE]);
    }

    public function isSignature(): bool
    {
        return $this === self::SIGNATURE;
    }

    /**
     * Get all field types as options array
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    /**
     * Get all field types as short options array
     */
    public static function shortOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->shortLabel();
        }
        return $options;
    }
}
