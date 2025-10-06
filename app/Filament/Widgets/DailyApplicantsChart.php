<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class DailyApplicantsChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Pendaftar Harian';

    protected static ?string $pollingInterval = '60s';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = collect(range(0, 13))
            ->map(fn (int $index) => Carbon::today()->subDays(13 - $index));

        $startDate = $days->first()->copy()->startOfDay();

        $counts = Applicant::query()
            ->selectRaw('DATE(registered_datetime) as date, COUNT(*) as total')
            ->where('registered_datetime', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $labels = $days->map(fn (Carbon $date) => $date->translatedFormat('d M'));
        $data = $days->map(fn (Carbon $date) => (int) ($counts[$date->toDateString()] ?? 0));

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pendaftar',
                    'data' => $data->all(),
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => $labels->all(),
        ];
    }
}
