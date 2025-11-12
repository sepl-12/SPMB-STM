<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormPreview extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'session_id',
        'form_version_id',
        'preview_data',
        'step_index',
        'previewed_at',
        'converted_to_submission',
    ];

    protected $casts = [
        'preview_data' => 'array',
        'previewed_at' => 'datetime',
        'converted_to_submission' => 'boolean',
        'step_index' => 'integer',
    ];

    /**
     * Relationships
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function formVersion(): BelongsTo
    {
        return $this->belongsTo(FormVersion::class);
    }

    /**
     * Scopes
     */
    public function scopeNotConverted($query)
    {
        return $query->where('converted_to_submission', false);
    }

    public function scopeConverted($query)
    {
        return $query->where('converted_to_submission', true);
    }

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Helper methods
     */
    public function isConverted(): bool
    {
        return $this->converted_to_submission;
    }

    public function markAsConverted(): void
    {
        $this->update(['converted_to_submission' => true]);
    }
}
