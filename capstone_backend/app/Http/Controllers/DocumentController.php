<?php

namespace App\Http\Controllers;

use App\Models\CharityDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Get document URL for viewing
     */
    public function view(CharityDocument $document)
    {
        // Check if file exists
        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json([
                'error' => 'Document file not found'
            ], 404);
        }

        // Return the file URL
        return response()->json([
            'fileUrl' => $document->file_path,
            'fileName' => $document->doc_type,
            'fileType' => pathinfo($document->file_path, PATHINFO_EXTENSION),
        ]);
    }

    /**
     * Download document
     */
    public function download(CharityDocument $document)
    {
        // Check if file exists
        $filePath = storage_path('app/public/' . $document->file_path);
        
        if (!file_exists($filePath)) {
            return response()->json([
                'error' => 'Document file not found'
            ], 404);
        }

        // Get file extension
        $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
        $fileName = $document->doc_type . '.' . $extension;

        // Return file download
        return response()->download(
            $filePath,
            $fileName,
            [
                'Content-Type' => $this->getMimeType($extension),
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]
        );
    }

    /**
     * Get MIME type based on file extension
     */
    private function getMimeType($extension)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
