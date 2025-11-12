<?php

namespace App\Http\Controllers;

use App\Models\FormPreview;
use App\Registration\Actions\SubmitRegistrationAction;
use App\Registration\Exceptions\RegistrationClosedException;
use App\Registration\Exceptions\RegistrationQuotaExceededException;
use App\Registration\Services\RegistrationSessionStore;
use App\Registration\Services\RegistrationWizardLoader;
use App\Services\FormPreviewService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PreviewController extends Controller
{
    public function __construct(
        private readonly RegistrationWizardLoader $wizardLoader,
        private readonly RegistrationSessionStore $sessionStore,
        private readonly FormPreviewService $previewService,
        private readonly SubmitRegistrationAction $submitRegistrationAction
    ) {
    }

    /**
     * Show preview page with all submitted data
     */
    public function show(Request $request)
    {
        // Check if user has completed the form
        $sessionData = $this->sessionStore->getData();

        if (empty($sessionData)) {
            return redirect()
                ->route('registration.index')
                ->with('error', 'Tidak ada data formulir untuk dipreview. Silakan isi formulir terlebih dahulu.');
        }

        try {
            $wizard = $this->wizardLoader->load();
        } catch (ModelNotFoundException $e) {
            \Log::warning('Registration wizard unavailable on preview', ['message' => $e->getMessage()]);

            return redirect()
                ->route('registration.index')
                ->with('error', 'Form pendaftaran tidak tersedia saat ini.');
        }

        $formVersion = $wizard->formVersion();

        // Compile preview data using FormPreviewService
        $previewData = $this->previewService->compilePreviewData($sessionData, $formVersion);

        // Save preview snapshot to database for tracking
        try {
            $this->savePreviewSnapshot($sessionData, $formVersion, $request);
        } catch (\Exception $e) {
            // Log error but don't fail the preview display
            \Log::warning('Failed to save preview snapshot', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
        }

        return view('registration-preview', [
            'previewData' => $previewData,
            'formTitle' => $formVersion->form_version_name,
            'sessionId' => session()->getId(),
        ]);
    }

    /**
     * Confirm and submit final registration
     */
    public function confirm(Request $request)
    {
        // Verify session data exists
        $sessionData = $this->sessionStore->getData();

        if (empty($sessionData)) {
            return redirect()
                ->route('registration.index')
                ->with('error', 'Session telah berakhir. Silakan isi formulir kembali.');
        }

        try {
            $wizard = $this->wizardLoader->load();
        } catch (ModelNotFoundException $e) {
            \Log::warning('Registration wizard unavailable on confirm', ['message' => $e->getMessage()]);

            return redirect()
                ->route('registration.index')
                ->with('error', 'Form pendaftaran tidak tersedia saat ini.');
        }

        // Submit registration
        try {
            $result = $this->submitRegistrationAction->execute($wizard, $sessionData);
        } catch (RegistrationClosedException $exception) {
            return redirect()->route('registration.index')
                ->with('error', $exception->getMessage());
        } catch (RegistrationQuotaExceededException $exception) {
            return redirect()->route('registration.index')
                ->with('error', $exception->getMessage());
        } catch (ValidationException $exception) {
            return redirect()->route('registration.index')
                ->withErrors($exception->validator)
                ->withInput()
                ->with('error', 'Terdapat kesalahan pada data yang diisi. Silakan periksa kembali.');
        } catch (\Throwable $exception) {
            \Log::error('Failed to submit registration from preview', [
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            return redirect()->route('registration.index')
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }

        // Mark preview as converted to submission
        try {
            $this->markPreviewAsConverted($request);
        } catch (\Exception $e) {
            // Log error but don't fail the submission - it's already saved
            \Log::warning('Failed to mark preview as converted', [
                'message' => $e->getMessage(),
            ]);
        }

        // Clear session
        $this->sessionStore->clear();

        return redirect()->route('registration.success', ['registration_number' => $result->registrationNumber]);
    }

    /**
     * Return to form for editing
     */
    public function edit(Request $request)
    {
        // Session data is preserved, just redirect back to form
        $jumpToStep = $request->input('jump_to_step', 0);

        // Validate step index
        try {
            $wizard = $this->wizardLoader->load();
            $stepCount = $wizard->stepCount();
            $normalizedStep = max(0, min($stepCount - 1, (int) $jumpToStep));

            $this->sessionStore->putCurrentStepIndex($normalizedStep);
        } catch (ModelNotFoundException $e) {
            \Log::warning('Registration wizard unavailable on edit from preview', ['message' => $e->getMessage()]);
        }

        return redirect()
            ->route('registration.index')
            ->with('info', 'Anda dapat mengedit data formulir. Klik tombol berikutnya untuk kembali ke preview.');
    }

    /**
     * Save preview snapshot to database for analytics and tracking
     */
    protected function savePreviewSnapshot(array $sessionData, $formVersion, Request $request): void
    {
        DB::transaction(function () use ($sessionData, $formVersion, $request) {
            $sessionId = session()->getId();

            // Check if preview already exists for this session
            $existingPreview = FormPreview::forSession($sessionId)
                ->where('form_version_id', $formVersion->id)
                ->notConverted()
                ->first();

            $currentStepIndex = $this->sessionStore->getCurrentStepIndex();

            if ($existingPreview) {
                // Update existing preview
                $existingPreview->update([
                    'preview_data' => $sessionData,
                    'step_index' => $currentStepIndex,
                    'previewed_at' => now(),
                ]);
            } else {
                // Create new preview record
                FormPreview::create([
                    'applicant_id' => null, // Will be set after submission
                    'session_id' => $sessionId,
                    'form_version_id' => $formVersion->id,
                    'preview_data' => $sessionData,
                    'step_index' => $currentStepIndex,
                    'previewed_at' => now(),
                    'converted_to_submission' => false,
                ]);
            }
        });
    }

    /**
     * Mark preview record as converted to final submission
     */
    protected function markPreviewAsConverted(Request $request): void
    {
        $sessionId = session()->getId();

        $preview = FormPreview::forSession($sessionId)
            ->notConverted()
            ->latest('previewed_at')
            ->first();

        if ($preview) {
            $preview->markAsConverted();
        }
    }
}
