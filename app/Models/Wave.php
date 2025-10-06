<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wave extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_active' => 'boolean',
        'registration_fee_amount' => 'decimal:2',
        'quota_limit' => 'integer',
    ];

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }
}
