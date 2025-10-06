<?php

namespace App\Filament\Resources\FormResource\RelationManagers;

use App\Models\FormField;
use App\Models\FormVersion;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FormFieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'formFields';

    protected static ?string $title = 'Pertanyaan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('field_label')
                            ->label('Label')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('field_key')
                            ->label('Key')
                            ->required()
                            ->maxLength(100)
                            ->helperText('Gunakan snake_case untuk konsistensi data.')
                            ->unique(ignoreRecord: true, modifyQueryUsing: function (Builder $query) {
                                $query->where('form_version_id', $this->getActiveVersion()->getKey());
                            })
                            ->dehydrateStateUsing(fn (?string $state) => $state ? Str::snake($state) : null),
                        Select::make('form_step_id')
                            ->label('Langkah')
                            ->options(fn () => $this->getStepOptions())
                            ->required()
                            ->searchable(),
                        Select::make('field_type')
                            ->label('Tipe Pertanyaan')
                            ->options([
                                'text' => 'Teks',
                                'textarea' => 'Textarea',
                                'number' => 'Angka',
                                'select' => 'Select (Single)',
                                'multi_select' => 'Select (Multiple)',
                                'date' => 'Tanggal',
                                'file' => 'Berkas',
                                'image' => 'Gambar',
                                'boolean' => 'Ya/Tidak',
                            ])
                            ->required()
                            ->default('text')
                            ->live(),
                    ]),
                TextInput::make('field_placeholder_text')
                    ->label('Placeholder')
                    ->maxLength(255),
                Textarea::make('field_help_text')
                    ->label('Teks Bantuan')
                    ->rows(2)
                    ->columnSpanFull(),
                Grid::make(3)
                    ->schema([
                        Toggle::make('is_required')
                            ->label('Wajib Isi')
                            ->default(false),
                        Toggle::make('is_filterable')
                            ->label('Bisa difilter')
                            ->default(false),
                        Toggle::make('is_exportable')
                            ->label('Bisa diexport')
                            ->default(false),
                    ]),
                Toggle::make('is_archived')
                    ->label('Arsipkan pertanyaan ini?')
                    ->helperText('Pertanyaan yang diarsipkan tidak akan tampil di formulir tetapi tetap tersimpan untuk referensi.')
                    ->default(false),
                Repeater::make('field_options')
                    ->label('Pilihan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('label')
                                    ->label('Label')
                                    ->required(),
                                TextInput::make('value')
                                    ->label('Value')
                                    ->required(),
                            ]),
                    ])
                    ->minItems(1)
                    ->visible(fn (callable $get) => in_array($get('field_type'), ['select', 'multi_select'], true))
                    ->default([])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('form_version_id', $this->getActiveVersion()->getKey()))
            ->defaultSort('field_order_number')
            ->columns([
                TextColumn::make('field_order_number')
                    ->label('Urutan')
                    ->sortable(),
                TextColumn::make('field_label')
                    ->label('Label')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('field_key')
                    ->label('Key')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('formStep.step_title')
                    ->label('Langkah')
                    ->badge()
                    ->color('info'),
                TextColumn::make('field_type')
                    ->label('Tipe')
                    ->badge()
                    ->color('primary'),
                IconColumn::make('is_required')
                    ->label('Wajib')
                    ->boolean(),
                IconColumn::make('is_filterable')
                    ->label('Filter')
                    ->boolean(),
                IconColumn::make('is_exportable')
                    ->label('Export')
                    ->boolean(),
                IconColumn::make('is_archived')
                    ->label('Arsip')
                    ->boolean(),
            ])
            ->reorderable('field_order_number')
            ->filters([
                TernaryFilter::make('is_archived')
                    ->label('Status Arsip'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Pertanyaan'),
            ])
            ->actions([
                EditAction::make(),
                Action::make('toggleArchive')
                    ->label(fn (FormField $record) => $record->is_archived ? 'Pulihkan' : 'Arsipkan')
                    ->icon('heroicon-o-archive-box')
                    ->color(fn (FormField $record) => $record->is_archived ? 'gray' : 'warning')
                    ->requiresConfirmation()
                    ->action(function (FormField $record) {
                        $record->is_archived = ! $record->is_archived;
                        $record->save();
                    })
                    ->successNotificationTitle('Status arsip diperbarui')
                    ->after(fn () => $this->getTable()->deselectAllRecords()),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->synchroniseVersionData($data);
        $data['field_order_number'] = $this->getNextOrderNumber();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->synchroniseVersionData($data);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['field_options'] = $data['field_options_json'] ?? [];

        return $data;
    }

    protected function synchroniseVersionData(array $data): array
    {
        $data['form_version_id'] = $this->getActiveVersion()->getKey();
        $data['field_options_json'] = Arr::map($data['field_options'] ?? [], function ($option) {
            return [
                'label' => $option['label'] ?? null,
                'value' => $option['value'] ?? null,
            ];
        });

        unset($data['field_options']);

        return $data;
    }

    protected function getNextOrderNumber(): int
    {
        return ($this->getActiveVersion()->formFields()->max('field_order_number') ?? 0) + 1;
    }

    protected function getActiveVersion(): FormVersion
    {
        $form = $this->getOwnerRecord();

        $version = $form->ensureActiveVersion();
        $version->loadMissing(['formSteps', 'formFields']);

        return $version;
    }

    public function getRelationship(): HasMany
    {
        return $this->getActiveVersion()->formFields();
    }

    protected function getStepOptions(): array
    {
        return $this->getActiveVersion()
            ->formSteps()
            ->orderBy('step_order_number')
            ->pluck('step_title', 'id')
            ->all();
    }
}
