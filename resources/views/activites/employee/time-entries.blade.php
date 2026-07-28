@extends('layouts.app')

@section('title', "Suivi d'activité")
@section('page-title', "Suivi d'activité — Saisie de temps")

@section('content')
<style>
  #sa-app{
    --sa-teal:#14b8a6; --sa-teal-dark:#0d9488; --sa-teal-light:#e6f7f5;
    --sa-ink:#0f1720; --sa-ink-2:#6b7684; --sa-line:#e8eaee; --sa-card:#ffffff;
    --sa-amber:#f59e0b; --sa-red:#ef4444; --sa-blue:#3b82f6; --sa-green:#22c55e;
    font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;
  }
  #sa-app .sa-page-head{margin-bottom:16px;}
  #sa-app .sa-page-head h1{font-size:19px; font-weight:800; margin:0 0 4px; color:var(--sa-ink);}
  #sa-app .sa-sub{font-size:12.5px; color:var(--sa-ink-2); margin:0;}

  #sa-app .sa-tabs{display:flex; gap:4px; border-bottom:1px solid var(--sa-line); margin-bottom:22px;}
  #sa-app .sa-tab{padding:10px 16px; font-size:13px; font-weight:600; color:var(--sa-ink-2); text-decoration:none; border-bottom:2px solid transparent; margin-bottom:-1px;}
  #sa-app .sa-tab.active{color:var(--sa-teal-dark); border-bottom-color:var(--sa-teal);}
  #sa-app .sa-tab:hover:not(.active){color:var(--sa-ink);}

  #sa-app .sa-card{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; padding:18px 20px; margin-bottom:22px;}
  #sa-app .sa-card h3{font-size:13.5px; font-weight:700; margin:0 0 14px; color:var(--sa-ink);}

  #sa-app .sa-form-grid{display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap;}
  #sa-app .sa-field{flex:1; min-width:160px; display:flex; flex-direction:column; gap:5px;}
  #sa-app .sa-field.sa-w-sm{flex:0 0 140px;}
  #sa-app .sa-field label{font-size:11.5px; color:var(--sa-ink-2); font-weight:600;}
  #sa-app .sa-field input, #sa-app .sa-field select, #sa-app .sa-field textarea{border:1px solid var(--sa-line); border-radius:8px; padding:9px 10px; font-size:13px; font-family:inherit; background:#fff; color:var(--sa-ink);}
  #sa-app .sa-field-error{color:var(--sa-red); font-size:11px; margin-top:2px;}
  #sa-app .sa-form-foot{display:flex; justify-content:space-between; align-items:center; gap:8px; margin-top:4px;}
  #sa-app .sa-hint{font-size:11.5px; color:var(--sa-ink-2);}

  #sa-app .sa-btn{display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; font-family:inherit; color:#fff; background:var(--sa-teal); border:1px solid var(--sa-teal); border-radius:8px; padding:9px 14px; cursor:pointer;}
  #sa-app .sa-or-sep{display:flex; align-items:center; gap:10px; color:var(--sa-ink-2); font-size:11.5px; margin:2px 0 12px;}
  #sa-app .sa-or-sep::before, #sa-app .sa-or-sep::after{content:""; flex:1; height:1px; background:var(--sa-line);}

  #sa-app .sa-table-wrap{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; overflow:hidden; overflow-x:auto;}
  #sa-app .sa-table{width:100%; border-collapse:collapse; font-size:12.5px;}
  #sa-app .sa-table th{text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:var(--sa-ink-2); padding:10px 16px; border-bottom:1px solid var(--sa-line); background:#fafbfc; white-space:nowrap;}
  #sa-app .sa-table td{padding:12px 16px; border-bottom:1px solid var(--sa-line); color:var(--sa-ink); vertical-align:middle;}
  #sa-app .sa-table tr:last-child td{border-bottom:none;}
  #sa-app .sa-cell-title{font-weight:600; color:var(--sa-ink);}
  #sa-app .sa-cell-muted{color:var(--sa-ink-2); font-size:12px;}

  #sa-app .sa-badge{padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap; display:inline-block;}
  #sa-app .sa-badge-amber{background:#fff3e0; color:var(--sa-amber);}
  #sa-app .sa-badge-green{background:#e7f9ee; color:var(--sa-green);}
  #sa-app .sa-badge-red{background:#fdeaea; color:var(--sa-red);}

  #sa-app .sa-icon-btn{width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--sa-line); border-radius:7px; background:#fff; color:var(--sa-ink-2); cursor:pointer;}
  #sa-app .sa-icon-btn:hover{color:var(--sa-red); border-color:#f6c9c9;}
  #sa-app .sa-icon-btn svg{width:14px; height:14px;}
  #sa-app .sa-empty-hint{font-size:12.5px; color:var(--sa-ink-2); text-align:center; padding:20px 0;}
</style>

<div id="sa-app">

  <div class="sa-page-head">
    <h1>Suivi d'activité</h1>
    <p class="sa-sub">Déclare le temps passé sur tes tâches, projet par projet.</p>
  </div>

  <div class="sa-tabs">
    <a href="{{ route('activites.my-tasks.index') }}" class="sa-tab {{ request()->routeIs('activites.my-tasks.*') ? 'active' : '' }}">Mes tâches</a>
    <a href="{{ route('activites.time-entries.index') }}" class="sa-tab {{ request()->routeIs('activites.time-entries.*') ? 'active' : '' }}">Saisie de temps</a>
  </div>

  <div class="sa-card">
    <h3>Nouvelle saisie</h3>

    @if ($projects->isEmpty())
      <p class="sa-hint">Aucune tâche ne t'est encore assignée sur un projet : impossible de saisir du temps pour l'instant.</p>
    @else
      <form method="POST" action="{{ route('activites.time-entries.store') }}" id="sa-time-form">
        @csrf
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Projet</label>
            <select name="project_id" id="sa-project-select" required>
              <option value="" disabled selected>Choisir un projet</option>
              @foreach ($projects as $proj)
                <option value="{{ $proj->id }}" @selected((int) old('project_id') === $proj->id)>{{ $proj->name }}</option>
              @endforeach
            </select>
            @error('project_id') <div class="sa-field-error">{{ $message }}</div> @enderror
          </div>
          <div class="sa-field">
            <label>Tâche</label>
            <select name="task_id" id="sa-task-select" required>
              <option value="" disabled selected>Choisis d'abord un projet</option>
            </select>
            @error('task_id') <div class="sa-field-error">{{ $message }}</div> @enderror
          </div>
          <div class="sa-field sa-w-sm">
            <label>Date</label>
            <input type="date" name="activity_date" value="{{ old('activity_date', now()->toDateString()) }}" required>
            @error('activity_date') <div class="sa-field-error">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="sa-form-grid">
          <div class="sa-field sa-w-sm">
            <label>Heure de début</label>
            <input type="time" name="start_time" value="{{ old('start_time') }}">
          </div>
          <div class="sa-field sa-w-sm">
            <label>Heure de fin</label>
            <input type="time" name="end_time" value="{{ old('end_time') }}">
            @error('end_time') <div class="sa-field-error">{{ $message }}</div> @enderror
          </div>
          <div class="sa-field sa-w-sm">
            <label>… ou durée</label>
            <input type="text" name="duration" placeholder="ex: 1h30" value="{{ old('duration') }}">
          </div>
        </div>
        @error('duration') <div class="sa-field-error" style="margin-top:-8px; margin-bottom:12px;">{{ $message }}</div> @enderror

        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Description du travail réalisé</label>
            <textarea name="comment" rows="2" required>{{ old('comment') }}</textarea>
            @error('comment') <div class="sa-field-error">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="sa-form-foot">
          <span class="sa-hint">Renseigne soit une heure de début/fin, soit une durée.</span>
          <button type="submit" class="sa-btn">Enregistrer la saisie</button>
        </div>
      </form>
    @endif
  </div>

  <div class="sa-card">
    <h3>Historique de mes saisies</h3>
    @if ($entries->isEmpty())
      <div class="sa-empty-hint">Aucune saisie de temps enregistrée pour l'instant.</div>
    @else
      <div class="sa-table-wrap">
        <table class="sa-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Projet</th>
              <th>Tâche</th>
              <th>Durée</th>
              <th>Description</th>
              <th>Statut</th>
              <th style="width:50px;"></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($entries as $entry)
              <tr>
                <td class="sa-cell-muted">{{ $entry->activity_date->format('d/m/Y') }}</td>
                <td class="sa-cell-muted">{{ $entry->task->project->name ?? '—' }}</td>
                <td class="sa-cell-title">{{ $entry->task->title ?? '—' }}</td>
                <td class="sa-cell-muted">{{ \App\Support\Duration::toHuman($entry->duration_minutes) }}</td>
                <td class="sa-cell-muted">{{ \Illuminate\Support\Str::limit($entry->comment, 50) }}</td>
                <td><span class="sa-badge {{ $entry->statusBadgeClass() }}">{{ $entry->statusLabel() }}</span></td>
                <td>
                  @if ($entry->status !== 'validee')
                    <form method="POST" action="{{ route('activites.time-entries.destroy', $entry) }}" onsubmit="return confirm('Supprimer cette saisie ?');">
                      @csrf @method('DELETE')
                      <button type="submit" class="sa-icon-btn" title="Supprimer">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z"/></svg>
                      </button>
                    </form>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div style="margin-top:16px;">{{ $entries->links() }}</div>
    @endif
  </div>

</div>

<script>
  (function () {
    const projectSelect = document.getElementById('sa-project-select');
    const taskSelect = document.getElementById('sa-task-select');
    if (!projectSelect || !taskSelect) return;

    const urlTemplate = @json(route('activites.time-entries.tasks-for-project', ['project' => '__ID__']));

    projectSelect.addEventListener('change', function () {
      const projectId = this.value;
      taskSelect.innerHTML = '<option value="" disabled selected>Chargement...</option>';
      if (!projectId) return;

      fetch(urlTemplate.replace('__ID__', projectId), { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(tasks => {
          if (!tasks.length) {
            taskSelect.innerHTML = '<option value="" disabled selected>Aucune tâche assignée sur ce projet</option>';
            return;
          }
          taskSelect.innerHTML = '<option value="" disabled selected>Choisir une tâche</option>' +
            tasks.map(t => `<option value="${t.id}">${t.title}</option>`).join('');
        })
        .catch(() => {
          taskSelect.innerHTML = '<option value="" disabled selected>Erreur de chargement</option>';
        });
    });
  })();
</script>
@endsection
