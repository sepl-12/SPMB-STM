<?php

namespace Tests\Unit;

use App\Enum\FormFieldType;
use App\Models\FormField;
use App\Services\FormFieldValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FormFieldValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FormFieldValidationService $validationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validationService = new FormFieldValidationService();
    }

    /** @test */
    public function it_validates_valid_email_addresses()
    {
        $field = new FormField([
            'field_key' => 'email',
            'field_label' => 'Email Address',
            'field_type' => FormFieldType::EMAIL->value,
            'is_required' => true,
        ]);

        $validEmails = [
            'test@example.com',
            'user.name@domain.co.id',
            'valid_email123@test-domain.org',
        ];

        foreach ($validEmails as $email) {
            try {
                $result = $this->validationService->validateSingleField($email, $field);
                $this->assertArrayHasKey('email', $result);
                $this->assertEquals(strtolower(trim($email)), $result['email']);
            } catch (ValidationException $e) {
                // If validation fails, let's see the actual error
                $this->fail('Email validation failed for: ' . $email . '. Errors: ' . json_encode($e->validator->errors()->all()));
            }
        }
    }

    /** @test */
    public function it_rejects_invalid_email_addresses()
    {
        $field = new FormField([
            'field_key' => 'email',
            'field_label' => 'Email Address',
            'field_type' => FormFieldType::EMAIL->value,
            'is_required' => true,
        ]);

        $invalidEmails = [
            'invalid-email',
            'test@',
            '@domain.com',
            'test..test@domain.com',
            'test@domain..com',
            'test@@domain.com',
            'test @domain.com',
            '123@domain.com', // starts with numbers only
            str_repeat('a', 65) . '@domain.com', // local part too long
        ];

        foreach ($invalidEmails as $email) {
            $this->expectException(ValidationException::class);
            $this->validationService->validateSingleField($email, $field);
        }
    }

    /** @test */
    public function it_rejects_disposable_email_domains()
    {
        $field = new FormField([
            'field_key' => 'email',
            'field_label' => 'Email Address',
            'field_type' => FormFieldType::EMAIL->value,
            'is_required' => true,
        ]);

        $disposableEmails = [
            'test@10minutemail.com',
            'user@tempmail.org',
            'fake@guerrillamail.com',
        ];

        foreach ($disposableEmails as $email) {
            $this->expectException(ValidationException::class);
            $this->validationService->validateSingleField($email, $field);
        }
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $requiredField = new FormField([
            'field_key' => 'email',
            'field_label' => 'Email Address',
            'field_type' => FormFieldType::EMAIL->value,
            'is_required' => true,
        ]);

        $optionalField = new FormField([
            'field_key' => 'optional_email',
            'field_label' => 'Optional Email',
            'field_type' => FormFieldType::EMAIL->value,
            'is_required' => false,
        ]);

        // Required field should fail with empty value
        $this->expectException(ValidationException::class);
        $this->validationService->validateSingleField('', $requiredField);

        // Optional field should pass with empty value
        $result = $this->validationService->validateSingleField('', $optionalField);
        $this->assertEmpty($result);
    }

    /** @test */
    public function it_validates_text_fields()
    {
        $textField = new FormField([
            'field_key' => 'name',
            'field_label' => 'Full Name',
            'field_type' => FormFieldType::TEXT->value,
            'is_required' => true,
        ]);

        $result = $this->validationService->validateSingleField('John Doe', $textField);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('John Doe', $result['name']);

        // Test max length
        $longText = str_repeat('a', 256);
        $this->expectException(ValidationException::class);
        $this->validationService->validateSingleField($longText, $textField);
    }

    /** @test */
    public function it_validates_number_fields()
    {
        $numberField = new FormField([
            'field_key' => 'age',
            'field_label' => 'Age',
            'field_type' => FormFieldType::NUMBER->value,
            'is_required' => true,
        ]);

        $result = $this->validationService->validateSingleField('25', $numberField);
        $this->assertArrayHasKey('age', $result);
        $this->assertEquals('25', $result['age']);

        // Test invalid number
        $this->expectException(ValidationException::class);
        $this->validationService->validateSingleField('not-a-number', $numberField);
    }
}
