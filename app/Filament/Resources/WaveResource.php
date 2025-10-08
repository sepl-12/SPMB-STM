<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaveResource\Pages;
use App\Models\Wave;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class WaveResource extends Resource
{
    protected static ?string $model = Wave::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'PPDB';

    protected static ?string $navigationLabel = 'Gelombang';

    protected static ?string $slug = 'waves';
    
    protected static ?string $modelLabel = 'Gelombang Pendaftaran';
    
    protected static ?string $pluralModelLabel = 'Gelombang Pendaftaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('wave_name')
                    ->label('Nama Gelombang')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('wave_code')
                    ->label('Kode')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true),
                Forms\Components\DateTimePicker::make('start_datetime')
                    ->label('Mulai')
                    ->required()
                    ->seconds(false)
                    ->live(onBlur: true),
                Forms\Components\DateTimePicker::make('end_datetime')
                    ->label('Selesai')
                    ->required()
                    ->seconds(false)
                    ->rule('after_or_equal:start_datetime'),
                Forms\Components\TextInput::make('quota_limit')
                    ->label('Kuota (opsional)')
                    ->numeric()
                    ->minValue(0)
                    ->nullable()
                    ->helperText('Kosongkan jika kuota tidak dibatasi.'),
                Forms\Components\TextInput::make('registration_fee_amount')
                    ->label('Biaya Pendaftaran')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->prefix('Rp'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif?')
                    ->inline(false)
                    ->helperText('Hanya satu gelombang aktif yang digunakan pada publik.')
                    ->default(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('wave_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('wave_code')
                    ->label('Kode')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('period')
                    ->label('Periode')
                    ->state(fn (Wave $record) => $record->start_datetime->format('d M Y') . ' → ' . $record->end_datetime->format('d M Y'))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('start_datetime', $direction)),
                TextColumn::make('quota_limit')
                    ->label('Kuota')
                    ->formatStateUsing(fn (?int $state) => $state ? number_format($state) : 'Tidak dibatasi')
                    ->sortable(),
                TextColumn::make('registration_fee_amount')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Gelombang?')
                    ->modalDescription(fn (Wave $record) => 
                        $record->applicants()->count() > 0
                            ? "Gelombang ini memiliki {$record->applicants()->count()} pendaftar. Menghapus gelombang akan menghapus semua data terkait."
                            : "Apakah Anda yakin ingin menghapus gelombang ini?"
                    )
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->successNotificationTitle('Gelombang berhasil dihapus')
                    ->before(function (Wave $record) {
                        // Optional: Add logic before delete
                        // Log the deletion for audit trail
                        Log::info("Deleting wave: {$record->wave_name} (ID: {$record->id})");
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Gelombang?')
                        ->modalDescription('Apakah Anda yakin ingin menghapus gelombang yang dipilih? Semua data terkait akan dihapus.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->successNotificationTitle('Gelombang berhasil dihapus'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWaves::route('/'),
            'create' => Pages\CreateWave::route('/create'),
            'edit' => Pages\EditWave::route('/{record}/edit'),
        ];
    }

    public static function canDelete(Model $record): bool
    {
        // Allow deletion
        // Note: Cascade delete is handled by database foreign key constraint
        return true;
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('is_active', true)->count() > 0 ? 'success' : 'gray';
    }
}
