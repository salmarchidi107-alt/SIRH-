<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\Document\DocumentPdfService;
use App\Services\Document\DocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentService $documentService,
        private DocumentPdfService $documentPdfService,
    ) {}

    public function index(Request $request)
    {
        return view('ged.index', $this->documentService->getIndexData($request));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'           => 'required|string|max:255',
            'employe_id'    => 'required|exists:employees,id',
            'modele_id'     => 'required|exists:document_modeles,id',
            'date_document' => 'required|date',
        ]);

        $this->documentService->create($validated);

        return redirect()->route('ged.index')
            ->with('success', 'Document généré avec succès.');
    }

    public function edit(Document $document)
    {
        return view('ged.edit', $this->documentService->getEditData($document));
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'nom'           => 'required|string|max:255',
            'employe_id'    => 'required|exists:employees,id',
            'modele_id'     => 'required|exists:document_modeles,id',
            'date_document' => 'required|date',
            'contenu'       => 'nullable|string',
        ]);

        $this->documentService->update($document, $validated);

        return redirect()->route('ged.index')
            ->with('success', 'Document modifié avec succès.');
    }

    public function destroy(Document $document)
    {
        $this->documentService->delete($document);

        return redirect()->route('ged.index')
            ->with('success', 'Document supprimé.');
    }

    public function download(Document $document)
    {
        $pdfData = $this->documentPdfService->generate($document);

        if (! $pdfData) {
            abort(404, 'Aucun contenu disponible pour ce document.');
        }

        $pdf = Pdf::loadHTML($pdfData['html'])
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled'    => true,
                'isRemoteEnabled'         => false,
                'defaultFont'             => 'DejaVu Sans',
                'isFontSubsettingEnabled' => true,
                'enable_css_float'        => true,
            ]);

        return $pdf->download($pdfData['filename']);
    }
}
