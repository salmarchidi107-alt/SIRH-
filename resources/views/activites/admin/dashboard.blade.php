@extends('layouts.app')

@section('title', "Suivi d'activité")
@section('page-title', "Suivi d'activité — État d'avancement")

@section('content')
<style>
  #sa-app{
    --sa-teal:#14b8a6; --sa-teal-dark:#0d9488;
    --sa-ink:#0f1720; --sa-ink-2:#6b7684; --sa-line:#e8eaee; --sa-card:#ffffff;
    --sa-amber:#f59e0b; --sa-red:#ef4444; --sa-green:#22c55e;
    font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;
  }
  #sa-app .sa-page-head{margin-bottom:16px;}
  #sa-app .sa-page-head h1{font-size:19px; font-weight:800; margin:0 0 4px; color:var(--sa-ink);}
  #sa-app .sa-sub{font-size:12.5px; color:var(--sa-ink-2); margin:0;}

  #sa-app .sa-tabs{display:flex; gap:4px; border-bottom:1px solid var(--sa-line); margin-bottom:24px;}
  #sa-app .sa-tab{padding:10px 16px; font-size:13px; font-weight:600; color:var(--sa-ink-2); text-decoration:none; border-bottom:2px solid transparent; margin-bottom:-1px;}
  #sa-app .sa-tab.active{color:var(--sa-teal-dark); border-bottom-color:var(--sa-teal);}
  #sa-app .sa-tab:hover:not(.active){color:var(--sa-ink);}

  /* KPIs : sobres, sans icône, juste le nombre + le libellé */
  #sa-app .sa-kpis{display:grid; grid-template-columns:repeat(4, 1fr); gap:1px; background:var(--sa-line); border:1px solid var(--sa-line); border-radius:12px; overflow:hidden; margin-bottom:28px;}
  @media (max-width:800px){ #sa-app .sa-kpis{grid-template-columns:repeat(2, 1fr);} }
  #sa-app .sa-kpi{background:var(--sa-card); padding:18px 20px;}
  #sa-app .sa-kpi-value{font-size:26px; font-weight:800; color:var(--sa-ink); line-height:1.1;}
  #sa-app .sa-kpi-label{font-size:12px; color:var(--sa-ink-2); margin-top:4px;}
  #sa-app .sa-kpi.sa-good .sa-kpi-value{color:var(--sa-green);}
  #sa-app .sa-kpi.sa-warn .sa-kpi-value{color:var(--sa-red);}

  #sa-app .sa-section{margin-bottom:28px;}
  #sa-app .sa-section-title{font-size:13.5px; font-weight:700; color:var(--sa-ink); margin:0 0 12px;}

  /* ── Avancement global ── */
  #sa-app .sa-progress-hero{
    position:relative; overflow:hidden;
    background:linear-gradient(180deg,#ffffff 0%, #fbfdfd 100%);
    border:1px solid var(--sa-line); border-radius:20px;
    padding:32px 38px; display:flex; align-items:center; gap:40px; flex-wrap:wrap;
  }
  #sa-app .sa-progress-hero::before{
    content:''; position:absolute; top:-60px; right:-60px; width:220px; height:220px;
    background:radial-gradient(circle, rgba(20,184,166,.10) 0%, rgba(20,184,166,0) 70%);
    pointer-events:none;
  }

  #sa-app .sa-progress-ring{position:relative; width:160px; height:160px; flex-shrink:0;}
  #sa-app .sa-progress-ring canvas{filter:drop-shadow(0 6px 14px rgba(13,148,136,.18));}
  #sa-app .sa-progress-ring-center{position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center;}
  #sa-app .sa-progress-pct{font-size:32px; font-weight:800; color:var(--sa-ink); letter-spacing:-.02em; line-height:1;}
  #sa-app .sa-progress-pct small{font-size:16px; font-weight:700; color:var(--sa-ink-2); margin-left:1px;}
  #sa-app .sa-progress-caption{font-size:11px; color:var(--sa-ink-2); text-transform:uppercase; letter-spacing:.06em; font-weight:600; margin-top:4px;}

  #sa-app .sa-progress-divider{width:1px; align-self:stretch; background:var(--sa-line); flex-shrink:0;}
  @media (max-width:700px){ #sa-app .sa-progress-divider{display:none;} }

  #sa-app .sa-progress-details{flex:1; min-width:220px; position:relative;}
  #sa-app .sa-progress-lead{font-size:15px; color:var(--sa-ink); margin:0 0 18px; line-height:1.5;}
  #sa-app .sa-progress-lead strong{font-weight:800; color:var(--sa-teal-dark);}

  #sa-app .sa-progress-chips{display:flex; gap:10px; flex-wrap:wrap;}
  #sa-app .sa-chip{
    background:#fff; border:1px solid var(--sa-line); border-radius:12px;
    padding:10px 16px; min-width:120px;
  }
  #sa-app .sa-chip-value{display:block; font-size:17px; font-weight:800; color:var(--sa-ink); line-height:1.2;}
  #sa-app .sa-chip-label{display:block; font-size:11px; color:var(--sa-ink-2); margin-top:2px;}

  /* Table employés — sobre, pas de badges colorés partout */
  #sa-app .sa-table-wrap{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; overflow:hidden;}
  #sa-app .sa-table{width:100%; border-collapse:collapse; font-size:13px;}
  #sa-app .sa-table th{text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:var(--sa-ink-2); padding:10px 18px; border-bottom:1px solid var(--sa-line); background:#fafbfc;}
  #sa-app .sa-table td{padding:12px 18px; border-bottom:1px solid var(--sa-line); color:var(--sa-ink); vertical-align:middle;}
  #sa-app .sa-table tr:last-child td{border-bottom:none;}
  #sa-app .sa-table-name{font-weight:600;}
  #sa-app .sa-table-muted{color:var(--sa-ink-2);}
  #sa-app .sa-status-ok{color:var(--sa-green); font-weight:600;}
  #sa-app .sa-status-pending{color:var(--sa-ink-2);}
  #sa-app .sa-status-late{color:var(--sa-red); font-weight:600;}
  #sa-app .sa-mini-track{width:80px; height:6px; background:#f1f3f5; border-radius:3px; overflow:hidden; display:inline-block; vertical-align:middle; margin-right:8px;}
  #sa-app .sa-mini-fill{height:100%; background:var(--sa-teal-dark); border-radius:3px;}

  #sa-app .sa-empty-hint{font-size:12.5px; color:var(--sa-ink-2); text-align:center; padding:28px 0;}
</style>

<div id="sa-app">

  <div class="sa-page-head">
    <h1>Suivi d'activité — Équipe</h1>
    <p class="sa-sub">Où en est l'équipe sur ses tâches et ses projets.</p>
  </div>

  <div class="sa-tabs">
    <a href="{{ route('activites.admin.dashboard') }}" class="sa-tab {{ request()->routeIs('activites.admin.dashboard') ? 'active' : '' }}">État d'avancement</a>
    <a href="{{ route('activites.admin.projects.index') }}" class="sa-tab {{ request()->routeIs('activites.admin.projects.*') ? 'active' : '' }}">Projets</a>
    <a href="{{ route('activites.admin.tasks.index') }}" class="sa-tab {{ request()->routeIs('activites.admin.tasks.*') ? 'active' : '' }}">Tâches</a>
  </div>

  <div class="sa-kpis">
    <div class="sa-kpi">
      <div class="sa-kpi-value">{{ $stats['total_projects'] }}</div>
      <div class="sa-kpi-label">Projets</div>
    </div>
    <div class="sa-kpi sa-good">
      <div class="sa-kpi-value">{{ $stats['done_tasks'] }}</div>
      <div class="sa-kpi-label">Tâches terminées</div>
    </div>
    <div class="sa-kpi">
      <div class="sa-kpi-value">{{ $stats['in_progress_tasks'] }}</div>
      <div class="sa-kpi-label">Tâches en cours</div>
    </div>
    <div class="sa-kpi sa-warn">
      <div class="sa-kpi-value">{{ $stats['late_tasks'] }}</div>
      <div class="sa-kpi-label">Tâches en retard</div>
    </div>
  </div>

  <div class="sa-section">
    <div class="sa-section-title">Avancement global</div>
    <div class="sa-progress-hero">
      <div class="sa-progress-ring">
        <canvas id="saGlobalChart" width="160" height="160"></canvas>
        <div class="sa-progress-ring-center">
          <div class="sa-progress-pct">{{ $stats['completion_rate'] }}<small>%</small></div>
          <div class="sa-progress-caption">Complété</div>
        </div>
      </div>

      <div class="sa-progress-divider"></div>

      <div class="sa-progress-details">
        <p class="sa-progress-lead">
          <strong>{{ $stats['done_tasks'] }}</strong> tâches terminées sur <strong>{{ $stats['total_tasks'] }}</strong> au total
        </p>
        <div class="sa-progress-chips">
          <div class="sa-chip">
            <span class="sa-chip-value">{{ $globalStats['employees_fully_done'] }}/{{ $globalStats['employees_with_tasks'] }}</span>
            <span class="sa-chip-label">Employés à jour</span>
          </div>
          <div class="sa-chip">
            <span class="sa-chip-value">{{ 100 - $stats['completion_rate'] }}%</span>
            <span class="sa-chip-label">Restant</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="sa-section">
    <div class="sa-section-title">Avancement par employé</div>

    @if (empty($employeeProgress))
      <div class="sa-table-wrap">
        <div class="sa-empty-hint">Aucune tâche assignée pour le moment.</div>
      </div>
    @else
      <div class="sa-table-wrap">
        <table class="sa-table">
          <thead>
            <tr>
              <th>Employé</th>
              <th>Tâches assignées</th>
              <th>Terminées</th>
              <th>En retard</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($employeeProgress as $row)
              <tr>
                <td class="sa-table-name">{{ $row['name'] }}</td>
                <td>
                  <span class="sa-mini-track"><span class="sa-mini-fill" style="width:{{ $row['percent'] }}%;"></span></span>
                  {{ $row['done'] }} / {{ $row['total'] }}
                </td>
                <td class="sa-table-muted">{{ $row['percent'] }}%</td>
                <td class="{{ $row['late'] > 0 ? 'sa-status-late' : 'sa-table-muted' }}">{{ $row['late'] > 0 ? $row['late'] : '—' }}</td>
                <td>
                  @if ($row['complete'])
                    <span class="sa-status-ok">Toutes terminées</span>
                  @elseif ($row['late'] > 0)
                    <span class="sa-status-late">{{ $row['remaining'] }} restante(s), dont en retard</span>
                  @else
                    <span class="sa-status-pending">{{ $row['remaining'] }} restante(s)</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  <div class="sa-section">
    <div class="sa-section-title">Avancement par projet</div>

    @if (empty($projectProgress))
      <div class="sa-table-wrap">
        <div class="sa-empty-hint">Aucun projet avec des tâches pour le moment.</div>
      </div>
    @else
      <div class="sa-table-wrap">
        <table class="sa-table">
          <thead>
            <tr>
              <th>Projet</th>
              <th>Tâches</th>
              <th>Terminées</th>
              <th>En retard</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($projectProgress as $row)
              <tr>
                <td class="sa-table-name">
                  {{ $row['name'] }}
                  @if ($row['status'] === 'archive')
                    <span class="sa-table-muted">(archivé)</span>
                  @endif
                </td>
                <td>
                  <span class="sa-mini-track"><span class="sa-mini-fill" style="width:{{ $row['percent'] }}%;"></span></span>
                  {{ $row['done'] }} / {{ $row['total'] }}
                </td>
                <td class="sa-table-muted">{{ $row['percent'] }}%</td>
                <td class="{{ $row['late'] > 0 ? 'sa-status-late' : 'sa-table-muted' }}">{{ $row['late'] > 0 ? $row['late'] : '—' }}</td>
                <td>
                  @if ($row['complete'])
                    <span class="sa-status-ok">Toutes terminées</span>
                  @elseif ($row['late'] > 0)
                    <span class="sa-status-late">{{ $row['remaining'] }} restante(s), dont en retard</span>
                  @else
                    <span class="sa-status-pending">{{ $row['remaining'] }} restante(s)</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const completion = {{ $stats['completion_rate'] }};
    const canvas = document.getElementById('saGlobalChart');
    const ctx = canvas.getContext('2d');

    const gradient = ctx.createLinearGradient(0, 0, 160, 160);
    gradient.addColorStop(0, '#2dd4bf');
    gradient.addColorStop(1, '#0d9488');

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [completion, 100 - completion],
                backgroundColor: [gradient, '#eef1f3'],
                borderWidth: 0,
                borderRadius: 8,
                spacing: 3,
            }],
        },
        options: {
            cutout: '80%',
            responsive: false,
            animation: { animateRotate: true, duration: 800, easing: 'easeOutQuart' },
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
        },
    });
});
</script>
@endpush
