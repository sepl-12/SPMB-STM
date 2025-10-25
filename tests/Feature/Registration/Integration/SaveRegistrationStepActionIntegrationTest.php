<?php

namespace Tests\Feature\Registration\Integration;

use App\Enum\FormFieldType;
use App\Registration\Actions\SaveRegistrationStepAction;
use App\Registration\Data\SaveStepResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithRegistrationForm;
use Tests\TestCase;

class SaveRegistrationStepActionIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRegistrationForm;

    private SaveRegistrationStepAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createActiveWave();

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
                'step' => ['step_key' => 'upload', 'step_title' => 'Upload Berkas'],
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

        $this->action = app(SaveRegistrationStepAction::class);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_next_step_result_with_valid_data(): void
    {
        /**
         * Cara kerja: panggil action pada step pertama dengan data valid.
         * Target: SaveStepResult menandakan pindah step dan menyimpan data.
         */
        $wizard = $this->loadRegistrationWizard();

        $request = Request::create('/daftar/save-step', 'POST', [
            'action' => 'next',
            'current_step' => 0,
            'nama_lengkap' => 'Tester',
            'email' => 'tester@example.com',
        ]);

        $result = $this->action->execute($request, $wizard, [], 0, 'next');

        $this->assertInstanceOf(SaveStepResult::class, $result);
        $this->assertFalse($result->shouldSubmit);
        $this->assertSame(1, $result->nextStepIndex);
        $this->assertSame('Tester', $result->registrationData['nama_lengkap']);
        $this->assertSame('tester@example.com', $result->registrationData['email']);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_stores_uploaded_files_and_signals_submit(): void
    {
        /**
         * Cara kerja: gunakan data existing dari step pertama, kemudian submit dengan file di step kedua.
         * Target: file tersimpan di disk dan SaveStepResult menunjukkan submit.
         */
        $wizard = $this->loadRegistrationWizard();

        $initialResult = $this->action->execute(
            Request::create('/daftar/save-step', 'POST', [
                'action' => 'next',
                'current_step' => 0,
                'nama_lengkap' => 'Tester',
                'email' => 'tester@example.com',
            ]),
            $wizard,
            [],
            0,
            'next'
        );

        $file = UploadedFile::fake()->create('rapor.pdf', 150, 'application/pdf');
        $request = Request::create('/daftar/save-step', 'POST', [
            'action' => 'submit',
            'current_step' => 1,
        ], [], ['rapor' => $file]);

        $result = $this->action->execute(
            $request,
            $wizard,
            $initialResult->registrationData,
            1,
            'submit'
        );

        $this->assertTrue($result->shouldSubmit);
        $this->assertArrayHasKey('rapor', $result->registrationData);
        $this->assertNotEmpty($result->registrationData['rapor']);
        $this->assertDatabaseMissing('applicants', ['applicant_email_address' => 'tester@example.com']);
        $this->assertTrue(Storage::disk('public')->exists($result->registrationData['rapor']), 'File upload harus tersimpan di disk public.');
    }
}
