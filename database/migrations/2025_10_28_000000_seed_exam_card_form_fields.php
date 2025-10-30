<?php

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormStep;
use App\Models\FormVersion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected string $formCode = 'ppdb-smk';

    protected array $systemFieldKeys = [
        'nama_lengkap',
        'full_name',
        'nisn',
        'no_hp',
        'phone',
        'telepon',
        'email',
        'email_address',
        'jurusan',
        'major',
        'program_studi',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $form = Form::query()->firstOrCreate(
                ['form_code' => $this->formCode],
                ['form_name' => 'Formulir PPDB SMK']
            );

            $version = FormVersion::query()->firstOrCreate(
                [
                    'form_id' => $form->id,
                    'version_number' => 1,
                ],
                [
                    'is_active' => true,
                    'published_datetime' => now(),
                ]
            );

            // Ensure active flag
            if (! $version->is_active) {
                $version->update(['is_active' => true]);
            }

            $dataSiswaStep = $this->ensureStep($version, [
                'step_key' => 'data_siswa',
                'step_title' => 'Data Siswa',
                'step_description' => 'Lengkapi data dasar siswa untuk kartu tes.',
                'step_order_number' => 1,
            ]);

            $dataOrtuStep = $this->ensureStep($version, [
                'step_key' => 'data_orang_tua',
                'step_title' => 'Data Orang Tua / Wali',
                'step_description' => 'Masukkan data orang tua atau wali beserta kontak yang dapat dihubungi.',
                'step_order_number' => 2,
            ]);

            $pilihanStep = $this->ensureStep($version, [
                'step_key' => 'pembayaran',
                'step_title' => 'Pilihan Jurusan & Konfirmasi',
                'step_description' => 'Pilih jurusan dan lengkapi informasi tambahan untuk kartu tes.',
                'step_order_number' => 4,
            ]);

            $jurusanOptions = [
                ['label' => 'Teknik Komputer dan Jaringan (TKJ)', 'value' => 'TKJ'],
                ['label' => 'Rekayasa Perangkat Lunak (RPL)', 'value' => 'RPL'],
                ['label' => 'Multimedia (MM)', 'value' => 'MM'],
                ['label' => 'Teknik Kendaraan Ringan (TKR)', 'value' => 'TKR'],
                ['label' => 'Akuntansi (AK)', 'value' => 'AK'],
            ];

            // Step: Data Siswa
            $this->ensureField($version, $dataSiswaStep, [
                'field_key' => 'nama_lengkap',
                'field_label' => 'Nama Lengkap',
                'field_type' => 'text',
                'field_placeholder_text' => 'Nama lengkap sesuai dokumen resmi',
                'field_help_text' => 'Isi nama lengkap sesuai akta kelahiran atau ijazah.',
                'is_required' => true,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 1,
            ]);

            $this->ensureField($version, $dataSiswaStep, [
                'field_key' => 'nisn',
                'field_label' => 'NISN',
                'field_type' => 'text',
                'field_placeholder_text' => 'Nomor Induk Siswa Nasional',
                'field_help_text' => 'Masukkan NISN 10 digit.',
                'is_required' => true,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 2,
            ]);

            $this->ensureField($version, $dataSiswaStep, [
                'field_key' => 'tempat_lahir',
                'field_label' => 'Tempat Lahir',
                'field_type' => 'text',
                'field_placeholder_text' => 'Kota / Kabupaten',
                'is_required' => true,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 3,
            ]);

            $this->ensureField($version, $dataSiswaStep, [
                'field_key' => 'tanggal_lahir',
                'field_label' => 'Tanggal Lahir',
                'field_type' => 'date',
                'field_placeholder_text' => null,
                'is_required' => true,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 4,
            ]);

            $this->ensureField($version, $dataSiswaStep, [
                'field_key' => 'alamat',
                'field_label' => 'Alamat Lengkap',
                'field_type' => 'textarea',
                'field_placeholder_text' => 'Alamat tempat tinggal saat ini',
                'is_required' => true,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 5,
            ]);

            // Step: Data Orang Tua / Wali
            $this->ensureField($version, $dataOrtuStep, [
                'field_key' => 'nama_ayah',
                'field_label' => 'Nama Ayah',
                'field_type' => 'text',
                'field_placeholder_text' => 'Nama lengkap ayah kandung / wali',
                'is_required' => true,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 1,
            ]);

            $this->ensureField($version, $dataOrtuStep, [
                'field_key' => 'nama_ibu',
                'field_label' => 'Nama Ibu',
                'field_type' => 'text',
                'field_placeholder_text' => 'Nama lengkap ibu kandung / wali',
                'is_required' => true,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 2,
            ]);

            $this->ensureField($version, $dataOrtuStep, [
                'field_key' => 'no_hp',
                'field_label' => 'No. HP Orang Tua',
                'field_type' => 'tel',
                'field_placeholder_text' => '08xxxxxxxxxx',
                'field_help_text' => 'Nomor HP/WA orang tua atau wali yang dapat dihubungi.',
                'is_required' => true,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 3,
            ]);

            $this->ensureField($version, $dataOrtuStep, [
                'field_key' => 'no_hp_siswa',
                'field_label' => 'No. HP / WA Siswa',
                'field_type' => 'tel',
                'field_placeholder_text' => 'Contoh: 08xxxxxxxxxx',
                'field_help_text' => 'Nomor WhatsApp siswa untuk pengiriman informasi kartu tes.',
                'is_required' => false,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 4,
            ]);

            $this->ensureField($version, $dataOrtuStep, [
                'field_key' => 'email',
                'field_label' => 'Email',
                'field_type' => 'email',
                'field_placeholder_text' => 'email@contoh.com',
                'is_required' => false,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 5,
            ]);

            // Step: Pilihan Jurusan & Konfirmasi
            $this->ensureField($version, $pilihanStep, [
                'field_key' => 'jurusan',
                'field_label' => 'Pilihan Jurusan 1 (Utama)',
                'field_type' => 'select',
                'field_options_json' => $jurusanOptions,
                'field_placeholder_text' => 'Pilih jurusan utama',
                'is_required' => true,
                'is_filterable' => true,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 1,
            ]);

            $this->ensureField($version, $pilihanStep, [
                'field_key' => 'pilihan_jurusan_2',
                'field_label' => 'Pilihan Jurusan 2',
                'field_type' => 'select',
                'field_options_json' => $jurusanOptions,
                'field_placeholder_text' => 'Pilih jurusan cadangan',
                'is_required' => false,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 2,
            ]);

            $this->ensureField($version, $pilihanStep, [
                'field_key' => 'pilihan_jurusan_3',
                'field_label' => 'Pilihan Jurusan 3',
                'field_type' => 'select',
                'field_options_json' => $jurusanOptions,
                'field_placeholder_text' => 'Pilih jurusan cadangan lainnya',
                'is_required' => false,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 3,
            ]);

            $this->ensureField($version, $pilihanStep, [
                'field_key' => 'asal_sekolah',
                'field_label' => 'Asal Sekolah',
                'field_type' => 'text',
                'field_placeholder_text' => 'Nama sekolah asal (SMP/MTs)',
                'is_required' => true,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 4,
            ]);

            $this->ensureField($version, $pilihanStep, [
                'field_key' => 'tanggal_tes',
                'field_label' => 'Tanggal Tes',
                'field_type' => 'date',
                'field_placeholder_text' => null,
                'field_help_text' => 'Isi jika jadwal tes telah ditentukan.',
                'is_required' => false,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 5,
            ]);

            $this->ensureField($version, $pilihanStep, [
                'field_key' => 'pas_foto',
                'field_label' => 'Pas Foto 3x4',
                'field_type' => 'image',
                'field_placeholder_text' => null,
                'field_help_text' => 'Unggah pas foto berwarna dengan latar belakang polos. Format jpg/png maks 5MB.',
                'is_required' => true,
                'is_filterable' => false,
                'is_exportable' => false,
                'is_archived' => false,
                'field_order_number' => 6,
            ]);

            $this->ensureField($version, $pilihanStep, [
                'field_key' => 'tanda_tangan_peserta',
                'field_label' => 'Tanda Tangan Peserta',
                'field_type' => 'signature',
                'field_placeholder_text' => null,
                'field_help_text' => 'Tandatangani kotak menggunakan kursor atau layar sentuh.',
                'is_required' => true,
                'is_filterable' => false,
                'is_exportable' => false,
                'is_archived' => false,
                'field_order_number' => 7,
            ]);

            $this->ensureField($version, $pilihanStep, [
                'field_key' => 'persetujuan',
                'field_label' => 'Saya menyatakan bahwa data yang saya isi adalah benar dan dapat dipertanggungjawabkan',
                'field_type' => 'checkbox',
                'is_required' => true,
                'is_filterable' => false,
                'is_exportable' => true,
                'is_archived' => false,
                'field_order_number' => 8,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(function () {
            $form = Form::query()->where('form_code', $this->formCode)->first();

            if (! $form) {
                return;
            }

            $version = $form->formVersions()->where('version_number', 1)->first();

            if (! $version) {
                return;
            }

            $fieldsToDelete = [
                'no_hp_siswa',
                'pilihan_jurusan_2',
                'pilihan_jurusan_3',
                'tanggal_tes',
                'pas_foto',
                'tanda_tangan_peserta',
            ];

            FormField::query()
                ->where('form_version_id', $version->id)
                ->whereIn('field_key', $fieldsToDelete)
                ->delete();

            FormField::query()
                ->where('form_version_id', $version->id)
                ->where('field_key', 'jurusan')
                ->update([
                    'field_label' => 'Jurusan yang Dipilih',
                    'field_placeholder_text' => 'Pilih jurusan',
                    'field_order_number' => 1,
                ]);

            FormField::query()
                ->where('form_version_id', $version->id)
                ->where('field_key', 'asal_sekolah')
                ->update(['field_order_number' => 2]);

            FormField::query()
                ->where('form_version_id', $version->id)
                ->where('field_key', 'persetujuan')
                ->update(['field_order_number' => 3]);

            FormField::query()
                ->where('form_version_id', $version->id)
                ->where('field_key', 'email')
                ->update(['field_order_number' => 6]);
        });
    }

    protected function ensureStep(FormVersion $version, array $attributes): FormStep
    {
        $step = FormStep::query()->firstOrCreate(
            [
                'form_version_id' => $version->id,
                'step_key' => $attributes['step_key'],
            ],
            [
                'step_title' => $attributes['step_title'],
                'step_description' => $attributes['step_description'] ?? null,
                'step_order_number' => $attributes['step_order_number'] ?? 1,
                'is_visible_for_public' => true,
            ]
        );

        $step->fill([
            'step_title' => $attributes['step_title'],
            'step_description' => $attributes['step_description'] ?? null,
            'step_order_number' => $attributes['step_order_number'] ?? $step->step_order_number,
            'is_visible_for_public' => $attributes['is_visible_for_public'] ?? $step->is_visible_for_public,
        ])->save();

        return $step;
    }

    protected function ensureField(FormVersion $version, FormStep $step, array $attributes): FormField
    {
        $payload = [
            'form_step_id' => $step->id,
            'field_label' => $attributes['field_label'],
            'field_type' => $attributes['field_type'],
            'field_placeholder_text' => $attributes['field_placeholder_text'] ?? null,
            'field_help_text' => $attributes['field_help_text'] ?? null,
            'field_options_json' => $attributes['field_options_json'] ?? null,
            'is_required' => $attributes['is_required'] ?? false,
            'is_filterable' => $attributes['is_filterable'] ?? false,
            'is_exportable' => $attributes['is_exportable'] ?? true,
            'is_archived' => $attributes['is_archived'] ?? false,
            'field_order_number' => $attributes['field_order_number'] ?? 1,
            'is_system_field' => in_array($attributes['field_key'], $this->systemFieldKeys, true),
        ];

        return FormField::query()->updateOrCreate(
            [
                'form_version_id' => $version->id,
                'field_key' => $attributes['field_key'],
            ],
            $payload
        );
    }
};
