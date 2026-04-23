<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Planning Hebdomadaire - {{ $startOfWeek->format('d/m/Y') }} au {{ $endOfWeek->format('d/m/Y') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; margin: 20px; color: #333; }
        h1 { text-align: center; margin-bottom: 10px; font-size: 16pt; }
        h2 { font-size: 12pt; margin: 20px 0 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: center; vertical-align: top; font-size: 9pt; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .employee-name { text-align: left !important; font-weight: bold; min-width: 180px; background-color: #fafafa; }
        .shift-matin { background-color: #e3f2fd; color: #1976d2; font-weight: bold; }
        .shift-apres-midi { background-color: #fff3e0; color: #f57c00; font-weight: bold; }
        .shift-nuit { background-color: #f3e5f5; color: #7b1fa2; font-weight: bold; }
        .shift-garde { background-color: #ffebee; color: #d32f2f; font-weight: bold; }
        .shift-journee { background-color: #e8f5e8; color: #388e3c; font-weight: bold; }
        .no-shift { color: #999; font-style: italic; }
        .header-info { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 10pt; }
        .page-break { page-break-before: always; }
        @page { margin: 15mm; }
    </style>
</head>
<body>
    <div class="header-info">
        <div>
            <strong>{{ config('app.name', 'HospitalRH') }}</strong><br>
            Planning Hebdomadaire
        </div>
        <div style="text-align: right;">
            Semaine {{ $week }} - {{ $startOfWeek->format('d/m/Y') }} → {{ $endOfWeek->format('d/m/Y') }}<br>
            Généré le: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="employee-name">Employé</th>
                @foreach($weekDays as $date => $day)
                    <th>{{ $day['day_name'] }}<br><small>{{ $day['date']->format('d/m') }}</small></th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $emp)
                @php
                    $empPlannings = $plannings->get($emp->id, collect());
                @endphp
                <tr>
                    <td class="employee-name">{{ $emp->full_name }}<br><small>{{ $emp->department }}</small></td>
                    @foreach($weekDays as $date => $day)
                        @php
                            $dayPlanning = $empPlannings->firstWhere('date', $day['date']);
                        @endphp
                        <td class="shift-{{ $dayPlanning->shift_type ?? 'no-shift' }} {{ $dayPlanning ? '' : 'no-shift' }}">
                            @if($dayPlanning)
                                <strong>{{ ucfirst(str_replace('_', ' ', $dayPlanning->shift_type)) }}</strong><br>
                                {{ substr($dayPlanning->shift_start ?? '', 0, 5) }}
                                @if($dayPlanning->shift_end)
                                    - {{ substr($dayPlanning->shift_end, 0, 5) }}
                                @endif
                                @if($dayPlanning->notes)
                                    <br><small>{{ Str::limit($dayPlanning->notes, 30) }}</small>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($weekDays) + 1 }}" style="text-align:center; padding:20px;">
                        Aucun planning trouvé pour cette période
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px; font-size: 8pt; color: #666;">
        <p><strong>Légende:</strong> Matin (🔵) Après-midi (🟠) Nuit (🟣) Garde (🔴) Journée (🟢)</p>
        <p>Document généré automatiquement par {{ config('app.name') }} - {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
