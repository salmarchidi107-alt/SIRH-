<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #185FA5; padding-bottom: 14px; }
        .header h1 { font-size: 16px; margin: 0 0 4px; color: #185FA5; }
        .header p { font-size: 11px; color: #64748b; margin: 0; }
        .section { margin-bottom: 16px; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        table.info td { padding: 3px 0; font-size: 12px; }
        table.info td:first-child { color: #64748b; width: 140px; }
        table.material th { text-align: left; font-size: 10px; color: #64748b; padding: 4px 0; border-bottom: 1px solid #e2e8f0; }
        table.material td { padding: 6px 0; font-size: 12px; border-bottom: 1px solid #f1f5f9; }
        .engagement { font-size: 11px; color: #475569; line-height: 1.6; margin: 16px 0; padding: 12px; background: #f8fafc; border-radius: 6px; }
        .signatures { width: 100%; margin-top: 30px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; padding: 0 10px; }
        .sig-box { height: 60px; border: 1px dashed #cbd5e1; border-radius: 6px; margin-top: 8px; }
        .footer { margin-top: 30px; font-size: 9px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Décharge de {{ $discharge->type === 'remise' ? 'remise' : 'restitution' }} de matériel</h1>
        <p>Document n° {{ $discharge->reference }} — généré le {{ $discharge->created_at->format('d/m/Y') }}</p>
    </div>

    <div class="section">
        <div class="section-title">Salarié</div>
        <table class="info">
            <tr><td>Nom &amp; prénom</td><td><strong>{{ $discharge->assignment->employee->full_name }}</strong></td></tr>
            <tr><td>Matricule</td><td>{{ $discharge->assignment->employee->matricule }}</td></tr>
            <tr><td>Fonction</td><td>{{ $discharge->assignment->employee->position }}</td></tr>
            <tr><td>Service</td><td>{{ $discharge->assignment->employee->department }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Matériel concerné</div>
        <table class="material">
            <thead>
                <tr><th>Désignation</th><th>Référence</th><th>État</th><th style="text-align:right">Valeur</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $discharge->assignment->equipment->designation }}</td>
                    <td>{{ $discharge->assignment->equipment->reference }}</td>
                    <td>{{ $discharge->type === 'remise' ? $discharge->assignment->condition_at_assignment : ($discharge->assignment->condition_at_return ?? '—') }}</td>
                    <td style="text-align:right">{{ number_format($discharge->assignment->equipment->value, 0, ',', ' ') }} MAD</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="engagement">
        Je soussigné(e) <strong>{{ $discharge->assignment->employee->full_name }}</strong>,
        reconnais avoir {{ $discharge->type === 'remise' ? 'reçu' : 'restitué' }} le matériel listé ci-dessus
        @if($discharge->type === 'remise')
            en bon état et m'engage à le restituer dans l'état à ma sortie de l'entreprise ou sur simple demande de la direction.
        @else
            et confirme que l'état constaté ci-dessus est conforme à la réalité.
        @endif
    </div>

    <table class="signatures">
        <tr>
            <td>
                Signature du salarié
                <div class="sig-box"></div>
            </td>
            <td>
                Cachet &amp; signature RH
                <div class="sig-box"></div>
            </td>
        </tr>
    </table>

    <div class="footer">Document généré automatiquement par le SIRH — Module Gestion des Équipements</div>

</body>
</html>
