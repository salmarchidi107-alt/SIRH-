<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 28px 24px; }
    body{font-family: Helvetica, Arial, sans-serif; font-size:11px; color:#0f1720;}

    .letterhead{display:table; width:100%; margin-bottom:16px; padding-bottom:12px; border-bottom:2px solid #14b8a6;}
    .letterhead .lh-left{display:table-cell; vertical-align:middle;}
    .letterhead .lh-right{display:table-cell; vertical-align:middle; text-align:right;}
    .lh-tenant{font-size:14px; font-weight:bold; color:#0f1720;}
    .lh-doc-title{font-size:18px; font-weight:bold; color:#0d9488; margin:2px 0 0;}
    .lh-meta{font-size:9.5px; color:#6b7684;}

    table{width:100%; border-collapse:collapse; margin-top:6px;}
    th{text-align:left; font-size:9px; text-transform:uppercase; letter-spacing:.02em; color:#6b7684; border-bottom:1px solid #d6dae0; padding:7px 8px; background:#f3f5f7;}
    td{padding:7px 8px; border-bottom:1px solid #eef0f3; vertical-align:top;}
    tr:nth-child(even) td{background:#fafbfc;}

    .badge{padding:2px 8px; border-radius:10px; font-size:9px; font-weight:bold;}
    .badge-green{background:#e7f9ee; color:#22c55e;}
    .badge-gray{background:#eef0f3; color:#51586b;}

    .footer{position:fixed; bottom:-14px; left:0; right:0; font-size:8.5px; color:#9aa2ad; text-align:center;}
  </style>
</head>
<body>

  <div class="letterhead">
    <div class="lh-left">
      <div class="lh-tenant">{{ $tenant->name ?? config('app.name', 'HospitalRH') }}</div>
      <div class="lh-doc-title">Liste des projets</div>
    </div>
    <div class="lh-right">
      <div class="lh-meta">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
      <div class="lh-meta">{{ $projects->count() }} projet(s)</div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Projet</th>
        <th>Statut</th>
        <th>Tâches</th>
        <th>Terminées</th>
        <th>Créé le</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($projects as $project)
        <tr>
          <td><strong>{{ $project->name }}</strong>@if($project->description)<br><span style="color:#6b7684;">{{ \Illuminate\Support\Str::limit($project->description, 80) }}</span>@endif</td>
          <td><span class="badge {{ $project->status === 'actif' ? 'badge-green' : 'badge-gray' }}">{{ $project->statusLabel() }}</span></td>
          <td>{{ $project->tasks_count }}</td>
          <td>{{ $project->done_tasks_count }}</td>
          <td>{{ $project->created_at->format('d/m/Y') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">{{ $tenant->name ?? config('app.name', 'SIRH') }} — Suivi d'activité — document généré automatiquement</div>
</body>
</html>
