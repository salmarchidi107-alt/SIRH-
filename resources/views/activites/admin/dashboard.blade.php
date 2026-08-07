@extends('layouts.app')

@section('title', "Suivi d'activité")
@section('page-title', "Suivi d'activité — État d'avancement")

@section('content')
<div id="sa-app">

  <div class="sa-page-head">
    <div>
      <h1>Suivi d'activité — Équipe</h1>
      <p class="sa-sub">Où en est l'équipe sur ses tâches et ses projets.</p>
    </div>
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
