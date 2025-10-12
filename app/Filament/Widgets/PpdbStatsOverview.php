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
        
        // Count paid applicants (those with successful payment)
        $paidCount = Applicant::whereHas('latestPayment', function ($query) {
            $query->where('payment_status_name', \App\Enum\PaymentStatus::SETTLEMENT->value);
        })->count();
        
        // Sum of all successful payments
        $totalPaidAmount = \App\Models\Payment::where('payment_status_name', \App\Enum\PaymentStatus::SETTLEMENT->value)
            ->sum('paid_amount_total');
            
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
