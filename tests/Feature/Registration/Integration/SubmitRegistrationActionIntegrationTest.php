<?php

namespace Tests\Feature\Registration\Integration;

use App\Enum\FormFieldType;
use App\Models\Applicant;
use App\Models\Wave;
use App\Registration\Actions\SubmitRegistrationAction;
use App\Registration\Exceptions\RegistrationQuotaExceededException;
use App\Registration\Events\ApplicantRegisteredEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithRegistrationForm;
use Tests\TestCase;

class SubmitRegistrationActionIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRegistrationForm;

    private Wave $wave;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wave = $this->createActiveWave(['quota_limit' => 5]);

        $this->createRegistrationStructure([
            [
                'step' => ['step_key' => 'data_diri', 'step_title' => 'Data Diri'],
                'fields' => [
                    [
                        'field_key' => 'nama_lengkap',
                        'field_label' => 'Nama Lengkap',
                        'field_type' => FormFieldType::TEXT->value,
                        'is_required' => true,
                    ],
                    [
                        'field_key' => 'email',
                        'field_label' => 'Email',
                        'field_type' => FormFieldType::EMAIL->value,
                        'is_required' => true,
                    ],
                ],
            ],
            [
                'step' => ['step_key' => 'upload', 'step_title' => 'Upload'],
                'fields' => [
                    [
                        'field_key' => 'rapor',
                        'field_label' => 'Rapor Semester',
                        'field_type' => FormFieldType::FILE->value,
                        'is_required' => true,
                    ],
                ],
            ],
        ]);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_persists_applicant_submission_and_dispatches_event(): void
    {
        /**
         * Cara kerja: simulasikan submit dengan data valid dan file yang sudah ada.
         * Target: applicant & submission tercipta dan event ApplicantRegistered ter-dispatch.
         */
        Storage::disk('public')->put('registration-files/rapor.pdf', 'fake-content');

        $wizard = $this->loadRegistrationWizard();
        $registrationData = [
            'nama_lengkap' => 'Siswa Integrasi',
            'email' => 'integrasi@example.com',
            'rapor' => 'registration-files/rapor.pdf',
        ];

        Event::fake([ApplicantRegisteredEvent::class]);

        $action = app(SubmitRegistrationAction::class);
        $result = $action->execute($wizard, $registrationData);

        $this->assertDatabaseHas('applicants', [
            'applicant_email_address' => 'integrasi@example.com',
        ]);

        $this->assertDatabaseHas('submissions', [
            'applicant_id' => $result->applicant->id,
        ]);

        Event::assertDispatched(ApplicantRegisteredEvent::class, function (ApplicantRegisteredEvent $event) use ($result) {
            return $event->applicant->is($result->applicant);
        });
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_when_quota_is_full(): void
    {
        /**
         * Cara kerja: isi quota wave lalu coba submit lagi.
         * Target: RegistrationQuotaExceededException dilempar.
         */
        $wizard = $this->loadRegistrationWizard();

        Applicant::factory()->count($this->wave->quota_limit)->create([
            'wave_id' => $this->wave->id,
        ]);

        $registrationData = [
            'nama_lengkap' => 'Over Quota',
            'email' => 'over@example.com',
            'rapor' => 'registration-files/rapor.pdf',
        ];

        Storage::disk('public')->put('registration-files/rapor.pdf', 'fake-content');

        $action = app(SubmitRegistrationAction::class);

        $this->expectException(RegistrationQuotaExceededException::class);

        $action->execute($wizard, $registrationData);
    }
}
