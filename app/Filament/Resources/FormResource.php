<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormResource\Pages;
use App\Filament\Resources\FormResource\RelationManagers\FormFieldsRelationManager;
use App\Filament\Resources\FormResource\RelationManagers\FormStepsRelationManager;
use App\Models\Form as FormModel;
use App\Models\FormVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
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
    
    protected static ?string $modelLabel = 'Formulir Pendaftaran';
    
    protected static ?string $pluralModelLabel = 'Formulir Pendaftaran';

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
                    ->sortable()
                    ->toggleable(true),
                TextColumn::make('activeFormVersion.published_datetime')
                    ->label('Terbit')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->toggleable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->state(fn (FormModel $record): bool => (bool) $record->activeFormVersion?->is_active)
                    ->disabled(fn (FormModel $record): bool => ! $record->formVersions()->exists())
                    ->updateStateUsing(function (ToggleColumn $column, bool $state) {
                        /** @var FormModel $record */
                        $record = $column->getRecord();

                        $version = $record->activeFormVersion()->first() ?? $record->formVersions()->latest('version_number')->first();

                        if (! $version) {
                            return false;
                        }

                        if ($state) {
                            FormVersion::query()
                                ->where('form_id', $record->getKey())
                                ->whereKeyNot($version->getKey())
                                ->update([
                                    'is_active' => false,
                                    'published_datetime' => null,
                                ]);

                            $version->update([
                                'is_active' => true,
                                'published_datetime' => now(),
                            ]);
                        } else {
                            $version->update([
                                'is_active' => false,
                                'published_datetime' => null,
                            ]);
                        }

                        $record->unsetRelation('activeFormVersion');

                        return (bool) $version->fresh()->is_active;
                    })
                    ->afterStateUpdated(function (ToggleColumn $column, bool $state) {
                        Notification::make()
                            ->title($state ? 'Formulir diaktifkan' : 'Formulir dinonaktifkan')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Action::make('manage')
                    ->label('Kelola')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->url(fn (FormModel $record) => self::getUrl('edit', ['record' => $record]))
                    ->button(),
                DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->successNotificationTitle('Formulir dihapus'),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->label('Hapus Dipilih')
                    ->requiresConfirmation(),
            ]);
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

}
