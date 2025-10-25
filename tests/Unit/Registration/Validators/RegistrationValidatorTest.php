<?php

namespace Tests\Unit\Registration\Validators;

use App\Enum\FormFieldType;
use App\Models\FormField;
use App\Registration\Validators\EmailFieldInspector;
use App\Registration\Validators\RegistrationRuleFactory;
use App\Registration\Validators\RegistrationValidationContext;
use App\Registration\Validators\RegistrationValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RegistrationValidatorTest extends TestCase
{
    use RefreshDatabase;

    private RegistrationValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new RegistrationValidator(
            new RegistrationRuleFactory(),
            new EmailFieldInspector()
        );
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_validated_step_data(): void
    {
        /**
         * Cara kerja: data step valid dengan field teks & email.
         * Target: data tervalidasi sama dengan input yang dipilih.
         */
        $fields = Collection::make([
            FormField::make([
                'field_key' => 'nama',
                'field_label' => 'Nama',
                'field_type' => FormFieldType::TEXT->value,
                'is_required' => true,
            ]),
            FormField::make([
                'field_key' => 'email',
                'field_label' => 'Email',
                'field_type' => FormFieldType::EMAIL->value,
                'is_required' => true,
            ]),
        ]);

        $context = new RegistrationValidationContext([], [
            'nama' => 'Shafly',
            'email' => 'shaflyschool@gmail.com',
        ], 'next', 0);

        $validated = $this->validator->validate($fields, $context, [
            'nama' => 'Shafly',
            'email' => 'shaflyschool@gmail.com',
        ]);

        $this->assertSame('Shafly', $validated['nama']);
        $this->assertSame('shaflyschool@gmail.com', $validated['email']);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_for_invalid_email(): void
    {
        /**
         * Cara kerja: email dengan pola tidak valid.
         * Target: ValidationException dilempar.
         */
        $fields = Collection::make([
            FormField::make([
                'field_key' => 'email',
                'field_label' => 'Email',
                'field_type' => FormFieldType::EMAIL->value,
                'is_required' => true,
            ]),
        ]);

        $context = new RegistrationValidationContext([], ['email' => 'foo..bar@mail.com'], 'next', 0);

        $this->expectException(ValidationException::class);

        $this->validator->validate($fields, $context, ['email' => 'foo..bar@mail.com']);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_accepts_existing_file_path_on_submit(): void
    {
        /**
         * Cara kerja: submit data dengan field file yang sudah bertipe string path.
         * Target: validator mengembalikan path tanpa error.
         */
        $fields = Collection::make([
            FormField::make([
                'field_key' => 'rapor',
                'field_label' => 'Rapor',
                'field_type' => FormFieldType::FILE->value,
                'is_required' => true,
            ]),
        ]);

        $context = new RegistrationValidationContext(
            ['rapor' => 'registration-files/rapor.pdf'],
            ['rapor' => 'registration-files/rapor.pdf'],
            'submit',
            0,
            RegistrationValidationContext::SCENARIO_SUBMIT
        );

        $validated = $this->validator->validate($fields, $context, [
            'rapor' => 'registration-files/rapor.pdf',
        ]);

        $this->assertSame('registration-files/rapor.pdf', $validated['rapor']);
    }
}
