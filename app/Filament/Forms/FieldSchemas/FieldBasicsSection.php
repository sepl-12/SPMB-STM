<?php

namespace App\Filament\Forms\FieldSchemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Support\Str;

class FieldBasicsSection
{
    public static function make(callable $getActiveVersion): Section
    {
        return Section::make('Informasi Dasar')
            ->description('Atur label dan kunci unik untuk pertanyaan')
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('field_label')
                            ->label('Label Pertanyaan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Nama Lengkap')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, $record) {
                                if ($record?->is_system_field) {
                                    return;
                                }

                                if (blank($state)) {
                                    return;
                                }

                                $set('field_key', Str::slug($state, '_'));
                            })
                            ->helperText(fn($record) => $record?->is_system_field ? '✅ Label boleh diubah untuk customization' : null),
                        TextInput::make('field_key')
                            ->label('Key (ID Unik)')
                            ->required()
                            ->maxLength(100)
                            ->helperText(
                                fn($record) => $record?->is_system_field
                                    ? '🔒 LOCKED - Field ini terhubung ke applicants table'
                                    : 'Otomatis dibuat dari label. Gunakan snake_case.'
                            )
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: function (Unique $rule) use ($getActiveVersion) {
                                    return $rule->where('form_version_id', ($getActiveVersion)()->getKey());
                                },
                            )
                            ->disabled()
                            ->dehydrated()
                            ->afterStateHydrated(function (?string $state, callable $set, $record) {
                                if (filled($state) || !$record) {
                                    return;
                                }

                                $label = $record->field_label;
                                if (filled($label)) {
                                    $set('field_key', Str::slug($label, '_'));
                                }
                            })
                            ->dehydrateStateUsing(fn(?string $state) => $state ? Str::slug($state, '_') : null)
                            ->extraAttributes(['readonly' => true]),
                    ]),
            ])
            ->collapsible();
    }
}
