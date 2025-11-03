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
        'conditional_rules' => 'array',
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
        'conditional_rules',
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

    /**
     * Check if this field has conditional visibility rules
     */
    public function hasConditionalRules(): bool
    {
        return !empty($this->conditional_rules) && isset($this->conditional_rules['show_if']);
    }

    /**
     * Get the parent field that controls this field's visibility
     */
    public function getControllerField()
    {
        if (!$this->hasConditionalRules()) {
            return null;
        }

        $rule = $this->conditional_rules['show_if'] ?? [];

        // Single condition
        if (isset($rule['field'])) {
            $parentFieldKey = $rule['field'];

            return static::where('form_version_id', $this->form_version_id)
                ->where('field_key', $parentFieldKey)
                ->first();
        }

        // Multiple conditions (all/any)
        if (isset($rule['all']) || isset($rule['any'])) {
            $conditions = $rule['all'] ?? $rule['any'] ?? [];
            if (!empty($conditions) && isset($conditions[0]['field'])) {
                $parentFieldKey = $conditions[0]['field'];

                return static::where('form_version_id', $this->form_version_id)
                    ->where('field_key', $parentFieldKey)
                    ->first();
            }
        }

        return null;
    }

    /**
     * Check if field should be visible based on form data
     */
    public function shouldBeVisible(array $formData): bool
    {
        if (!$this->hasConditionalRules()) {
            return true; // Always visible if no rules
        }

        $rule = $this->conditional_rules['show_if'] ?? [];

        if (isset($rule['field'])) {
            // Single condition
            return $this->evaluateCondition($rule, $formData);
        }

        if (isset($rule['all'])) {
            // AND logic - all conditions must be true
            foreach ($rule['all'] as $condition) {
                if (!$this->evaluateCondition($condition, $formData)) {
                    return false;
                }
            }
            return true;
        }

        if (isset($rule['any'])) {
            // OR logic - at least one condition must be true
            foreach ($rule['any'] as $condition) {
                if ($this->evaluateCondition($condition, $formData)) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Evaluate a single condition
     */
    protected function evaluateCondition(array $condition, array $formData): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? 'equals';
        $expectedValue = $condition['value'] ?? null;
        $actualValue = $formData[$field] ?? null;

        // Normalize boolean values
        if ($expectedValue === 'true' || $expectedValue === '1') {
            $expectedValue = true;
        } elseif ($expectedValue === 'false' || $expectedValue === '0') {
            $expectedValue = false;
        }

        if ($actualValue === 'on' || $actualValue === '1') {
            $actualValue = true;
        } elseif ($actualValue === '0' || $actualValue === '') {
            $actualValue = false;
        }

        return match ($operator) {
            'equals' => $actualValue == $expectedValue,
            'not_equals' => $actualValue != $expectedValue,
            'contains' => is_string($actualValue) && str_contains($actualValue, (string) $expectedValue),
            'not_contains' => is_string($actualValue) && !str_contains($actualValue, (string) $expectedValue),
            'greater_than' => is_numeric($actualValue) && is_numeric($expectedValue) && $actualValue > $expectedValue,
            'less_than' => is_numeric($actualValue) && is_numeric($expectedValue) && $actualValue < $expectedValue,
            'is_empty' => empty($actualValue),
            'is_not_empty' => !empty($actualValue),
            default => false,
        };
    }
}
