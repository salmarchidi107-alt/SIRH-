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
            'societe'   => $tenant?->name          ?? config('app.name'),
            'adresse'   => $tenant?->address        ?? '',
            'telephone' => $tenant?->phone          ?? '',
            'email'     => $tenant?->email_societe  ?? '',
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

        $html     = $document->contenu ?? $document->modele?->contenu ?? null;
        $tenant   = auth()->user()?->tenant;
        $entete   = DocumentEntete::getActive();
        $employee = $document->employe;

        if (!$html) {
            abort(404, 'Aucun contenu disponible pour ce document.');
        }

        // ── Valeurs indexées par nom de variable ──────────────────────────────
        $values = [
    'nom'           => $employee?->last_name     ?? '—',
    'prenom'        => $employee?->first_name    ?? '—',
    'matricule'     => $employee?->matricule     ?? '—',
    'poste'         => $employee?->position      ?? '—',
    'departement'   => $employee?->department    ?? '—',
    'contrat'       => $employee?->contract_type ?? '—',
    'date_embauche' => $employee?->hire_date
                        ? \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y')
                        : '—',
    'salaire'       => $employee?->salary
                        ? number_format($employee->salary, 2, ',', ' ') . ' MAD'
                        : '—',
    'date'          => $document->date_document?->format('d/m/Y') ?? now()->format('d/m/Y'),
    'aujourd_hui'   => now()->format('d/m/Y'),
    'mois_annee'    => now()->translatedFormat('F Y'),
    'annee'         => now()->format('Y'),

    // ── Société — lire depuis Tenant en priorité ──────────────
    'societe'       => $entete?->nom_societe ?: ($tenant?->name          ?? '—'),
    'adresse'       => $entete?->adresse     ?: ($tenant?->address       ?? '—'),
    'telephone'     => $entete?->telephone   ?: ($tenant?->phone         ?? '—'),
    'email_societe' => $entete?->email       ?: ($tenant?->email_societe ?? '—'),
    'site_web'      => $entete?->site_web    ?: ($tenant?->website       ?? '—'),
    'ice'           => $entete?->ice         ?: ($tenant?->ice           ?? '—'),
    // ──────────────────────────────────────────────────────────

    'logo_societe'  => $this->buildLogoHtml($entete, $tenant),
];


        $search  = [];
        $replace = [];
        foreach ($values as $key => $value) {
            $search[]  = '@{{' . $key . '}}';
            $replace[] = $value;
            $search[]  = '{{' . $key . '}}';
            $replace[] = $value;
        }

        $html = str_replace($search, $replace, $html);

        // Normalise le HTML pour éviter les coupures parasites de DomPDF
        $html = $this->normaliserHtmlPourDomPdf($html);

        $htmlEntete    = $this->buildEnteteHtml($entete, $tenant);

        // Pied de page — on passe les replacements au format {{variable}}
        $replacementsForPied = array_combine(
            array_map(fn($k) => '{{' . $k . '}}', array_keys($values)),
            array_values($values)
        );
        $htmlPiedInner = $this->buildPiedDePageInner($entete, $replacementsForPied);

        // Si pas d'entête → marge minimale pour éviter l'espace vide en haut
        $enteteHauteur = $htmlEntete    ? '145px' : '20px';
        $piedHauteur   = $htmlPiedInner ? '60px'  : '20px';

        $htmlFull = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin-top:    ' . $enteteHauteur . ';
            margin-bottom: ' . $piedHauteur . ';
            margin-left:   40px;
            margin-right:  40px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 13px;
            color: #0d2238;
            line-height: 1.7;
            margin: 0;
            padding: 0;
        }

        p {
            margin: 0 0 8px 0;
            padding: 0;
            white-space: normal;
            word-wrap: break-word;
        }

        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 4px 8px; }

        /*
         * Entête fixée en haut de chaque page.
         * top négatif = remonte dans la marge @page (comportement DomPDF).
         */
        .entete-fixee {
            position: fixed;
            top:    -' . $enteteHauteur . ';
            left:   0;
            right:  0;
            height: ' . $enteteHauteur . ';
            background: #fff;
            padding-bottom: 8px;
            overflow: hidden;
        }

        /*
         * Pied de page fixé en bas de chaque page.
         * bottom négatif = descend dans la marge @page (comportement DomPDF).
         */
        .pied-de-page {
            position: fixed;
            bottom: -' . $piedHauteur . ';
            left:   0;
            right:  0;
            height: ' . $piedHauteur . ';
            font-size: 11px;
            color: #64748b;
            border-top: 1.5px solid #e2e8f0;
            padding-top: 6px;
            background: #fff;
        }

        .contenu-principal {
            padding-top: 4px;
        }
    </style>
</head>
<body>

    ' . ($htmlPiedInner ? '<div class="pied-de-page">' . $htmlPiedInner . '</div>' : '') . '

    ' . ($htmlEntete ? '<div class="entete-fixee">' . $htmlEntete . '</div>' : '') . '

    <div class="contenu-principal">
        ' . $html . '
    </div>

</body>
</html>';

        $pdf = Pdf::loadHTML($htmlFull)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled'    => true,
                'isRemoteEnabled'         => false,
                'defaultFont'             => 'DejaVu Sans',
                'isFontSubsettingEnabled' => true,
                'enable_css_float'        => true,
            ]);

        $filename = 'document_' . str_replace(' ', '_', $document->nom) . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    /* ── Normalise le HTML pour éviter les coupures parasites ───
     *
     * DomPDF interprète les \n dans le source HTML comme des espaces,
     * ce qui peut couper visuellement les phrases en deux lignes.
     * On supprime les \n à l'intérieur des <p> et entre balises inline.
     * ─────────────────────────────────────────────────────────── */
    private function normaliserHtmlPourDomPdf(string $html): string
    {
        // Normalise les fins de ligne
        $html = preg_replace('/\r\n|\r/', "\n", $html);

        // Supprime les \n entre balises inline fermantes et ouvrantes
        $html = preg_replace(
            '/(<\/(?:span|strong|em|a|b|i|u|small|sub|sup)>)\s*\n\s*(<(?:span|strong|em|a|b|i|u|small|sub|sup))/i',
            '$1 $2',
            $html
        );

        // Supprime les \n à l'intérieur de chaque <p>...</p>
        $html = preg_replace_callback('/<p([^>]*)>(.*?)<\/p>/is', function ($matches) {
            $content = preg_replace('/\s*\n\s*/', ' ', $matches[2]);
            return '<p' . $matches[1] . '>' . $content . '</p>';
        }, $html);

        return $html;
    }

    /* ── Entête HTML : logo gauche, infos centre ─────────────── */
    private function buildEnteteHtml(?DocumentEntete $entete, $tenant): string
    {
        if (!$entete) return '';

        $logoHtml = '';
        if ($entete->logo_path) {
            $logoAbsPath = storage_path('app/public/' . $entete->logo_path);
            if (file_exists($logoAbsPath)) {
                $logoData = base64_encode(file_get_contents($logoAbsPath));
                $logoMime = mime_content_type($logoAbsPath);
                $logoHtml = '<img src="data:' . $logoMime . ';base64,' . $logoData . '"
                                  style="height:65px;max-width:150px;object-fit:contain;" />';
            }
        }

        $infos = '';
        if ($entete->nom_societe) $infos .= '<strong style="font-size:15px;color:#0d2238;">' . e($entete->nom_societe) . '</strong><br>';
        if ($entete->adresse)     $infos .= e($entete->adresse)   . '<br>';
        if ($entete->telephone)   $infos .= 'Tél : '   . e($entete->telephone) . '<br>';
        if ($entete->email)       $infos .= 'Email : ' . e($entete->email)     . '<br>';
        if ($entete->site_web)    $infos .= 'Web : '   . e($entete->site_web)  . '<br>';
        if ($entete->rc)          $infos .= 'RC : '    . e($entete->rc)        . '<br>';
        if ($entete->ice)         $infos .= 'ICE : '   . e($entete->ice)       . '<br>';

        $html = '
        <table width="100%" style="border-collapse:collapse;margin-bottom:8px;">
            <tr>
                <td width="150" style="vertical-align:middle;text-align:left;padding:0;">
                    ' . $logoHtml . '
                </td>
                <td style="vertical-align:middle;text-align:center;
                           font-family:DejaVu Sans,Arial,sans-serif;
                           font-size:12px;color:#0d2238;line-height:1.8;padding:0;">
                    ' . $infos . '
                </td>
                <td width="150" style="padding:0;"></td>
            </tr>
        </table>';

        if (!empty($entete->contenu_libre)) {
            $html .= '<div style="font-size:12px;color:#0d2238;margin-top:6px;">'
                  . $entete->contenu_libre
                  . '</div>';
        }

        return $html;
    }

    /* ── Pied de page — contenu intérieur ───────────────────── */
    private function buildPiedDePageInner(?DocumentEntete $entete, array $replacements): string
    {
        if (!$entete || empty($entete->contenu_pied_de_page)) {
            return '';
        }

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $entete->contenu_pied_de_page
        );
    }

    /* ── Logo HTML (fallback tenant si pas d'entête) ─────────── */
    private function buildLogoHtml(?DocumentEntete $entete, $tenant): string
    {
        if ($entete?->logo_path) {
            $path = storage_path('app/public/' . $entete->logo_path);
            if (file_exists($path)) {
                $data = base64_encode(file_get_contents($path));
                $mime = mime_content_type($path);
                return '<img src="data:' . $mime . ';base64,' . $data . '" style="height:60px;object-fit:contain;">';
            }
        }

        if ($tenant?->logo_path && Storage::exists($tenant->logo_path)) {
            $path = storage_path('app/' . $tenant->logo_path);
            if (file_exists($path)) {
                $data = base64_encode(file_get_contents($path));
                $mime = mime_content_type($path);
                return '<img src="data:' . $mime . ';base64,' . $data . '" style="height:60px;object-fit:contain;">';
            }
        }

        return '';
    }
}