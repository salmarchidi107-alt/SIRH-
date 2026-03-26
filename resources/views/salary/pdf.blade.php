<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de Paie</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --ink: #1a1a2e;
            --accent: #c8102e;
            --muted: #6b7280;
            --line: #d1d5db;
            --bg: #f9f7f4;
            --white: #ffffff;
            --light-row: #f3f4f6;
        }

        body {
            font-family: 'IBM Plex Sans', sans-serif;
            background: var(--bg);
            color: var(--ink);
            padding: 2rem;
            min-height: 100vh;
        }

        .bulletin {
            max-width: 900px;
            margin: 0 auto;
            background: var(--white);
            border: 2px solid var(--ink);
            box-shadow: 6px 6px 0 var(--ink);
        }

        /* ── HEADER ── */
        .header {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: start;
            padding: 1.5rem 2rem;
            border-bottom: 2px solid var(--ink);
            gap: 1rem;
        }

        .company-info h1 {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .company-info p {
            font-size: 0.78rem;
            color: var(--muted);
            line-height: 1.6;
            margin-top: 0.25rem;
        }

        .bulletin-title {
            text-align: center;
        }

        .bulletin-title h2 {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.65rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .bulletin-title .period {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--accent);
            margin-top: 0.25rem;
            border: 2px solid var(--accent);
            padding: 0.2rem 0.75rem;
            display: inline-block;
        }

        .employee-ref {
            text-align: right;
        }

        .employee-ref p {
            font-size: 0.78rem;
            color: var(--muted);
            line-height: 1.6;
        }

        .employee-ref strong {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.85rem;
            color: var(--ink);
            display: block;
        }

        /* ── EMPLOYEE BAND ── */
        .employee-band {
            background: var(--ink);
            color: var(--white);
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            padding: 0.75rem 2rem;
            gap: 1rem;
        }

        .employee-band .field label {
            font-size: 0.6rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            opacity: 0.6;
            display: block;
        }

        .employee-band .field span {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* ── TABLE ── */
        .pay-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
        }

        .pay-table thead tr {
            background: var(--ink);
            color: var(--white);
        }

        .pay-table thead th {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.65rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 0.6rem 1rem;
            text-align: right;
            font-weight: 500;
        }

        .pay-table thead th:first-child {
            text-align: left;
        }

        .pay-table tbody tr {
            border-bottom: 1px solid var(--line);
        }

        .pay-table tbody tr:nth-child(even) {
            background: var(--light-row);
        }

        .pay-table tbody tr.section-header td {
            font-size: 0.6rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--accent);
            font-weight: 600;
            padding: 0.85rem 1rem 0.25rem;
            background: var(--white);
            border-bottom: none;
        }

        .pay-table tbody tr.total-row td {
            font-weight: 600;
            font-family: 'IBM Plex Mono', monospace;
            background: #f0f0f0;
            border-top: 2px solid var(--ink);
            border-bottom: 2px solid var(--ink);
        }

        .pay-table td {
            padding: 0.45rem 1rem;
            text-align: right;
        }

        .pay-table td:first-child {
            text-align: left;
            font-size: 0.82rem;
        }

        .pay-table td.mono {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.8rem;
        }

        .pay-table td.gain {
            color: #166534;
        }

        .pay-table td.retenue {
            color: var(--accent);
        }

        .pay-table td.taux {
            color: var(--muted);
            font-size: 0.75rem;
        }

        /* ── FOOTER SUMMARY ── */
        .summary-bar {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border-top: 2px solid var(--ink);
        }

        .summary-bar .block {
            padding: 1rem 1.5rem;
            border-right: 1px solid var(--line);
        }

        .summary-bar .block:last-child {
            border-right: none;
            background: var(--ink);
            color: var(--white);
        }

        .summary-bar .block label {
            font-size: 0.6rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--muted);
            display: block;
            margin-bottom: 0.3rem;
        }

        .summary-bar .block:last-child label {
            color: rgba(255,255,255,0.6);
        }

        .summary-bar .block .amount {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .summary-bar .block:last-child .amount {
            color: #86efac;
        }

        /* ── DOCUMENT FOOTER ── */
        .doc-footer {
            padding: 1rem 2rem;
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.7rem;
            color: var(--muted);
        }

        .doc-footer .stamp {
            font-family: 'IBM Plex Mono', monospace;
            border: 1.5px solid var(--line);
            padding: 0.25rem 0.6rem;
            letter-spacing: 0.08em;
        }

        @media print {
            body { padding: 0; background: white; }
            .bulletin { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

<div class="bulletin">

    {{-- ── EN-TÊTE ── --}}
    <div class="header">
        <div class="company-info">
            <h1>{{ $entreprise['nom'] ?? 'Société Exemple SARL' }}</h1>
            <p>
                {{ $entreprise['adresse'] ?? 'Rue des Entreprises, Casablanca' }}<br>
                ICE : {{ $entreprise['ice'] ?? '000123456789000' }}<br>
                CNSS : {{ $entreprise['cnss'] ?? '1234567' }}
            </p>
        </div>

        <div class="bulletin-title">
            <h2>Bulletin de Paie</h2>
            <div class="period">
                {{ \Carbon\Carbon::parse($bulletin['periode'] ?? now())->translatedFormat('F Y') }}
            </div>
        </div>

        <div class="employee-ref">
            <p>
                <strong>{{ $employe['nom'] ?? 'NOM Prénom' }}</strong>
                Matricule : {{ $employe['matricule'] ?? '---' }}<br>
                CIN : {{ $employe['cin'] ?? '---' }}<br>
                CNSS : {{ $employe['num_cnss'] ?? '---' }}
            </p>
        </div>
    </div>

    {{-- ── BANDEAU EMPLOYÉ ── --}}
    <div class="employee-band">
        <div class="field">
            <label>Poste</label>
            <span>{{ $employe['poste'] ?? '—' }}</span>
        </div>
        <div class="field">
            <label>Département</label>
            <span>{{ $employe['departement'] ?? '—' }}</span>
        </div>
        <div class="field">
            <label>Date d'entrée</label>
            <span>{{ $employe['date_entree'] ?? '—' }}</span>
        </div>
        <div class="field">
            <label>Catégorie</label>
            <span>{{ $employe['categorie'] ?? '—' }}</span>
        </div>
    </div>

    {{-- ── TABLEAU DES ÉLÉMENTS DE PAIE ── --}}
    <table class="pay-table">
        <thead>
            <tr>
                <th>Libellé</th>
                <th>Nbre ou Taux</th>
                <th>Gain</th>
                <th>Total</th>
                <th>Retenues</th>
            </tr>
        </thead>
        <tbody>

            {{-- RÉMUNÉRATION DE BASE --}}
            <tr class="section-header">
                <td colspan="5">Rémunération</td>
            </tr>

            <tr>
                <td>Salaire de base</td>
                <td></td>
                <td class="mono gain">{{ number_format($salaire['base'] ?? 5000.53, 2, ',', ' ') }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Prime d'ancienneté</td>
                <td class="taux">{{ ($salaire['taux_anciennete'] ?? 10) }}%</td>
                <td class="mono gain">{{ number_format($salaire['prime_anciennete'] ?? 490.75, 2, ',', ' ') }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Indemnités imposables</td>
                <td></td>
                <td class="mono gain">{{ number_format($salaire['indemnites_imposables'] ?? 0, 2, ',', ' ') }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Indemnités non imposables</td>
                <td></td>
                <td></td>
                <td class="mono">{{ number_format($salaire['indemnites_non_imposables'] ?? 0, 2, ',', ' ') }}</td>
                <td></td>
            </tr>

            {{-- SALAIRE BRUT --}}
            <tr class="total-row">
                <td>Salaire brut</td>
                <td></td>
                <td class="mono gain">{{ number_format($salaire['brut'] ?? 5000.28, 2, ',', ' ') }}</td>
                <td class="mono">{{ number_format($salaire['brut'] ?? 5000.28, 2, ',', ' ') }}</td>
                <td></td>
            </tr>

            {{-- DÉDUCTIONS --}}
            <tr class="section-header">
                <td colspan="5">Déductions</td>
            </tr>

            <tr>
                <td>Frais professionnels</td>
                <td class="taux">{{ $cotisations['taux_frais_pro'] ?? 20 }}%</td>
                <td class="mono">{{ number_format($cotisations['base_frais_pro'] ?? 2500, 2, ',', ' ') }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Cotisation CNSS</td>
                <td class="taux">{{ $cotisations['taux_cnss'] ?? 4.48 }}%</td>
                <td class="mono">{{ number_format($cotisations['base_cnss'] ?? 6000, 2, ',', ' ') }}</td>
                <td></td>
                <td class="mono retenue">{{ number_format($cotisations['montant_cnss'] ?? 268.80, 2, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Cotisation AMO</td>
                <td class="taux">{{ $cotisations['taux_amo'] ?? 2.26 }}%</td>
                <td class="mono">{{ number_format($cotisations['base_amo'] ?? 3333, 2, ',', ' ') }}</td>
                <td></td>
                <td class="mono retenue">{{ number_format($cotisations['montant_amo'] ?? 529.96, 2, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Assurance retraite complémentaire</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="mono retenue">{{ number_format($cotisations['retraite_complementaire'] ?? 0, 2, ',', ' ') }}</td>
            </tr>

            {{-- SALAIRE NET IMPOSABLE --}}
            <tr class="total-row">
                <td>Salaire net imposable</td>
                <td></td>
                <td class="mono gain">{{ number_format($salaire['net_imposable'] ?? 2333.52, 2, ',', ' ') }}</td>
                <td></td>
                <td></td>
            </tr>

            {{-- IR --}}
            <tr class="section-header">
                <td colspan="5">Impôt sur le Revenu</td>
            </tr>

            <tr>
                <td>Intérêts crédit logement</td>
                <td></td>
                <td class="mono">{{ number_format($ir['interets_credit_logement'] ?? 0, 2, ',', ' ') }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Base imposable</td>
                <td></td>
                <td class="mono">{{ number_format($ir['base_imposable'] ?? 3333, 2, ',', ' ') }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Impôt sur le revenu</td>
                <td class="taux">{{ $ir['taux'] ?? 38 }}%</td>
                <td></td>
                <td></td>
                <td class="mono retenue">{{ number_format($ir['montant'] ?? 0, 2, ',', ' ') }}</td>
            </tr>

        </tbody>
    </table>

    {{-- ── BARRE RÉCAPITULATIVE ── --}}
    <div class="summary-bar">
        <div class="block">
            <label>Total gains bruts</label>
            <div class="amount">{{ number_format($resume['total_gains'] ?? 5000.28, 2, ',', ' ') }} MAD</div>
        </div>
        <div class="block">
            <label>Total retenues</label>
            <div class="amount" style="color: var(--accent)">
                {{ number_format($resume['total_retenues'] ?? 798.76, 2, ',', ' ') }} MAD
            </div>
        </div>
        <div class="block">
            <label>Net à payer</label>
            <div class="amount">{{ number_format($resume['net_a_payer'] ?? 4201.52, 2, ',', ' ') }} MAD</div>
        </div>
    </div>

    {{-- ── PIED DE PAGE ── --}}
    <div class="doc-footer">
        <span>Document généré le {{ now()->format('d/m/Y à H:i') }}</span>
        <span class="stamp">CONFIDENTIEL</span>
        <span>Conserver ce document · Réf : {{ $bulletin['reference'] ?? 'BP-2024-001' }}</span>
    </div>

</div>

</body>
</html>