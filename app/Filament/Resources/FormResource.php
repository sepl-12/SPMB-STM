<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormResource\Pages;
use App\Filament\Resources\FormResource\RelationManagers\FormFieldsRelationManager;
use App\Filament\Resources\FormResource\RelationManagers\FormStepsRelationManager;
use App\Models\Form as FormModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FormResource extends Resource
{
    protected static ?string $model = FormModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'PPDB';

    protected static ?string $navigationLabel = 'Formulir';

    protected static ?string $slug = 'forms';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('form_name')
                    ->label('Nama Formulir')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('form_code', Str::slug($state ?? ''))),
                Forms\Components\TextInput::make('form_code')
                    ->label('Kode Formulir')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->disabled()
                    ->dehydrated()
                    ->afterStateHydrated(function (?string $state, callable $set, $record) {
                        if (filled($state)) {
                            return;
                        }

                        $name = $record?->form_name;

                        if (filled($name)) {
                            $set('form_code', Str::slug($name));
                        }
                    })
                    ->dehydrateStateUsing(fn (?string $state) => Str::slug($state ?? ''))
                    ->extraAttributes(['readonly' => true]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('activeFormVersion'))
            ->columns([
                TextColumn::make('form_name')
                    ->label('Nama Formulir')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('form_code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('activeFormVersion.version_number')
                    ->label('Versi Aktif')
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => $state ? 'v' . $state : 'Belum ada')
                    ->sortable(),
                TextColumn::make('activeFormVersion.published_datetime')
                    ->label('Terbit')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
            ])
            ->actions([
                Action::make('manage')
                    ->label('Kelola')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->url(fn (FormModel $record) => self::getUrl('edit', ['record' => $record]))
                    ->button(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            FormStepsRelationManager::class,
            FormFieldsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'edit' => Pages\EditForm::route('/{record}/edit'),
        ];
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
