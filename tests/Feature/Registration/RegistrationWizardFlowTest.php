<?php

namespace Tests\Feature\Registration;

use App\Enum\FormFieldType;
use App\Services\GmailMailableSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\Concerns\InteractsWithRegistrationForm;
use Tests\TestCase;

class RegistrationWizardFlowTest extends TestCase
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
                    [
                        'field_key' => 'email',
                        'field_label' => 'Email',
                        'field_type' => FormFieldType::EMAIL->value,
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
    public function it_validates_step_data_before_moving_forward(): void
    {
        /**
         * Cara kerja: kirim request tanpa data pada langkah pertama.
         * Target: mendapat redirect kembali dengan error nama & email.
         */
        $response = $this->from(route('registration.index'))
            ->post(route('registration.save-step'), [
                'current_step' => 0,
                'action' => 'next',
            ]);

        $response->assertRedirect(route('registration.index'));
        $response->assertSessionHasErrors(['nama_lengkap', 'email']);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_stores_step_data_and_moves_to_next_step(): void
    {
        /**
         * Cara kerja: mengirim data valid pada langkah pertama dengan aksi next.
         * Target: data tersimpan di session dan current_step berpindah ke langkah berikutnya.
         */
        $response = $this->post(route('registration.save-step'), [
            'current_step' => 0,
            'action' => 'next',
            'nama_lengkap' => 'Siswa Cerdas',
            'email' => 'siswa@example.com',
        ]);

        $response->assertRedirect(route('registration.index'));
        $this->assertEquals(1, session('current_step'));
        $this->assertSame('Siswa Cerdas', session('registration_data')['nama_lengkap']);
        $this->assertSame('siswa@example.com', session('registration_data')['email']);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_submits_registration_and_clears_session(): void
    {
        /**
         * Cara kerja: isi langkah pertama, kemudian submit langkah kedua dengan file upload.
         * Target: redirect ke halaman sukses, data session terhapus, applicant tercatat.
         */
        $this->post(route('registration.save-step'), [
            'current_step' => 0,
            'action' => 'next',
            'nama_lengkap' => 'Siswa Lulus',
            'email' => 'lulus@example.com',
        ]);

        $mockSender = Mockery::mock(GmailMailableSender::class);
        $mockSender->shouldReceive('send')->once()->andReturn('mock-message-id');
        $this->app->instance(GmailMailableSender::class, $mockSender);

        $response = $this->post(route('registration.save-step'), [
            'current_step' => 1,
            'action' => 'submit',
            'rapor' => UploadedFile::fake()->create('rapor.pdf', 120, 'application/pdf'),
        ]);

        $location = $response->headers->get('Location');
        $this->assertNotNull($location);
        $this->assertStringContainsString('/daftar/success/', $location);
        $this->assertNull(session('registration_data'));
        $this->assertNull(session('current_step'));

        $this->assertDatabaseHas('applicants', [
            'applicant_full_name' => 'Siswa Lulus',
            'applicant_email_address' => 'lulus@example.com',
        ]);
    }
}
