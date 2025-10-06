<?php

namespace App\Filament\Widgets;

use App\Models\SubmissionAnswer;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopSchoolsTable extends TableWidget
{
    protected static ?string $heading = 'Top 10 Asal Sekolah';

    protected function getTableQuery(): Builder
    {
        return SubmissionAnswer::query()
            ->selectRaw('MIN(id) as id, answer_value_text as school, COUNT(*) as total')
            ->where('field_key', 'asal_sekolah')
            ->whereNotNull('answer_value_text')
            ->groupBy('answer_value_text')
            ->orderByDesc('total')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('school')
                ->label('Sekolah')
                ->wrap()
                ->sortable(),
            Tables\Columns\TextColumn::make('total')
                ->label('Jumlah Pendaftar')
                ->sortable(),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

}
