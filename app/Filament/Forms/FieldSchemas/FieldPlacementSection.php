<?php

namespace App\Filament\Forms\FieldSchemas;

use App\Enum\FormFieldType;
use App\Models\FormVersion;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;

class FieldPlacementSection
{
    public static function make(callable $getActiveVersion): Section
    {
        return Section::make('Penempatan & Tipe')
            ->description('Tentukan di mana dan bagaimana pertanyaan ditampilkan')
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('form_step_id')
                            ->label('Ditampilkan Pada Langkah')
                            ->options(fn () => self::stepsOptions(($getActiveVersion)()))
                            ->searchable()
                            ->required()
                            ->disabled(fn($record) => $record?->is_system_field ?? false)
                            ->helperText(fn($record) => $record?->is_system_field ? '🔒 System field tidak dapat dipindah langkah' : null),
                        Select::make('field_type')
                            ->label('Tipe Pertanyaan')
                            ->options(FormFieldType::shortOptions())
                            ->required()
                            ->disabled(fn($record) => $record?->is_system_field ?? false)
                            ->helperText(fn($record) => $record?->is_system_field ? '🔒 Tipe tidak dapat diubah' : null),
                    ]),
            ])
            ->collapsible();
    }

    protected static function stepsOptions(FormVersion $version): array
    {
        return $version->formSteps()
            ->orderBy('step_order_number')
            ->pluck('step_title', 'id')
            ->toArray();
    }

}
