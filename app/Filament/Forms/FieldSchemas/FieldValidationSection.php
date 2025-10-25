<?php

namespace App\Filament\Forms\FieldSchemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;

class FieldValidationSection
{
    public static function make(): Section
    {
        return Section::make('Pengaturan Validasi & Export')
            ->description('Atur validasi dan pengaturan data')
            ->schema([
                Grid::make(3)
                    ->schema([
                        Toggle::make('is_required')
                            ->label('Wajib Isi')
                            ->helperText('User harus mengisi ini')
                            ->default(false)
                            ->inline(false),
                        Toggle::make('is_filterable')
                            ->label('Bisa Difilter')
                            ->helperText('Tampil di filter admin')
                            ->default(false)
                            ->inline(false),
                        Toggle::make('is_exportable')
                            ->label('Bisa Diexport')
                            ->helperText('Termasuk saat export data')
                            ->default(true)
                            ->inline(false),
                    ]),
                Toggle::make('is_archived')
                    ->label('Arsipkan Pertanyaan')
                    ->helperText(
                        fn($record) => $record?->is_system_field
                            ? '🔒 System field tidak dapat diarsipkan'
                            : 'Pertanyaan yang diarsipkan tidak akan tampil di formulir tetapi tetap tersimpan untuk referensi'
                    )
                    ->default(false)
                    ->disabled(fn($record) => $record?->is_system_field ?? false)
                    ->inline(false),
            ])
            ->collapsible()
            ->collapsed();
    }
}
