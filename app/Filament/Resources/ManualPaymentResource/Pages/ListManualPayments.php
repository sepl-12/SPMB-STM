<?php

namespace App\Filament\Resources\ManualPaymentResource\Pages;

use App\Filament\Resources\ManualPaymentResource;
use App\Models\ManualPayment;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListManualPayments extends ListRecords
{
    protected static string $resource = ManualPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - manual payments are created by users uploading proofs
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ManualPaymentResource\Widgets\ManualPaymentStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(ManualPayment::count()),

            'pending' => Tab::make('Menunggu Verifikasi')
                ->badge(ManualPayment::where('approval_status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('approval_status', 'pending')),

            'approved' => Tab::make('Disetujui')
                ->badge(ManualPayment::where('approval_status', 'approved')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('approval_status', 'approved')),

            'rejected' => Tab::make('Ditolak')
                ->badge(ManualPayment::where('approval_status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('approval_status', 'rejected')),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'pending';
    }
}
