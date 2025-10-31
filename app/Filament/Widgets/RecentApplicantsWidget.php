<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentApplicantsWidget extends BaseWidget
{
    protected static ?string $heading = 'Pendaftar Terbaru';

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Applicant::query()
            ->with(['wave', 'latestPayment'])
            ->latest('created_at')
            ->limit(10);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')
                    ->label('No. Pendaftaran')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium')
                    ->icon('heroicon-m-identification')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('applicant_full_name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Applicant $record): string => $record->applicant_email_address ?? '-')
                    ->wrap(),

                Tables\Columns\TextColumn::make('applicant_phone_number')
                    ->label('No. HP')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->iconColor('success'),

                Tables\Columns\TextColumn::make('chosen_major_name')
                    ->label('Jurusan Pilihan')
                    ->badge()
                    ->color('info')
                    ->default('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('wave.wave_name')
                    ->label('Gelombang')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('latestPayment.payment_status_name')
                    ->label('Status Bayar')
                    ->formatStateUsing(fn($state) => $state?->label() ?? 'Belum Bayar')
                    ->color(fn($state): string => $state?->color() ?? 'gray')
                    ->sortable()
                    ->default('Belum Bayar'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl. Daftar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->description(fn (Applicant $record): string => $record->created_at->diffForHumans())
                    ->icon('heroicon-m-calendar')
                    ->iconColor('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Applicant $record): string => route('filament.admin.resources.applicants.view', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->striped()
            ->paginated(false);
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
