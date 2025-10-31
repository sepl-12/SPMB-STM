<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ExamCardFieldConfig extends Model
{
    protected $fillable = [
        'field_key',
        'field_aliases',
        'label',
        'position_left',
        'position_top',
        'width',
        'height',
        'field_type',
        'font_size',
        'is_enabled',
        'order',
        'fallback_value',
        'is_required',
    ];

    protected $casts = [
        'field_aliases' => 'array',
        'position_left' => 'decimal:2',
        'position_top' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'font_size' => 'decimal:2',
        'is_enabled' => 'boolean',
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope untuk field yang enabled
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope untuk ordering field berdasarkan order column
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('id');
    }

    /**
     * Scope untuk filter berdasarkan field type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('field_type', $type);
    }

    /**
     * Get all possible keys for this field (field_key + aliases)
     */
    public function getAllKeys(): array
    {
        $keys = [$this->field_key];

        if ($this->field_aliases && is_array($this->field_aliases)) {
            $keys = array_merge($keys, $this->field_aliases);
        }

        return $keys;
    }

    /**
     * Get field value dari submission dengan mencoba semua aliases
     */
    public function getFieldValue($submission): mixed
    {
        if (!$submission) {
            return $this->fallback_value;
        }

        $keys = $this->getAllKeys();

        // Jika submission adalah Applicant model
        if (method_exists($submission, 'latestSubmission')) {
            $submission = $submission->latestSubmission;
        }

        // Coba ambil dari answers
        if ($submission && isset($submission->answers)) {
            $answers = is_string($submission->answers)
                ? json_decode($submission->answers, true)
                : $submission->answers;

            foreach ($keys as $key) {
                if (isset($answers[$key]) && !empty($answers[$key])) {
                    return $answers[$key];
                }
            }
        }

        return $this->fallback_value;
    }

    /**
     * Generate CSS position string
     */
    public function getCssPosition(): string
    {
        $css = "left: {$this->position_left}mm; top: {$this->position_top}mm;";

        if ($this->width) {
            $css .= " width: {$this->width}mm;";
        }

        if ($this->height) {
            $css .= " height: {$this->height}mm;";
        }

        if ($this->field_type === 'text') {
            $css .= " font-size: {$this->font_size}pt;";
        }

        return $css;
    }

    /**
     * Validate apakah posisi dalam bounds A4 (210mm x 297mm)
     */
    public function validatePosition(): bool
    {
        $maxWidth = 210; // A4 width in mm
        $maxHeight = 297; // A4 height in mm

        if ($this->position_left < 0 || $this->position_left > $maxWidth) {
            return false;
        }

        if ($this->position_top < 0 || $this->position_top > $maxHeight) {
            return false;
        }

        // Check if element doesn't overflow
        if ($this->width && ($this->position_left + $this->width) > $maxWidth) {
            return false;
        }

        if ($this->height && ($this->position_top + $this->height) > $maxHeight) {
            return false;
        }

        return true;
    }

    /**
     * Check if this field overlaps with another field
     */
    public function overlaps(ExamCardFieldConfig $other): bool
    {
        // Simple overlap detection
        $thisLeft = $this->position_left;
        $thisRight = $thisLeft + ($this->width ?? 50); // default 50mm if width not set
        $thisTop = $this->position_top;
        $thisBottom = $thisTop + ($this->height ?? 10); // default 10mm if height not set

        $otherLeft = $other->position_left;
        $otherRight = $otherLeft + ($other->width ?? 50);
        $otherTop = $other->position_top;
        $otherBottom = $otherTop + ($other->height ?? 10);

        return !($thisRight < $otherLeft ||
                 $thisLeft > $otherRight ||
                 $thisBottom < $otherTop ||
                 $thisTop > $otherBottom);
    }
}
