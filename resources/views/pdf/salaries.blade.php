<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Résumé Salaires</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .summary { background: #f0f8ff; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .summary h3 { margin: 0 0 10px 0; color: #2c3e50; }
        .metric { display: inline-block; width: 30%; margin: 5px; text-align: center; }
        .metric value { font-size: 18px; font-weight: bold; color: #27ae60; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .total-row { font-weight: bold; background-color: #e8f5e8; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HospitalRH - Résumé Salaire</h1>
        <p>{{ $monthName }} {{ $year }} | Généré le {{ $generatedAt }}</p>
    </div>

    <div class="summary">
        <h3>📊 Synthèse Financière</h3>
        <div class="metric">
            <label>Masse Salariale Brute</label><br>
            <value>{{ number_format($summary['masse_salariale'], 2, ',', ' ') }} DZD</value>
        </div>
        <div class="metric">
            <label>CNSS à payer</label><br>
            <value>{{ number_format($summary['cnss'], 2, ',', ' ') }} DZD</value>
        </div>
        <div class="metric">
            <label>Salaires Nets</label><br>
            <value>{{ number_format($summary['total_net'], 2, ',', ' ') }} DZD</value>
        </div>
        <div class="metric">
            <label>Bulletins Total</label><br>
            <value>{{ $summary['count_total'] }}</value>
        </div>
        <div class="metric">
            <label>Payés</label><br>
            <value>{{ $summary['count_paid'] }}</value>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Employé</th>
                <th>Salaire Brut</th>
                <th>Net à payer</th>
                <th>CNSS</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($salaries as $salary)
            <tr>
                <td>{{ $salary->employee->matricule ?? $salary->employee_id }}</td>
                <td>{{ $salary->employee->first_name }} {{ $salary->employee->last_name }}</td>
                <td>{{ number_format($salary->gross_salary, 2, ',', ' ') }}</td>
                <td>{{ number_format($salary->net_salary, 2, ',', ' ') }}</td>
                <td>{{ number_format($salary->cnss_deduction, 2, ',', ' ') }}</td>
                <td>{{ $salary->status_label }}</td>
            </tr>
            @empty
            <tr><td colspan="6">Aucun bulletin</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2"><strong>TOTAL</strong></td>
                <td><strong>{{ number_format($summary['masse_salariale'], 2, ',', ' ') }}</strong></td>
                <td><strong>{{ number_format($summary['total_net'], 2, ',', ' ') }}</strong></td>
                <td><strong>{{ number_format($summary['cnss'], 2, ',', ' ') }}</strong></td>
                <td><strong>{{ $summary['count_paid'] }} / {{ $summary['count_total'] }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        HospitalRH - Rapport automatique généré par Assistant RH
    </div>
</body>
</html>

