<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Wave;
use App\Registration\Actions\SaveRegistrationStepAction;
use App\Registration\Actions\SubmitRegistrationAction;
use App\Registration\Data\RegistrationPageViewModel;
use App\Registration\Data\RegistrationWizard;
use App\Registration\Exceptions\RegistrationClosedException;
use App\Registration\Exceptions\RegistrationQuotaExceededException;
use App\Registration\Exceptions\RegistrationStepValidationException;
use App\Registration\Services\RegistrationSessionStore;
use App\Registration\Services\RegistrationWizardLoader;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationWizardLoader $wizardLoader,
        private readonly RegistrationSessionStore $sessionStore,
        private readonly SaveRegistrationStepAction $saveStepAction,
        private readonly SubmitRegistrationAction $submitRegistrationAction
    ) {
    }

    /**
     * Show registration form
     */
    public function index()
    {
        // Check if there's an active wave
        $activeWave = Wave::where('is_active', true)
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now())
            ->first();

        if (!$activeWave) {
            $this->sessionStore->clear();
            return view('registration-closed');
        }

        try {
            $wizard = $this->wizardLoader->load();
        } catch (ModelNotFoundException $e) {
            \Log::warning('Registration wizard unavailable', ['message' => $e->getMessage()]);

            return view('registration-closed');
        }

        $currentStepIndex = $this->resolveCurrentStepIndex($wizard->stepCount());

        $viewModel = new RegistrationPageViewModel($wizard, $this->sessionStore->getData(), $currentStepIndex);

        return view('registration', [
            'viewModel' => $viewModel,
        ]);
    }

    /**
     * Save current step data
     */
    public function saveStep(Request $request)
    {
        $currentStepIndex = (int) $request->input('current_step', 0);
        $action = $request->input('action', 'next');

        try {
            $wizard = $this->wizardLoader->load();
        } catch (ModelNotFoundException $e) {
            \Log::warning('Registration wizard unavailable on save step.', ['message' => $e->getMessage()]);

            return redirect()
                ->route('registration.index')
                ->with('error', 'Form pendaftaran tidak tersedia saat ini. Silakan coba beberapa saat lagi.');
        }

        $existingData = $this->sessionStore->getData();

        try {
            $result = $this->saveStepAction->execute($request, $wizard, $existingData, $currentStepIndex, $action);
        } catch (RegistrationStepValidationException $exception) {
            $this->sessionStore->putData($exception->getRegistrationData());
            $this->sessionStore->putCurrentStepIndex($exception->getStepIndex());

            return redirect()->route('registration.index')
                ->withErrors($exception->getValidationException()->validator)
                ->withInput()
                ->with('validation_step', $exception->getStepIndex());
        }

        $this->sessionStore->putData($result->registrationData);

        if ($result->shouldSubmit) {
            // Redirect to preview page instead of directly submitting
            return redirect()->route('registration.preview');
        }

        $this->sessionStore->putCurrentStepIndex($result->nextStepIndex ?? $result->currentStepIndex);

        return redirect()->route('registration.index');
    }

    /**
     * Jump to specific step
     */
    public function jumpToStep(Request $request)
    {
        $jumpToStep = (int) $request->input('jump_to_step', 0);

        try {
            $wizard = $this->wizardLoader->load();
        } catch (ModelNotFoundException $e) {
            \Log::warning('Registration wizard unavailable on jump step.', ['message' => $e->getMessage()]);

            return redirect()
                ->route('registration.index')
                ->with('error', 'Form pendaftaran tidak tersedia saat ini.');
        }

        if ($wizard->stepCount() <= 0) {
            $this->sessionStore->putCurrentStepIndex(0);

            return redirect()->route('registration.index');
        }

        $jumpToStep = max(0, min($wizard->stepCount() - 1, $jumpToStep));

        $this->sessionStore->putCurrentStepIndex($jumpToStep);

        return redirect()->route('registration.index');
    }

    /**
     * Submit complete registration
     */
    protected function submitRegistration(RegistrationWizard $wizard, array $registrationData)
    {
        try {
            $result = $this->submitRegistrationAction->execute($wizard, $registrationData);
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
            \Log::error('Failed to submit registration', [
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            return redirect()->route('registration.index')
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }

        $this->sessionStore->clear();

        return redirect()->route('registration.success', ['registration_number' => $result->registrationNumber]);
    }

    /**
     * Show success page
     */
    public function success($registration_number)
    {
        $applicant = Applicant::where('registration_number', $registration_number)->firstOrFail();

        return view('registration-success', compact('applicant'));
    }

    protected function resolveCurrentStepIndex(int $stepCount): int
    {
        if ($stepCount <= 0) {
            $this->sessionStore->putCurrentStepIndex(0);

            return 0;
        }

        $currentStepIndex = $this->sessionStore->getCurrentStepIndex();
        $normalizedIndex = max(0, min($stepCount - 1, $currentStepIndex));

        if ($normalizedIndex !== $currentStepIndex) {
            $this->sessionStore->putCurrentStepIndex($normalizedIndex);
        }

        return $normalizedIndex;
    }

}
