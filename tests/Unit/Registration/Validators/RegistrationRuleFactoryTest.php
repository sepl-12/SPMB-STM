<?php

namespace Tests\Unit\Registration\Validators;

use App\Enum\FormFieldType;
use App\Models\FormField;
use App\Registration\Validators\RegistrationRuleFactory;
use App\Registration\Validators\RegistrationValidationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rules\In as InRule;
use Tests\TestCase;

class RegistrationRuleFactoryTest extends TestCase
{
    use RefreshDatabase;

    private RegistrationRuleFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new RegistrationRuleFactory();
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_builds_required_text_field_rules_for_step_validation(): void
    {
        /**
         * Cara kerja: membuat field teks wajib, memanggil factory dengan context aksi "next".
         * Target: rule mengandung required + string + max, dan pesan required tersedia.
         */
        $field = FormField::make([
            'field_key' => 'nama_lengkap',
            'field_label' => 'Nama Lengkap',
            'field_type' => FormFieldType::TEXT->value,
            'is_required' => true,
        ]);

        $context = new RegistrationValidationContext([], [], 'next', 0);

        $rule = $this->factory->make($field, $context);

        $this->assertContains('required', $rule->rules);
        $this->assertContains('string', $rule->rules);
        $this->assertContains('max:255', $rule->rules);
        $this->assertArrayHasKey('nama_lengkap.required', $rule->messages);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_marks_optional_text_field_nullable_when_moving_previous(): void
    {
        /**
         * Cara kerja: context aksi "previous" membuat factory tidak memaksa validasi.
         * Target: rule hanya berupa nullable.
         */
        $field = FormField::make([
            'field_key' => 'alamat',
            'field_label' => 'Alamat',
            'field_type' => FormFieldType::TEXT->value,
            'is_required' => false,
        ]);

        $context = new RegistrationValidationContext([], [], 'previous', 1);

        $rule = $this->factory->make($field, $context);

        $this->assertSame(['nullable', 'string', 'max:255'], $rule->rules);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_builds_select_rules_with_in_constraint(): void
    {
        /**
         * Cara kerja: membuat field select dengan opsi, memeriksa rule dihasilkan.
         * Target: rule mengandung string + In rule, dan pesan in tersedia.
         */
        $options = [
            ['label' => 'IPA', 'value' => 'ipa'],
            ['label' => 'IPS', 'value' => 'ips'],
        ];

        $field = FormField::make([
            'field_key' => 'jurusan',
            'field_label' => 'Jurusan',
            'field_type' => FormFieldType::SELECT->value,
            'field_options_json' => $options,
            'is_required' => true,
        ]);

        $context = new RegistrationValidationContext([], [], 'next', 0);

        $rule = $this->factory->make($field, $context);

        $this->assertContains('required', $rule->rules);
        $this->assertContains('string', $rule->rules);
        $this->assertTrue(collect($rule->rules)->contains(fn ($rule) => $rule instanceof InRule));
        $this->assertArrayHasKey('jurusan.in', $rule->messages);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_builds_multi_select_rules_with_nested_in_constraint(): void
    {
        /**
         * Cara kerja: field multi select menghasilkan rule array dan rule tambahan untuk setiap item.
         * Target: terdapat rule array dan tambahan field_key.* dengan In rule.
         */
        $field = FormField::make([
            'field_key' => 'ekskul',
            'field_label' => 'Ekskul',
            'field_type' => FormFieldType::MULTI_SELECT->value,
            'field_options_json' => [
                ['label' => 'Basket', 'value' => 'basket'],
                ['label' => 'Futsal', 'value' => 'futsal'],
            ],
            'is_required' => false,
        ]);

        $context = new RegistrationValidationContext([], [], 'next', 0);

        $rule = $this->factory->make($field, $context);

        $this->assertContains('nullable', $rule->rules);
        $this->assertContains('array', $rule->rules);
        $this->assertArrayHasKey('ekskul.*', $rule->additionalRules);
        $this->assertTrue(collect($rule->additionalRules['ekskul.*'])->contains(fn ($rule) => $rule instanceof InRule));
        $this->assertArrayHasKey('ekskul.*.in', $rule->messages);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_allows_existing_file_on_step_when_already_uploaded(): void
    {
        /**
         * Cara kerja: context step memiliki file yang sudah tersimpan, factory tidak mewajibkan upload ulang.
         * Target: rule memuat nullable + file tanpa required.
         */
        $field = FormField::make([
            'field_key' => 'akta_kelahiran',
            'field_label' => 'Akta Kelahiran',
            'field_type' => FormFieldType::FILE->value,
            'is_required' => true,
        ]);

        $context = new RegistrationValidationContext(
            ['akta_kelahiran' => 'registration-files/akta.pdf'],
            [],
            'next',
            1
        );

        $rule = $this->factory->make($field, $context);

        $this->assertSame(['nullable', 'file', 'mimes:pdf,doc,docx,jpeg,png,jpg', 'max:5120'], $rule->rules);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_file_during_submit_when_missing(): void
    {
        /**
         * Cara kerja: context submit tanpa file existing, factory menambahkan required.
         * Target: rule diawali required.
         */
        $field = FormField::make([
            'field_key' => 'rapor',
            'field_label' => 'Rapor',
            'field_type' => FormFieldType::FILE->value,
            'is_required' => true,
        ]);

        $context = new RegistrationValidationContext([], [], 'submit', 0, RegistrationValidationContext::SCENARIO_SUBMIT);

        $rule = $this->factory->make($field, $context);

        $this->assertContains('required', $rule->rules);
    }
}
