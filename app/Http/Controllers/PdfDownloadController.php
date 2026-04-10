<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class PdfDownloadController extends Controller
{
    public function download(Request $request, $filename)
    {
        // Remove pdfs/ prefix if present, security check
        $cleanFilename = str_replace('pdfs/', '', $filename);
        if (!Str::endsWith($cleanFilename, '.pdf') || Str::contains($cleanFilename, '../') || Str::contains($cleanFilename, '..\\')) {
            abort(404, 'Fichier invalide');
        }

        $fullPath = storage_path('app/public/pdfs/' . $cleanFilename);
        
        if (!file_exists($fullPath)) {
            abort(404, 'PDF not found');
        }

        return Response::download($fullPath, basename($filename), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . basename($filename) . '"',
        ]);
    }

    public function stream(Request $request, $filename)
    {
        // Same security
        $cleanFilename = str_replace('pdfs/', '', $filename);
        if (!Str::endsWith($cleanFilename, '.pdf') || Str::contains($cleanFilename, '../') || Str::contains($cleanFilename, '..\\')) {
            abort(404, 'Fichier invalide');
        }

        $fullPath = storage_path('app/public/pdfs/' . $cleanFilename);
        
        if (!file_exists($fullPath)) {
            abort(404, 'PDF not found');
        }

        return Response::file($fullPath, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
