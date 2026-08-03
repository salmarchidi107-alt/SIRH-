<?php

namespace App\Services\Document;

use App\Models\Document;
use App\Models\DocumentEntete;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class DocumentPdfService
{
    public function generate(Document $document): ?array
    {
        $document->load(['modele', 'employe']);

        $html = $document->contenu ?? $document->modele?->contenu ?? null;

        if (! $html) {
            return null;
        }

        $tenant   = auth()->user()?->tenant;
        $entete   = DocumentEntete::getActive();
        $employee = $document->employe;

        $values = $this->buildMergeValues($document, $employee, $tenant, $entete);

        $html = $this->replacePlaceholders($html, $values);
        $html = $this->normaliserHtmlPourDomPdf($html);

        $htmlEntete = $this->buildEnteteHtml($entete, $tenant);

        $replacementsForPied = array_combine(
            array_map(fn ($k) => '{{' . $k . '}}', array_keys($values)),
            array_values($values)
        );
        $htmlPiedInner = $this->buildPiedDePageInner($entete, $replacementsForPied);

        $htmlFull = $this->buildFullHtmlDocument($html, $htmlEntete, $htmlPiedInner);

        $filename = 'document_' . str_replace(' ', '_', $document->nom) . '_' . now()->format('Ymd_His') . '.pdf';

        return ['html' => $htmlFull, 'filename' => $filename];
    }

    private function buildMergeValues(Document $document, $employee, $tenant, ?DocumentEntete $entete): array
    {
        return [
            'nom'               => $employee?->last_name     ?? '—',
            'prenom'            => $employee?->first_name    ?? '—',
            'matricule'         => $employee?->matricule     ?? '—',
            'poste'             => $employee?->position      ?? '—',
            'departement'       => $employee?->department    ?? '—',
            'contrat'           => $employee?->contract_type ?? '—',
            'date_embauche'     => $employee?->hire_date
                                    ? Carbon::parse($employee->hire_date)->format('d/m/Y')
                                    : '—',
            'salaire'           => $employee?->salary
                                    ? number_format($employee->salary, 2, ',', ' ') . ' MAD'
                                    : '—',
            // ── Nouvelles variables ─────────────────────────────
            'adresse_employe'   => $employee?->address ?? '—',
            'cin'               => $employee?->cin     ?? '—',
            'telephone_employe' => $employee?->phone   ?? '—',
            'date_fin_contrat'  => $employee?->contract_end_date
                                    ? Carbon::parse($employee->contract_end_date)->format('d/m/Y')
                                    : '—',
            'date_naissance'    => $employee?->birth_date
                                    ? Carbon::parse($employee->birth_date)->format('d/m/Y')
                                    : '—',
            'date'          => $document->date_document?->format('d/m/Y') ?? now()->format('d/m/Y'),
            'mois_annee'    => now()->translatedFormat('F Y'),
            'annee'         => now()->format('Y'),
            'societe'       => $tenant?->name          ? $tenant?->name          : ($entete?->nom_societe ?? '—'),
            'adresse'       => $tenant?->address       ? $tenant?->address       : ($entete?->adresse     ?? '—'),
            'telephone'     => $tenant?->phone         ? $tenant?->phone         : ($entete?->telephone   ?? '—'),
            'email_societe' => $tenant?->email_societe ? $tenant?->email_societe : ($entete?->email_societe ?? '—'),
            'site_web'      => $tenant?->website       ? $tenant?->website       : ($entete?->site_web    ?? '—'),
            'ice'           => $tenant?->ice           ? $tenant?->ice           : ($entete?->ice         ?? '—'),
            'logo_societe'  => $this->buildLogoHtml($entete, $tenant),
        ];
    }

    private function replacePlaceholders(string $html, array $values): string
    {
        $search  = [];
        $replace = [];

        foreach ($values as $key => $value) {
            $search[]  = '@{{' . $key . '}}';
            $replace[] = $value;
            $search[]  = '{{' . $key . '}}';
            $replace[] = $value;
        }

        return str_replace($search, $replace, $html);
    }

    private function normaliserHtmlPourDomPdf(string $html): string
    {
        $html = preg_replace('/\r\n|\r/', "\n", $html);

        $html = preg_replace(
            '/(<\/(?:span|strong|em|a|b|i|u|small|sub|sup)>)\s*\n\s*(<(?:span|strong|em|a|b|i|u|small|sub|sup))/i',
            '$1 $2',
            $html
        );

        $html = preg_replace_callback('/<p([^>]*)>(.*?)<\/p>/is', function ($matches) {
            $content = preg_replace('/\s*\n\s*/', ' ', $matches[2]);
            return '<p' . $matches[1] . '>' . $content . '</p>';
        }, $html);

        return $html;
    }

    private function buildEnteteHtml(?DocumentEntete $entete, $tenant): string
    {
        if (! $entete) return '';

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

        if (! empty($entete->contenu_libre)) {
            $html .= '<div style="font-size:12px;color:#0d2238;margin-top:6px;">'
                  . $entete->contenu_libre
                  . '</div>';
        }

        return $html;
    }

    private function buildPiedDePageInner(?DocumentEntete $entete, array $replacements): string
    {
        if (! $entete || empty($entete->contenu_pied_de_page)) {
            return '';
        }

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $entete->contenu_pied_de_page
        );
    }

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

    private function buildFullHtmlDocument(string $html, string $htmlEntete, string $htmlPiedInner): string
    {
        $enteteHauteur = $htmlEntete    ? '145px' : '20px';
        $piedHauteur   = $htmlPiedInner ? '100px' : '20px';

        return '<!DOCTYPE html>
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
    }
}
