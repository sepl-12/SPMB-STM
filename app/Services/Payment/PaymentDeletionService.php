<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Enum\PaymentStatus;
use Illuminate\Support\Facades\DB;

class PaymentDeletionService
{
    /**
     * Check if payment can be deleted
     */
    public function canDelete(Payment $payment): array
    {
        $errors = [];

        // Rule 1: Settlement payment tidak boleh dihapus
        if ($payment->payment_status_name === PaymentStatus::SETTLEMENT) {
            $errors[] = 'Payment yang sudah settlement tidak dapat dihapus';
        }

        // Rule 2: Payment < 7 hari tidak boleh dihapus (untuk rekonsiliasi)
        if ($payment->created_at->diffInDays(now()) < 7) {
            $errors[] = 'Payment belum melewati periode rekonsiliasi minimum (7 hari)';
        }

        // Rule 3: Cek permission user (jika sudah ada auth)
        if (auth()->check() && !auth()->user()->can('delete', $payment)) {
            $errors[] = 'Anda tidak memiliki permission untuk menghapus payment';
        }

        return [
            'can_delete' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Delete payment with reason (soft delete)
     */
    public function delete(Payment $payment, string $reason): bool
    {
        $validation = $this->canDelete($payment);

        if (!$validation['can_delete']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        return DB::transaction(function () use ($payment, $reason) {
            // Set metadata sebelum delete
            $payment->deleted_by = auth()->id();
            $payment->deletion_reason = $reason;
            $payment->save();

            // Soft delete
            return $payment->delete();
        });
    }

    /**
     * Restore deleted payment
     */
    public function restore(Payment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            // Clear deletion metadata
            $payment->deleted_by = null;
            $payment->deletion_reason = null;
            $payment->save();

            // Restore
            return $payment->restore();
        });
    }

    /**
     * Force delete (permanent) - hanya untuk admin khusus
     */
    public function forceDelete(Payment $payment): bool
    {
        // Extra validation untuk force delete
        if ($payment->payment_status_name === PaymentStatus::SETTLEMENT) {
            throw new \Exception('Settlement payment TIDAK BOLEH dihapus permanen');
        }

        if (!auth()->user()->hasRole('superadmin')) {
            throw new \Exception('Hanya superadmin yang dapat melakukan force delete');
        }

        return DB::transaction(function () use ($payment) {
            return $payment->forceDelete();
        });
    }

    /**
     * Get deletion statistics
     */
    public function getStats(): array
    {
        return [
            'total_deleted' => Payment::onlyTrashed()->count(),
            'deleted_this_month' => Payment::onlyTrashed()
                ->whereMonth('deleted_at', now()->month)
                ->whereYear('deleted_at', now()->year)
                ->count(),
            'deleted_by_status' => Payment::onlyTrashed()
                ->selectRaw('payment_status_name, COUNT(*) as count')
                ->groupBy('payment_status_name')
                ->get()
                ->mapWithKeys(fn($item) => [$item->payment_status_name->label() => $item->count])
                ->toArray(),
        ];
    }
}
