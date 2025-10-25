<?php

namespace App\Filament\Forms\FieldSchemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class FieldDisplaySection
{
    public static function make(): Section
    {
        return Section::make('Tampilan Input')
            ->description('Tambahkan petunjuk untuk membantu user mengisi form')
            ->schema([
                TextInput::make('field_placeholder_text')
                    ->label('Placeholder')
                    ->maxLength(255)
                    ->placeholder('Contoh isi yang diharapkan')
                    ->helperText('Teks yang muncul saat input kosong'),
                Textarea::make('field_help_text')
                    ->label('Teks Bantuan')
                    ->rows(2)
                    ->placeholder('Instruksi atau penjelasan tambahan...')
                    ->helperText('Penjelasan di bawah input untuk membantu user')
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->collapsed();
    }
}
