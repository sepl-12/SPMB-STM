<?php

namespace Database\Seeders;

use App\Models\ExportTemplate;
use App\Models\ExportTemplateColumn;
use App\Models\Form;
use Illuminate\Database\Seeder;

class ExportTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $form = Form::first();

        if (!$form) {
            $this->command->warn('Tidak ada formulir. Lewati seeding template ekspor.');
            return;
        }

        // Hapus template lama jika ada
        ExportTemplate::where('form_id', $form->id)->delete();

        // Buat template default
        $template = ExportTemplate::create([
            'form_id' => $form->id,
            'template_name' => 'Rekap Lengkap Pendaftar',
            'template_description' => 'Template ekspor lengkap dengan semua data pendaftar',
            'is_default' => true,
        ]);

        // Kolom-kolom standar
        $columns = [
            [
                'column_order_number' => 1,
                'column_header_label' => 'No. Registrasi',
                'source_type_name' => 'expression',
                'source_key_name' => 'registration_number',
                'column_format_hint' => null,
            ],
            [
                'column_order_number' => 2,
                'column_header_label' => 'Gelombang',
                'source_type_name' => 'expression',
                'source_key_name' => 'wave.wave_name',
                'column_format_hint' => null,
            ],
            [
                'column_order_number' => 3,
                'column_header_label' => 'Tahun',
                'source_type_name' => 'expression',
                'source_key_name' => 'wave.year',
                'column_format_hint' => null,
            ],
            [
                'column_order_number' => 4,
                'column_header_label' => 'Tanggal Daftar',
                'source_type_name' => 'expression',
                'source_key_name' => 'registered_datetime',
                'column_format_hint' => 'datetime',
            ],
        ];

        // Tambahkan kolom dari form fields yang is_exportable
        $version = $form->activeFormVersion()->first() ?? $form->ensureActiveVersion();
        
        if ($version) {
            $exportableFields = $version->formFields()
                ->where('is_exportable', true)
                ->where('is_archived', false)
                ->orderBy('field_order_number')
                ->get();

            $orderNumber = 5;
            foreach ($exportableFields as $field) {
                $columns[] = [
                    'column_order_number' => $orderNumber++,
                    'column_header_label' => $field->field_label,
                    'source_type_name' => 'form_field',
                    'source_key_name' => $field->field_key,
                    'column_format_hint' => $this->getFormatHintForFieldType($field->field_type),
                ];
            }
        }

        // Insert kolom
        foreach ($columns as $columnData) {
            ExportTemplateColumn::create([
                'export_template_id' => $template->id,
                ...$columnData,
            ]);
        }

        $this->command->info('✓ Template ekspor berhasil di-seed dengan ' . count($columns) . ' kolom');
    }

    private function getFormatHintForFieldType(string $fieldType): ?string
    {
        return match ($fieldType) {
            'date' => 'date',
            'email' => 'lowercase',
            'number' => 'number',
            default => null,
        };
    }
}
