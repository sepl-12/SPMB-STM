<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormVersion;
use App\Models\FormStep;
use App\Models\FormField;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main form
        $form = Form::create([
            'form_name' => 'Formulir PPDB SMK',
            'form_code' => 'ppdb-smk',
        ]);

        // Create form version
        $version = FormVersion::create([
            'form_id' => $form->id,
            'version_number' => 1,
            'is_active' => true,
            'published_datetime' => now(),
        ]);

        // Step 1: Data Diri Siswa
        $step1 = FormStep::create([
            'form_version_id' => $version->id,
            'step_key' => 'data_siswa',
            'step_title' => 'Data Siswa',
            'step_description' => 'Masukkan informasi pribadi calon siswa.',
            'step_order_number' => 1,
            'is_visible_for_public' => true,
        ]);

        $fields1 = [
            [
                'field_key' => 'nama_lengkap',
                'field_label' => 'Nama Lengkap',
                'field_type' => 'text',
                'is_required' => true,
                'field_placeholder_text' => 'Nama lengkap sesuai akta',
                'field_help_text' => 'Tulis nama lengkap sesuai dengan akta kelahiran atau ijazah',
            ],
            [
                'field_key' => 'nisn',
                'field_label' => 'NISN',
                'field_type' => 'text',
                'is_required' => true,
                'field_placeholder_text' => 'Nomor Induk Siswa Nasional',
            ],
            [
                'field_key' => 'tempat_lahir',
                'field_label' => 'Tempat Lahir',
                'field_type' => 'text',
                'is_required' => true,
                'field_placeholder_text' => 'Kota tempat lahir',
            ],
            [
                'field_key' => 'tanggal_lahir',
                'field_label' => 'Tanggal Lahir',
                'field_type' => 'date',
                'is_required' => true,
            ],
            [
                'field_key' => 'jenis_kelamin',
                'field_label' => 'Jenis Kelamin',
                'field_type' => 'radio',
                'is_required' => true,
                'field_options_json' => [
                    ['label' => 'Laki-laki', 'value' => 'L'],
                    ['label' => 'Perempuan', 'value' => 'P'],
                ],
            ],
            [
                'field_key' => 'agama',
                'field_label' => 'Agama',
                'field_type' => 'select',
                'is_required' => true,
                'field_placeholder_text' => 'Pilih agama',
                'field_options_json' => [
                    ['label' => 'Islam', 'value' => 'Islam'],
                    ['label' => 'Kristen Protestan', 'value' => 'Kristen Protestan'],
                    ['label' => 'Kristen Katolik', 'value' => 'Kristen Katolik'],
                    ['label' => 'Hindu', 'value' => 'Hindu'],
                    ['label' => 'Buddha', 'value' => 'Buddha'],
                    ['label' => 'Konghucu', 'value' => 'Konghucu'],
                ],
            ],
            [
                'field_key' => 'alamat',
                'field_label' => 'Alamat Lengkap',
                'field_type' => 'textarea',
                'is_required' => true,
                'field_placeholder_text' => 'Alamat tempat tinggal saat ini',
            ],
        ];

        foreach ($fields1 as $index => $fieldData) {
            FormField::create(array_merge($fieldData, [
                'form_version_id' => $version->id,
                'form_step_id' => $step1->id,
                'field_order_number' => $index + 1,
                'is_filterable' => in_array($fieldData['field_key'], ['jenis_kelamin', 'agama']),
                'is_exportable' => true,
                'is_archived' => false,
            ]));
        }

        // Step 2: Data Orang Tua
        $step2 = FormStep::create([
            'form_version_id' => $version->id,
            'step_key' => 'data_orang_tua',
            'step_title' => 'Data Orang Tua',
            'step_description' => 'Masukkan data orang tua atau wali.',
            'step_order_number' => 2,
            'is_visible_for_public' => true,
        ]);

        $fields2 = [
            [
                'field_key' => 'nama_ayah',
                'field_label' => 'Nama Ayah',
                'field_type' => 'text',
                'is_required' => true,
                'field_placeholder_text' => 'Nama lengkap ayah',
            ],
            [
                'field_key' => 'pekerjaan_ayah',
                'field_label' => 'Pekerjaan Ayah',
                'field_type' => 'text',
                'is_required' => true,
                'field_placeholder_text' => 'Pekerjaan ayah',
            ],
            [
                'field_key' => 'nama_ibu',
                'field_label' => 'Nama Ibu',
                'field_type' => 'text',
                'is_required' => true,
                'field_placeholder_text' => 'Nama lengkap ibu',
            ],
            [
                'field_key' => 'pekerjaan_ibu',
                'field_label' => 'Pekerjaan Ibu',
                'field_type' => 'text',
                'is_required' => true,
                'field_placeholder_text' => 'Pekerjaan ibu',
            ],
            [
                'field_key' => 'no_hp',
                'field_label' => 'No. HP Orang Tua',
                'field_type' => 'tel',
                'is_required' => true,
                'field_placeholder_text' => '08xxxxxxxxxx',
                'field_help_text' => 'Nomor HP yang dapat dihubungi',
            ],
            [
                'field_key' => 'email',
                'field_label' => 'Email',
                'field_type' => 'email',
                'is_required' => false,
                'field_placeholder_text' => 'email@contoh.com',
            ],
        ];

        foreach ($fields2 as $index => $fieldData) {
            FormField::create(array_merge($fieldData, [
                'form_version_id' => $version->id,
                'form_step_id' => $step2->id,
                'field_order_number' => $index + 1,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
            ]));
        }

        // Step 3: Upload Berkas
        $step3 = FormStep::create([
            'form_version_id' => $version->id,
            'step_key' => 'upload_berkas',
            'step_title' => 'Upload Berkas',
            'step_description' => 'Upload dokumen persyaratan pendaftaran.',
            'step_order_number' => 3,
            'is_visible_for_public' => true,
        ]);

        $fields3 = [
            [
                'field_key' => 'foto_siswa',
                'field_label' => 'Pas Foto 3x4',
                'field_type' => 'image',
                'is_required' => true,
                'field_help_text' => 'Format: JPG, PNG. Maksimal 2MB',
            ],
            [
                'field_key' => 'ijazah',
                'field_label' => 'Ijazah/SKHUN',
                'field_type' => 'file',
                'is_required' => true,
                'field_help_text' => 'Format: PDF. Maksimal 2MB',
            ],
            [
                'field_key' => 'kartu_keluarga',
                'field_label' => 'Kartu Keluarga',
                'field_type' => 'file',
                'is_required' => true,
                'field_help_text' => 'Format: PDF. Maksimal 2MB',
            ],
            [
                'field_key' => 'akta_kelahiran',
                'field_label' => 'Akta Kelahiran',
                'field_type' => 'file',
                'is_required' => false,
                'field_help_text' => 'Format: PDF. Maksimal 2MB (Opsional)',
            ],
        ];

        foreach ($fields3 as $index => $fieldData) {
            FormField::create(array_merge($fieldData, [
                'form_version_id' => $version->id,
                'form_step_id' => $step3->id,
                'field_order_number' => $index + 1,
                'is_filterable' => false,
                'is_exportable' => false,
                'is_archived' => false,
            ]));
        }

        // Step 4: Pembayaran
        $step4 = FormStep::create([
            'form_version_id' => $version->id,
            'step_key' => 'pembayaran',
            'step_title' => 'Pembayaran',
            'step_description' => 'Informasi pembayaran dan konfirmasi.',
            'step_order_number' => 4,
            'is_visible_for_public' => true,
        ]);

        $fields4 = [
            [
                'field_key' => 'jurusan',
                'field_label' => 'Jurusan yang Dipilih',
                'field_type' => 'select',
                'is_required' => true,
                'field_placeholder_text' => 'Pilih jurusan',
                'field_options_json' => [
                    ['label' => 'Teknik Komputer dan Jaringan (TKJ)', 'value' => 'TKJ'],
                    ['label' => 'Rekayasa Perangkat Lunak (RPL)', 'value' => 'RPL'],
                    ['label' => 'Multimedia (MM)', 'value' => 'MM'],
                    ['label' => 'Teknik Kendaraan Ringan (TKR)', 'value' => 'TKR'],
                    ['label' => 'Akuntansi (AK)', 'value' => 'AK'],
                ],
            ],
            [
                'field_key' => 'asal_sekolah',
                'field_label' => 'Asal Sekolah',
                'field_type' => 'text',
                'is_required' => true,
                'field_placeholder_text' => 'Nama sekolah asal (SMP/MTs)',
            ],
            [
                'field_key' => 'persetujuan',
                'field_label' => 'Saya menyatakan bahwa data yang saya isi adalah benar dan dapat dipertanggungjawabkan',
                'field_type' => 'checkbox',
                'is_required' => true,
            ],
        ];

        foreach ($fields4 as $index => $fieldData) {
            FormField::create(array_merge($fieldData, [
                'form_version_id' => $version->id,
                'form_step_id' => $step4->id,
                'field_order_number' => $index + 1,
                'is_filterable' => $fieldData['field_key'] === 'jurusan',
                'is_exportable' => true,
                'is_archived' => false,
            ]));
        }

        $this->command->info('Form with steps and fields created successfully!');
    }
}
