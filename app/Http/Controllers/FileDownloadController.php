<?php

namespace App\Http\Controllers;

use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileDownloadController extends Controller
{
    public function download(Request $request, int $fileId): StreamedResponse
    {
        $file = SubmissionFile::findOrFail($fileId);

        // Check if file exists in storage
        if (!$file->stored_disk_name || !$file->stored_file_path) {
            abort(404, 'File tidak ditemukan.');
        }

        $disk = Storage::disk($file->stored_disk_name);

        if (!$disk->exists($file->stored_file_path)) {
            abort(404, 'File tidak ditemukan di storage.');
        }

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

    public function preview(Request $request, int $fileId)
    {
        $file = SubmissionFile::findOrFail($fileId);

        // Check if file exists in storage
        if (!$file->stored_disk_name || !$file->stored_file_path) {
            abort(404, 'File tidak ditemukan.');
        }

        $disk = Storage::disk($file->stored_disk_name);

        if (!$disk->exists($file->stored_file_path)) {
            abort(404, 'File tidak ditemukan di storage.');
        }

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
