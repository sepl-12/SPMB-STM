<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class PpdbStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan PPDB';

    protected function getCards(): array
    {
        $totalApplicants = Applicant::count();
        $paidCount = Applicant::where('payment_status', 'paid')->count();
        $percentagePaid = $totalApplicants > 0 ? round(($paidCount / $totalApplicants) * 100, 1) : 0;
        $todayCount = Applicant::whereDate('registered_datetime', Carbon::today())->count();

        return [
            Card::make('Total Pendaftar', number_format($totalApplicants))
                ->description('Keseluruhan pendaftar yang tercatat')
                ->icon('heroicon-o-user-group')
                ->color('primary'),
            Card::make('Persentase Sudah Bayar', ($totalApplicants > 0 ? $percentagePaid : 0) . '%')
                ->description($paidCount . ' dari ' . max($totalApplicants, 1) . ' pendaftar')
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Card::make('Pendaftar Hari Ini', number_format($todayCount))
                ->description('Dari pukul 00:00 hingga sekarang')
                ->icon('heroicon-o-clock')
                ->color('warning'),
        ];
    }
}
