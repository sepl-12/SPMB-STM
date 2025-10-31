<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ExamCardFieldConfig;
use App\Services\Applicant\ExamCardDataResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExamCardPreviewController extends Controller
{
    /**
     * Generate preview exam card dengan dummy atau real data
     */
    public function preview(Request $request, ExamCardDataResolver $resolver)
    {
        $applicantId = $request->query('applicant_id');

        // Jika ada applicant_id, gunakan data real
        if ($applicantId) {
            $applicant = Applicant::with(['wave', 'latestSubmission.submissionFiles.formField'])
                ->findOrFail($applicantId);

            // Resolve data menggunakan ExamCardDataResolver
            $data = $resolver->resolve($applicant);

            $filename = 'preview-kartu-tes-' . $applicant->registration_number . '.pdf';
        } else {
            // Gunakan dummy data
            $data = $this->getDummyData();
            $filename = 'preview-kartu-tes-dummy.pdf';
        }

        // Generate PDF
        $pdf = Pdf::loadView('pdf.exam-card', $data);
        $pdf->setPaper('A4', 'portrait');

        // Return PDF untuk preview
        return $pdf->stream($filename);
    }

    /**
     * Generate dummy data untuk preview
     */
    protected function getDummyData(): array
    {
        // Load field configurations
        $fieldConfigs = ExamCardFieldConfig::enabled()->ordered()->get();

        // Generate dummy data untuk preview
        $fields = [];
        foreach ($fieldConfigs as $config) {
            $dummyValue = $this->getDummyValue($config->field_key, $config->field_type);

            $fields[$config->field_key] = [
                'value' => $dummyValue,
                'config' => $config,
            ];
        }

        // Build data array untuk template
        return [
            'fields' => $fields,
            'applicant' => null,

            // Legacy format untuk backward compatibility
            'registration_number' => 'PPDB-2024-001',
            'nisn' => '1234567890',
            'name' => 'CONTOH NAMA PESERTA',
            'birth_place' => 'Jakarta',
            'birth_date' => Carbon::parse('2008-01-15'),
            'address' => 'Jl. Contoh Alamat No. 123, Kelurahan Contoh, Kecamatan Contoh',
            'parent_father' => 'NAMA AYAH CONTOH',
            'parent_mother' => 'NAMA IBU CONTOH',
            'whatsapp_parent' => '081234567890',
            'whatsapp_student' => '081298765432',
            'email' => 'contoh.email@example.com',
            'major_first' => 'Teknik Komputer dan Jaringan',
            'major_second' => 'Rekayasa Perangkat Lunak',
            'major_third' => 'Multimedia',
            'previous_school' => 'SMP Negeri 1 Contoh',
            'exam_date' => Carbon::now()->addDays(7),
            'signature_city' => setting('exam_card_signature_city', 'Sangatta'),
            'signature_day_month' => Carbon::now()->addDays(7)->translatedFormat('d F'),
            'signature_name' => 'CONTOH NAMA PESERTA',
            'photo_path' => null, // No photo for preview
            'signature_image_path' => null, // No signature for preview
        ];
    }

    /**
     * Generate dummy value berdasarkan field key dan type
     */
    protected function getDummyValue(string $fieldKey, string $fieldType): mixed
    {
        // Return dummy values based on field key
        return match ($fieldKey) {
            'registration_number' => 'PPDB-2024-001',
            'nisn' => '1234567890',
            'name' => 'CONTOH NAMA PESERTA',
            'birth_place' => 'Jakarta',
            'birth_date' => Carbon::parse('2008-01-15'),
            'address' => 'Jl. Contoh Alamat No. 123, Kelurahan Contoh, Kecamatan Contoh',
            'parent_father' => 'NAMA AYAH CONTOH',
            'parent_mother' => 'NAMA IBU CONTOH',
            'whatsapp_parent' => '081234567890',
            'whatsapp_student' => '081298765432',
            'email' => 'contoh.email@example.com',
            'major_first' => 'Teknik Komputer dan Jaringan',
            'major_second' => 'Rekayasa Perangkat Lunak',
            'major_third' => 'Multimedia',
            'previous_school' => 'SMP Negeri 1 Contoh',
            'exam_date' => Carbon::now()->addDays(7),
            'signature_date' => Carbon::now()->addDays(7)->translatedFormat('d F'),
            'signature_name' => 'CONTOH NAMA PESERTA',
            'photo' => null,
            'signature_image' => null,
            default => 'Contoh Data',
        };
    }
}
