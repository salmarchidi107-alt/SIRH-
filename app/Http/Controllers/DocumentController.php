<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentEntete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
    /* ── Liste ───────────────────────────────────────────────── */
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

    /* ── Créer ───────────────────────────────────────────────── */
    public function store(Request $request)
    {
        $request->validate([
            'nom'           => 'required|string|max:255',
            'employe_id'    => 'required|exists:employees,id',
            'modele_id'     => 'required|exists:document_modeles,id',
            'date_document' => 'required|date',
        ]);

        $modele = \App\Models\DocumentModele::find($request->modele_id);

        Document::create([
            'nom'           => $request->nom,
            'employe_id'    => $request->employe_id,
            'modele_id'     => $request->modele_id,
            'date_document' => $request->date_document,
            'contenu'       => $modele?->contenu,
            'created_by'    => Auth::id(),
            'type'          => 'Autre',
        ]);

        return redirect()->route('ged.index')
            ->with('success', 'Document généré avec succès.');
    }

    /* ── Page édition ────────────────────────────────────────── */
    public function edit(Document $document)
    {
        $document->load(['employe', 'modele']);

        $employes = \App\Models\Employee::orderBy('last_name')->orderBy('first_name')->get();
        $modeles  = \App\Models\DocumentModele::orderBy('nom')->get();
        $tenant   = auth()->user()?->tenant;

        $contenuInitial = base64_encode($document->contenu ?? $document->modele?->contenu ?? '');

        $modelesContenu = $modeles->pluck('id')->mapWithKeys(function ($id) use ($modeles) {
            $mod = $modeles->firstWhere('id', $id);
            return [$id => base64_encode($mod->contenu ?? '')];
        });

        $employesJson = $employes->mapWithKeys(function ($emp) {
            return [$emp->id => [
                'id'            => $emp->id,
                'nom'           => $emp->last_name     ?? '',
                'prenom'        => $emp->first_name    ?? '',
                'matricule'     => $emp->matricule     ?? '',
                'poste'         => $emp->position      ?? '',
                'departement'   => $emp->department    ?? '',
                'contrat'       => $emp->contract_type ?? '',
                'date_embauche' => $emp->hire_date
                                    ? \Carbon\Carbon::parse($emp->hire_date)->format('d/m/Y')
                                    : '',
                'salaire'       => $emp->salary
                                    ? number_format($emp->salary, 2, ',', ' ') . ' MAD'
                                    : '',
            ]];
        });

        $tenantJson = [
            'societe'   => $tenant?->name      ?? config('app.name'),
            'adresse'   => $tenant?->adresse   ?? '',
            'telephone' => $tenant?->telephone ?? '',
            'email'     => $tenant?->email     ?? '',
        ];

        return view('ged.edit', compact(
            'document', 'employes', 'modeles',
            'contenuInitial', 'modelesContenu', 'employesJson', 'tenantJson'
        ));
    }

    /* ── Mettre à jour ───────────────────────────────────────── */
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
            'contenu'       => $request->contenu,
        ]);

        return redirect()->route('ged.index')
            ->with('success', 'Document modifié avec succès.');
    }

    /* ── Supprimer ───────────────────────────────────────────── */
    public function destroy(Document $document)
    {
        $document->delete();

        return redirect()->route('ged.index')
            ->with('success', 'Document supprimé.');
    }

    /* ── Télécharger PDF ─────────────────────────────────────── */
    public function download(Document $document)
    {
        $document->load(['modele', 'employe']);

        $html = $document->contenu ?? $document->modele?->contenu ?? null;

        if (!$html) {
            abort(404, 'Aucun contenu disponible pour ce document.');
        }

        $employee = $document->employe;
        $tenant   = auth()->user()?->tenant;

        // ── Récupérer l'entête active ──────────────────────────
        $entete = DocumentEntete::getActive();

        $replacements = [
            '{{nom}}'          => $employee?->last_name  ?? $employee?->nom    ?? '—',
            '{{prenom}}'       => $employee?->first_name ?? $employee?->prenom ?? '—',
            '{{matricule}}'    => $employee?->matricule  ?? '—',
            '{{poste}}'        => $employee?->position   ?? $employee?->poste  ?? '—',
            '{{departement}}'  => $employee?->department ?? $employee?->departement ?? '—',
            '{{contrat}}'      => $employee?->contract_type ?? '—',
            '{{date_embauche}}'=> $employee?->hire_date
                                    ? \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y')
                                    : '—',
            '{{salaire}}'      => $employee?->salary
                                    ? number_format($employee->salary, 2, ',', ' ') . ' MAD'
                                    : '—',
            '{{date}}'         => $document->date_document?->format('d/m/Y') ?? now()->format('d/m/Y'),
            '{{aujourd_hui}}'  => now()->format('d/m/Y'),
            '{{mois_annee}}'   => now()->translatedFormat('F Y'),
            '{{annee}}'        => now()->format('Y'),
            '{{societe}}'      => $entete?->nom_societe ?? $tenant?->name     ?? config('app.name'),
            '{{adresse}}'      => $entete?->adresse     ?? $tenant?->adresse  ?? '—',
            '{{telephone}}'    => $entete?->telephone   ?? $tenant?->telephone ?? '—',
            '{{email_societe}}'=> $entete?->email       ?? $tenant?->email    ?? '—',
            '{{logo_societe}}' => $this->buildLogoHtml($entete, $tenant),
        ];

        $html = str_replace(array_keys($replacements), array_values($replacements), $html);

        // ── Construire le HTML de l'entête ─────────────────────
        $htmlEntete = $this->buildEnteteHtml($entete, $tenant);

        // ── HTML complet ───────────────────────────────────────
        $htmlFull = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 13px;
            color: #0d2238;
            line-height: 1.7;
            padding: 40px;
        }
        table  { width:100%; border-collapse:collapse; }
        td, th { padding:6px 10px; }
        p      { margin:0 0 10px; }
        .entete-table { margin-bottom: 24px; }
        .entete-separator {
            border: none;
            border-bottom: 2px solid #14b8a6;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
    ' . $htmlEntete . '
    ' . $html . '
</body>
</html>';

        $pdf      = Pdf::loadHTML($htmlFull)->setPaper('A4', 'portrait');
        $filename = 'document_' . str_replace(' ', '_', $document->nom) . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    /* ── Helpers privés ──────────────────────────────────────── */

    private function buildEnteteHtml(?DocumentEntete $entete, $tenant): string
    {
        // Pas d'entête configurée → retourner vide
        if (!$entete) {
            return '';
        }

        // Logo
        $logoHtml = '';
        if ($entete->logo_path) {
            $logoAbsPath = storage_path('app/public/' . $entete->logo_path);
            if (file_exists($logoAbsPath)) {
                $logoData    = base64_encode(file_get_contents($logoAbsPath));
                $logoMime    = mime_content_type($logoAbsPath);
                $logoHtml    = '<img src="data:' . $logoMime . ';base64,' . $logoData . '"
                                     style="height:70px;max-width:160px;object-fit:contain;" />';
            }
        }

        // Infos société
        $infos = '';
        if ($entete->nom_societe) $infos .= '<strong style="font-size:14px;">' . e($entete->nom_societe) . '</strong><br>';
        if ($entete->adresse)     $infos .= e($entete->adresse) . '<br>';
        if ($entete->telephone)   $infos .= 'Tél : '    . e($entete->telephone) . '<br>';
        if ($entete->email)       $infos .= 'Email : '  . e($entete->email)     . '<br>';
        if ($entete->site_web)    $infos .= 'Web : '    . e($entete->site_web)  . '<br>';
        if ($entete->rc)          $infos .= 'RC : '     . e($entete->rc)        . '<br>';
        if ($entete->ice)         $infos .= 'ICE : '    . e($entete->ice)       . '<br>';

        // Ligne principale logo + infos
        $html = '
        <table class="entete-table" width="100%">
            <tr>
                <td width="160" style="vertical-align:middle;">' . $logoHtml . '</td>
                <td style="vertical-align:middle;padding-left:20px;
                           font-family:DejaVu Sans,Arial,sans-serif;
                           font-size:12px;color:#0d2238;line-height:1.8;">
                    ' . $infos . '
                </td>
            </tr>
        </table>';

        // Contenu libre TinyMCE (optionnel)
        if (!empty($entete->contenu_libre)) {
            $html .= $entete->contenu_libre
                  . '<hr style="border:none;border-bottom:1px solid #e2e8f0;margin:16px 0;">';
        }

        return $html;
    }

    private function buildLogoHtml(?DocumentEntete $entete, $tenant): string
    {
        // Priorité : logo de l'entête GED
        if ($entete?->logo_path) {
            $path = storage_path('app/public/' . $entete->logo_path);
            if (file_exists($path)) {
                $data = base64_encode(file_get_contents($path));
                $mime = mime_content_type($path);
                return '<img src="data:' . $mime . ';base64,' . $data . '" style="height:60px;object-fit:contain;">';
            }
        }

        // Fallback : logo du tenant
        if ($tenant?->logo_path && Storage::exists($tenant->logo_path)) {
            return '<img src="' . storage_path('app/' . $tenant->logo_path) . '" style="height:60px;object-fit:contain;">';
        }

        return '';
    }
}