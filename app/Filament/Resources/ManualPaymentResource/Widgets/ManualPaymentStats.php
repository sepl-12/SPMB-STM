<?php

namespace App\Filament\Resources\ManualPaymentResource\Widgets;

use App\Models\ManualPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ManualPaymentStats extends BaseWidget
{
    protected function getStats(): array
    {
        $pendingCount = ManualPayment::where('approval_status', 'pending')->count();
        $approvedToday = ManualPayment::where('approval_status', 'approved')
            ->whereDate('approved_at', today())
            ->count();
        $rejectedToday = ManualPayment::where('approval_status', 'rejected')
            ->whereDate('approved_at', today())
            ->count();
        $uploadedThisWeek = ManualPayment::whereBetween('upload_datetime', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])->count();

        return [
            Stat::make('Menunggu Verifikasi', $pendingCount)
                ->description('Perlu segera diproses')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->chart($this->getPendingChart()),

            Stat::make('Disetujui Hari Ini', $approvedToday)
                ->description('Pembayaran disetujui')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Ditolak Hari Ini', $rejectedToday)
                ->description('Pembayaran ditolak')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Upload Minggu Ini', $uploadedThisWeek)
                ->description('Total upload minggu ini')
                ->descriptionIcon('heroicon-o-arrow-up-tray')
                ->color('info'),
        ];
    }

    /**
     * Get chart data for pending payments over last 7 days
     */
    protected function getPendingChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = ManualPayment::where('approval_status', 'pending')
                ->whereDate('upload_datetime', '<=', $date)
                ->count();
            $data[] = $count;
        }

        return $data;
    }
}
