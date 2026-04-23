<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<style>
  * { margin:10px; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1e293b; }
  .header { background:#0f2132; color:white; padding:20px 28px; margin-bottom:20px; }
  .header h1 { font-size:18px; font-weight:700; margin-bottom:4px; }
  .header p  { font-size:10px; opacity:.7; }
  .badge { display:inline-block; background:#1a8a74; color:white; padding:2px 10px; border-radius:20px; font-size:10px; margin-left:8px; }
  table { width:100%; border-collapse:collapse; }
  thead th { background:#f1f5f9; padding:8px 12px; text-align:left; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; border-bottom:2px solid #e2e8f0; }
  tbody td { padding:9px 12px; border-bottom:1px solid #f1f5f9; font-size:11px; }
  tbody tr:nth-child(even) td { background:#fafbfc; }
  .empty { text-align:center; padding:30px; color:#94a3b8; font-size:12px; }
  .footer { margin-top:20px; padding-top:10px; border-top:1px solid #e2e8f0; font-size:9px; color:#94a3b8; display:flex; justify-content:space-between; }
  .type-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:10px; }
  .type-conge   { background:#dbeafe; color:#1d4ed8; }
  .type-maladie { background:#fee2e2; color:#dc2626; }
  .type-autre   { background:#f3e8ff; color:#7c3aed; }
</style>
</head>
<body>
<div class="header">
  <h1>📋 Absences du jour <span class="badge">{{ $today->format('d/m/Y') }}</span></h1>
  <p>Généré le {{ $generatedAt }} — HospitalRH</p>
</div>

@if($absences->isEmpty())
  <div class="empty">Aucune absence approuvée enregistrée pour aujourd'hui.</div>
@else
<table>
  <thead>
    <tr>
      <th>Employé</th>
      <th>Matricule</th>
      <th>Département</th>
      <th>Type</th>
      <th>Début</th>
      <th>Fin</th>
      <th>Durée</th>
    </tr>
  </thead>
  <tbody>
    @foreach($absences as $absence)
      @php
        $employee = $absence->employee;
        $name     = $employee ? ($employee->first_name . ' ' . $employee->last_name) : 'Inconnu';
        $mat      = $employee->matricule ?? 'N/A';
        $dept     = $employee->department ?? '—';
        $type     = $absence->type ?? 'Autre';
        $start    = \Carbon\Carbon::parse($absence->start_date);
        $end      = \Carbon\Carbon::parse($absence->end_date);
        $days     = $start->diffInDays($end) + 1;
        $typeClass = match(strtolower($type)) {
          'congé', 'conge'   => 'type-conge',
          'maladie'          => 'type-maladie',
          default            => 'type-autre',
        };
      @endphp
      <tr>
        <td><strong>{{ $name }}</strong></td>
        <td>{{ $mat }}</td>
        <td>{{ $dept }}</td>
        <td><span class="type-badge {{ $typeClass }}">{{ $type }}</span></td>
        <td>{{ $start->format('d/m/Y') }}</td>
        <td>{{ $end->format('d/m/Y') }}</td>
        <td>{{ $days }} jour(s)</td>
      </tr>
    @endforeach
  </tbody>
</table>
@endif

<div class="footer">
  <span>HospitalRH — Gestion RH</span>
  <span>Total : {{ $absences->count() }} absence(s)</span>
</div>
</body>
</html>
