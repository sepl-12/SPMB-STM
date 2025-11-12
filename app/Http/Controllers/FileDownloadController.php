<?php

namespace App\Http\Controllers;

use App\Models\ManualPayment;
use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileDownloadController extends Controller
{
    /**
     * Download file with signed URL verification and UUID
     */
    public function download(Request $request, SubmissionFile $file): StreamedResponse
    {
        // Verify signed URL signature
        if (!$request->hasValidSignature()) {
            abort(401, 'Link download tidak valid atau sudah expired. Silakan generate link baru.');
        }

        // Check if file exists in storage
        if (!$file->stored_disk_name || !$file->stored_file_path) {
            abort(404, 'File tidak ditemukan.');
        }

        $disk = Storage::disk($file->stored_disk_name);

        if (!$disk->exists($file->stored_file_path)) {
            abort(404, 'File tidak ditemukan di storage.');
        }

        // Audit log - track file access
        Log::info('File downloaded', [
            'file_uuid' => $file->uuid,
            'file_name' => $file->original_file_name,
            'submission_id' => $file->submission_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);

        // Force download with proper headers
        return $disk->download(
            $file->stored_file_path,
            $file->original_file_name,
            [
                'Content-Type' => $file->mime_type_name,
                'Content-Disposition' => 'attachment; filename="' . $file->original_file_name . '"',
            ]
        );
    }

    /**
     * Preview file inline with signed URL verification and UUID
     */
    public function preview(Request $request, SubmissionFile $file)
    {
        // Verify signed URL signature
        if (!$request->hasValidSignature()) {
            abort(401, 'Link preview tidak valid atau sudah expired. Silakan generate link baru.');
        }

        // Check if file exists in storage
        if (!$file->stored_disk_name || !$file->stored_file_path) {
            abort(404, 'File tidak ditemukan.');
        }

        $disk = Storage::disk($file->stored_disk_name);

        if (!$disk->exists($file->stored_file_path)) {
            abort(404, 'File tidak ditemukan di storage.');
        }

        // Audit log - track file access
        Log::info('File previewed', [
            'file_uuid' => $file->uuid,
            'file_name' => $file->original_file_name,
            'submission_id' => $file->submission_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);

        // Return file for inline viewing
        return response()->file(
            $disk->path($file->stored_file_path),
            [
                'Content-Type' => $file->mime_type_name,
                'Content-Disposition' => 'inline; filename="' . $file->original_file_name . '"',
            ]
        );
    }

    /**
     * View manual payment proof image (admin only, no signature required)
     * This is for Filament admin panel image display
     */
    public function viewManualPaymentProof(Request $request, ManualPayment $manualPayment)
    {
        // Only allow authenticated admin users
        if (!auth()->check()) {
            abort(403, 'Unauthorized access.');
        }

        // Check if proof image exists
        if (!$manualPayment->proof_image_path) {
            abort(404, 'Bukti pembayaran tidak ditemukan.');
        }

        $disk = Storage::disk('private');

        if (!$disk->exists($manualPayment->proof_image_path)) {
            abort(404, 'File tidak ditemukan di storage.');
        }

        // Audit log - track access
        Log::info('Manual payment proof viewed', [
            'manual_payment_id' => $manualPayment->id,
            'applicant_id' => $manualPayment->applicant_id,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'timestamp' => now()->toISOString(),
        ]);

        // Get file info
        $filePath = $disk->path($manualPayment->proof_image_path);
        $mimeType = $disk->mimeType($manualPayment->proof_image_path);
        $fileName = basename($manualPayment->proof_image_path);

        // Return file for inline viewing
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }
}
