<?php

namespace App\Filament\Forms\FieldSchemas;

use App\Enum\FormFieldType;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class FieldOptionsSection
{
    public static function make(): Section
    {
        return Section::make('Pilihan Jawaban')
            ->description('Atur pilihan untuk select/multi-select')
            ->schema([
                Placeholder::make('options_info')
                    ->label('')
                    ->content('Tambahkan minimal 1 pilihan. Masukkan label/teks yang ditampilkan, nilai penyimpanan akan dibuat otomatis.')
                    ->visible(fn(callable $get) => FormFieldType::tryFrom($get('field_type'))?->requiresOptions() ?? false),
                Repeater::make('field_options_json')
                    ->label('Daftar Pilihan')
                    ->schema([
                        TextInput::make('label')
                            ->label('Data Opsi')
                            ->required()
                            ->placeholder('Contoh: Laki-laki'),
                    ])
                    ->minItems(1)
                    ->addActionLabel('Tambah Pilihan')
                    ->reorderableWithButtons()
                    ->collapsed()
                    ->itemLabel(fn(array $state): ?string => $state['label'] ?? 'Pilihan')
                    ->visible(fn(callable $get) => FormFieldType::tryFrom($get('field_type'))?->requiresOptions() ?? false)
                    ->default([])
                    ->columnSpanFull()
                    ->mutateDehydratedStateUsing(fn(?array $state): array => static::normalizeOptions($state)),
            ])
            ->collapsible()
            ->collapsed();
    }

    /**
     * @param array<int, array<string, mixed>>|null $state
     * @return array<int, array{label: string, value: string}>
     */
    private static function normalizeOptions(?array $state): array
    {
        if (! is_array($state)) {
            return [];
        }

        $normalized = [];
        $usedValues = [];

        foreach ($state as $item) {
            $label = trim((string) ($item['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $value = $item['value'] ?? null;
            $value = is_string($value) ? trim($value) : ($value === null ? '' : (string) $value);

            if ($value === '') {
                $value = static::generateOptionValue($label);
            }

            $value = static::ensureUniqueValue($value, $usedValues, $label);
            $usedValues[] = $value;

            $normalized[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $normalized;
    }

    /**
     * @param array<int, string> $usedValues
     */
    private static function ensureUniqueValue(string $value, array $usedValues, string $label): string
    {
        $base = $value !== '' ? $value : static::generateOptionValue($label);
        $candidate = $base;
        $suffix = 2;

        while (in_array($candidate, $usedValues, true)) {
            $candidate = "{$base}_{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    private static function generateOptionValue(string $label): string
    {
        $slug = Str::slug($label, '_');

        return $slug !== '' ? $slug : 'option';
    }
}
