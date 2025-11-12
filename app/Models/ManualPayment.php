<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ManualPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'applicant_id',
        'proof_image_path',
        'upload_datetime',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'paid_amount',
        'payment_notes',
    ];

    protected $casts = [
        'upload_datetime' => 'datetime',
        'approved_at' => 'datetime',
        'paid_amount' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }

    /**
     * Helper Methods
     */
    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function getProofImageUrl(): ?string
    {
        if (!$this->proof_image_path) {
            return null;
        }

        // Generate route URL for viewing proof (requires authentication)
        return route('manual-payment.proof', ['manualPayment' => $this->id]);
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->approval_status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'gray',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->approval_status) {
            'pending' => 'Menunggu Verifikasi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => 'Unknown',
        };
    }
}
