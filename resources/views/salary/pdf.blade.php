<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de Paie — {{ $salary->employee->full_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #fff;
            padding: 30px 40px;
        }

        /* ── En-tête ── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 24px;
            border-bottom: 2px solid #185FA5;
            padding-bottom: 16px;
        }
        .header-left { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }
        .company-name { font-size: 18px; font-weight: bold; color: #185FA5; }
        .bulletin-title { font-size: 15px; font-weight: bold; color: #333; }
        .bulletin-period { font-size: 12px; color: #666; margin-top: 2px; }

        /* ── Infos employé ── */
        .employee-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 14px 16px;
        }
        .employee-left  { display: table-cell; width: 50%; vertical-align: top; }
        .employee-right { display: table-cell; width: 50%; vertical-align: top; }
        .info-row { margin-bottom: 5px; }
        .info-label { color: #666; font-size: 10px; }
        .info-value { font-weight: bold; font-size: 11px; }

        /* ── Table principale ── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th {
            background: #185FA5;
            color: #fff;
            padding: 7px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 7px 10px;
            border-bottom: 1px solid #e8ecf0;
            font-size: 11px;
        }
        tr:last-child td { border-bottom: none; }
        tr:nth-child(even) td { background: #f9fafb; }
        .amount { text-align: right; font-weight: bold; }
        .gain-row td { color: #0F6E56; }
        .deduction-row td { color: #991b1b; }
        .subtotal-row td { background: #eef2ff !important; font-weight: bold; }

        /* ── Totaux ── */
        .totals-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            gap: 12px;
        }
        .total-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 10px 16px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .total-box:not(:last-child) { margin-right: 12px; }
        .total-box-net { background: #185FA5; color: #fff; border-color: #185FA5; }
        .total-label { font-size: 10px; opacity: 0.75; margin-bottom: 4px; }
        .total-value { font-size: 16px; font-weight: bold; }
        .total-box-net .total-value { font-size: 18px; }

        /* ── Charges patronales ── */
        .patronal-section {
            background: #fff8e1;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 16px;
        }
        .patronal-title { font-weight: bold; color: #78350f; margin-bottom: 8px; font-size: 11px; }
        .patronal-grid { display: table; width: 100%; }
        .patronal-col { display: table-cell; width: 33%; }

        /* ── Footer ── */
        .footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; font-size: 10px; color: #999; }
        .footer-right { display: table-cell; text-align: right; font-size: 10px; color: #999; }
        .signature-box {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 8px;
            text-align: center;
            width: 160px;
            float: right;
            margin-top: 30px;
            margin-right: 20px;
        }
        .signature-label { font-size: 10px; color: #666; margin-bottom: 30px; }
    </style>
</head>
<body>

    {{-- ── En-tête entreprise ─────────────────────────────── --}}
    <div class="header">
        <div class="header-left">
            <div class="company-name">SIRH — Mon Entreprise</div>
            <div style="font-size:10px;color:#666;margin-top:2px">Siège social — Maroc</div>
        </div>
        <div class="header-right">
            <div class="bulletin-title">BULLETIN DE PAIE</div>
            <div class="bulletin-period">
                {{ $salary->month_name }} {{ $salary->year }}
            </div>
            <div style="font-size:10px;color:#999;margin-top:4px">
                Généré le {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </div>

    {{-- ── Informations employé ─────────────────────────── --}}
    <div class="employee-section">
        <div class="employee-left">
            <div class="info-row">
                <div class="info-label">Nom & Prénom</div>
                <div class="info-value">{{ $salary->employee->full_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">CIN</div>
                <div class="info-value">{{ $salary->employee->cin ?? '—' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">N° CNSS</div>
                <div class="info-value">{{ $salary->employee->cnss_number ?? '—' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Poste</div>
                <div class="info-value">{{ $salary->employee->position }} — {{ $salary->employee->department }}</div>
            </div>
        </div>
        <div class="employee-right">
            <div class="info-row">
                <div class="info-label">Type de contrat</div>
                <div class="info-value">{{ $salary->employee->contract_type ?? 'CDI' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date d'embauche</div>
                <div class="info-value">
                    {{ $salary->employee->hire_date ? $salary->employee->hire_date->format('d/m/Y') : '—' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Ancienneté</div>
                <div class="info-value">{{ $salary->employee->seniority_years }} an(s) ({{ $salary->employee->seniority_rate * 100 }}%)</div>
            </div>
            <div class="info-row">
                <div class="info-label">Situation familiale</div>
                <div class="info-value">
                    {{ ucfirst($salary->employee->family_status ?? 'Célibataire') }}
                    — {{ $salary->employee->children_count ?? 0 }} enfant(s)
                </div>
            </div>
        </div>
    </div>

    {{-- ── Éléments de rémunération ──────────────────────── --}}
    <table>
        <thead>
            <tr>
                <th style="width:40%">Rubrique</th>
                <th style="width:20%;text-align:right">Base</th>
                <th style="width:15%;text-align:right">Taux</th>
                <th style="width:25%;text-align:right">Montant (MAD)</th>
            </tr>
        </thead>
        <tbody>
            {{-- GAINS --}}
            <tr>
                <td colspan="4" style="background:#f0f9ff;font-weight:bold;color:#185FA5;font-size:10px;text-transform:uppercase">
                    Gains
                </td>
            </tr>
            <tr class="gain-row">
                <td>Salaire de base</td>
                <td class="amount">191 h</td>
                <td class="amount">—</td>
                <td class="amount">{{ number_format($salary->base_salary, 2, ',', ' ') }}</td>
            </tr>
            @if($salary->seniority_bonus > 0)
            <tr class="gain-row">
                <td>Prime d'ancienneté ({{ $salary->employee->seniority_rate * 100 }}%)</td>
                <td class="amount">{{ number_format($salary->base_salary, 2, ',', ' ') }}</td>
                <td class="amount">{{ $salary->employee->seniority_rate * 100 }}%</td>
                <td class="amount">{{ number_format($salary->seniority_bonus, 2, ',', ' ') }}</td>
            </tr>
            @endif
            @if($salary->overtime_amount > 0)
            <tr class="gain-row">
                <td>Heures supplémentaires ({{ $salary->overtime_hours }}h)</td>
                <td class="amount">25%</td>
                <td class="amount">—</td>
                <td class="amount">{{ number_format($salary->overtime_amount, 2, ',', ' ') }}</td>
            </tr>
            @endif
            @if($salary->bonuses > 0)
            <tr class="gain-row">
                <td>Primes & indemnités</td>
                <td class="amount">—</td>
                <td class="amount">—</td>
                <td class="amount">{{ number_format($salary->bonuses, 2, ',', ' ') }}</td>
            </tr>
            @endif
            @if($salary->transport_allowance > 0)
            <tr class="gain-row">
                <td>Indemnité de transport</td>
                <td class="amount">—</td>
                <td class="amount">—</td>
                <td class="amount">{{ number_format($salary->transport_allowance, 2, ',', ' ') }}</td>
            </tr>
            @endif
            <tr class="subtotal-row">
                <td><strong>Salaire Brut</strong></td>
                <td></td><td></td>
                <td class="amount">{{ number_format($salary->gross_salary, 2, ',', ' ') }}</td>
            </tr>

            {{-- RETENUES --}}
            <tr>
                <td colspan="4" style="background:#fff0f0;font-weight:bold;color:#991b1b;font-size:10px;text-transform:uppercase">
                    Retenues salariales
                </td>
            </tr>
            <tr class="deduction-row">
                <td>CNSS prestations sociales (plafonné 6 000 MAD)</td>
                <td class="amount">{{ number_format(min($salary->gross_salary, 6000), 2, ',', ' ') }}</td>
                <td class="amount">4,48%</td>
                <td class="amount">-{{ number_format($salary->cnss_deduction, 2, ',', ' ') }}</td>
            </tr>
            <tr class="deduction-row">
                <td>AMO (Assurance Maladie Obligatoire)</td>
                <td class="amount">{{ number_format($salary->gross_salary, 2, ',', ' ') }}</td>
                <td class="amount">2,26%</td>
                <td class="amount">-{{ number_format($salary->amo_deduction, 2, ',', ' ') }}</td>
            </tr>
            <tr style="background:#f0fff4">
                <td style="color:#0F6E56">Frais professionnels déductibles (20%, max 2 500)</td>
                <td class="amount" style="color:#0F6E56">{{ number_format($salary->gross_salary, 2, ',', ' ') }}</td>
                <td class="amount" style="color:#0F6E56">20%</td>
                <td class="amount" style="color:#0F6E56">-{{ number_format($salary->fp_deduction, 2, ',', ' ') }}</td>
            </tr>
            <tr class="subtotal-row">
                <td><strong>Net Imposable</strong></td>
                <td></td><td></td>
                <td class="amount">{{ number_format($salary->taxable_income, 2, ',', ' ') }}</td>
            </tr>
            <tr class="deduction-row">
                <td>Impôt sur le Revenu (IR) — barème progressif</td>
                <td class="amount">{{ number_format($salary->taxable_income, 2, ',', ' ') }}</td>
                <td class="amount">—</td>
                <td class="amount">-{{ number_format($salary->ir_deduction, 2, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── Totaux ─────────────────────────────────────────── --}}
    <div class="totals-section">
        <div class="total-box">
            <div class="total-label">Salaire Brut</div>
            <div class="total-value">{{ number_format($salary->gross_salary, 2, ',', ' ') }}</div>
            <div style="font-size:9px;color:#999;margin-top:2px">MAD</div>
        </div>
        <div class="total-box">
            <div class="total-label">Total Retenues</div>
            <div class="total-value" style="color:#991b1b">
                -{{ number_format($salary->cnss_deduction + $salary->amo_deduction + $salary->ir_deduction, 2, ',', ' ') }}
            </div>
            <div style="font-size:9px;color:#999;margin-top:2px">MAD</div>
        </div>
        <div class="total-box total-box-net">
            <div class="total-label">NET À PAYER</div>
            <div class="total-value">{{ number_format($salary->net_salary, 2, ',', ' ') }}</div>
            <div style="font-size:9px;opacity:0.8;margin-top:2px">MAD</div>
        </div>
    </div>

    {{-- ── Charges patronales (info) ───────────────────────── --}}
    <div class="patronal-section">
        <div class="patronal-title">Charges patronales (informatives — non déduites du net)</div>
        <div class="patronal-grid">
            <div class="patronal-col">
                <div style="font-size:10px;color:#78350f">CNSS patronale (10,29%)</div>
                <div style="font-weight:bold">{{ number_format($salary->patronal_cnss, 2, ',', ' ') }} MAD</div>
            </div>
            <div class="patronal-col">
                <div style="font-size:10px;color:#78350f">AMO patronale (2,26%)</div>
                <div style="font-weight:bold">{{ number_format($salary->patronal_amo, 2, ',', ' ') }} MAD</div>
            </div>
            <div class="patronal-col">
                <div style="font-size:10px;color:#78350f">TFP (1,6%)</div>
                <div style="font-weight:bold">{{ number_format($salary->patronal_tfp, 2, ',', ' ') }} MAD</div>
            </div>
        </div>
    </div>

    {{-- ── Signature ───────────────────────────────────────── --}}
    <div style="margin-top:20px">
        <div class="signature-box">
            <div class="signature-label">Signature employeur</div>
            <div style="font-size:10px;color:#ccc">Cachet & signature</div>
        </div>
    </div>

    {{-- ── Footer ──────────────────────────────────────────── --}}
    <div class="footer">
        <div class="footer-left">
            Document généré automatiquement — Confidentiel<br>
            Ce bulletin est à conserver sans limitation de durée.
        </div>
        <div class="footer-right">
            {{ $salary->month_name }} {{ $salary->year }}<br>
            Statut : {{ $salary->status_label }}
        </div>
    </div>

</body>
</html>
