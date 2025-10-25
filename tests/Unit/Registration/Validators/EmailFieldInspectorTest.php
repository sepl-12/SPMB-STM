<?php

namespace Tests\Unit\Registration\Validators;

use App\Enum\FormFieldType;
use App\Models\FormField;
use App\Registration\Validators\EmailFieldInspector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class EmailFieldInspectorTest extends TestCase
{
    use RefreshDatabase;

    private EmailFieldInspector $inspector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inspector = new EmailFieldInspector();
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_passes_for_valid_email(): void
    {
        /**
         * Cara kerja: valid email diperiksa oleh inspector.
         * Target: validator tidak menambahkan error baru.
         */
        $validator = Validator::make(['email' => 'shaflyschool@gmail.com'], ['email' => 'string']);
        $fields = Collection::make([
            FormField::make([
                'field_key' => 'email',
                'field_label' => 'Email',
                'field_type' => FormFieldType::EMAIL->value,
            ]),
        ]);

        $this->inspector->inspect($validator, $fields, ['email' => 'shaflyschool@gmail.com']);

        $this->assertFalse($validator->errors()->has('email'));
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_flags_email_with_double_dot(): void
    {
        /**
         * Cara kerja: email dengan pola ".." diperiksa.
         * Target: error ditambahkan dengan pesan custom.
         */
        $validator = Validator::make(['email' => 'foo..bar@mail.com'], ['email' => 'string']);
        $fields = Collection::make([
            FormField::make([
                'field_key' => 'email',
                'field_label' => 'Email',
                'field_type' => FormFieldType::EMAIL->value,
            ]),
        ]);

        $this->inspector->inspect($validator, $fields, ['email' => 'foo..bar@mail.com']);

        $this->assertTrue($validator->errors()->has('email'));
        $this->assertStringContainsString('tidak diizinkan', $validator->errors()->first('email'));
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_rejects_disposable_email_domains(): void
    {
        /**
         * Cara kerja: email domain disposable diperiksa.
         * Target: error domain sementara muncul.
         */
        $validator = Validator::make(['email' => 'fake@tempmail.org'], ['email' => 'string']);
        $fields = Collection::make([
            FormField::make([
                'field_key' => 'email',
                'field_label' => 'Email',
                'field_type' => FormFieldType::EMAIL->value,
            ]),
        ]);

        $this->inspector->inspect($validator, $fields, ['email' => 'fake@tempmail.org']);

        $this->assertTrue($validator->errors()->has('email'));
        $this->assertStringContainsString('tidak boleh menggunakan layanan email sementara', $validator->errors()->first('email'));
    }
}
