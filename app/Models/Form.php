<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
