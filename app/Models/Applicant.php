<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Applicant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'registered_datetime' => 'datetime',
    ];

    protected ?array $latestSubmissionAnswersCache = null;

    public function wave(): BelongsTo
    {
        return $this->belongsTo(Wave::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestSubmission(): HasOne
    {
        return $this->hasOne(Submission::class)->latestOfMany('submitted_datetime');
    }

    public function getLatestSubmissionAnswers(): array
    {
        if ($this->latestSubmissionAnswersCache !== null) {
            return $this->latestSubmissionAnswersCache;
        }

        $submission = $this->relationLoaded('latestSubmission')
            ? $this->latestSubmission
            : $this->latestSubmission()->first();

        return $this->latestSubmissionAnswersCache = $submission?->answers_json ?? [];
    }

    public function getLatestAnswerForField(string $fieldKey): mixed
    {
        return $this->getLatestSubmissionAnswers()[$fieldKey] ?? null;
    }
}
