<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin de Paie</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9pt;
            color: #000;
            background: #fff;
            width: 210mm;
            margin: 0;
            padding: 0;
        }
        .bulletin-paie { width: 198mm; margin: 0 auto; padding: 5mm 0; }
        .societe-nom { font-weight: bold; font-size: 14pt; letter-spacing: 2px; margin-bottom: 1mm; }
        .cnss-line { font-size: 8pt; margin-bottom: 3mm; }
        .header-grid { width: 100%; border: 1px solid #000; margin-bottom: 2mm; }
        .header-grid table { width: 100%; border-collapse: collapse; }
        .header-titre {
            font-weight: bold; font-size: 11pt; text-decoration: underline;
            text-align: center; padding: 5px 10px; border-right: 1px solid #000; width: 65%;
        }
        .header-periode { text-align: center; padding: 3px 8px; vertical-align: middle; width: 35%; }
        .periode-label { font-size: 8pt; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 2px; }
        .periode-valeur { font-size: 8pt; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1.5mm; font-size: 8pt; }
        table td, table th { border: 1px solid #000; padding: 2px 3px; vertical-align: middle; font-size: 8pt; }
        .row-header th { font-weight: bold; background: #fff; text-align: center; font-size: 7.5pt; }
        .table-lignes thead th {
            text-align: center; font-weight: bold; font-size: 8pt;
            border: 1px solid #000; padding: 3px 4px; background: #e8e8e8;
        }
        .table-lignes tbody td { border: 1px solid #000; padding: 5px 4px; font-size: 8pt; height: 18px; }
        .col-libelle { text-align: left; width: 46%; }
        .col-nbre-taux { text-align: center; width: 14%; }
        .col-gain { text-align: right; width: 13%; }
        .col-total { text-align: right; width: 13%; }
        .col-retenues { text-align: right; width: 14%; }
        .ligne-spacer td {
            height: 10px !important;
            border-left: 1px solid #000 !important;
            border-right: 1px solid #000 !important;
            border-top: none !important;
            border-bottom: none !important;
            padding: 0 !important;
        }
        .table-pied { margin-top: 1mm; }
        .table-pied th, .table-pied td { font-size: 7.5pt; padding: 4px 3px; border: 1px solid #000; text-align: center; }
        .label-cell { text-align: left !important; font-weight: bold; }
        .net-a-payer { text-align: right; font-weight: bold; font-size: 11pt; padding: 2px 5px; }
        .col-matricule { width: 18mm; }
        .col-nom { width: 50mm; }
        .col-fonction { width: auto; }
        .col-sm { width: 12mm; }
        .cur { font-size: 7pt; color: #555; }
    </style>
</head>
<body>
@php
    $emp = $salary->employee;
    $cur = $salary->currency ?? 'MAD';

    // ── Normalisation situation familiale ─────────────────────────────────────
    // CORRECTION : lire family_situation et non family_status
    $rawStatus = strtolower(trim($emp->family_situation ?? ''));
    // Supprimer accents et suffixes courants
    $rawStatus = str_replace(
        ['é','è','ê','ë','à','â','(e)','(é)', ' '],
        ['e','e','e','e','a','a','',   '',    '_'],
        $rawStatus
    );
    $familyStatusNormalized = match(true) {
        in_array($rawStatus, ['marie', 'marie_e', 'mariee', 'epouse', 'epoux', 'conjoint']) => 'marie',
        in_array($rawStatus, ['divorce', 'divorce_e', 'divorcee'])                           => 'divorce',
        in_array($rawStatus, ['veuf', 'veuve'])                                              => 'veuf',
        default                                                                               => 'celibataire',
    };
    $familyStatusLabels = [
        'marie'       => 'Marié(e)',
        'divorce'     => 'Divorcé(e)',
        'veuf'        => 'Veuf/Veuve',
        'celibataire' => 'Célibataire',
    ];
    $familyStatusLabel = $familyStatusLabels[$familyStatusNormalized] ?? 'Célibataire';

    // Periode
    $mois_debut  = str_pad($salary->month, 2, '0', STR_PAD_LEFT);
    $annee       = $salary->year;
    $dernierJour = \Carbon\Carbon::create($annee, $salary->month, 1)->endOfMonth()->day;
    $debut_str   = '01/' . $mois_debut . '/' . $annee;
    $fin_str     = $dernierJour . '/' . $mois_debut . '/' . $annee;

    // Taux selon devise
    $taux_cnss = $cur === 'MRU' ? '1%'   : '4,48%';
    $taux_amo  = $cur === 'MRU' ? '4%'   : '2,26%';
    $label_amo = $cur === 'MRU' ? 'CNAM' : 'AMO';
    $label_ir  = $cur === 'MRU' ? 'ITS'  : 'IR';

    // Société (depuis tenant ou config)
    $tenant       = auth()->user()?->tenant;
    $societe_nom  = $tenant?->name ?? config('app.name', 'HospitalRH');
    $societe_cnss = $tenant?->cnss ?? '';
@endphp

<div class="bulletin-paie">

    {{-- En-tête société --}}
    <div class="societe-nom">{{ strtoupper($societe_nom) }}</div>
    <div class="cnss-line">N° C.N.S.S. : {{ $societe_cnss }}</div>

    {{-- Titre + Période --}}
    <div class="header-grid">
        <table>
            <tr>
                <td class="header-titre">
                    Bulletin de paie
                    @if($cur === 'MRU')
                        <span style="font-size:8pt;font-weight:normal;"> — Système mauritanien (MRU)</span>
                    @endif
                </td>
                <td class="header-periode">
                    <div class="periode-label">Période de paie</div>
                    <div class="periode-valeur">Du {{ $debut_str }} Au {{ $fin_str }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Identité employé - ligne 1 --}}
    <table>
        <thead>
            <tr class="row-header">
                <th class="col-matricule">Matricule</th>
                <th class="col-nom">Nom et prénom de l'employé</th>
                <th class="col-fonction">Fonction</th>
                <th class="col-sm">Paie</th>
                <th class="col-sm">Départ.</th>
                <th class="col-sm">Sect.</th>
                <th class="col-sm">Categ.</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align:center;">{{ $emp->matricule ?? '—' }}</td>
                <td><strong>{{ strtoupper($emp->last_name) }} {{ $emp->first_name }}</strong></td>
                <td style="text-align:center;font-weight:bold;">{{ $emp->position ?? '—' }}</td>
                <td style="text-align:center;">01</td>
                <td style="text-align:center;">{{ $emp->department ?? '—' }}</td>
                <td style="text-align:center;">01</td>
                <td style="text-align:center;">01</td>
            </tr>
        </tbody>
    </table>

    {{-- Adresse --}}
    <table>
        <tbody>
            <tr>
                <td>Adresse : {{ $emp->address ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Identité employé - ligne 2 --}}
    <table>
        <thead>
            <tr class="row-header">
                <th style="width:22mm;">Naissance</th>
                <th style="width:25mm;">Embauche</th>
                <th style="width:25mm;">Paie</th>
                <th style="width:20mm;">Sit. Famil.</th>
                <th style="width:10mm;">CH</th>
                <th>N° C.I.N</th>
                <th>N° C.N.S.S</th>
                <th>N° CIMR</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align:center;">{{ $emp->birth_date ? \Carbon\Carbon::parse($emp->birth_date)->format('d/m/Y') : '' }}</td>
                <td style="text-align:center;">{{ $emp->hire_date ? \Carbon\Carbon::parse($emp->hire_date)->format('d/m/Y') : '—' }}</td>
                <td style="text-align:center;">{{ $debut_str }}</td>
                {{-- ── CORRECTION : affiche la valeur normalisée depuis family_situation ── --}}
                <td style="text-align:center;">{{ $familyStatusLabel }}</td>
                <td style="text-align:center;">{{ $emp->children_count ?? 0 }}</td>
                <td style="text-align:center;">{{ $emp->cin ?? '—' }}</td>
                <td style="text-align:center;">{{ $emp->cnss_number ?? '—' }}</td>
                <td style="text-align:center;"></td>
            </tr>
        </tbody>
    </table>

    {{-- Table des lignes de paie --}}
    <table class="table-lignes">
        <thead>
            <tr>
                <th class="col-libelle">Libellé</th>
                <th class="col-nbre-taux">Nbre ou taux</th>
                <th class="col-gain">Gain ({{ $cur }})</th>
                <th class="col-total">Total</th>
                <th class="col-retenues">Retenues ({{ $cur }})</th>
            </tr>
        </thead>
        <tbody>

            <tr>
                <td class="col-libelle">Salaire de base</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain">{{ number_format($salary->base_salary, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>

            @if($salary->seniority_bonus > 0)
            <tr>
                <td class="col-libelle">Prime d'ancienneté</td>
                <td class="col-nbre-taux">{{ ($emp->seniority_rate * 100) }}%</td>
                <td class="col-gain">{{ number_format($salary->seniority_bonus, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>
            @endif

            @if($salary->overtime_day_amount > 0 || $salary->overtime_night_amount > 0 || $salary->overtime_weekend_amount > 0)
            <tr>
                <td class="col-libelle">Heures supplémentaires</td>
                <td class="col-nbre-taux">{{ number_format($salary->overtime_hours, 2, ',', ' ') }} h</td>
                <td class="col-gain">{{ number_format($salary->overtime_day_amount + $salary->overtime_night_amount + $salary->overtime_weekend_amount, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>
            @endif

            @if(($salary->garde_indemnite ?? 0) > 0)
            <tr>
                <td class="col-libelle">Indemnité de garde</td>
                <td class="col-nbre-taux">{{ number_format($salary->garde_hours ?? 0, 2, ',', ' ') }} h</td>
                <td class="col-gain">{{ number_format($salary->garde_indemnite, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>
            @endif

            @if($salary->performance_bonus > 0)
            <tr>
                <td class="col-libelle">Prime de rendement</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain">{{ number_format($salary->performance_bonus, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>
            @endif

            @if($salary->transport_allowance > 0)
            <tr>
                <td class="col-libelle">Indemnité de transport</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain">{{ number_format($salary->transport_allowance, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>
            @endif

            @if($salary->meal_allowance > 0)
            <tr>
                <td class="col-libelle">Prime de panier</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain">{{ number_format($salary->meal_allowance, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>
            @endif

            @if($salary->housing_allowance > 0)
            <tr>
                <td class="col-libelle">Indemnité logement</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain">{{ number_format($salary->housing_allowance, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>
            @endif

            @if($salary->responsibility_allowance > 0)
            <tr>
                <td class="col-libelle">Indemnité de responsabilité</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain">{{ number_format($salary->responsibility_allowance, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>
            @endif

            @if($salary->other_gains > 0)
            <tr>
                <td class="col-libelle">Autres gains</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain">{{ number_format($salary->other_gains, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>
            @endif

            <tr class="ligne-spacer"><td></td><td></td><td></td><td></td><td></td></tr>

            <tr>
                <td class="col-libelle"><strong>Salaire brut</strong></td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain"><strong>{{ number_format($salary->gross_salary, 2, ',', ' ') }}</strong></td>
                <td class="col-total"><strong>{{ number_format($salary->gross_salary, 2, ',', ' ') }}</strong></td>
                <td class="col-retenues"></td>
            </tr>
            {{-- Éléments variables gains --}}
@php
    $variableGains = $salary->employee->variableElements()
        ->where('month', $salary->month)
        ->where('year', $salary->year)
        ->where('type', 'gain')
        ->get();
    $variableRetenues = $salary->employee->variableElements()
        ->where('month', $salary->month)
        ->where('year', $salary->year)
        ->where('type', 'retenue')
        ->get();
@endphp

@foreach($variableGains as $ve)
<tr>
    <td class="col-libelle">{{ $ve->label }}</td>
    <td class="col-nbre-taux"></td>
    <td class="col-gain">{{ number_format($ve->amount, 2, ',', ' ') }}</td>
    <td class="col-total"></td>
    <td class="col-retenues"></td>
</tr>
@endforeach

            @if($cur !== 'MRU' && $salary->fp_deduction > 0)
            <tr>
                <td class="col-libelle">Frais professionnels</td>
                <td class="col-nbre-taux">20%</td>
                <td class="col-gain">{{ number_format($salary->fp_deduction, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>
            @endif

            <tr>
                <td class="col-libelle">Cotisation CNSS</td>
                <td class="col-nbre-taux">{{ $taux_cnss }} &nbsp; {{ number_format($salary->cnss_base, 0, ',', ' ') }}</td>
                <td class="col-gain"></td>
                <td class="col-total"></td>
                <td class="col-retenues">{{ number_format($salary->cnss_deduction, 2, ',', ' ') }}</td>
            </tr>

            <tr>
                <td class="col-libelle">Cotisation {{ $label_amo }}</td>
                <td class="col-nbre-taux">{{ $taux_amo }} &nbsp; {{ number_format($salary->gross_salary, 0, ',', ' ') }}</td>
                <td class="col-gain"></td>
                <td class="col-total"></td>
                <td class="col-retenues">{{ number_format($salary->amo_deduction, 2, ',', ' ') }}</td>
            </tr>

            <tr class="ligne-spacer"><td></td><td></td><td></td><td></td><td></td></tr>

            <tr>
                <td class="col-libelle"><strong>{{ $cur === 'MRU' ? 'Revenu imposable ITS' : 'Salaire net imposable' }}</strong></td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain"><strong>{{ number_format($salary->taxable_income, 2, ',', ' ') }}</strong></td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>

            <tr class="ligne-spacer"><td></td><td></td><td></td><td></td><td></td></tr>

            @if($salary->absence_deduction > 0)
            <tr>
                <td class="col-libelle">Absences ({{ $salary->absence_days }} j)</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain"></td>
                <td class="col-total"></td>
                <td class="col-retenues">{{ number_format($salary->absence_deduction, 2, ',', ' ') }}</td>
            </tr>
            @endif

            @if($salary->advance_deduction > 0)
            <tr>
                <td class="col-libelle">Avance sur salaire</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain"></td>
                <td class="col-total"></td>
                <td class="col-retenues">{{ number_format($salary->advance_deduction, 2, ',', ' ') }}</td>
            </tr>
            @endif

            @if($salary->loan_deduction > 0)
            <tr>
                <td class="col-libelle">Remboursement prêt</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain"></td>
                <td class="col-total"></td>
                <td class="col-retenues">{{ number_format($salary->loan_deduction, 2, ',', ' ') }}</td>
            </tr>
            @endif

            @if($salary->garnishment_deduction > 0)
            <tr>
                <td class="col-libelle">Saisie sur salaire</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain"></td>
                <td class="col-total"></td>
                <td class="col-retenues">{{ number_format($salary->garnishment_deduction, 2, ',', ' ') }}</td>
            </tr>
            @endif

            @if($salary->other_deductions > 0)
            <tr>
                <td class="col-libelle">Autres retenues</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain"></td>
                <td class="col-total"></td>
                <td class="col-retenues">{{ number_format($salary->other_deductions, 2, ',', ' ') }}</td>
            </tr>
            @endif

            <tr>
                <td class="col-libelle">{{ $label_ir }} — Impôt {{ $cur === 'MRU' ? 'sur Traitements et Salaires' : 'sur le Revenu' }}</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain"></td>
                <td class="col-total"></td>
                <td class="col-retenues">{{ number_format($salary->ir_deduction, 2, ',', ' ') }}</td>
            </tr>

            {{-- Lignes vides --}}
            <tr style="height:18px;"><td class="col-libelle"></td><td class="col-nbre-taux"></td><td class="col-gain"></td><td class="col-total"></td><td class="col-retenues"></td></tr>
            <tr style="height:18px;"><td class="col-libelle"></td><td class="col-nbre-taux"></td><td class="col-gain"></td><td class="col-total"></td><td class="col-retenues"></td></tr>
            <tr style="height:18px;"><td class="col-libelle"></td><td class="col-nbre-taux"></td><td class="col-gain"></td><td class="col-total"></td><td class="col-retenues"></td></tr>

        </tbody>
    </table>

    {{-- Pied de page --}}
    <table class="table-pied">
        <thead>
            <tr>
                <th class="label-cell" style="width:18mm;">Décompte</th>
                <th style="width:12mm;">200</th>
                <th style="width:12mm;">100</th>
                <th style="width:10mm;">50</th>
                <th style="width:10mm;">20</th>
                <th style="width:10mm;">10</th>
                <th style="width:10mm;">5</th>
                <th style="width:10mm;">1</th>
                <th style="width:18mm;">Mode Régl.</th>
                <th colspan="2"><strong>Total</strong></th>
                <td rowspan="3" style="text-align:center;vertical-align:middle;font-size:7.5pt;border-left:1px solid #000;width:14mm;">
                    Cumul retenues {{ $label_ir }}
                </td>
            </tr>
            <tr>
                <th class="label-cell">Monétaire</th>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td>
                <td colspan="2" style="text-align:center;font-size:8pt;">Cumuls retenus</td>
            </tr>
            <tr>
                <th class="label-cell">Jours {{ $label_ir }}</th>
                <td colspan="2" style="font-weight:bold;">Hrs {{ $label_ir }}</td>
                <td colspan="3" style="font-weight:bold;text-align:center;">Cumul brut impos.</td>
                <td colspan="3" style="font-weight:bold;text-align:center;">Cumul base impos.</td>
                <td colspan="2" style="font-weight:bold;text-align:center;">Net à Payer</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td style="text-align:center;font-size:8pt;">{{ $emp->payment_method === 'virement' ? 'Virement' : ucfirst($emp->payment_method ?? 'Espèces') }}</td>
                <td colspan="2" class="net-a-payer">
                    {{ number_format($salary->net_salary, 2, ',', ' ') }} {{ $cur }}
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- Signature --}}
    <div style="margin-top:8mm;display:flex;justify-content:space-between;">
        <div style="font-size:8pt;">
            <div>L'employé(e)</div>
            <div style="margin-top:12mm;">__________________________</div>
            <div>{{ $emp->full_name }}</div>
        </div>
        <div style="font-size:8pt;text-align:right;">
            <div>L'employeur</div>
            <div style="margin-top:12mm;">__________________________</div>
            <div>{{ $societe_nom }}</div>
        </div>
    </div>

</div>
</body>
</html>
