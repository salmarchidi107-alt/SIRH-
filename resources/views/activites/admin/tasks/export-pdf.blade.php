<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 26px 20px; }
    body{font-family: Helvetica, Arial, sans-serif; font-size:10.5px; color:#0f1720;}

    .letterhead{display:table; width:100%; margin-bottom:14px; padding-bottom:10px; border-bottom:2px solid #14b8a6;}
    .letterhead .lh-left{display:table-cell; vertical-align:middle;}
    .letterhead .lh-right{display:table-cell; vertical-align:middle; text-align:right;}
    .lh-tenant{font-size:13px; font-weight:bold; color:#0f1720;}
    .lh-doc-title{font-size:16px; font-weight:bold; color:#0d9488; margin:2px 0 0;}
    .lh-meta{font-size:9px; color:#6b7684;}

    table{width:100%; border-collapse:collapse; margin-top:6px;}
    th{text-align:left; font-size:8.5px; text-transform:uppercase; letter-spacing:.02em; color:#6b7684; border-bottom:1px solid #d6dae0; padding:6px 6px; background:#f3f5f7;}
    td{padding:6px 6px; border-bottom:1px solid #eef0f3; vertical-align:top;}
    tr:nth-child(even) td{background:#fafbfc;}

    .badge{padding:2px 7px; border-radius:10px; font-size:8.5px; font-weight:bold;}
    .badge-gray{background:#eef0f3; color:#51586b;}
    .badge-blue{background:#e6f0ff; color:#3b82f6;}
    .badge-amber{background:#fff3e0; color:#f59e0b;}
    .badge-green{background:#e7f9ee; color:#22c55e;}
    .badge-red{background:#fdeaea; color:#ef4444;}

    .footer{position:fixed; bottom:-14px; left:0; right:0; font-size:8px; color:#9aa2ad; text-align:center;}
  </style>
</head>
<body>

  <div class="letterhead">
    <div class="lh-left">
      <div class="lh-tenant">{{ $tenant->name ?? config('app.name', 'HospitalRH') }}</div>
      <div class="lh-doc-title">Liste des tâches</div>
    </div>
    <div class="lh-right">
      <div class="lh-meta">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
      <div class="lh-meta">{{ $tasks->count() }} tâche(s)</div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Projet</th>
        <th>Tâche</th>
        <th>Assignée à</th>
        <th>Priorité</th>
        <th>Statut</th>
        <th>Échéance</th>
        <th>% avanc.</th>
        <th>Temps loggé</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($tasks as $task)
        <tr>
          <td>{{ $task->project->name ?? '' }}</td>
          <td><strong>{{ $task->title }}</strong></td>
          <td>{{ $task->assignee->name ?? '—' }}</td>
          <td><span class="badge {{ str_replace('sa-', '', $task->priorityBadgeClass()) }}">{{ $task->priorityLabel() }}</span></td>
          <td><span class="badge {{ str_replace('sa-', '', $task->statusBadgeClass()) }}">{{ $task->statusLabel() }}</span></td>
          <td>{{ optional($task->due_date)->format('d/m/Y') ?? '—' }}</td>
          <td>{{ $task->percent_complete }}%</td>
          <td>{{ \App\Support\Duration::toHuman($task->logged_minutes) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">{{ $tenant->name ?? config('app.name', 'SIRH') }} — Suivi d'activité — document généré automatiquement</div>
</body>
</html>
