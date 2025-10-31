<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamCardFieldConfigResource\Pages;
use App\Models\ExamCardFieldConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExamCardFieldConfigResource extends Resource
{
    protected static ?string $model = ExamCardFieldConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Konfigurasi Kartu Tes';

    protected static ?string $modelLabel = 'Konfigurasi Kartu Tes';

    protected static ?string $pluralModelLabel = 'Konfigurasi Kartu Tes';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Field Configuration')
                    ->description('Konfigurasi dasar untuk field di kartu tes')
                    ->schema([
                        Forms\Components\TextInput::make('field_key')
                            ->label('Field Key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Key unik untuk field ini (contoh: nama_lengkap, nisn)')
                            ->disabled(fn($record) => $record !== null), // Disable on edit to prevent breaking references

                        Forms\Components\TextInput::make('label')
                            ->label('Label')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Label yang akan ditampilkan'),

                        Forms\Components\TagsInput::make('field_aliases')
                            ->label('Field Aliases')
                            ->helperText('Key alternatif untuk field ini. Tekan Enter setelah mengetik setiap alias.')
                            ->placeholder('Ketik alias dan tekan Enter')
                            ->separator(','),

                        Forms\Components\Select::make('field_type')
                            ->label('Tipe Field')
                            ->options([
                                'text' => 'Text',
                                'image' => 'Image',
                                'signature' => 'Signature',
                            ])
                            ->required()
                            ->default('text')
                            ->live(),

                        Forms\Components\Toggle::make('is_enabled')
                            ->label('Aktif')
                            ->helperText('Apakah field ini ditampilkan di kartu tes?')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Position & Style')
                    ->description('Atur posisi dan tampilan field di kartu tes (A4: 210mm x 297mm)')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('position_left')
                                    ->label('Posisi Horizontal (Left)')
                                    ->required()
                                    ->numeric()
                                    ->suffix('mm')
                                    ->minValue(0)
                                    ->maxValue(210)
                                    ->helperText('Posisi dari kiri (0-210mm)'),

                                Forms\Components\TextInput::make('position_top')
                                    ->label('Posisi Vertikal (Top)')
                                    ->required()
                                    ->numeric()
                                    ->suffix('mm')
                                    ->minValue(0)
                                    ->maxValue(297)
                                    ->helperText('Posisi dari atas (0-297mm)'),

                                Forms\Components\TextInput::make('width')
                                    ->label('Lebar')
                                    ->numeric()
                                    ->suffix('mm')
                                    ->minValue(0)
                                    ->maxValue(210)
                                    ->helperText('Lebar field (opsional)'),

                                Forms\Components\TextInput::make('height')
                                    ->label('Tinggi')
                                    ->numeric()
                                    ->suffix('mm')
                                    ->minValue(0)
                                    ->maxValue(297)
                                    ->helperText('Tinggi field (opsional, untuk image)'),

                                Forms\Components\TextInput::make('font_size')
                                    ->label('Ukuran Font')
                                    ->numeric()
                                    ->suffix('pt')
                                    ->default(12.5)
                                    ->minValue(6)
                                    ->maxValue(72)
                                    ->helperText('Ukuran font dalam point (untuk text)')
                                    ->visible(fn(Forms\Get $get) => $get('field_type') === 'text'),

                                Forms\Components\TextInput::make('order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Urutan rendering field')
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Section::make('Defaults')
                    ->description('Nilai default dan validasi')
                    ->schema([
                        Forms\Components\TextInput::make('fallback_value')
                            ->label('Nilai Default')
                            ->maxLength(255)
                            ->helperText('Nilai yang ditampilkan jika field kosong (opsional)'),

                        Forms\Components\Toggle::make('is_required')
                            ->label('Wajib Diisi')
                            ->helperText('Apakah field ini wajib memiliki nilai?')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('field_key')
                    ->label('Field Key')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('label')
                    ->label('Label')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('field_type')
                    ->label('Tipe')
                    ->colors([
                        'primary' => 'text',
                        'success' => 'image',
                        'warning' => 'signature',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Posisi')
                    ->state(function (ExamCardFieldConfig $record): string {
                        return "{$record->position_left}mm × {$record->position_top}mm";
                    })
                    ->sortable(['position_left', 'position_top']),

                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('field_type')
                    ->label('Tipe Field')
                    ->options([
                        'text' => 'Text',
                        'image' => 'Image',
                        'signature' => 'Signature',
                    ]),

                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->label('Duplikat')
                    ->excludeAttributes(['field_key'])
                    ->beforeReplicaSaved(function (ExamCardFieldConfig $replica): void {
                        $replica->field_key = $replica->field_key . '_copy_' . time();
                        $replica->is_enabled = false;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('enable')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_enabled' => true]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('disable')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_enabled' => false]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExamCardFieldConfigs::route('/'),
            'create' => Pages\CreateExamCardFieldConfig::route('/create'),
            'edit' => Pages\EditExamCardFieldConfig::route('/{record}/edit'),
        ];
    }
}
