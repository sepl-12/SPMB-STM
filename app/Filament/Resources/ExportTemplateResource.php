<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExportTemplateResource\Pages;
use App\Filament\Resources\ExportTemplateResource\RelationManagers\ExportTemplateColumnsRelationManager;
use App\Models\ExportTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ExportTemplateResource extends Resource
{
    protected static ?string $model = ExportTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'PPDB';

    protected static ?string $navigationLabel = 'Template Ekspor';

    protected static ?string $slug = 'export-templates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('form_id')
                    ->label('Formulir')
                    ->relationship('form', 'form_name')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('template_name')
                    ->label('Nama Template')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Textarea::make('template_description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_default')
                    ->label('Jadikan default')
                    ->helperText('Template default dipakai pada ekspor cepat dan laporan ringkas.')
                    ->default(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('form'))
            ->columns([
                TextColumn::make('template_name')
                    ->label('Nama Template')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('form.form_name')
                    ->label('Form')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('form_id')
                    ->label('Form')
                    ->relationship('form', 'form_name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('setDefault')
                    ->label('Set Default')
                    ->icon('heroicon-o-star')
                    ->visible(fn (ExportTemplate $record) => ! $record->is_default)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (ExportTemplate $record) {
                        $record->form
                            ->exportTemplates()
                            ->whereKeyNot($record->getKey())
                            ->update(['is_default' => false]);

                        $record->update(['is_default' => true]);

                        Notification::make()
                            ->title('Template default diperbarui')
                            ->success()
                            ->send();
                    }),
                Action::make('preview')
                    ->label('Uji Coba')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->action(fn (ExportTemplate $record) => Notification::make()
                        ->title('Uji coba ekspor')
                        ->body('Integrasikan generator file untuk mengekspor contoh data menggunakan template ' . $record->template_name . '.')
                        ->info()
                        ->send()),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            ExportTemplateColumnsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExportTemplates::route('/'),
            'create' => Pages\CreateExportTemplate::route('/create'),
            'edit' => Pages\EditExportTemplate::route('/{record}/edit'),
        ];
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
