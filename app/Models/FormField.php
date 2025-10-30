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

    protected $fillable = [
        'form_version_id',
        'form_step_id',
        'field_key',
        'field_label',
        'field_type',
        'field_options_json',
        'linked_field_group',
        'is_required',
        'is_filterable',
        'is_exportable',
        'is_archived',
        'field_placeholder_text',
        'field_help_text',
        'field_order_number',
        'is_system_field',
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

    /**
     * Get all fields in the same linked group
     */
    public function getLinkedFields(): \Illuminate\Support\Collection
    {
        if (!$this->linked_field_group) {
            return collect([]);
        }

        return static::query()
            ->where('form_version_id', $this->form_version_id)
            ->where('linked_field_group', $this->linked_field_group)
            ->where('id', '!=', $this->id)
            ->where('is_archived', false)
            ->orderBy('field_order_number')
            ->get();
    }

    /**
     * Check if this field is part of a linked group
     */
    public function isLinked(): bool
    {
        return !empty($this->linked_field_group);
    }
}
