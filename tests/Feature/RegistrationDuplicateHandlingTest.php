<?php

namespace Tests\Feature;

use App\Enum\PaymentMethod;
use App\Enum\PaymentStatus;
use App\Models\Applicant;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormStep;
use App\Models\FormVersion;
use App\Models\Payment;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\Wave;
use App\Registration\Actions\SubmitRegistrationAction;
use App\Registration\Data\RegistrationWizard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RegistrationDuplicateHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected $submitAction;
    protected $wave;
    protected $form;
    protected $formVersion;
    protected $wizard;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->submitAction = app(SubmitRegistrationAction::class);

        // Setup Wave
        $this->wave = Wave::create([
            'wave_name' => 'Gelombang 1',
            'wave_code' => 'G1',
            'start_datetime' => now()->subDays(1),
            'end_datetime' => now()->addDays(7),
            'quota_limit' => 100,
            'registration_fee_amount' => 150000,
            'is_active' => true,
        ]);

        // Setup Form
        $this->form = Form::create([
            'form_name' => 'Formulir Pendaftaran',
            'form_code' => 'FORM-REG-01',
        ]);

        $this->formVersion = FormVersion::create([
            'form_id' => $this->form->id,
            'version_number' => 1,
            'is_active' => true,
            'published_datetime' => now(),
        ]);

        // Setup Step
        $step = FormStep::create([
            'form_version_id' => $this->formVersion->id,
            'step_key' => 'step-1',
            'step_title' => 'Identitas Diri',
            'step_order_number' => 1,
            'is_visible_for_public' => true,
        ]);

        // Setup Fields
        FormField::create([
            'form_version_id' => $this->formVersion->id,
            'form_step_id' => $step->id,
            'field_label' => 'Nama Lengkap',
            'field_key' => 'nama_lengkap',
            'field_type' => 'text',
            'is_required' => true,
            'is_filterable' => true,
            'is_exportable' => true,
            'is_archived' => false,
            'field_order_number' => 1,
        ]);

        FormField::create([
            'form_version_id' => $this->formVersion->id,
            'form_step_id' => $step->id,
            'field_label' => 'Email',
            'field_key' => 'email',
            'field_type' => 'email',
            'is_required' => true,
            'is_filterable' => true,
            'is_exportable' => true,
            'is_archived' => false,
            'field_order_number' => 2,
        ]);

        FormField::create([
            'form_version_id' => $this->formVersion->id,
            'form_step_id' => $step->id,
            'field_label' => 'Pilih Jurusan',
            'field_key' => 'jurusan',
            'field_type' => 'text',
            'is_required' => true,
            'is_filterable' => true,
            'is_exportable' => true,
            'is_archived' => false,
            'field_order_number' => 3,
        ]);

        // Mock Wizard
        $this->wizard = \Mockery::mock(RegistrationWizard::class);
        $this->wizard->shouldReceive('form')->andReturn($this->form);
        $this->wizard->shouldReceive('formVersion')->andReturn($this->formVersion);
    }

    /** @test */
    public function it_creates_new_applicant_successfully()
    {
        $data = [
            'nama_lengkap' => 'Budi Baru',
            'email' => 'budi@example.com',
            'jurusan' => 'RPL'
        ];

        $result = $this->submitAction->execute($this->wizard, $data);

        $this->assertDatabaseCount('applicants', 1);
        $this->assertEquals('budi@example.com', $result->applicant->applicant_email_address);
    }

    /** @test */
    public function it_rejects_duplicate_registration_if_payment_is_success()
    {
        // 1. Create existing applicant
        $applicant = Applicant::create([
            'registration_number' => 'REG-001',
            'applicant_full_name' => 'Sultan',
            'applicant_email_address' => 'sultan@example.com',
            'applicant_nisn' => '123',
            'applicant_phone_number' => '081',
            'chosen_major_name' => 'TKJ',
            'wave_id' => $this->wave->id,
            'registered_datetime' => now()->subDays(2),
        ]);

        // 2. Add successful payment
        Payment::create([
            'applicant_id' => $applicant->id,
            'payment_gateway_name' => 'manual',
            'merchant_order_code' => 'ORD-1',
            'paid_amount_total' => 150000,
            'currency_code' => 'IDR',
            'payment_method_name' => PaymentMethod::MANUAL_TRANSFER->value,
            'payment_status_name' => PaymentStatus::SETTLEMENT->value,
            'status_updated_datetime' => now(),
        ]);

        // 3. Try to register again with same email
        $data = [
            'nama_lengkap' => 'Sultan Lagi',
            'email' => 'sultan@example.com',
            'jurusan' => 'RPL'
        ];

        $this->expectException(ValidationException::class);
        
        try {
            $this->submitAction->execute($this->wizard, $data);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
            throw $e;
        }
    }

    /** @test */
    public function it_rejects_duplicate_registration_if_payment_is_pending()
    {
        // 1. Create existing applicant
        $applicant = Applicant::create([
            'registration_number' => 'REG-002',
            'applicant_full_name' => 'Pending User',
            'applicant_email_address' => 'pending@example.com',
            'applicant_nisn' => '123',
            'applicant_phone_number' => '081',
            'chosen_major_name' => 'TKJ',
            'wave_id' => $this->wave->id,
            'registered_datetime' => now()->subDays(2),
        ]);

        // 2. Add pending payment
        Payment::create([
            'applicant_id' => $applicant->id,
            'payment_gateway_name' => 'midtrans',
            'merchant_order_code' => 'ORD-2',
            'paid_amount_total' => 150000,
            'currency_code' => 'IDR',
            'payment_method_name' => PaymentMethod::QRIS->value,
            'payment_status_name' => PaymentStatus::PENDING->value,
            'status_updated_datetime' => now(),
        ]);

        // 3. Try to register again
        $data = [
            'nama_lengkap' => 'Pending Lagi',
            'email' => 'pending@example.com',
            'jurusan' => 'RPL'
        ];

        $this->expectException(ValidationException::class);
        $this->submitAction->execute($this->wizard, $data);
    }

    /** @test */
    public function it_updates_existing_applicant_if_no_payment_or_expired()
    {
        // 1. Create existing applicant
        $oldDate = now()->subDays(5);
        $applicant = Applicant::create([
            'registration_number' => 'REG-OLD',
            'applicant_full_name' => 'Nama Lama',
            'applicant_email_address' => 'renew@example.com',
            'applicant_nisn' => '111',
            'applicant_phone_number' => '081',
            'chosen_major_name' => 'TKJ',
            'wave_id' => $this->wave->id,
            'registered_datetime' => $oldDate,
        ]);

        // Add EXPIRED payment
        Payment::create([
            'applicant_id' => $applicant->id,
            'payment_gateway_name' => 'midtrans',
            'merchant_order_code' => 'ORD-EXP',
            'paid_amount_total' => 150000,
            'currency_code' => 'IDR',
            'payment_method_name' => PaymentMethod::QRIS->value,
            'payment_status_name' => PaymentStatus::EXPIRE->value,
            'status_updated_datetime' => $oldDate,
        ]);

        // 2. Try to register again
        $data = [
            'nama_lengkap' => 'Nama Baru', // Name changed
            'email' => 'renew@example.com', // Same email
            'jurusan' => 'RPL' // Major changed
        ];

        $result = $this->submitAction->execute($this->wizard, $data);

        // Assertions
        $this->assertDatabaseCount('applicants', 1);
        
        $applicant->refresh();
        $this->assertEquals('Nama Baru', $applicant->applicant_full_name);
        $this->assertEquals('RPL', $applicant->chosen_major_name);
        $this->assertTrue($applicant->registered_datetime->greaterThan($oldDate));
        
        // Should have a new submission
        $this->assertDatabaseCount('submissions', 1);
        $this->assertEquals('Nama Baru', $result->submission->answers_json['nama_lengkap']);
    }

    /** @test */
    public function it_deletes_physical_file_when_submission_file_is_deleted()
    {
        Storage::fake('private');

        // Setup Dependencies for FK
        $applicant = Applicant::create([
            'registration_number' => 'REG-FILE',
            'applicant_full_name' => 'File Owner',
            'applicant_email_address' => 'file@example.com',
            'applicant_nisn' => '111',
            'applicant_phone_number' => '081',
            'chosen_major_name' => 'TKJ',
            'wave_id' => $this->wave->id,
            'registered_datetime' => now(),
        ]);

        $submission = Submission::create([
            'applicant_id' => $applicant->id,
            'form_id' => $this->form->id,
            'form_version_id' => $this->formVersion->id,
            'answers_json' => [],
            'submitted_datetime' => now(),
        ]);

        $field = FormField::first();

        // 1. Create dummy file
        $file = UploadedFile::fake()->image('ijazah.jpg');
        $path = $file->store('documents', 'private');

        // 2. Create DB record
        $submissionFile = new SubmissionFile();
        $submissionFile->submission_id = $submission->id;
        $submissionFile->form_field_id = $field->id;
        $submissionFile->stored_disk_name = 'private';
        $submissionFile->stored_file_path = $path;
        $submissionFile->original_file_name = 'ijazah.jpg';
        $submissionFile->mime_type_name = 'image/jpeg';
        $submissionFile->file_size_bytes = 1000;
        $submissionFile->uploaded_datetime = now();
        $submissionFile->save();

        // Verify file exists
        Storage::disk('private')->assertExists($path);

        // 3. Delete DB record
        $submissionFile->delete();

        // 4. Verify file deleted
        Storage::disk('private')->assertMissing($path);
    }
}
