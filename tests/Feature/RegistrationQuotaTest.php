<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\Form;
use App\Models\FormVersion;
use App\Models\Wave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegistrationQuotaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test wave with quota limit
        $this->wave = Wave::create([
            'wave_name' => 'Test Wave',
            'wave_code' => 'TEST-2025',
            'start_datetime' => now()->subHour(),
            'end_datetime' => now()->addHour(),
            'quota_limit' => 2, // Small quota for testing
            'registration_fee_amount' => 100000,
            'is_active' => true,
        ]);

        // Create a test form
        $form = Form::create([
            'form_name' => 'Test Form',
            'form_code' => 'TEST_FORM',
        ]);

        $formVersion = FormVersion::create([
            'form_id' => $form->id,
            'version_number' => 1,
            'is_active' => true,
            'published_datetime' => now(),
        ]);
    }

    /** @test */
    public function it_prevents_registration_when_quota_is_full()
    {
        // Fill the quota first
        Applicant::create([
            'registration_number' => 'TEST-2025-00001',
            'applicant_full_name' => 'Test User 1',
            'applicant_nisn' => '1234567890',
            'applicant_phone_number' => '08123456789',
            'applicant_email_address' => 'test1@example.com',
            'chosen_major_name' => 'Test Major',
            'wave_id' => $this->wave->id,
            'registered_datetime' => now(),
        ]);

        Applicant::create([
            'registration_number' => 'TEST-2025-00002',
            'applicant_full_name' => 'Test User 2',
            'applicant_nisn' => '1234567891',
            'applicant_phone_number' => '08123456790',
            'applicant_email_address' => 'test2@example.com',
            'chosen_major_name' => 'Test Major',
            'wave_id' => $this->wave->id,
            'registered_datetime' => now(),
        ]);

        // Set registration data in session
        session([
            'registration_data' => [
                'nama_lengkap' => 'Test User 3',
                'nisn' => '1234567892',
                'no_hp' => '08123456791',
                'email' => 'test3@example.com',
                'jurusan' => 'Test Major',
            ]
        ]);

        // Try to register when quota is full
        $response = $this->post(route('registration.save-step'), [
            'action' => 'submit',
            'current_step' => 0,
        ]);

        $response->assertRedirect(route('registration.index'));
        $response->assertSessionHas('error', 'Kuota pendaftaran untuk gelombang ini sudah penuh.');

        // Verify no new applicant was created
        $this->assertEquals(2, Applicant::where('wave_id', $this->wave->id)->count());
    }

    /** @test */
    public function it_allows_registration_when_quota_is_available()
    {
        // Create only one applicant (quota is 2)
        Applicant::create([
            'registration_number' => 'TEST-2025-00001',
            'applicant_full_name' => 'Test User 1',
            'applicant_nisn' => '1234567890',
            'applicant_phone_number' => '08123456789',
            'applicant_email_address' => 'test1@example.com',
            'chosen_major_name' => 'Test Major',
            'wave_id' => $this->wave->id,
            'registered_datetime' => now(),
        ]);

        // Set registration data in session
        session([
            'registration_data' => [
                'nama_lengkap' => 'Test User 2',
                'nisn' => '1234567891',
                'no_hp' => '08123456790',
                'email' => 'test2@example.com',
                'jurusan' => 'Test Major',
            ]
        ]);

        // Try to register when quota is available
        $response = $this->post(route('registration.save-step'), [
            'action' => 'submit',
            'current_step' => 0,
        ]);

        // Should redirect to success page
        $response->assertRedirect();
        $this->assertStringContainsString('/daftar/success/', $response->getTargetUrl());

        // Verify new applicant was created
        $this->assertEquals(2, Applicant::where('wave_id', $this->wave->id)->count());
    }

    /** @test */
    public function it_handles_concurrent_registrations_safely()
    {
        // Create one applicant (quota is 2, so only 1 slot left)
        Applicant::create([
            'registration_number' => 'TEST-2025-00001',
            'applicant_full_name' => 'Test User 1',
            'applicant_nisn' => '1234567890',
            'applicant_phone_number' => '08123456789',
            'applicant_email_address' => 'test1@example.com',
            'chosen_major_name' => 'Test Major',
            'wave_id' => $this->wave->id,
            'registered_datetime' => now(),
        ]);

        // Simulate concurrent registrations
        $results = [];
        $exceptions = [];

        // Use database transactions to simulate concurrent access
        for ($i = 0; $i < 3; $i++) {
            try {
                DB::transaction(function () use ($i, &$results) {
                    // Simulate the quota check and registration process
                    $controller = new \App\Http\Controllers\RegistrationController();
                    $reflection = new \ReflectionClass($controller);
                    $method = $reflection->getMethod('checkQuotaAvailability');
                    $method->setAccessible(true);

                    if ($method->invoke($controller, $this->wave)) {
                        // Create applicant if quota is available
                        $applicant = Applicant::create([
                            'registration_number' => 'TEST-2025-' . str_pad($i + 2, 5, '0', STR_PAD_LEFT),
                            'applicant_full_name' => 'Test User ' . ($i + 2),
                            'applicant_nisn' => '123456789' . $i,
                            'applicant_phone_number' => '0812345679' . $i,
                            'applicant_email_address' => 'test' . ($i + 2) . '@example.com',
                            'chosen_major_name' => 'Test Major',
                            'wave_id' => $this->wave->id,
                            'registered_datetime' => now(),
                        ]);
                        $results[] = $applicant->id;
                    }
                });
            } catch (\Exception $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        // Only one additional registration should succeed (total = 2)
        $totalApplicants = Applicant::where('wave_id', $this->wave->id)->count();
        $this->assertEquals(2, $totalApplicants, 'Only 2 applicants should be registered (quota limit)');
        $this->assertCount(1, $results, 'Only 1 concurrent registration should succeed');
    }

    /** @test */
    public function it_allows_unlimited_registration_when_no_quota_limit()
    {
        // Update wave to have no quota limit
        $this->wave->update(['quota_limit' => null]);

        // Create multiple applicants
        for ($i = 1; $i <= 5; $i++) {
            Applicant::create([
                'registration_number' => 'TEST-2025-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'applicant_full_name' => 'Test User ' . $i,
                'applicant_nisn' => '123456789' . $i,
                'applicant_phone_number' => '0812345679' . $i,
                'applicant_email_address' => 'test' . $i . '@example.com',
                'chosen_major_name' => 'Test Major',
                'wave_id' => $this->wave->id,
                'registered_datetime' => now(),
            ]);
        }

        // Set registration data in session
        session([
            'registration_data' => [
                'nama_lengkap' => 'Test User 6',
                'nisn' => '1234567896',
                'no_hp' => '08123456796',
                'email' => 'test6@example.com',
                'jurusan' => 'Test Major',
            ]
        ]);

        // Try to register when no quota limit
        $response = $this->post(route('registration.save-step'), [
            'action' => 'submit',
            'current_step' => 0,
        ]);

        // Should succeed
        $response->assertRedirect();
        $this->assertStringContainsString('/daftar/success/', $response->getTargetUrl());

        // Verify new applicant was created
        $this->assertEquals(6, Applicant::where('wave_id', $this->wave->id)->count());
    }
}
