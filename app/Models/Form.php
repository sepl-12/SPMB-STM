<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Form extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function formVersions(): HasMany
    {
        return $this->hasMany(FormVersion::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function submissionDrafts(): HasMany
    {
        return $this->hasMany(SubmissionDraft::class);
    }

    public function exportTemplates(): HasMany
    {
        return $this->hasMany(ExportTemplate::class);
    }

    public function activeFormVersion(): HasOne
    {
        return $this->hasOne(FormVersion::class)->where('is_active', true)->latestOfMany('version_number');
    }

    public function ensureActiveVersion(): FormVersion
    {
        $active = $this->activeFormVersion()->first();

        if ($active) {
            return $active;
        }

        $nextVersion = ($this->formVersions()->max('version_number') ?? 0) + 1;

        return tap($this->formVersions()->create([
            'version_number' => $nextVersion,
            'is_active' => true,
        ]), function (FormVersion $version) {
            $this->formVersions()
                ->whereKeyNot($version->getKey())
                ->update(['is_active' => false]);
        });
    }
}
