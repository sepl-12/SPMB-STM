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
        $totalPaidAmount = Applicant::where('payment_status', 'paid')
            ->withSum('payments as total_paid_sum', 'paid_amount_total')
            ->get()
            ->sum('total_paid_sum');
        $todayCount = Applicant::whereDate('registered_datetime', Carbon::today())->count();

        return [
            Card::make('Total Pendaftar', number_format($totalApplicants))
                ->description('Keseluruhan pendaftar yang tercatat')
                ->icon('heroicon-o-user-group')
                ->color('primary'),
            Card::make('Uang Masuk', 'Rp ' . number_format($totalPaidAmount, 0, ',', '.'))
                ->description($paidCount . ' pendaftar sudah membayar')
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Card::make('Pendaftar Hari Ini', number_format($todayCount))
                ->description('Dari pukul 00:00 hingga sekarang')
                ->icon('heroicon-o-clock')
                ->color('warning'),
        ];
    }
}
