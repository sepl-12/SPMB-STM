<?php

namespace App\Filament\Resources\FormResource\RelationManagers;

use App\Models\FormVersion;
use App\Models\FormStep;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class FormStepsRelationManager extends RelationManager
{
    protected static string $relationship = 'formSteps';

    protected static ?string $title = 'Langkah';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('step_title')
                    ->label('Judul Langkah')
                    ->required()
                    ->maxLength(120)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('step_key', Str::slug($state ?? '', '_'))),
                TextInput::make('step_key')
                    ->label('Key')
                    ->required()
                    ->maxLength(50)
                    ->alphaDash()
                    ->helperText('Gunakan huruf kecil dan tanda hubung untuk konsistensi.')
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

                        $title = $record?->step_title;

                        if (filled($title)) {
                            $set('step_key', Str::slug($title, '_'));
                        }
                    })
                    ->dehydrateStateUsing(fn (?string $state) => $state ? Str::slug($state, '_') : null)
                    ->extraAttributes(['readonly' => true]),
                Textarea::make('step_description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_visible_for_public')
                    ->label('Tampil untuk publik?')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('form_version_id', $this->getActiveVersion()->getKey()))
            ->defaultSort('step_order_number')
            ->columns([
                TextColumn::make('step_order_number')
                    ->label('Urutan')
                    ->sortable(),
                TextColumn::make('step_title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('step_key')
                    ->label('Key')
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_visible_for_public')
                    ->label('Publik')
                    ->boolean(),
            ])
            ->reorderable('step_order_number')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Langkah'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('Arsipkan')
                    ->requiresConfirmation()
                    ->successNotificationTitle('Langkah diarsipkan'),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['form_version_id'] = $this->getActiveVersion()->getKey();
        $data['step_order_number'] = $this->getNextOrderNumber();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['form_version_id'] = $this->getActiveVersion()->getKey();

        return $data;
    }

    protected function getNextOrderNumber(): int
    {
        return ($this->getActiveVersion()->formSteps()->max('step_order_number') ?? 0) + 1;
    }

    protected function getActiveVersion(): FormVersion
    {
        $form = $this->getOwnerRecord();

        return $form->ensureActiveVersion()->loadMissing('formSteps');
    }

    public function getRelationship(): HasMany
    {
        return $this->getActiveVersion()->formSteps();
    }
}
