<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<style>
  * { margin:10px; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1e293b; }
  .header { background:#0f2132; color:white; padding:18px 28px; margin-bottom:20px; }
  .header h1 { font-size:17px; font-weight:700; margin-bottom:3px; }
  .header p  { font-size:9px; opacity:.7; }
  .emp-card { background:#e8f7f4; border-left:4px solid #1a8a74; padding:12px 16px; margin-bottom:20px; border-radius:4px; }
  .emp-card h2 { font-size:14px; font-weight:700; color:#0f6e56; margin-bottom:2px; }
  .emp-card p  { font-size:10px; color:#334155; }
  table { width:100%; border-collapse:collapse; }
  thead th { background:#0f2132; color:white; padding:9px 14px; text-align:center; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; }
  tbody td { padding:10px 14px; border:1px solid #e2e8f0; text-align:center; vertical-align:middle; }
  .shift { display:inline-block; padding:4px 10px; border-radius:6px; font-size:10px; font-weight:700; }
  .shift-matin     { background:#dbeafe; color:#1d4ed8; }
  .shift-apres     { background:#fef3c7; color:#d97706; }
  .shift-nuit      { background:#ede9fe; color:#7c3aed; }
  .shift-garde     { background:#fee2e2; color:#dc2626; }
  .shift-repos     { background:#f1f5f9; color:#94a3b8; }
  .day-header { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; }
  .day-date   { font-size:9px; opacity:.7; }
  .no-shift   { color:#cbd5e1; font-size:10px; }
  .footer { margin-top:16px; padding-top:8px; border-top:1px solid #e2e8f0; font-size:8px; color:#94a3b8; display:flex; justify-content:space-between; }
  .week-label { background:#1a8a74; color:white; display:inline-block; padding:2px 10px; border-radius:20px; font-size:9px; margin-left:8px; }
</style>
</head>
<body>
<div class="header">
  <h1>📅 Planning Hebdomadaire
    <span class="week-label">Sem. {{ $weekStart->format('d/m') }} → {{ $weekEnd->format('d/m/Y') }}</span>
  </h1>
  <p>Généré le {{ $generatedAt }} — HospitalRH</p>
</div>

<div class="emp-card">
  <h2>{{ $employee->first_name }} {{ $employee->last_name }}</h2>
  <p>Matricule : {{ $employee->matricule }} &nbsp;|&nbsp;
     Poste : {{ $employee->position ?? '—' }} &nbsp;|&nbsp;
     Département : {{ $employee->department ?? '—' }}</p>
</div>

@php
  $days = \Carbon\CarbonPeriod::create($weekStart, $weekEnd);
  $planningByDate = $plannings->keyBy(fn($p) => \Carbon\Carbon::parse($p->date)->format('Y-m-d'));
  $dayLabels = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
  $dayIndex  = 0;
@endphp

<table>
  <thead>
    <tr>
      @foreach($days as $day)
        <th>
          <div class="day-header">{{ $dayLabels[$dayIndex++] }}</div>
          <div class="day-date">{{ $day->format('d/m') }}</div>
        </th>
      @endforeach
    </tr>
  </thead>
  <tbody>
    <tr>
      @foreach($days as $day)
        @php
          $key  = $day->format('Y-m-d');
          $plan = $planningByDate->get($key);
          $type = strtolower($plan->shift_type ?? '');
          $shiftClass = match(true) {
            str_contains($type, 'matin')  => 'shift-matin',
            str_contains($type, 'après')  => 'shift-apres',
            str_contains($type, 'apres')  => 'shift-apres',
            str_contains($type, 'nuit')   => 'shift-nuit',
            str_contains($type, 'garde')  => 'shift-garde',
            $plan !== null                => 'shift-repos',
            default                       => '',
          };
        @endphp
        <td>
          @if($plan)
            <span class="shift {{ $shiftClass }}">{{ $plan->shift_type }}</span>
            <br/>
            <small style="color:#64748b;font-size:9px;">
              {{ $plan->shift_start ?? '' }}
              @if($plan->shift_start && $plan->shift_end) – @endif
              {{ $plan->shift_end ?? '' }}
            </small>
          @else
            <span class="no-shift">—</span>
          @endif
        </td>
      @endforeach
    </tr>
  </tbody>
</table>

<div class="footer">
  <span>HospitalRH — Gestion RH</span>
  <span>{{ $plannings->count() }} shift(s) sur la semaine</span>
</div>
</body>
</html>
