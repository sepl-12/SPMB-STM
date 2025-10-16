<?php

namespace App\Http\Controllers;

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
}
