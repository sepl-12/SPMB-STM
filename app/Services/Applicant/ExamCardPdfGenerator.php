<?php

namespace App\Services\Applicant;

use App\Models\Applicant;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ExamCardPdfGenerator
{
    protected const STORAGE_SUBDIR = 'exam-cards';

    public function __construct(
        protected readonly ExamCardDataResolver $dataResolver
    ) {
    }

    public function generate(Applicant $applicant): DomPdf
    {
        $data = $this->dataResolver->resolve($applicant);

        Pdf::setOptions([
            'isRemoteEnabled' => true,
            'dpi' => 96,
        ]);

        return Pdf::loadView('pdf.exam-card', $data)
            ->setPaper('a4', 'portrait');
    }

    public function getStoredPath(Applicant $applicant): ?string
    {
        $path = $this->absolutePath($applicant);

        return file_exists($path) ? $path : null;
    }

    public function generateAndStore(Applicant $applicant, bool $force = false): string
    {
        $existingPath = $this->getStoredPath($applicant);

        if ($existingPath && ! $force && ! $this->isStale($applicant, $existingPath)) {
            return $existingPath;
        }

        $absolutePath = $this->absolutePath($applicant);
        $directory = dirname($absolutePath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $this->generate($applicant)->save($absolutePath);

        return $absolutePath;
    }

    protected function absolutePath(Applicant $applicant): string
    {
        $registration = $applicant->registration_number ?: $applicant->getKey();
        $fileName = Str::slug($registration) . '.pdf';

        return storage_path('app/' . static::STORAGE_SUBDIR . '/' . $fileName);
    }

    protected function isStale(Applicant $applicant, string $path): bool
    {
        $modifiedTimestamp = @filemtime($path);

        if (! $modifiedTimestamp) {
            return true;
        }

        $timestamps = [
            $applicant->updated_at,
            $applicant->registered_datetime,
            $applicant->latestSubmission?->updated_at,
            $applicant->latestSubmission?->submitted_datetime,
        ];

        $latestDataChange = collect($timestamps)
            ->filter()
            ->map(fn ($time) => $time instanceof DateTimeInterface ? $time->getTimestamp() : null)
            ->max() ?? 0;

        return $modifiedTimestamp < $latestDataChange;
    }
}
