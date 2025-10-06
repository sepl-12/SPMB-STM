<?php

namespace App\Filament\Resources\ExportTemplateResource\RelationManagers;

use App\Models\ExportTemplateColumn;
use App\Models\FormField;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExportTemplateColumnsRelationManager extends RelationManager
{
    protected static string $relationship = 'exportTemplateColumns';

    protected static ?string $title = 'Kolom Template';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ToggleButtons::make('source_type_name')
                    ->label('Sumber Data')
                    ->options([
                        'applicant' => 'Data Pendaftar',
                        'form_field' => 'Field Formulir',
                        'expression' => 'Ekspresi',
                    ])
                    ->colors([
                        'applicant' => 'info',
                        'form_field' => 'primary',
                        'expression' => 'warning',
                    ])
                    ->icons([
                        'applicant' => 'heroicon-m-user-circle',
                        'form_field' => 'heroicon-m-clipboard-document-list',
                        'expression' => 'heroicon-m-code-bracket',
                    ])
                    ->required()
                    ->inline()
                    ->live(),
                Select::make('source_key_select')
                    ->label('Field Form')
                    ->options(fn () => $this->getFormFieldOptions())
                    ->searchable()
                    ->visible(fn (callable $get) => $get('source_type_name') === 'form_field')
                    ->required(fn (callable $get) => $get('source_type_name') === 'form_field'),
                TextInput::make('source_key_input')
                    ->label('Source Key')
                    ->visible(fn (callable $get) => $get('source_type_name') !== 'form_field')
                    ->required(fn (callable $get) => $get('source_type_name') !== 'form_field'),
                TextInput::make('column_header_label')
                    ->label('Header Label')
                    ->required()
                    ->maxLength(150),
                TextInput::make('column_format_hint')
                    ->label('Format (opsional)')
                    ->placeholder('mis. date:Y-m-d atau number:0.00')
                    ->maxLength(50),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('column_order_number'))
            ->defaultSort('column_order_number')
            ->columns([
                TextColumn::make('column_order_number')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('column_header_label')
                    ->label('Header')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('source_type_name')
                    ->label('Sumber')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'applicant' => 'Applicant',
                        'form_field' => 'Form Field',
                        'expression' => 'Expression',
                        default => ucfirst($state),
                    }),
                TextColumn::make('source_key_name')
                    ->label('Key')
                    ->copyable()
                    ->wrap(),
                TextColumn::make('column_format_hint')
                    ->label('Format')
                    ->placeholder('-'),
            ])
            ->reorderable('column_order_number')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Kolom'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->synchroniseFormData($data);
        $data['column_order_number'] = $this->getNextOrderNumber();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->synchroniseFormData($data);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (($data['source_type_name'] ?? null) === 'form_field') {
            $data['source_key_select'] = $data['source_key_name'];
        } else {
            $data['source_key_input'] = $data['source_key_name'];
        }

        return $data;
    }

    protected function synchroniseFormData(array $data): array
    {
        if (($data['source_type_name'] ?? null) === 'form_field') {
            $data['source_key_name'] = $data['source_key_select'] ?? $data['source_key_name'] ?? null;
        } else {
            $data['source_key_name'] = $data['source_key_input'] ?? $data['source_key_name'] ?? null;
        }

        unset($data['source_key_select'], $data['source_key_input']);

        return $data;
    }

    protected function getNextOrderNumber(): int
    {
        return ($this->getRelationship()->max('column_order_number') ?? 0) + 1;
    }

    protected function getFormFieldOptions(): array
    {
        $template = $this->getOwnerRecord();
        $form = $template->form;

        if (! $form) {
            return [];
        }

        $version = $form->activeFormVersion()->first() ?? $form->ensureActiveVersion();

        if (! $version) {
            return [];
        }

        return FormField::query()
            ->where('form_version_id', $version->getKey())
            ->where('is_archived', false)
            ->orderBy('field_order_number')
            ->pluck('field_label', 'field_key')
            ->all();
    }
}
