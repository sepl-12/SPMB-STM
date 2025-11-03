<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Semua authenticated user bisa melihat list payment
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payment $payment): bool
    {
        // Semua authenticated user bisa melihat detail payment
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Payment tidak bisa dibuat manual lewat admin
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payment $payment): bool
    {
        // Payment tidak bisa diedit manual
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payment $payment): bool
    {
        // Settlement payment tidak boleh dihapus
        if ($payment->payment_status_name === \App\Enum\PaymentStatus::SETTLEMENT) {
            return false;
        }

        // Hanya admin yang bisa delete
        // Jika Anda menggunakan Spatie Permission atau sejenisnya, ganti dengan:
        // return $user->hasRole(['admin', 'superadmin']);

        // Untuk sementara, izinkan semua authenticated user (ubah sesuai kebutuhan)
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Payment $payment): bool
    {
        // Hanya admin yang bisa restore
        // return $user->hasRole(['admin', 'superadmin']);
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Payment $payment): bool
    {
        // Settlement payment tidak boleh force delete
        if ($payment->payment_status_name === \App\Enum\PaymentStatus::SETTLEMENT) {
            return false;
        }

        // Hanya superadmin yang bisa force delete
        // return $user->hasRole('superadmin');

        // Untuk sementara disabled (ubah sesuai kebutuhan)
        return false;
    }
}
