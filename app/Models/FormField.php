<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormField extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'field_options_json' => 'array',
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'is_exportable' => 'boolean',
        'is_archived' => 'boolean',
        'is_system_field' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (FormField $field) {
            if ($field->field_order_number) {
                return;
            }

            $maxOrder = static::query()
                ->where('form_version_id', $field->form_version_id)
                ->max('field_order_number');

            $field->field_order_number = ($maxOrder ?? 0) + 1;
        });

        // Protect system fields from critical changes (silent protection)
        static::updating(function (FormField $field) {
            if ($field->is_system_field) {
                // Silently revert field_key changes
                if ($field->isDirty('field_key')) {
                    $field->field_key = $field->getOriginal('field_key');
                }

                // Silently revert field_type changes
                if ($field->isDirty('field_type')) {
                    $field->field_type = $field->getOriginal('field_type');
                }

                // Silently prevent archiving
                if ($field->isDirty('is_archived') && $field->is_archived) {
                    $field->is_archived = false;
                }
            }
        });

        // Prevent deleting system fields (silent fail)
        static::deleting(function (FormField $field) {
            if ($field->is_system_field) {
                throw new \Exception('System fields cannot be deleted');
            }
        });
    }

    public function formVersion(): BelongsTo
    {
        return $this->belongsTo(FormVersion::class);
    }

    public function formStep(): BelongsTo
    {
        return $this->belongsTo(FormStep::class);
    }

    public function submissionAnswers(): HasMany
    {
        return $this->hasMany(SubmissionAnswer::class);
    }

    public function submissionFiles(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }
}
