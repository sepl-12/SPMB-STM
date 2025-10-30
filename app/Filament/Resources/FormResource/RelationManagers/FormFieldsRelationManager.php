<?php

namespace App\Filament\Resources\FormResource\RelationManagers;

use App\Enum\FormFieldType;
use App\Filament\Forms\FieldSchemas\FieldBasicsSection;
use App\Filament\Forms\FieldSchemas\FieldDisplaySection;
use App\Filament\Forms\FieldSchemas\FieldOptionsSection;
use App\Filament\Forms\FieldSchemas\FieldPlacementSection;
use App\Filament\Forms\FieldSchemas\FieldValidationSection;
use App\Models\FormField;
use App\Models\FormVersion;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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

class FormFieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'formFields';

    protected static ?string $title = 'Pertanyaan';

    public function form(Form $form): Form
    {
        $activeVersionResolver = fn () => $this->getActiveVersion();

        return $form->schema([
            FieldBasicsSection::make($activeVersionResolver),
            FieldPlacementSection::make($activeVersionResolver),
            FieldDisplaySection::make(),
            FieldValidationSection::make(),
            FieldOptionsSection::make(),

            // Field Linking Section
            Section::make('Field Linking (Opsional)')
                ->description('Hubungkan field ini dengan field select lainnya untuk membuat opsi yang saling eksklusif.')
                ->schema([
                    Select::make('linked_field_group')
                        ->label('Nama Grup Linked')
                        ->placeholder('Pilih grup yang ada atau buat baru...')
                        ->helperText('Field dengan nama grup yang sama akan berbagi opsi eksklusif. Pilih dari daftar atau ketik nama baru.')
                        ->options(function () {
                            // Get existing linked groups from current form version
                            $activeVersion = $this->getActiveVersion();

                            $existingGroups = FormField::query()
                                ->where('form_version_id', $activeVersion->id)
                                ->whereNotNull('linked_field_group')
                                ->where('linked_field_group', '!=', '')
                                ->distinct()
                                ->pluck('linked_field_group', 'linked_field_group')
                                ->toArray();

                            return $existingGroups;
                        })
                        ->searchable()
                        ->allowHtml()
                        ->getSearchResultsUsing(function (string $search) {
                            // Allow creating new group by typing
                            $activeVersion = $this->getActiveVersion();

                            $existingGroups = FormField::query()
                                ->where('form_version_id', $activeVersion->id)
                                ->whereNotNull('linked_field_group')
                                ->where('linked_field_group', '!=', '')
                                ->where('linked_field_group', 'like', "%{$search}%")
                                ->distinct()
                                ->pluck('linked_field_group', 'linked_field_group')
                                ->toArray();

                            // Add search term as new option
                            if (!empty($search) && !in_array($search, $existingGroups)) {
                                $existingGroups[$search] = "✨ Buat baru: \"{$search}\"";
                            }

                            return $existingGroups;
                        })
                        ->getOptionLabelUsing(function ($value) {
                            return $value;
                        })
                        ->visible(fn ($get) => in_array($get('field_type'), ['select', 'radio']))
                        ->reactive()
                        ->dehydrateStateUsing(function ($state) {
                            // Clean up the value (remove "Buat baru:" prefix if exists)
                            return $state;
                        })
                        ->hint('💡 Ketik untuk membuat grup baru')
                        ->hintColor('success'),

                    Placeholder::make('linked_fields_preview')
                        ->label('Terhubung Dengan')
                        ->content(function ($record, $get) {
                            if (!$record || !$get('linked_field_group')) {
                                return '—';
                            }

                            $linkedFields = $record->getLinkedFields();
                            if ($linkedFields->isEmpty()) {
                                return '⚠️ Belum ada field lain dalam grup ini';
                            }

                            $fieldsList = $linkedFields->map(function ($field) {
                                return "• {$field->field_label} ({$field->field_key})";
                            })->join("\n");

                            return new \Illuminate\Support\HtmlString(
                                '<div class="text-sm space-y-1">' . nl2br($fieldsList) . '</div>'
                            );
                        })
                        ->visible(fn ($get, $record) => !empty($get('linked_field_group')) && $record),

                    Placeholder::make('linking_help')
                        ->label('Cara Kerja')
                        ->content(new \Illuminate\Support\HtmlString('
                            <div class="text-xs text-gray-600 space-y-1">
                                <p>🔗 <strong>Linked fields</strong> akan berbagi opsi secara eksklusif:</p>
                                <ul class="list-disc list-inside ml-2 space-y-0.5">
                                    <li>Jika user pilih "Teknik Informatika" di field pertama</li>
                                    <li>Maka "Teknik Informatika" akan hilang di field lainnya</li>
                                    <li>Cocok untuk: Pilihan Jurusan 1, 2, 3</li>
                                </ul>
                            </div>
                        '))
                        ->visible(fn ($get) => in_array($get('field_type'), ['select', 'radio'])),
                ])
                ->collapsible()
                ->collapsed(),
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
                    ->color(fn(string $state): string => FormFieldType::tryFrom($state)?->badgeColor() ?? 'gray')
                    ->formatStateUsing(fn(string $state): string => FormFieldType::tryFrom($state)?->shortLabel() ?? $state)
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
                    ->options(FormFieldType::shortOptions())
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
                        ->action(fn (FormField $record) => $this->duplicateField($record))
                        ->successNotificationTitle('Pertanyaan berhasil diduplikat'),

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
                        ->action(fn (FormField $record) => $this->toggleFieldArchive($record))
                        ->successNotificationTitle('Status arsip diperbarui')

                        ->hidden(fn(FormField $record) => $record->is_system_field),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn(FormField $record) => $record->is_system_field)
                        ->modalHeading('Hapus Pertanyaan')
                        ->modalDescription('Pertanyaan yang dihapus tidak dapat dipulihkan. Yakin ingin melanjutkan?')
                        ->after(fn (FormField $record) => $this->notifyOnSystemFieldDeletionAttempt($record)),
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
                    ->action(fn (Collection $records, array $data) => $this->moveFieldsToStep($records, $data))
                    ->successNotificationTitle('Pertanyaan berhasil dipindahkan')
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('archive')
                    ->label('Arsipkan')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $this->archiveSelectedFields($records))
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
                    ->before(fn (Collection $records) => $this->beforeBulkDelete($records))
                    ->after(fn (Collection $records) => $this->afterBulkDelete($records)),

            ])
            ->defaultPaginationPageOption(50)
            ->emptyStateHeading('Belum Ada Pertanyaan')
            ->emptyStateDescription('Tambahkan pertanyaan pertama untuk formulir Anda.')
            ->emptyStateIcon('heroicon-o-question-mark-circle');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['form_version_id'] = $this->getActiveVersion()->getKey();
        $data['field_order_number'] = $this->getNextOrderNumber();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['form_version_id'] = $this->getActiveVersion()->getKey();

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

    private function duplicateField(FormField $record): void
    {
        $newRecord = $record->replicate();
        $newRecord->field_key = $record->field_key . '_copy_' . uniqid('', true);
        $newRecord->field_label = $record->field_label . ' (Copy)';
        $newRecord->field_order_number = $this->getNextOrderNumber();
        $newRecord->save();
    }

    private function toggleFieldArchive(FormField $record): void
    {
        $record->is_archived = !$record->is_archived;
        $record->save();
    }

    private function notifyOnSystemFieldDeletionAttempt(FormField $record): void
    {
        // Jika field masih ada setelah delete, berarti protected
        if ($record->exists && $record->is_system_field) {
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('Field Sistem Tidak Dapat Dihapus')
                ->body("Field '{$record->field_label}' adalah field sistem dan tidak dapat dihapus.")
                ->send();
        }
    }

    private function moveFieldsToStep(Collection $records, array $data): void
    {
        $records->each(function (FormField $record) use ($data) {
            $record->form_step_id = $data['form_step_id'];
            $record->save();
        });
    }

    private function archiveSelectedFields(Collection $records): void
    {
        $systemFields = $records->filter(fn($record) => $record->is_system_field);
        $regularFields = $records->reject(fn($record) => $record->is_system_field);

        // Only archive non-system fields
        $regularFields->each->update(['is_archived' => true]);

        // Show notification if system fields were skipped
        if ($systemFields->isNotEmpty()) {
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('Field Sistem Dilewati')
                ->body("Field sistem tidak dapat diarsipkan: {$systemFields->pluck('field_label')->join(', ')}")
                ->send();
        }
    }

    private function beforeBulkDelete(Collection $records): void
    {
        $systemFields = $records->filter(fn($record) => $record->is_system_field);

        if ($systemFields->isNotEmpty()) {
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('Field Sistem Tidak Akan Dihapus')
                ->body("Field sistem yang dipilih akan dilewati: {$systemFields->pluck('field_label')->join(', ')}")
                ->persistent()
                ->send();
        }
    }

    private function afterBulkDelete(Collection $records): void
    {
        $deletedCount = $records->filter(fn($record) => !$record->exists)->count();
        $protectedCount = $records->filter(fn($record) => $record->exists && $record->is_system_field)->count();

        if ($protectedCount > 0) {
            \Filament\Notifications\Notification::make()
                ->info()
                ->title('Penghapusan Selesai')
                ->body("Dihapus: {$deletedCount}, Dilewati (field sistem): {$protectedCount}")
                ->send();
        }
    }
}
