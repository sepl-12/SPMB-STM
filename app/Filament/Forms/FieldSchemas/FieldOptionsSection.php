<?php

namespace App\Filament\Forms\FieldSchemas;

use App\Enum\FormFieldType;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;

class FieldOptionsSection
{
    public static function make(): Section
    {
        return Section::make('Pilihan Jawaban')
            ->description('Atur pilihan untuk select/multi-select')
            ->schema([
                Placeholder::make('options_info')
                    ->label('')
                    ->content('Tambahkan minimal 1 pilihan. Label adalah yang ditampilkan, Value adalah yang disimpan di database.')
                    ->visible(fn(callable $get) => FormFieldType::tryFrom($get('field_type'))?->requiresOptions() ?? false),
                Repeater::make('field_options_json')
                    ->label('Daftar Pilihan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('label')
                                    ->label('Label (Ditampilkan)')
                                    ->required()
                                    ->placeholder('Contoh: Laki-laki'),
                                TextInput::make('value')
                                    ->label('Value (Disimpan)')
                                    ->required()
                                    ->placeholder('Contoh: L'),
                            ]),
                    ])
                    ->minItems(1)
                    ->addActionLabel('Tambah Pilihan')
                    ->reorderableWithButtons()
                    ->collapsed()
                    ->itemLabel(fn(array $state): ?string => $state['label'] ?? 'Pilihan')
                    ->visible(fn(callable $get) => FormFieldType::tryFrom($get('field_type'))?->requiresOptions() ?? false)
                    ->default([])
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->collapsed();
    }
}
