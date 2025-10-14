<?php

namespace App\Filament\Resources\FormResource\RelationManagers;

use App\Models\FormField;
use App\Models\FormVersion;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class FormFieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'formFields';

    protected static ?string $title = 'Pertanyaan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Placeholder::make('system_field_warning')
                //     ->label('')
                //     ->content('⚠️ PERINGATAN - Field ini terhubung ke kolom `applicants` table. **field_key** dan **field_type** tidak dapat diubah. Label boleh diubah untuk customization.')
                //     ->visible(fn ($record) => $record?->is_system_field ?? false)
                //     ->extraAttributes(['class' => 'text-warning-600 font-semibold border-2 border-warning-500 rounded-lg p-4']),

                Forms\Components\Section::make('Informasi Dasar')
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
                                    ->afterStateUpdated(fn(?string $state, callable $set, $record) => !$record?->is_system_field ? $set('field_key', Str::slug($state ?? '', '_')) : null)
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
                                        modifyRuleUsing: function (Unique $rule) {
                                            return $rule->where('form_version_id', $this->getActiveVersion()->getKey());
                                        },
                                    )
                                    ->disabled()
                                    ->dehydrated()
                                    ->afterStateHydrated(function (?string $state, callable $set, $record) {
                                        if (filled($state)) {
                                            return;
                                        }

                                        $label = $record?->field_label;

                                        if (filled($label)) {
                                            $set('field_key', Str::slug($label, '_'));
                                        }
                                    })
                                    ->dehydrateStateUsing(fn(?string $state) => $state ? Str::slug($state, '_') : null)
                                    ->extraAttributes(['readonly' => true]),
                            ]),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Penempatan & Tipe')
                    ->description('Tentukan di mana dan bagaimana pertanyaan ditampilkan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('form_step_id')
                                    ->label('Langkah Wizard')
                                    ->options(fn() => $this->getStepOptions())
                                    ->required()
                                    ->searchable()
                                    ->placeholder('Pilih langkah...')
                                    ->helperText('Pertanyaan akan tampil di langkah ini'),
                                Select::make('field_type')
                                    ->label('Tipe Input')
                                    ->options([
                                        'text' => '📝 Teks - Input teks pendek',
                                        'textarea' => '📄 Textarea - Input teks panjang',
                                        'number' => '🔢 Angka - Input numerik',
                                        'select' => '📋 Select - Pilihan tunggal',
                                        'multi_select' => '☑️ Multi Select - Pilihan ganda',
                                        'date' => '📅 Tanggal - Pemilih tanggal',
                                        'file' => '📎 File - Upload file',
                                        'image' => '🖼️ Gambar - Upload gambar',
                                        'boolean' => '✅ Ya/Tidak - Toggle on/off',
                                    ])
                                    ->required()
                                    ->default('text')
                                    ->searchable()
                                    ->live()
                                    ->disabled(fn($record) => $record?->is_system_field ?? false)
                                    ->helperText(
                                        fn($record) => $record?->is_system_field
                                            ? '🔒 LOCKED - Tipe tidak dapat diubah untuk system field'
                                            : 'Pilih tipe yang sesuai dengan data yang akan dikumpulkan'
                                    ),
                            ]),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Teks Pembantu')
                    ->description('Tambahkan petunjuk untuk membantu user mengisi form')
                    ->schema([
                        TextInput::make('field_placeholder_text')
                            ->label('Placeholder')
                            ->maxLength(255)
                            ->placeholder('Contoh isi yang diharapkan')
                            ->helperText('Text yang muncul saat input kosong'),
                        Textarea::make('field_help_text')
                            ->label('Teks Bantuan')
                            ->rows(2)
                            ->placeholder('Instruksi atau penjelasan tambahan...')
                            ->helperText('Penjelasan di bawah input untuk membantu user')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Pengaturan Validasi & Export')
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
                    ->collapsed(),

                Forms\Components\Section::make('Pilihan Jawaban')
                    ->description('Atur pilihan untuk select/multi-select')
                    ->schema([
                        Forms\Components\Placeholder::make('options_info')
                            ->label('')
                            ->content('Tambahkan minimal 1 pilihan. Label adalah yang ditampilkan, Value adalah yang disimpan di database.')
                            ->visible(fn(callable $get) => in_array($get('field_type'), ['select', 'multi_select'], true)),
                        Repeater::make('field_options')
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
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['label'] ?? 'Pilihan')
                            ->visible(fn(callable $get) => in_array($get('field_type'), ['select', 'multi_select'], true))
                            ->default([])
                            ->columnSpanFull(),
                    ])
                    ->visible(fn(callable $get) => in_array($get('field_type'), ['select', 'multi_select'], true))
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('form_version_id', $this->getActiveVersion()->getKey()))
            ->defaultSort('field_order_number')
            ->defaultGroup('formStep.step_title')
            ->columns([
                TextColumn::make('field_order_number')
                    ->label('#')
                    ->sortable()
                    ->width('60px')
                    ->alignCenter(),
                TextColumn::make('formStep.step_title')
                    ->label('Langkah')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('field_label')
                    ->label('Label Pertanyaan')
                    ->searchable()
                    ->sortable()
                    ->description(fn(FormField $record) => $record->field_key)
                    ->badge(fn(FormField $record) => $record->is_system_field)
                    ->color(fn(FormField $record) => $record->is_system_field ? 'warning' : null)
                    ->icon(fn(FormField $record) => $record->is_system_field ? 'heroicon-o-lock-closed' : null)
                    ->wrap(),
                TextColumn::make('field_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'text', 'textarea', 'number' => 'primary',
                        'select', 'multi_select' => 'warning',
                        'date' => 'success',
                        'file', 'image' => 'danger',
                        'boolean' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'text' => 'Teks',
                        'textarea' => 'Textarea',
                        'number' => 'Angka',
                        'select' => 'Select',
                        'multi_select' => 'Multi Select',
                        'date' => 'Tanggal',
                        'file' => 'File',
                        'image' => 'Gambar',
                        'boolean' => 'Ya/Tidak',
                        default => $state,
                    })
                    ->sortable(),
                IconColumn::make('is_required')
                    ->label('Wajib')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(),
                IconColumn::make('is_filterable')
                    ->label('Filter')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_exportable')
                    ->label('Export')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_archived')
                    ->label('Arsip')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(),
                IconColumn::make('is_system_field')
                    ->label('System')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(),
            ])
            ->reorderable('field_order_number')
            ->groups([
                Group::make('formStep.step_title')
                    ->label('Langkah')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false),
                Group::make('field_type')
                    ->label('Tipe Pertanyaan')
                    ->collapsible(),
            ])
            ->filters([
                SelectFilter::make('form_step_id')
                    ->label('Filter Langkah')
                    ->options(fn() => $this->getStepOptions())
                    ->placeholder('Semua Langkah'),
                SelectFilter::make('field_type')
                    ->label('Filter Tipe')
                    ->options([
                        'text' => 'Teks',
                        'textarea' => 'Textarea',
                        'number' => 'Angka',
                        'select' => 'Select',
                        'multi_select' => 'Multi Select',
                        'date' => 'Tanggal',
                        'file' => 'File',
                        'image' => 'Gambar',
                        'boolean' => 'Ya/Tidak',
                    ])
                    ->placeholder('Semua Tipe'),
                TernaryFilter::make('is_required')
                    ->label('Wajib Isi')
                    ->placeholder('Semua')
                    ->trueLabel('Wajib')
                    ->falseLabel('Opsional'),
                TernaryFilter::make('is_archived')
                    ->label('Status Arsip')
                    ->placeholder('Semua')
                    ->trueLabel('Diarsipkan')
                    ->falseLabel('Aktif'),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->persistFiltersInSession()
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Pertanyaan')
                    ->icon('heroicon-o-plus-circle')
                    ->modalHeading('Tambah Pertanyaan Baru')
                    ->modalWidth('3xl')
                    ->successNotificationTitle('Pertanyaan berhasil ditambahkan'),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading('Edit Pertanyaan')
                        ->modalWidth('3xl'),
                    Action::make('duplicate')
                        ->label('Duplikat')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Duplikat Pertanyaan')
                        ->modalDescription('Pertanyaan akan diduplikat dengan urutan baru')
                        ->action(function (FormField $record) {
                            $newRecord = $record->replicate();
                            $newRecord->field_key = $record->field_key . '_copy_' . time();
                            $newRecord->field_label = $record->field_label . ' (Copy)';
                            $newRecord->field_order_number = $this->getNextOrderNumber();
                            $newRecord->save();
                        })
                        ->successNotificationTitle('Pertanyaan berhasil diduplikat')
                        ->after(fn() => $this->getTable()->deselectAllRecords()),
                    Action::make('toggleArchive')
                        ->label(fn(FormField $record) => $record->is_archived ? 'Pulihkan' : 'Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->color(fn(FormField $record) => $record->is_archived ? 'success' : 'warning')
                        ->requiresConfirmation()
                        ->modalHeading(fn(FormField $record) => $record->is_archived ? 'Pulihkan Pertanyaan' : 'Arsipkan Pertanyaan')
                        ->modalDescription(
                            fn(FormField $record) => $record->is_archived
                                ? 'Pertanyaan akan dimunculkan kembali di formulir'
                                : 'Pertanyaan tidak akan tampil di formulir tetapi tetap tersimpan'
                        )
                        ->action(function (FormField $record) {
                            $record->is_archived = ! $record->is_archived;
                            $record->save();
                        })
                        ->successNotificationTitle('Status arsip diperbarui')
                        ->after(fn() => $this->getTable()->deselectAllRecords())
                        ->hidden(fn(FormField $record) => $record->is_system_field),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn(FormField $record) => $record->is_system_field)
                        ->modalHeading('Hapus Pertanyaan')
                        ->modalDescription('Pertanyaan yang dihapus tidak dapat dipulihkan. Yakin ingin melanjutkan?')
                        ->after(function (FormField $record) {
                            // Jika field masih ada setelah delete, berarti protected
                            if ($record->exists && $record->is_system_field) {
                                \Filament\Notifications\Notification::make()
                                    ->warning()
                                    ->title('Field Sistem Tidak Dapat Dihapus')
                                    ->body("Field '{$record->field_label}' adalah field sistem dan tidak dapat dihapus.")
                                    ->send();
                            }
                        }),
                ])
            ])
            ->bulkActions([
                BulkAction::make('moveToStep')
                    ->label('Pindah ke Langkah')
                    ->icon('heroicon-o-arrows-right-left')
                    ->form([
                        Select::make('form_step_id')
                            ->label('Pindah ke Langkah')
                            ->options(fn() => $this->getStepOptions())
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $records->each(function (FormField $record) use ($data) {
                            $record->form_step_id = $data['form_step_id'];
                            $record->save();
                        });
                    })
                    ->successNotificationTitle('Pertanyaan berhasil dipindahkan')
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('archive')
                    ->label('Arsipkan')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $systemFields = $records->filter(fn($record) => $record->is_system_field);
                        $regularFields = $records->reject(fn($record) => $record->is_system_field);

                        // Only archive non-system fields
                        $regularFields->each->update(['is_archived' => true]);

                        // Show notification if system fields were skipped
                        if ($systemFields->count() > 0) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Field Sistem Dilewati')
                                ->body("Field sistem tidak dapat diarsipkan: {$systemFields->pluck('field_label')->join(', ')}")
                                ->send();
                        }
                    })
                    ->successNotificationTitle(
                        fn(Collection $records) =>
                        'Berhasil diarsipkan: ' . $records->reject(fn($record) => $record->is_system_field)->count() . ' pertanyaan'
                    )
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('restore')
                    ->label('Pulihkan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn(Collection $records) => $records->each->update(['is_archived' => false]))
                    ->successNotificationTitle('Pertanyaan berhasil dipulihkan')
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Pertanyaan')
                    ->modalDescription('Pertanyaan yang dihapus tidak dapat dipulihkan. Yakin ingin melanjutkan?')
                    ->before(function (Collection $records) {
                        $systemFields = $records->filter(fn($record) => $record->is_system_field);

                        if ($systemFields->count() > 0) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Field Sistem Tidak Akan Dihapus')
                                ->body("Field sistem yang dipilih akan dilewati: {$systemFields->pluck('field_label')->join(', ')}")
                                ->persistent()
                                ->send();
                        }
                    })
                    ->after(function (Collection $records) {
                        $deleted = $records->filter(fn($record) => !$record->exists);
                        $protected = $records->filter(fn($record) => $record->exists && $record->is_system_field);

                        if ($protected->count() > 0) {
                            \Filament\Notifications\Notification::make()
                                ->info()
                                ->title('Penghapusan Selesai')
                                ->body("Dihapus: {$deleted->count()}, Dilewati (field sistem): {$protected->count()}")
                                ->send();
                        }
                    }),

            ])
            ->defaultPaginationPageOption(50)
            ->emptyStateHeading('Belum Ada Pertanyaan')
            ->emptyStateDescription('Tambahkan pertanyaan pertama untuk formulir Anda.')
            ->emptyStateIcon('heroicon-o-question-mark-circle');
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
