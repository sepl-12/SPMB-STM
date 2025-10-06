<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

class PpdbOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Rekap & Statistik';

    protected static ?string $slug = 'ppdb-overview';

    protected static string $view = 'filament.pages.ppdb-overview';

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\PpdbStatsOverview::class,
            \App\Filament\Widgets\DailyApplicantsChart::class,
            \App\Filament\Widgets\TopSchoolsTable::class,
        ];
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getVisibleWidgets(): array
    {
        return $this->getWidgets();
    }

    public function getWidgetData(): array
    {
        return [];
    }

    /**
     * @return int | string | array<string, int | string | null>
     */
    public function getColumns(): int | string | array
    {
        return [
            'sm' => 1,
            'xl' => 2,
        ];
    }
}
