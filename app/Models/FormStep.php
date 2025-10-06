<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormStep extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_visible_for_public' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (FormStep $step) {
            if ($step->step_order_number) {
                return;
            }

            $maxOrder = static::query()
                ->where('form_version_id', $step->form_version_id)
                ->max('step_order_number');

            $step->step_order_number = ($maxOrder ?? 0) + 1;
        });
    }

    public function formVersion(): BelongsTo
    {
        return $this->belongsTo(FormVersion::class);
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class);
    }
}
