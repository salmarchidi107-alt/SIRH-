<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planning Mensuel</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #222; margin: 24px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p { margin: 2px 0 8px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; font-size: 10px; }
        th { background: #f0f0f0; text-align: left; }
        .meta { margin-bottom: 16px; font-size: 11px; }
        .note { margin-top: 12px; font-size: 10px; color: #555; }
    </style>
</head>
<body>
    <h1>Planning Mensuel</h1>
    <div class="meta">
        <p>Mois : {{ \Carbon\Carbon::create($year, $month)->locale('fr')->monthName }} {{ $year }}</p>
        <p>Période : {{ $startOfMonth->format('d/m/Y') }} - {{ $endOfMonth->format('d/m/Y') }}</p>
        @if(!empty($department))
            <p>Service : {{ $department }}</p>
        @endif
        @if(!empty($search))
            <p>Recherche : {{ $search }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Employé</th>
                <th>Département</th>
                <th>Date</th>
                <th>Type</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Salle</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                @php $employeePlannings = $plannings->get($employee->id, collect()); @endphp
                @forelse($employeePlannings as $planning)
                    <tr>
                        <td>{{ $employee->full_name }}</td>
                        <td>{{ $employee->department }}</td>
                        <td>{{ $planning->date?->format('d/m/Y') }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $planning->shift_type)) }}</td>
                        <td>{{ $planning->shift_start }}</td>
                        <td>{{ $planning->shift_end }}</td>
                        <td>{{ $planning->room ?? '' }}</td>
                        <td>{{ $planning->notes ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td>{{ $employee->full_name }}</td>
                        <td>{{ $employee->department }}</td>
                        <td colspan="6" style="text-align:center;">Aucun shift planifié ce mois-ci</td>
                    </tr>
                @endforelse
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">Aucun planning disponible pour cette sélection.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="note">
        Ce document est généré pour export PDF et peut être utilisé dans un éditeur PDF.
    </div>
</body>
</html>
