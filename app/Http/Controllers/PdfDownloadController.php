<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PdfDownloadController extends Controller
{
    public function download(Request $request, string $filename)
    {
        // Sécurité : uniquement des noms de fichier simples .pdf
        $cleanFilename = basename($filename);

        if (
            !Str::endsWith($cleanFilename, '.pdf') ||
            Str::contains($cleanFilename, ['..', '/', '\\'])
        ) {
            abort(404, 'Fichier invalide');
        }

        // Chercher dans storage/app/public/pdfs/
        $fullPath = storage_path('app/public/pdfs/' . $cleanFilename);

        Log::debug('[PdfDownload] Recherche fichier', [
            'filename' => $cleanFilename,
            'path'     => $fullPath,
            'exists'   => file_exists($fullPath),
        ]);

        if (!file_exists($fullPath)) {
            abort(404, 'PDF introuvable : ' . $cleanFilename);
        }

        return Response::download($fullPath, $cleanFilename, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $cleanFilename . '"',
        ]);
    }

    public function stream(Request $request, string $filename)
    {
        $cleanFilename = basename($filename);

        if (
            !Str::endsWith($cleanFilename, '.pdf') ||
            Str::contains($cleanFilename, ['..', '/', '\\'])
        ) {
            abort(404, 'Fichier invalide');
        }

        $fullPath = storage_path('app/public/pdfs/' . $cleanFilename);

        if (!file_exists($fullPath)) {
            abort(404, 'PDF introuvable : ' . $cleanFilename);
        }

        return Response::file($fullPath, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
