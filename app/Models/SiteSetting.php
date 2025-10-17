<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * DEPRECATED: This model is no longer used.
 * 
 * Replaced by: AppSetting model with key-value storage
 * Migration: site_settings table will be dropped
 * 
 * This file will be removed after migration is complete.
 * @deprecated Use AppSetting::get($key) instead
 */
class SiteSetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'faq_items_json' => 'array',
        'timeline_items_json' => 'array',
    ];
}
