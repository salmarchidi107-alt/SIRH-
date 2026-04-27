<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
    // ── Liste ────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Document::with(['employe', 'createdBy', 'modele'])->latest();

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        $documents = $query->paginate(15)->withQueryString();
        $employes  = \App\Models\Employee::orderBy('last_name')->orderBy('first_name')->get();
        $modeles   = \App\Models\DocumentModele::orderBy('nom')->get();

        return view('ged.index', compact('documents', 'employes', 'modeles'));
    }

    // ── Créer ────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'nom'           => 'required|string|max:255',
            'employe_id'    => 'required|exists:employees,id',
            'modele_id'     => 'required|exists:document_modeles,id',
            'date_document' => 'required|date',
        ]);

        // Charger le contenu du modèle pour le stocker dans le document
        $modele = \App\Models\DocumentModele::find($request->modele_id);

        Document::create([
            'nom'           => $request->nom,
            'employe_id'    => $request->employe_id,
            'modele_id'     => $request->modele_id,
            'date_document' => $request->date_document,
            'contenu'       => $modele?->contenu,   // ← stocke le contenu initial du modèle
            'created_by'    => Auth::id(),
            'type'          => 'Autre',
        ]);

        return redirect()->route('ged.index')
            ->with('success', 'Document généré avec succès.');
    }

    // ── Page édition ─────────────────────────────────────────────────────────
    public function edit(Document $document)
    {
        $employes = \App\Models\Employee::orderBy('last_name')->orderBy('first_name')->get();
        $modeles  = \App\Models\DocumentModele::orderBy('nom')->get();

        return view('ged.edit', compact('document', 'employes', 'modeles'));
    }

    // ── Mettre à jour ────────────────────────────────────────────────────────
    public function update(Request $request, Document $document)
    {
        $request->validate([
            'nom'           => 'required|string|max:255',
            'employe_id'    => 'required|exists:employees,id',
            'modele_id'     => 'required|exists:document_modeles,id',
            'date_document' => 'required|date',
            'contenu'       => 'nullable|string',
        ]);

        $document->update([
            'nom'           => $request->nom,
            'employe_id'    => $request->employe_id,
            'modele_id'     => $request->modele_id,
            'date_document' => $request->date_document,
            'contenu'       => $request->contenu,   // ← sauvegarde le contenu TinyMCE modifié
        ]);

        return redirect()->route('ged.index')
            ->with('success', 'Document modifié avec succès.');
    }

    // ── Supprimer ────────────────────────────────────────────────────────────
    public function destroy(Document $document)
    {
        $document->delete();

        return redirect()->route('ged.index')
            ->with('success', 'Document supprimé.');
    }

    // ── Télécharger en PDF ───────────────────────────────────────────────────
    public function download(Document $document)
    {
        $document->load(['modele', 'employe']);

        // Priorité : contenu personnalisé du document, sinon contenu du modèle
        $html = $document->contenu ?? $document->modele?->contenu ?? null;

        if (! $html) {
            abort(404, 'Aucun contenu disponible pour ce document.');
        }

        $employee = $document->employe;

        // Remplacement des variables
        $replacements = [
            '{{nom}}'       => $employee?->last_name  ?? $employee?->nom    ?? '—',
            '{{prenom}}'    => $employee?->first_name ?? $employee?->prenom ?? '—',
            '{{matricule}}' => $employee?->matricule  ?? '—',
            '{{date}}'      => $document->date_document?->format('d/m/Y') ?? now()->format('d/m/Y'),
            '{{poste}}'     => $employee?->position   ?? $employee?->poste ?? '—',
        ];

        $html = str_replace(array_keys($replacements), array_values($replacements), $html);

        // Envelopper dans un HTML complet pour DomPDF
        $htmlFull = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 13px;
            color: #0d2238;
            line-height: 1.6;
            padding: 40px;
        }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 6px 10px; }
    </style>
</head>
<body>' . $html . '</body>
</html>';

        $pdf      = Pdf::loadHTML($htmlFull)->setPaper('A4', 'portrait');
        $filename = 'document_' . str_replace(' ', '_', $document->nom) . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }
}
