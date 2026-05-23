<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: DejaVu Sans, Helvetica, sans-serif;
    font-size: 9pt;
    color: #1a202c;
    background: #fff;
    line-height: 1.5;
}

/* ── En-tête ── */
.header {
    display: table;
    width: 100%;
    padding: 18px 24px;
    border-bottom: 3px solid #0d9488;
    margin-bottom: 18px;
}
.header-left  { display: table-cell; vertical-align: middle; width: 65%; }
.header-right { display: table-cell; vertical-align: middle; text-align: right; width: 35%; }

.header-org {
    font-size: 15pt;
    font-weight: bold;
    color: #0d2238;
    letter-spacing: .3px;
}
.header-title {
    font-size: 10pt;
    color: #475569;
    margin-top: 3px;
}
.header-meta {
    font-size: 7.5pt;
    color: #94a3b8;
    margin-top: 2px;
}
.header-type {
    display: inline-block;
    background: #0d9488;
    color: #fff;
    padding: 2px 10px;
    border-radius: 3px;
    font-size: 7.5pt;
    font-weight: bold;
    letter-spacing: .05em;
    margin-top: 6px;
    text-transform: uppercase;
}

/* ── Bande info période ── */
.info-band {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #0d9488;
    padding: 8px 14px;
    margin-bottom: 20px;
    font-size: 8pt;
    color: #334155;
    display: table;
    width: 100%;
}
.info-band .ib-left  { display: table-cell; vertical-align: middle; width: 70%; }
.info-band .ib-right { display: table-cell; vertical-align: middle; text-align: right; width: 30%; color: #64748b; }
.info-band strong { color: #0d2238; }

/* ── Sections ── */
.section {
    margin-bottom: 20px;
    page-break-inside: avoid;
}
.section-title {
    font-size: 9pt;
    font-weight: bold;
    color: #0d2238;
    text-transform: uppercase;
    letter-spacing: .08em;
    padding: 7px 12px;
    background: #f1f5f9;
    border-left: 4px solid #0d9488;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 0;
}

/* ── Tableaux de données ── */
table.tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 8.5pt;
}
table.tbl thead tr {
    background: #0d2238;
    color: #fff;
}
table.tbl thead th {
    padding: 7px 11px;
    text-align: left;
    font-size: 7.5pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .05em;
}
table.tbl thead th.r { text-align: right; }
table.tbl thead th.c { text-align: center; }

table.tbl tbody tr {
    border-bottom: 1px solid #e2e8f0;
}
table.tbl tbody tr:nth-child(even) {
    background: #f8fafc;
}
table.tbl tbody td {
    padding: 7px 11px;
    vertical-align: middle;
}
table.tbl tbody td.r { text-align: right; }
table.tbl tbody td.c { text-align: center; }
table.tbl tbody td.mono { font-family: 'Courier New', monospace; font-weight: bold; }

table.tbl tfoot tr {
    background: #e0f2f1;
    border-top: 2px solid #0d9488;
    font-weight: bold;
}
table.tbl tfoot td { padding: 7px 11px; }
table.tbl tfoot td.r { text-align: right; }

/* Ligne de sous-total / séparation */
tr.sep td {
    background: #f1f5f9 !important;
    font-weight: bold;
    font-size: 7.5pt;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .05em;
    border-top: 1px solid #cbd5e1;
    padding: 5px 11px;
}

/* ── Badges ── */
.badge {
    display: inline-block;
    padding: 1px 7px;
    border-radius: 3px;
    font-size: 7pt;
    font-weight: bold;
}
.b-green  { background: #dcfce7; color: #15803d; }
.b-red    { background: #fee2e2; color: #b91c1c; }
.b-amber  { background: #fef3c7; color: #b45309; }
.b-blue   { background: #dbeafe; color: #1d4ed8; }
.b-purple { background: #ede9fe; color: #6d28d9; }
.b-gray   { background: #f1f5f9; color: #475569; }
.b-teal   { background: #ccfbf1; color: #0f766e; }

/* ── Barre de progression ── */
.prog-row { display: table; width: 100%; margin: 3px 0; }
.prog-label-cell { display: table-cell; width: 30%; font-size: 7.5pt; color: #334155; vertical-align: middle; }
.prog-bar-cell   { display: table-cell; width: 55%; vertical-align: middle; padding: 0 8px; }
.prog-pct-cell   { display: table-cell; width: 15%; text-align: right; font-size: 7.5pt; font-weight: bold; color: #0f766e; vertical-align: middle; }
.prog-bg   { background: #e2e8f0; height: 5px; border-radius: 3px; }
.prog-fill { height: 5px; border-radius: 3px; background: #0d9488; }

/* ── Texte utilitaires ── */
.txt-muted  { color: #94a3b8; }
.txt-green  { color: #15803d; }
.txt-red    { color: #b91c1c; }
.txt-teal   { color: #0f766e; }
.txt-amber  { color: #b45309; }
.bold       { font-weight: bold; }

/* ── Pied de page fixe ── */
.footer {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    border-top: 1px solid #e2e8f0;
    padding: 5px 24px;
    font-size: 7pt;
    color: #94a3b8;
    background: #fff;
    display: table;
    width: 100%;
}
.footer .fl { display: table-cell; }
.footer .fr { display: table-cell; text-align: right; }
.footer .conf { color: #b91c1c; font-weight: bold; }

/* ── Saut de page ── */
.pb { page-break-after: always; }

/* ── Note ── */
.note {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: 3px solid #94a3b8;
    padding: 8px 12px;
    font-size: 7.5pt;
    color: #64748b;
    margin-top: 14px;
    border-radius: 0 3px 3px 0;
}
</style>
</head>
<body>

{{-- ── Pied de page (fixe sur toutes les pages) ── --}}
<div class="footer">
    <span class="fl">
        <span class="conf">CONFIDENTIEL</span>
        &nbsp;—&nbsp;
        {{ $tenant?->name ?? config('app.name') }}
        &nbsp;—&nbsp;
        Rapport RH généré le {{ $generatedAt }}
    </span>
    <span class="fr">medstaff HR Solutions</span>
</div>

{{-- ══════════════════════════════════════════════
     EN-TÊTE
══════════════════════════════════════════════ --}}
<div class="header">
    <div class="header-left">
        <div class="header-org">{{ $tenant?->name ?? config('app.name') }}</div>
        <div class="header-title">Rapport RH — Récapitulatif opérationnel et financier</div>
        <div class="header-meta">Département : {{ $deptName }}</div>
        <div>
            <span class="header-type">
                {{ str_replace(['month','quarter','year','custom'],['Mensuel','Trimestriel','Annuel','Personnalisé'], request('periode','month')) }}
            </span>
        </div>
    </div>
    <div class="header-right">
        <div style="font-size:8.5pt;color:#475569">Généré le</div>
        <div style="font-size:10pt;font-weight:bold;color:#0d2238">{{ $generatedAt }}</div>
    </div>
</div>

{{-- ── Bande période ── --}}
<div class="info-band">
    <div class="ib-left">
        Période : <strong>{{ $startDate->format('d/m/Y') }}</strong>
        au <strong>{{ $endDate->format('d/m/Y') }}</strong>
        &nbsp;&middot;&nbsp; <strong>{{ $joursOuvrables }}</strong> jours ouvrables
        &nbsp;&middot;&nbsp; <strong>{{ $nbrSalaries }}</strong> salarié(s) actif(s)
    </div>
    <div class="ib-right">Département : {{ $deptName }}</div>
</div>

{{-- ══════════════════════════════════════════════
     PARTIE 1 — RESSOURCES HUMAINES
══════════════════════════════════════════════ --}}
<div class="section">
    <div class="section-title">Partie I — Indicateurs Ressources Humaines</div>
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:55%">Indicateur</th>
                <th class="r">Valeur</th>
                <th class="c" style="width:18%">Statut</th>
            </tr>
        </thead>
        <tbody>
            <tr class="sep"><td colspan="3">Effectifs</td></tr>
            <tr>
                <td>Salariés actifs sur la période</td>
                <td class="r mono">{{ number_format($nbrSalaries) }}</td>
                <td class="c">—</td>
            </tr>
            <tr>
                <td>Employés sans planning sur la période</td>
                <td class="r mono">{{ $empSansPlanning }}</td>
                <td class="c">
                    @if($empSansPlanning > 0)
                        <span class="badge b-amber">À régulariser</span>
                    @else
                        <span class="badge b-green">Complet</span>
                    @endif
                </td>
            </tr>

            <tr class="sep"><td colspan="3">Absences</td></tr>
            <tr>
                <td>Nombre de demandes d'absence approuvées</td>
                <td class="r mono">{{ number_format($nbrAbsences) }}</td>
                <td class="c">—</td>
            </tr>
            <tr>
                <td>Jours d'absence cumulés</td>
                <td class="r mono">{{ $joursAbsence }} j</td>
                <td class="c">—</td>
            </tr>
            <tr>
                <td>Taux d'absentéisme
                    <span class="txt-muted" style="font-size:7.5pt">(jours abs. / jours ouvrables × effectif)</span>
                </td>
                <td class="r mono">{{ $tauxAbsenteisme }}%</td>
                <td class="c">
                    @if($tauxAbsenteisme > 5)
                        <span class="badge b-red">Élevé (&gt; 5%)</span>
                    @else
                        <span class="badge b-green">Normal</span>
                    @endif
                </td>
            </tr>

            <tr class="sep"><td colspan="3">Temps de travail</td></tr>
            <tr>
                <td>Heures planifiées (pause déjeuner déduite)</td>
                <td class="r mono">{{ number_format($heurePlanifiees, 1) }} h</td>
                <td class="c">—</td>
            </tr>
            <tr>
                <td>Heures pointées validées</td>
                <td class="r mono">{{ number_format($heuresPointees, 1) }} h</td>
                <td class="c">—</td>
            </tr>
            <tr>
                <td>Heures supplémentaires</td>
                <td class="r mono">+{{ number_format($heuresSupp, 1) }} h</td>
                <td class="c">
                    @if($heuresSupp > 0)
                        <span class="badge b-teal">Surplus</span>
                    @else
                        <span class="badge b-gray">Néant</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Taux de présence global</td>
                <td class="r mono">{{ $tauxPresence }}%</td>
                <td class="c">
                    @if($tauxPresence >= 90)
                        <span class="badge b-green">Bon</span>
                    @elseif($tauxPresence >= 70)
                        <span class="badge b-amber">Moyen</span>
                    @else
                        <span class="badge b-red">Faible</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── Absences par type ── --}}
@if($absencesParType->isNotEmpty())
<div class="section">
    <div class="section-title">Détail des absences par type</div>
    <table class="tbl">
        <thead>
            <tr>
                <th>Type d'absence</th>
                <th class="c">Demandes</th>
                <th class="c">Jours totaux</th>
                <th class="r">Moy. j / demande</th>
                <th class="r">Part du total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalJours = $absencesParType->sum('jours');
                $labels     = ['conge_paye' => 'Congé payé', 'maladie' => 'Maladie', 'sans_solde' => 'Sans solde', 'maternite' => 'Maternité', 'autre' => 'Autre'];
            @endphp
            @foreach($absencesParType as $abs)
            <tr>
                <td>{{ $labels[$abs->type] ?? ucfirst(str_replace('_', ' ', $abs->type)) }}</td>
                <td class="c mono">{{ $abs->count }}</td>
                <td class="c mono">{{ round($abs->jours, 1) }} j</td>
                <td class="r mono">{{ $abs->count > 0 ? number_format($abs->jours / $abs->count, 1) : '—' }}</td>
                <td class="r mono">{{ $totalJours > 0 ? round(($abs->jours / $totalJours) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="bold">Total</td>
                <td class="c mono bold">{{ $absencesParType->sum('count') }}</td>
                <td class="c mono bold">{{ round($totalJours, 1) }} j</td>
                <td class="r">—</td>
                <td class="r mono bold">100%</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- ── Répartition par département ── --}}
@if($repartitionDept->isNotEmpty())
<div class="section">
    <div class="section-title">Répartition des effectifs par département</div>
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:40%">Département</th>
                <th class="c">Effectif</th>
                <th style="width:45%">Répartition</th>
                <th class="r">Part</th>
            </tr>
        </thead>
        <tbody>
            @foreach($repartitionDept as $dept)
            @php $pct = $nbrSalaries > 0 ? round(($dept->total / $nbrSalaries) * 100) : 0; @endphp
            <tr>
                <td>{{ $dept->dept }}</td>
                <td class="c mono">{{ $dept->total }}</td>
                <td>
                    <div class="prog-bg">
                        <div class="prog-fill" style="width:{{ $pct }}%"></div>
                    </div>
                </td>
                <td class="r mono">{{ $pct }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="bold">Total</td>
                <td class="c mono bold">{{ $nbrSalaries }}</td>
                <td></td>
                <td class="r mono bold">100%</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

<div class="pb"></div>

{{-- ══════════════════════════════════════════════
     PARTIE 2 — FINANCIER
══════════════════════════════════════════════ --}}

{{-- En-tête de la 2e page --}}
<div class="header">
    <div class="header-left">
        <div class="header-org">{{ $tenant?->name ?? config('app.name') }}</div>
        <div class="header-title">Rapport RH — Récapitulatif financier et paie</div>
        <div class="header-meta">Département : {{ $deptName }}</div>
    </div>
    <div class="header-right">
        <div style="font-size:8.5pt;color:#475569">Période</div>
        <div style="font-size:9pt;font-weight:bold;color:#0d2238">
            {{ $startDate->format('d/m/Y') }} — {{ $endDate->format('d/m/Y') }}
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">Partie II — Masse salariale et charges sociales</div>
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:52%">Libellé</th>
                <th class="r">Montant (MAD)</th>
                <th class="r">Taux / % masse brute</th>
                <th class="c" style="width:14%">Nature</th>
            </tr>
        </thead>
        <tbody>
            <tr class="sep"><td colspan="4">Masse salariale</td></tr>
            <tr>
                <td class="bold">Masse salariale brute</td>
                <td class="r mono bold">{{ number_format($masseSalarialeBrute, 2, ',', ' ') }}</td>
                <td class="r">Base — 100%</td>
                <td class="c"><span class="badge b-blue">Base</span></td>
            </tr>
            <tr>
                <td class="bold txt-teal">Net à payer total</td>
                <td class="r mono bold txt-teal">{{ number_format($netTotal, 2, ',', ' ') }}</td>
                <td class="r">{{ $masseSalarialeBrute > 0 ? number_format($netTotal/$masseSalarialeBrute*100, 1) : 0 }}%</td>
                <td class="c"><span class="badge b-teal">Débit</span></td>
            </tr>

            <tr class="sep"><td colspan="4">Cotisations salariales (déduites du brut)</td></tr>
            <tr>
                <td>CNSS — part salariale</td>
                <td class="r mono">{{ number_format($cnssEmployee, 2, ',', ' ') }}</td>
                <td class="r">4,48%</td>
                <td class="c"><span class="badge b-blue">Retenue</span></td>
            </tr>
            <tr>
                <td>AMO — part salariale</td>
                <td class="r mono">{{ number_format($amoEmployee, 2, ',', ' ') }}</td>
                <td class="r">2,26%</td>
                <td class="c"><span class="badge b-blue">Retenue</span></td>
            </tr>
            <tr>
                <td>Impôt sur le revenu (IR) retenu à la source</td>
                <td class="r mono txt-red">{{ number_format($irRetenu, 2, ',', ' ') }}</td>
                <td class="r">{{ $masseSalarialeBrute > 0 ? number_format($irRetenu/$masseSalarialeBrute*100, 1) : 0 }}%</td>
                <td class="c"><span class="badge b-red">DGI/IR</span></td>
            </tr>
            <tr>
                <td>Total charges salariales</td>
                <td class="r mono bold">{{ number_format($chargesSalariales, 2, ',', ' ') }}</td>
                <td class="r">{{ $masseSalarialeBrute > 0 ? number_format($chargesSalariales/$masseSalarialeBrute*100, 1) : 0 }}%</td>
                <td class="c">—</td>
            </tr>

            <tr class="sep"><td colspan="4">Charges patronales (à la charge de l'employeur)</td></tr>
            <tr>
                <td>CNSS — part patronale</td>
                <td class="r mono">{{ number_format($cnssPatron, 2, ',', ' ') }}</td>
                <td class="r">8,98%</td>
                <td class="c"><span class="badge b-amber">Charge</span></td>
            </tr>
            <tr>
                <td>AMO — part patronale</td>
                <td class="r mono">{{ number_format($amoPatron, 2, ',', ' ') }}</td>
                <td class="r">2,26%</td>
                <td class="c"><span class="badge b-amber">Charge</span></td>
            </tr>
            @php $tfp = $masseSalarialeBrute * 0.016; @endphp
            <tr>
                <td>Taxe de formation professionnelle</td>
                <td class="r mono">{{ number_format($tfp, 2, ',', ' ') }}</td>
                <td class="r">1,60%</td>
                <td class="c"><span class="badge b-amber">Charge</span></td>
            </tr>

            <tr class="sep"><td colspan="4">Déclaration fiscale</td></tr>
            <tr>
                <td>DGI — Déclaration mensuelle (IR + TFP)</td>
                <td class="r mono txt-red bold">{{ number_format($dgiMensuelle, 2, ',', ' ') }}</td>
                <td class="r">{{ $masseSalarialeBrute > 0 ? number_format($dgiMensuelle/$masseSalarialeBrute*100, 1) : 0 }}%</td>
                <td class="c"><span class="badge b-purple">Fiscal</span></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td class="bold">Coût employeur total (brut + charges patronales)</td>
                <td class="r mono bold">{{ number_format($coutEmployeur, 2, ',', ' ') }}</td>
                <td class="r bold">{{ $masseSalarialeBrute > 0 ? number_format($coutEmployeur/$masseSalarialeBrute*100, 1) : 0 }}%</td>
                <td class="c"><span class="badge b-teal">Total</span></td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- ── Bulletins de paie ── --}}
<div class="section">
    <div class="section-title">Partie III — État des bulletins de paie</div>
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:55%">Indicateur</th>
                <th class="r">Valeur</th>
                <th class="c" style="width:20%">Observation</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Bulletins générés</td>
                <td class="r mono">{{ $bulletinsTotal }}</td>
                <td class="c">—</td>
            </tr>
            <tr>
                <td>Bulletins validés ou payés</td>
                <td class="r mono">{{ $bulletinsValides }}</td>
                <td class="c">
                    @php $pctVal = $bulletinsTotal > 0 ? round($bulletinsValides/$bulletinsTotal*100) : 0; @endphp
                    <span class="badge {{ $pctVal == 100 ? 'b-green' : ($pctVal >= 50 ? 'b-amber' : 'b-red') }}">
                        {{ $pctVal }}% validés
                    </span>
                </td>
            </tr>
            <tr>
                <td>Bulletins en attente</td>
                <td class="r mono">{{ max(0, $bulletinsTotal - $bulletinsValides) }}</td>
                <td class="c">
                    @if($bulletinsTotal - $bulletinsValides > 0)
                        <span class="badge b-amber">À valider</span>
                    @else
                        <span class="badge b-green">Complet</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Salaire moyen brut par bulletin</td>
                <td class="r mono">{{ number_format($salaireMoyenBrut, 2, ',', ' ') }} MAD</td>
                <td class="c">—</td>
            </tr>
            <tr>
                <td>Salaire moyen net par bulletin</td>
                <td class="r mono txt-teal bold">{{ number_format($salaireMoyenNet, 2, ',', ' ') }} MAD</td>
                <td class="c">—</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── Évolution masse salariale ── --}}
@if(!empty($evolutionMasse) && collect($evolutionMasse)->sum('montant') > 0)
<div class="section">
    <div class="section-title">Évolution de la masse salariale — 3 derniers mois</div>
    <table class="tbl">
        <thead>
            <tr>
                <th>Période</th>
                <th class="r">Masse salariale brute (MAD)</th>
                <th class="r">Variation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($evolutionMasse as $i => $evo)
            <tr>
                <td>{{ $evo['label'] }}</td>
                <td class="r mono">{{ number_format($evo['montant'], 2, ',', ' ') }}</td>
                <td class="r">
                    @if($i > 0 && $evolutionMasse[$i-1]['montant'] > 0)
                    @php
                        $prev = $evolutionMasse[$i-1]['montant'];
                        $diff = round((($evo['montant'] - $prev) / $prev) * 100, 1);
                    @endphp
                    <span class="badge {{ $diff >= 0 ? 'b-green' : 'b-red' }}">
                        {{ $diff >= 0 ? '+' : '' }}{{ $diff }}%
                    </span>
                    @else
                    <span class="txt-muted">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Récapitulatif exécutif ── --}}
<div class="section">
    <div class="section-title">Récapitulatif exécutif</div>
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:60%">Indicateur clé</th>
                <th class="r">Valeur</th>
                <th class="c" style="width:18%">Appréciation</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Effectif actif</td>
                <td class="r mono bold">{{ $nbrSalaries }} salariés</td>
                <td class="c">—</td>
            </tr>
            <tr>
                <td>Taux d'absentéisme</td>
                <td class="r mono">{{ $tauxAbsenteisme }}%</td>
                <td class="c">
                    <span class="badge {{ $tauxAbsenteisme > 5 ? 'b-red' : 'b-green' }}">
                        {{ $tauxAbsenteisme > 5 ? 'Élevé' : 'Normal' }}
                    </span>
                </td>
            </tr>
            <tr>
                <td>Taux de présence</td>
                <td class="r mono">{{ $tauxPresence }}%</td>
                <td class="c">
                    <span class="badge {{ $tauxPresence >= 90 ? 'b-green' : ($tauxPresence >= 70 ? 'b-amber' : 'b-red') }}">
                        {{ $tauxPresence >= 90 ? 'Bon' : ($tauxPresence >= 70 ? 'Moyen' : 'Faible') }}
                    </span>
                </td>
            </tr>
            <tr>
                <td>Masse salariale brute</td>
                <td class="r mono bold">{{ number_format($masseSalarialeBrute, 2, ',', ' ') }} MAD</td>
                <td class="c">—</td>
            </tr>
            <tr>
                <td>Coût employeur total</td>
                <td class="r mono">{{ number_format($coutEmployeur, 2, ',', ' ') }} MAD</td>
                <td class="c">—</td>
            </tr>
            <tr>
                <td>Net à payer total</td>
                <td class="r mono txt-teal bold">{{ number_format($netTotal, 2, ',', ' ') }} MAD</td>
                <td class="c">—</td>
            </tr>
            <tr>
                <td>IR retenu à la source</td>
                <td class="r mono txt-red">{{ number_format($irRetenu, 2, ',', ' ') }} MAD</td>
                <td class="c">—</td>
            </tr>
            <tr>
                <td>DGI — Déclaration mensuelle</td>
                <td class="r mono">{{ number_format($dgiMensuelle, 2, ',', ' ') }} MAD</td>
                <td class="c">—</td>
            </tr>
            <tr>
                <td>Bulletins de paie validés / générés</td>
                <td class="r mono">{{ $bulletinsValides }} / {{ $bulletinsTotal }}</td>
                <td class="c">
                    <span class="badge {{ $bulletinsValides >= $bulletinsTotal && $bulletinsTotal > 0 ? 'b-green' : 'b-amber' }}">
                        {{ $bulletinsTotal > 0 ? round($bulletinsValides/$bulletinsTotal*100) : 0 }}%
                    </span>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="note">
        Taux de cotisation appliqués (Maroc) : CNSS salariale 4,48% — CNSS patronale 8,98% — AMO 2,26% (part salariale et patronale) — Taxe de formation professionnelle 1,60%.
        Les montants IR sont issus des bulletins de paie validés. Ce rapport est confidentiel et généré automatiquement par {{ $tenant?->name ?? config('app.name') }}.
    </div>
</div>

</body>
</html>
