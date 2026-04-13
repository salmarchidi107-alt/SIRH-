<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin de Paie</title>
    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9pt;
            color: #000;
            background: #fff;
            width: 210mm;
            margin: 0;
            padding: 0;
        }

        .bulletin-paie {
            width: 198mm;
            margin: 0 auto;
            padding: 5mm 0;
        }

        /* ─── EN-TÊTE SOCIÉTÉ ─── */
        .societe-nom {
            font-weight: bold;
            font-size: 14pt;
            letter-spacing: 2px;
            margin-bottom: 1mm;
        }

        .cnss-line {
            font-size: 8pt;
            margin-bottom: 3mm;
        }

        /* ─── BLOC TITRE + PÉRIODE ─── */
        .header-grid {
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 2mm;
        }

        .header-grid table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-titre {
            font-weight: bold;
            font-size: 11pt;
            text-decoration: underline;
            text-align: center;
            padding: 5px 10px;
            border-right: 1px solid #000;
            width: 65%;
        }

        .header-periode {
            text-align: center;
            padding: 3px 8px;
            vertical-align: middle;
            width: 35%;
        }

        .periode-label {
            font-size: 8pt;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }

        .periode-valeur {
            font-size: 8pt;
        }

        /* ─── TABLES GÉNÉRALES ─── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5mm;
            font-size: 8pt;
        }

        table td,
        table th {
            border: 1px solid #000;
            padding: 2px 3px;
            vertical-align: middle;
            font-size: 8pt;
        }

        .row-header th {
            font-weight: bold;
            background: #fff;
            text-align: center;
            font-size: 7.5pt;
        }

        /* ─── TABLE LIGNES DE PAIE ─── */
        .table-lignes thead th {
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            border: 1px solid #000;
            padding: 3px 4px;
            background: #e8e8e8;
        }

        .table-lignes tbody td {
            border: 1px solid #000;
            padding: 5px 4px;
            font-size: 8pt;
            height: 18px;
        }

        .col-libelle {
            text-align: left;
            width: 46%;
        }

        .col-nbre-taux {
            text-align: center;
            width: 14%;
        }

        .col-gain {
            text-align: right;
            width: 13%;
        }

        .col-total {
            text-align: right;
            width: 13%;
        }

        .col-retenues {
            text-align: right;
            width: 14%;
        }

        .ligne-spacer td {
            height: 10px !important;
            border-left: 1px solid #000 !important;
            border-right: 1px solid #000 !important;
            border-top: none !important;
            border-bottom: none !important;
            padding: 0 !important;
        }

        /* ─── TABLE PIED ─── */
        .table-pied {
            margin-top: 1mm;
        }

        .table-pied th,
        .table-pied td {
            font-size: 7.5pt;
            padding: 4px 3px;
            border: 1px solid #000;
            text-align: center;
        }

        .label-cell {
            text-align: left !important;
            font-weight: bold;
        }

        .net-a-payer {
            text-align: right;
            font-weight: bold;
            font-size: 11pt;
            padding: 2px 5px;
        }

        .strikethrough {
            text-decoration: line-through;
        }

        /* ─── LARGEURS COLONNES TABLE EMPLOYÉ ─── */
        .col-matricule { width: 18mm; }
        .col-nom       { width: 50mm; }
        .col-fonction  { width: auto; }
        .col-sm        { width: 12mm; }

    </style>
</head>
<body>
<div class="bulletin-paie">

    {{-- En-tête --}}
    <div class="societe-nom">{{ $societe['nom'] ?? 'HOSPITALRH' }}</div>
    <div class="cnss-line">N° C.N.S.S. : {{ $societe['cnss'] ?? '' }}</div>

    {{-- Titre + Période --}}
    <div class="header-grid">
        <table>
            <tr>
                <td class="header-titre">Bulletin de paie</td>
                <td class="header-periode">
                    <div class="periode-label">Période de paie</div>
                    <div class="periode-valeur">Du {{ $periode['debut'] ?? '01/03/2026' }} Au {{ $periode['fin'] ?? '31/03/2026' }}</div>
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
                <td style="text-align:center;">{{ $employe['matricule'] ?? '1' }}</td>
                <td><strong>{{ $employe['nom'] ?? 'ER-RCHIDI SALMA' }}</strong></td>
                <td style="text-align:center; font-weight:bold;">{{ $employe['fonction'] ?? 'docteur' }}</td>
                <td style="text-align:center;">{{ $employe['paie'] ?? '01' }}</td>
                <td style="text-align:center;">{{ $employe['depart'] ?? 'Pédiatrie' }}</td>
                <td style="text-align:center;">{{ $employe['sect'] ?? '01' }}</td>
                <td style="text-align:center;">{{ $employe['categ'] ?? '01' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Adresse --}}
    <table>
        <tbody>
            <tr>
                <td>Adresse : {{ $employe['adresse'] ?? '' }}</td>
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
                <td></td>
                <td style="text-align:center;">{{ $employe['date_embauche'] ?? '01/01/2026' }}</td>
                <td style="text-align:center;">{{ $employe['date_paie'] ?? '01/02/2026' }}</td>
                <td style="text-align:center;">{{ $employe['sit_familiale'] ?? 'Marié' }}</td>
                <td style="text-align:center;">{{ $employe['charge'] ?? '0' }}</td>
                <td style="text-align:center;">{{ $employe['cin'] ?? '' }}</td>
                <td style="text-align:center;">{{ $employe['num_cnss'] ?? '' }}</td>
                <td style="text-align:center;">{{ $employe['num_cimr'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Table des lignes de paie --}}
    <table class="table-lignes">
        <thead>
            <tr>
                <th class="col-libelle">Libellé</th>
                <th class="col-nbre-taux">Nbre ou taux</th>
                <th class="col-gain">Gain</th>
                <th class="col-total">Total</th>
                <th class="col-retenues">Retenues</th>
            </tr>
        </thead>
        <tbody>

            <tr>
                <td class="col-libelle">Salaire de base</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain">{{ number_format($paie['salaire_base'] ?? 30000, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>

            <tr class="ligne-spacer"><td></td><td></td><td></td><td></td><td></td></tr>

            <tr>
                <td class="col-libelle">Prime d'ancienneté</td>
                <td class="col-nbre-taux">{{ $paie['taux_anciennete'] ?? '' }}</td>
                <td class="col-gain">{{ number_format($paie['prime_anciennete'] ?? 0, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>

            <tr>
                <td class="col-libelle">Indemnités imposables</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain">{{ number_format($paie['indemnites_imposables'] ?? 0, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>

            <tr>
                <td class="col-libelle">Indemnités non imposables</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain">{{ number_format($paie['indemnites_non_imposables'] ?? 0, 2, ',', ' ') }}</td>
                <td class="col-total">{{ number_format($paie['total_indemnites'] ?? 0, 2, ',', ' ') }}</td>
                <td class="col-retenues"></td>
            </tr>

            <tr class="ligne-spacer"><td></td><td></td><td></td><td></td><td></td></tr>

            <tr>
                <td class="col-libelle"><strong>Salaire brut</strong></td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain"><strong>{{ number_format($paie['salaire_brut'] ?? 30000, 2, ',', ' ') }}</strong></td>
                <td class="col-total"><strong>{{ number_format($paie['salaire_brut'] ?? 30000, 2, ',', ' ') }}</strong></td>
                <td class="col-retenues"></td>
            </tr>

            <tr>
                <td class="col-libelle">Frais professionnels</td>
                <td class="col-nbre-taux">{{ $paie['taux_frais_pro'] ?? '20%' }}</td>
                <td class="col-gain">{{ number_format($paie['frais_professionnels'] ?? 2500, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>

            <tr>
                <td class="col-libelle">Cotisation CNSS</td>
                <td class="col-nbre-taux">{{ $paie['taux_cnss'] ?? '4.48%' }} &nbsp; {{ number_format($paie['base_cnss'] ?? 6000, 0, ',', ' ') }}</td>
                <td class="col-gain"></td>
                <td class="col-total"></td>
                <td class="col-retenues">{{ number_format($paie['cotisation_cnss'] ?? 268.80, 2, ',', ' ') }}</td>
            </tr>

            <tr>
                <td class="col-libelle">Cotisation AMO</td>
                <td class="col-nbre-taux">{{ $paie['taux_amo'] ?? '2.26%' }} &nbsp; {{ number_format($paie['base_amo'] ?? 3333, 0, ',', ' ') }}</td>
                <td class="col-gain"></td>
                <td class="col-total"></td>
                <td class="col-retenues">{{ number_format($paie['cotisation_amo'] ?? 678.00, 2, ',', ' ') }}</td>
            </tr>

            <tr>
                <td class="col-libelle">Assurance retraite complémentaire</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain"></td>
                <td class="col-total"></td>
                <td class="col-retenues">{{ number_format($paie['retraite_complementaire'] ?? 0, 2, ',', ' ') }}</td>
            </tr>

            <tr class="ligne-spacer"><td></td><td></td><td></td><td></td><td></td></tr>

            <tr>
                <td class="col-libelle"><strong>Salaire net imposable</strong></td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain"><strong>{{ number_format($paie['salaire_net_imposable'] ?? 2333.52, 2, ',', ' ') }}</strong></td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>

            <tr class="ligne-spacer"><td></td><td></td><td></td><td></td><td></td></tr>

            <tr>
                <td class="col-libelle">Intérêts crédit logement</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain">{{ number_format($paie['interets_credit_logement'] ?? 0, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>

            <tr>
                <td class="col-libelle">Base imposable</td>
                <td class="col-nbre-taux"></td>
                <td class="col-gain">{{ number_format($paie['base_imposable'] ?? 3333, 2, ',', ' ') }}</td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>

            <tr class="ligne-spacer"><td></td><td></td><td></td><td></td><td></td></tr>

            <tr>
                <td class="col-libelle">Impôt sur le revenu</td>
                <td class="col-nbre-taux">{{ $paie['taux_ir'] ?? '38%' }}</td>
                <td class="col-gain"></td>
                <td class="col-total"></td>
                <td class="col-retenues"></td>
            </tr>

            {{-- Lignes vides --}}
            <tr style="height:18px;"><td class="col-libelle"></td><td class="col-nbre-taux"></td><td class="col-gain"></td><td class="col-total"></td><td class="col-retenues"></td></tr>
            <tr style="height:18px;"><td class="col-libelle"></td><td class="col-nbre-taux"></td><td class="col-gain"></td><td class="col-total"></td><td class="col-retenues"></td></tr>
            <tr style="height:18px;"><td class="col-libelle"></td><td class="col-nbre-taux"></td><td class="col-gain"></td><td class="col-total"></td><td class="col-retenues"></td></tr>
            <tr style="height:18px;"><td class="col-libelle"></td><td class="col-nbre-taux"></td><td class="col-gain"></td><td class="col-total"></td><td class="col-retenues"></td></tr>
            <tr style="height:18px;"><td class="col-libelle"></td><td class="col-nbre-taux"></td><td class="col-gain"></td><td class="col-total"></td><td class="col-retenues"></td></tr>

        </tbody>
    </table>

    {{-- Pied de page du bulletin --}}
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
                <td rowspan="3" style="text-align:right; vertical-align:bottom; font-weight:bold; font-size:8pt; border-left:1px solid #000; width:14mm;">
                    .{{ $paie['cumul_retenues_cents'] ?? '28' }}<br>
                    <span class="strikethrough">Cumul déductions</span>
                </td>
                <td rowspan="3" style="text-align:center; vertical-align:middle; font-size:7.5pt; border-left:1px solid #000; width:14mm;">
                    Cumul retenues IR
                </td>
            </tr>
            <tr>
                <th class="label-cell">Monétaire</th>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td>
                <td colspan="2" style="text-align:center; font-size:8pt;">Cumuls retenus</td>
            </tr>
            <tr>
                <th class="label-cell">Jours IR</th>
                <td colspan="2" style="font-weight:bold;">Hrs IR</td>
                <td colspan="3" style="font-weight:bold; text-align:center;">Cumul brut impos.</td>
                <td colspan="3" style="font-weight:bold; text-align:center;">Cumul base impos.</td>
                <td colspan="2" style="font-weight:bold; text-align:center;">Net à Payer</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td>
                <td colspan="2" class="net-a-payer">
                    {{ number_format($paie['net_a_payer'] ?? 20996.32, 2, ',', ' ') }}
                </td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

</div>
</body>
</html>