<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SubmissionFile extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'uploaded_datetime' => 'datetime',
    ];

    /**
     * Boot method - auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the route key for the model (use UUID instead of ID)
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function formField(): BelongsTo
    {
        return $this->belongsTo(FormField::class);
    }

    /**
     * Generate signed download URL (valid for 24 hours)
     */
    public function getSignedDownloadUrl(int $expiryHours = 24): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'file.download',
            now()->addHours($expiryHours),
            ['file' => $this->uuid]
        );
    }

    /**
     * Generate signed preview URL (valid for 24 hours)
     */
    public function getSignedPreviewUrl(int $expiryHours = 24): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'file.preview',
            now()->addHours($expiryHours),
            ['file' => $this->uuid]
        );
    }
}
