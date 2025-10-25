<?php

namespace Tests\Feature\Registration;

use App\Enum\FormFieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRegistrationForm;
use Tests\TestCase;

class RegistrationSubmitValidationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRegistrationForm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createActiveWave();

        $this->createRegistrationStructure([
            [
                'step' => [
                    'step_key' => 'data_diri',
                    'step_title' => 'Data Diri',
                ],
                'fields' => [
                    [
                        'field_key' => 'nama_lengkap',
                        'field_label' => 'Nama Lengkap',
                        'field_type' => FormFieldType::TEXT->value,
                        'is_required' => true,
                    ],
                ],
            ],
            [
                'step' => [
                    'step_key' => 'upload',
                    'step_title' => 'Upload Berkas',
                ],
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
    public function it_keeps_session_data_when_submit_validation_fails(): void
    {
        /**
         * Cara kerja: isi step pertama, kemudian submit tanpa upload file wajib.
         * Target: redirect kembali dengan error file dan data session tetap ada.
         */
        $this->post(route('registration.save-step'), [
            'current_step' => 0,
            'action' => 'next',
            'nama_lengkap' => 'Siswa Percobaan',
        ]);

        $response = $this->from(route('registration.index'))
            ->post(route('registration.save-step'), [
                'current_step' => 1,
                'action' => 'submit',
            ]);

        $response->assertRedirect(route('registration.index'));
        $response->assertSessionHasErrors(['rapor']);
        $this->assertSame('Siswa Percobaan', session('registration_data')['nama_lengkap']);
    }
}
