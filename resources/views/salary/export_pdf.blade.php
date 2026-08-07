<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Rapport de Paie — {{ $periodLabel }}</title>
<style>
/* ═══════════════════════════════════════════════════
   RESET & BASE — noir et blanc, sobre, professionnel
═══════════════════════════════════════════════════ */
* { margin:0; padding:0; box-sizing:border-box; }

@page {
    size: A4 portrait;
    margin: 10mm 8mm 10mm 8mm;
}

html, body {
    width: 100%;
    overflow-x: hidden;
}

body {
    font-family: "Times New Roman", Times, serif;
    font-size: 11pt;
    color: #000000;
    background: #ffffff;
    line-height: 1.45;
}

.page-container {
    max-width: 190mm;
    margin: 0 auto;
    padding: 10mm 0;
}

table { table-layout: fixed; width: 100%; border-collapse: collapse; }
td, th { word-wrap: break-word; overflow-wrap: break-word; }

h1, h2, .title-14 {
    font-size: 13pt;
    font-weight: bold;
    color: #000000;
}

/* ═══════════════════════════════════════════════════
   EN-TÊTE
═══════════════════════════════════════════════════ */
.letterhead {
    text-align: center;
    padding-bottom: 10pt;
    border-bottom: 1.5pt solid #000000;
    margin-bottom: 16pt;
}
.tenant-name {
    font-size: 13pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
}
.tenant-tagline {
    font-size: 11pt;
    margin-top: 2pt;
}
.doc-title {
    font-size: 13pt;
    font-weight: bold;
    text-transform: uppercase;
    margin-top: 10pt;
    letter-spacing: 0.5pt;
}

.ref-table {
    width: 100%;
    margin-top: 12pt;
    border: 1pt solid #000000;
}
.ref-table td {
    padding: 4pt 9pt;
    font-size: 11pt;
    border: 0.5pt solid #000000;
}
.ref-table td.label {
    font-weight: bold;
    width: 25%;
    background: #f0f0f0;
}

/* ═══════════════════════════════════════════════════
   AVERTISSEMENTS DEVISE
═══════════════════════════════════════════════════ */
.currency-notice {
    border: 1pt solid #000000;
    font-size: 10pt;
    font-style: italic;
    padding: 6pt 10pt;
    margin: 12pt 0;
}
.currency-notice.warning {
    border: 1.25pt solid #000000;
    background: #f5f5f5;
    font-weight: bold;
    font-style: normal;
}

/* ═══════════════════════════════════════════════════
   TITRES DE SECTION
═══════════════════════════════════════════════════ */
.section-title {
    font-size: 13pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.3pt;
    padding-bottom: 4pt;
    border-bottom: 1pt solid #000000;
    margin: 20pt 0 10pt 0;
    display: table;
    width: 100%;
}
.section-title-text { display: table-cell; vertical-align: bottom; }
.section-count {
    display: table-cell;
    text-align: right;
    vertical-align: bottom;
    font-size: 10pt;
    font-weight: normal;
    text-transform: none;
    letter-spacing: 0;
}

/* ═══════════════════════════════════════════════════
   TABLEAU RÉSUMÉ
═══════════════════════════════════════════════════ */
.summary-table {
    width: 100%;
    border: 1pt solid #000000;
    font-size: 10.5pt;
}
.summary-table thead th {
    padding: 6pt 9pt;
    text-align: left;
    font-size: 10pt;
    font-weight: bold;
    text-transform: uppercase;
    background: #e0e0e0;
    border: 0.75pt solid #000000;
}
.summary-table thead th.right { text-align: right; }
.summary-table tbody td {
    padding: 6pt 9pt;
    border: 0.5pt solid #000000;
}
.summary-table tbody td.right { text-align: right; font-weight: bold; }
.summary-table tbody td.sub { font-size: 9pt; font-style: italic; }
.summary-table .total-row td {
    background: #e0e0e0;
    font-weight: bold;
    font-size: 11pt;
    border-top: 1.5pt solid #000000;
    border-bottom: 1.5pt solid #000000;
}

/* ═══════════════════════════════════════════════════
   TABLEAU PRINCIPAL — DÉTAIL DES BULLETINS
═══════════════════════════════════════════════════ */
.main-table {
    width: 100%;
    font-size: 9pt;
    border: 1pt solid #000000;
}
.main-table thead th {
    padding: 5pt 4pt;
    text-align: left;
    font-size: 8.5pt;
    font-weight: bold;
    text-transform: uppercase;
    background: #e0e0e0;
    border: 0.75pt solid #000000;
    line-height: 1.2;
}
.main-table thead th.right  { text-align: right; }
.main-table thead th.center { text-align: center; }

.main-table tbody td {
    padding: 5pt 3pt;
    vertical-align: middle;
    border: 0.5pt solid #000000;
    overflow: hidden;
}
.main-table tbody td.right  { text-align: right; }
.main-table tbody td.center { text-align: center; }
.main-table tbody td.bold   { font-weight: bold; }
.main-table tbody td.italic { font-style: italic; }

.emp-name { font-weight: bold; font-size: 9.5pt; }
.emp-pos  { font-size: 8pt; font-style: italic; margin-top: 1pt; }

.status-tag {
    display: inline-block;
    padding: 1pt 3pt;
    border: 0.75pt solid #000000;
    font-size: 7pt;
    font-weight: bold;
    white-space: normal;
    word-break: break-word;
    max-width: 100%;
}

/* Ligne dont la devise n'est pas définie en base — mise en évidence
   par une bordure pointillée plutôt qu'une couleur, pour rester
   cohérent avec le rendu noir et blanc du document. */
.row-currency-undefined td {
    border-top: 0.75pt dashed #000000;
    border-bottom: 0.75pt dashed #000000;
}
.currency-undefined-tag {
    display: inline-block;
    font-size: 6.5pt;
    font-weight: bold;
    font-style: italic;
    margin-top: 1pt;
}

/* Étiquette de devise affichée systématiquement sous chaque montant,
   qu'il y ait une seule devise dans le rapport ou plusieurs. Avant,
   elle n'apparaissait qu'en cas de devises mixtes ($mixedCurrencies),
   ce qui masquait une devise erronée tant qu'un seul employé/une
   seule devise était présent dans le rapport. */
.currency-tag {
    display: inline-block;
    font-size: 7pt;
    font-weight: normal;
    margin-top: 1pt;
}

.total-row td {
    background: #e0e0e0;
    font-weight: bold;
    font-size: 9.5pt;
    padding: 6pt 4pt;
    border-top: 1.5pt solid #000000;
    border-bottom: 1.5pt solid #000000;
}

/* ═══════════════════════════════════════════════════
   RÉCAP CHARGES PATRONALES
═══════════════════════════════════════════════════ */
.charges-table {
    width: 100%;
    font-size: 10.5pt;
    margin-top: 2pt;
    border: 1pt solid #000000;
}
.charges-table thead th {
    padding: 6pt 9pt;
    text-align: left;
    font-size: 10pt;
    font-weight: bold;
    text-transform: uppercase;
    background: #e0e0e0;
    border: 0.75pt solid #000000;
}
.charges-table thead th.right { text-align: right; }
.charges-table tbody td {
    padding: 6pt 9pt;
    border: 0.5pt solid #000000;
}
.charges-table tbody td.right { text-align: right; }
.charges-table tbody td.sub { font-style: italic; font-size: 9pt; }
.charges-table .total-row td {
    background: #e0e0e0;
    font-weight: bold;
    border-top: 1.5pt solid #000000;
    border-bottom: 1.5pt solid #000000;
}

/* ═══════════════════════════════════════════════════
   PIED DE PAGE
═══════════════════════════════════════════════════ */
.footer {
    margin-top: 24pt;
    padding-top: 8pt;
    border-top: 1pt solid #000000;
    display: table;
    width: 100%;
}
.footer-left  {
    display: table-cell; width: 55%;
    font-size: 9pt; vertical-align: middle; line-height: 1.45;
}
.footer-right {
    display: table-cell; width: 45%; text-align: right;
    font-size: 9pt; vertical-align: middle; line-height: 1.45;
}
.footer-legal {
    margin-top: 8pt;
    font-size: 8pt;
    font-style: italic;
    text-align: center;
}

.page-break { page-break-after: always; }
</style>
</head>
<body>
<div class="page-container">

@php
    /*
     * ── Détection de la devise réelle du rapport ────────────────────────
     * IMPORTANT : on ne "devine" plus la devise d'un bulletin dont la
     * colonne `currency` est null en base. Avant, `$sal->currency ?? $reportCurrency`
     * masquait silencieusement ce cas en rattachant le bulletin à la devise
     * du rapport (souvent MAD par défaut) — ce qui produisait des PDF
     * affichant "MAD" pour des bulletins réellement saisis en MRU mais mal
     * enregistrés. On isole maintenant ces cas dans un bucket "N/D".
     */
    $allSalaries      = $allEmployees->flatMap->salaries;
    $currenciesFound  = $allSalaries->pluck('currency')->filter()->unique()->values();
    $hasUndefinedCurrency = $allSalaries->contains(fn($s) => empty($s->currency));

    // Si un filtre devise a été appliqué côté contrôleur (paramètre
    // `currency` transmis depuis l'index), on l'utilise directement comme
    // référence d'affichage : tous les bulletins récupérés ont déjà été
    // filtrés sur cette devise en base, donc c'est la source de vérité.
    if (!empty($currency)) {
        $reportCurrency = $currency;
    } elseif ($currenciesFound->count() === 1) {
        $reportCurrency = $currenciesFound->first();
    } else {
        $reportCurrency = $tenant->currency ?? 'MAD';
    }

    $mixedCurrencies = $currenciesFound->count() > 1;
@endphp

{{-- ══════════════════════════════════
     EN-TÊTE
══════════════════════════════════ --}}
<div class="letterhead">
    @if(!empty($tenant?->name))
    <div class="tenant-name">{{ strtoupper($tenant->name) }}</div>
    <div class="tenant-tagline">Système d'Information des Ressources Humaines</div>
    @endif
    <div class="doc-title">Rapport de Paie</div>

    <table class="ref-table">
        <tr>
            <td class="label">Période</td>
            <td>{{ $periodLabel }}</td>
            <td class="label">Devise</td>
            <td>{{ !empty($currency) ? $currency . ' (filtré)' : $reportCurrency }}</td>
        </tr>
        <tr>
            <td class="label">Département</td>
            <td>{{ $department ?? 'Tous départements' }}</td>
            <td class="label">Édité le</td>
            <td>{{ now()->locale('fr')->isoFormat('D MMM YYYY, HH:mm') }}</td>
        </tr>
        @if($status)
        <tr>
            <td class="label">Filtre statut</td>
            <td colspan="3">{{ ucfirst($status) }}</td>
        </tr>
        @endif
    </table>
</div>

@if($mixedCurrencies)
<div class="currency-notice">
    Attention : cette période regroupe des bulletins générés dans plusieurs devises ({{ $currenciesFound->join(', ') }}).
    Chaque ligne indique sa devise d'origine ; les totaux ne doivent pas être additionnés entre devises différentes.
</div>
@endif

@if($hasUndefinedCurrency)
<div class="currency-notice warning">
    ⚠ Certains bulletins de cette période n'ont aucune devise enregistrée en base de données.
    Ils sont signalés « Devise N/D » dans le tableau ci-dessous et exclus des totaux par devise
    afin d'éviter tout montant faussé. Rouvrez ces bulletins via « Saisir » pour leur affecter
    explicitement MAD ou MRU, puis régénérez ce rapport.
</div>
@endif

{{-- ══════════════════════════════════
     RÉSUMÉ DE LA PÉRIODE
══════════════════════════════════ --}}
<div class="section-title">
    <div class="section-title-text">Résumé de la période</div>
</div>

<table class="summary-table">
    <colgroup>
        <col style="width:40%">
        <col style="width:28%">
        <col style="width:32%">
    </colgroup>
    <thead>
        <tr>
            <th>Poste</th>
            <th class="right">Montant ({{ $reportCurrency }})</th>
            <th>Détail</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Masse salariale brute</td>
            <td class="right">{{ number_format($summary['total_gross'], 0, ',', ' ') }}</td>
            <td class="sub">Coût employeur total : {{ number_format($summary['total_employer_cost'] ?? 0, 0, ',', ' ') }} {{ $reportCurrency }}</td>
        </tr>
        <tr>
            <td>Charges salariales (CNSS + AMO)</td>
            <td class="right">{{ number_format($summary['total_cnss_sal'] + $summary['total_amo_sal'], 0, ',', ' ') }}</td>
            <td class="sub">CNSS : {{ number_format($summary['total_cnss_sal'], 0, ',', ' ') }} — AMO : {{ number_format($summary['total_amo_sal'], 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td>IR retenu à la source</td>
            <td class="right">{{ number_format($summary['total_ir'], 0, ',', ' ') }}</td>
            <td class="sub">DGI — déclaration mensuelle</td>
        </tr>
        <tr class="total-row">
            <td>Net à payer total</td>
            <td class="right">{{ number_format($summary['total_net'], 0, ',', ' ') }}</td>
            <td class="sub">{{ $summary['count_validated'] }} validés / {{ $summary['count'] }} bulletins</td>
        </tr>
    </tbody>
</table>

{{-- ══════════════════════════════════
     TABLEAU DES BULLETINS
══════════════════════════════════ --}}
<div class="section-title">
    <div class="section-title-text">Détail des bulletins</div>
    <div class="section-count">{{ $allEmployees->count() }} employés</div>
</div>

<table class="main-table">
    <colgroup>
        <col style="width:17%">
        <col style="width:9%">
        @if($dateDebut && $dateFin)<col style="width:8%">@endif
        <col style="width:{{ ($dateDebut && $dateFin) ? '10%' : '14%' }}">
        <col style="width:7%">
        <col style="width:10%">
        <col style="width:10%">
        <col style="width:8%">
        <col style="width:{{ ($dateDebut && $dateFin) ? '11%' : '15%' }}">
        <col style="width:10%">
    </colgroup>
    <thead>
        <tr>
            <th>Employé</th>
            <th>Département</th>
            @if($dateDebut && $dateFin)
            <th class="center">Période</th>
            @endif
            <th class="center">Mode paiement</th>
            <th class="right">Base</th>
            <th class="right">Brut</th>
            <th class="right">CNSS+AMO</th>
            <th class="right">IR</th>
            <th class="right">Net à payer</th>
            <th class="center">Statut</th>
        </tr>
    </thead>
    <tbody>
    @php
        $totBrut = 0; $totCnss = 0; $totIr = 0; $totNet = 0;
        $totalsByCurrency = [];
        $undefinedCount = 0;
    @endphp

    @forelse($allEmployees as $emp)
        @php $salList = $emp->salaries; @endphp
        @if($salList->isEmpty())
        <tr>
            <td>
                <div class="emp-name">{{ $emp->full_name }}</div>
                <div class="emp-pos">{{ $emp->position }}</div>
            </td>
            <td>{{ $emp->department ?? '—' }}</td>
            @if($dateDebut && $dateFin)<td class="center">—</td>@endif
            <td class="center">{{ ucfirst($emp->payment_method ?? '—') }}</td>
            <td class="right">{{ number_format($emp->base_salary, 0, ',', ' ') }}</td>
            <td class="center">—</td>
            <td class="center">—</td>
            <td class="center">—</td>
            <td class="center">—</td>
            <td class="center"><span class="status-tag">Non généré</span></td>
        </tr>
        @else
            @foreach($salList as $sal)
            @php
                // ── Ne plus rattacher silencieusement une devise nulle
                //    à $reportCurrency : on la marque explicitement.
                $currIsUndefined = empty($sal->currency);
                $curr = $currIsUndefined ? null : $sal->currency;

                if ($currIsUndefined) {
                    $undefinedCount++;
                } else {
                    $totBrut += $sal->gross_salary;
                    $totCnss += $sal->cnss_deduction + $sal->amo_deduction;
                    $totIr   += $sal->ir_deduction;
                    $totNet  += $sal->net_salary;

                    $totalsByCurrency[$curr] ??= ['gross' => 0, 'cnss' => 0, 'ir' => 0, 'net' => 0];
                    $totalsByCurrency[$curr]['gross'] += $sal->gross_salary;
                    $totalsByCurrency[$curr]['cnss']  += $sal->cnss_deduction + $sal->amo_deduction;
                    $totalsByCurrency[$curr]['ir']    += $sal->ir_deduction;
                    $totalsByCurrency[$curr]['net']   += $sal->net_salary;
                }

                $badgeLabel = match($sal->status) {
                    'validated' => 'Validé',
                    'paid'      => 'Rémunéré',
                    'draft'     => 'Brouillon',
                    default     => ucfirst($sal->status ?? 'Inconnu'),
                };
            @endphp
            <tr class="{{ $currIsUndefined ? 'row-currency-undefined' : '' }}">
                <td>
                    <div class="emp-name">{{ $emp->full_name }}</div>
                    <div class="emp-pos">{{ $emp->position }}</div>
                </td>
                <td>{{ $emp->department ?? '—' }}</td>
                @if($dateDebut && $dateFin)
                <td class="center">
                    {{ \Carbon\Carbon::create($sal->year, $sal->month)->locale('fr')->isoFormat('MMM YYYY') }}
                </td>
                @endif
                <td class="center" style="font-size:8pt;">
                    @if($emp->payment_method == 'virement')
                        Virement{{ $emp->bank ? ' '.$emp->bank : '' }}
                    @else
                        {{ ucfirst($emp->payment_method ?? '—') }}
                    @endif
                </td>
                <td class="right">{{ number_format($emp->base_salary, 0, ',', ' ') }}</td>
                <td class="right bold">
                    {{ number_format($sal->gross_salary, 0, ',', ' ') }}
                    @if($currIsUndefined)
                        <br><span class="currency-undefined-tag">Devise N/D ⚠</span>
                    @else
                        <br><span class="currency-tag">{{ $curr }}</span>
                    @endif
                </td>
                <td class="right">{{ number_format($sal->cnss_deduction + $sal->amo_deduction, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($sal->ir_deduction, 0, ',', ' ') }}</td>
                <td class="right bold">
                    {{ number_format($sal->net_salary, 0, ',', ' ') }}
                    @if($currIsUndefined)
                        <br><span class="currency-undefined-tag">Devise N/D ⚠</span>
                    @else
                        <br><span class="currency-tag">{{ $curr }}</span>
                    @endif
                </td>
                <td class="center"><span class="status-tag">{{ $badgeLabel }}</span></td>
            </tr>
            @endforeach
        @endif
    @empty
        <tr>
            <td colspan="{{ ($dateDebut && $dateFin) ? 10 : 9 }}"
                style="text-align:center;padding:20pt;font-style:italic;">
                Aucun bulletin trouvé pour cette période.
            </td>
        </tr>
    @endforelse

    @if($allEmployees->count() > 0)
        @if($mixedCurrencies || $undefinedCount > 0)
            {{-- Une ligne de total distincte par devise réellement connue --}}
            @foreach($totalsByCurrency as $curr => $t)
            <tr class="total-row">
                <td colspan="{{ ($dateDebut && $dateFin) ? 5 : 4 }}" style="text-align:left;">
                    TOTAL ({{ $curr }})
                </td>
                <td class="right">{{ number_format($t['gross'], 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($t['cnss'], 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($t['ir'], 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($t['net'], 0, ',', ' ') }}</td>
                <td class="center">—</td>
            </tr>
            @endforeach
            @if($undefinedCount > 0)
            <tr class="total-row">
                <td colspan="{{ ($dateDebut && $dateFin) ? 5 : 4 }}" style="text-align:left;">
                    {{ $undefinedCount }} bulletin(s) — Devise N/D (exclu des totaux)
                </td>
                <td class="right" colspan="4" style="text-align:center;font-style:italic;font-weight:normal;">
                    À corriger via « Saisir » avant de refaire ce rapport
                </td>
            </tr>
            @endif
        @else
        <tr class="total-row">
            <td colspan="{{ ($dateDebut && $dateFin) ? 5 : 4 }}" style="text-align:left;">
                TOTAL — {{ $summary['count'] }} bulletins
            </td>
            <td class="right">{{ number_format($totBrut, 0, ',', ' ') }} {{ $reportCurrency }}</td>
            <td class="right">{{ number_format($totCnss, 0, ',', ' ') }} {{ $reportCurrency }}</td>
            <td class="right">{{ number_format($totIr, 0, ',', ' ') }} {{ $reportCurrency }}</td>
            <td class="right">{{ number_format($totNet, 0, ',', ' ') }} {{ $reportCurrency }}</td>
            <td class="center" style="font-size:8pt;">
                {{ $summary['count_validated'] }}V / {{ $summary['count_paid'] }}P / {{ $summary['count_draft'] }}B
            </td>
        </tr>
        @endif
    @endif
    </tbody>
</table>

{{-- ══════════════════════════════════
     RÉCAP CHARGES PATRONALES
══════════════════════════════════ --}}
@if(($summary['total_employer_cost'] ?? 0) > 0)
<div class="section-title">
    <div class="section-title-text">Récapitulatif des charges patronales</div>
</div>
<table class="charges-table">
    <colgroup>
        <col style="width:48%">
        <col style="width:22%">
        <col style="width:30%">
    </colgroup>
    <thead>
        <tr>
            <th>Poste</th>
            <th>Taux</th>
            <th class="right">Montant ({{ $reportCurrency }})</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>CNSS salariale</td>
            <td class="sub">4,48 %</td>
            <td class="right">{{ number_format($summary['total_cnss_sal'], 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td>AMO salariale</td>
            <td class="sub">2,26 %</td>
            <td class="right">{{ number_format($summary['total_amo_sal'], 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td>IR retenu à la source</td>
            <td class="sub">Barème progressif</td>
            <td class="right">{{ number_format($summary['total_ir'], 0, ',', ' ') }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="2"><strong>Coût employeur total</strong></td>
            <td class="right">{{ number_format($summary['total_employer_cost'] ?? 0, 0, ',', ' ') }}</td>
        </tr>
    </tbody>
</table>
@endif

{{-- ══════════════════════════════════
     PIED DE PAGE
══════════════════════════════════ --}}
<div class="footer">
    <div class="footer-left">
        <strong>{{ $tenant?->name ?? 'Entreprise' }}</strong><br>
        Document confidentiel — Usage interne uniquement
    </div>
    <div class="footer-right">
        Rapport de paie — {{ $periodLabel }}<br>
        Généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</div>
<div class="footer-legal">
    Ce document est généré automatiquement par le système de gestion RH.
    Sauf indication contraire, les montants sont exprimés en {{ $reportCurrency }}.
    @if($hasUndefinedCurrency)
        Les bulletins marqués « Devise N/D » n'ont pas de devise enregistrée et sont exclus des totaux ci-dessus.
    @endif
</div>

</div>
</body>
</html>a
