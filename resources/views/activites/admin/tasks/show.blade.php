@extends('layouts.app')

@section('title', "Suivi d'activité")
@section('page-title', "Suivi d'activité — {$task->title}")

@section('content')
<style>
  #sa-app{
    --sa-teal:#14b8a6; --sa-teal-dark:#0d9488; --sa-teal-light:#e6f7f5;
    --sa-ink:#0f1720; --sa-ink-2:#6b7684; --sa-line:#e8eaee; --sa-card:#ffffff;
    --sa-amber:#f59e0b; --sa-red:#ef4444; --sa-blue:#3b82f6; --sa-green:#22c55e;
    font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;
  }
  #sa-app .sa-back{display:inline-flex; align-items:center; gap:6px; font-size:12px; color:var(--sa-ink-2); text-decoration:none; margin-bottom:8px;}
  #sa-app .sa-back svg{width:14px; height:14px;}
  #sa-app .sa-page-head h1{font-size:19px; font-weight:800; margin:0 0 4px; color:var(--sa-ink);}
  #sa-app .sa-sub{font-size:12.5px; color:var(--sa-ink-2); margin:0 0 20px;}

  #sa-app .sa-btn{display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; font-family:inherit; color:var(--sa-ink); background:#fff; border:1px solid var(--sa-line); border-radius:8px; padding:9px 14px; cursor:pointer; text-decoration:none; line-height:1;}
  #sa-app .sa-btn-primary{background:var(--sa-teal); border-color:var(--sa-teal); color:#fff;}
  #sa-app .sa-btn-danger{color:var(--sa-red); border-color:#f6c9c9;}

  #sa-app .sa-grid-2{display:grid; grid-template-columns:1.3fr 1fr; gap:16px; margin-bottom:22px;}
  @media (max-width:900px){ #sa-app .sa-grid-2{grid-template-columns:1fr;} }

  #sa-app .sa-card{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; padding:18px 20px;}
  #sa-app .sa-card h3{font-size:13.5px; font-weight:700; margin:0 0 14px; color:var(--sa-ink);}

  #sa-app .sa-form-grid{display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap;}
  #sa-app .sa-field{flex:1; min-width:160px; display:flex; flex-direction:column; gap:5px;}
  #sa-app .sa-field.sa-w-sm{flex:0 0 150px;}
  #sa-app .sa-field label{font-size:11.5px; color:var(--sa-ink-2); font-weight:600;}
  #sa-app .sa-field input, #sa-app .sa-field select, #sa-app .sa-field textarea{border:1px solid var(--sa-line); border-radius:8px; padding:9px 10px; font-size:13px; font-family:inherit; background:#fff; color:var(--sa-ink);}
  #sa-app .sa-field-error{color:var(--sa-red); font-size:11px; margin-top:2px;}
  #sa-app .sa-form-foot{display:flex; justify-content:flex-end; gap:8px; margin-top:4px;}

  #sa-app .sa-badge{padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap; display:inline-block;}
  #sa-app .sa-badge-gray{background:#eef0f3; color:#51586b;}
  #sa-app .sa-badge-blue{background:#e6f0ff; color:var(--sa-blue);}
  #sa-app .sa-badge-amber{background:#fff3e0; color:var(--sa-amber);}
  #sa-app .sa-badge-green{background:#e7f9ee; color:var(--sa-green);}
  #sa-app .sa-badge-red{background:#fdeaea; color:var(--sa-red);}

  #sa-app .sa-progress-wrap{display:flex; align-items:center; gap:8px; margin-bottom:16px;}
  #sa-app .sa-progress-track{flex:1; height:8px; background:#f1f3f5; border-radius:4px; overflow:hidden;}
  #sa-app .sa-progress-fill{height:100%; border-radius:4px; background:var(--sa-teal-dark);}

  #sa-app .sa-meta-list{list-style:none; margin:0; padding:0; font-size:12.5px;}
  #sa-app .sa-meta-list li{display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--sa-line);}
  #sa-app .sa-meta-list li:last-child{border-bottom:none;}
  #sa-app .sa-meta-list span:first-child{color:var(--sa-ink-2);}
  #sa-app .sa-meta-list span:last-child{font-weight:600; color:var(--sa-ink);}

  #sa-app .sa-table-wrap{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; overflow:hidden; overflow-x:auto;}
  #sa-app .sa-table{width:100%; border-collapse:collapse; font-size:12.5px;}
  #sa-app .sa-table th{text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:var(--sa-ink-2); padding:10px 16px; border-bottom:1px solid var(--sa-line); background:#fafbfc; white-space:nowrap;}
  #sa-app .sa-table td{padding:12px 16px; border-bottom:1px solid var(--sa-line); color:var(--sa-ink); vertical-align:middle;}
  #sa-app .sa-table tr:last-child td{border-bottom:none;}
  #sa-app .sa-cell-muted{color:var(--sa-ink-2); font-size:12px;}
  #sa-app .sa-empty-hint{font-size:12.5px; color:var(--sa-ink-2); text-align:center; padding:20px 0;}
</style>

<div id="sa-app">

  <a href="{{ route('activites.admin.tasks.index') }}" class="sa-back">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Retour aux tâches
  </a>

  <div class="sa-page-head">
    <h1>{{ $task->title }}</h1>
    <p class="sa-sub">
      Projet <a href="{{ route('activites.admin.projects.show', $task->project) }}">{{ $task->project->name }}</a>
      · <span class="sa-badge {{ $task->statusBadgeClass() }}">{{ $task->statusLabel() }}</span>
      · <span class="sa-badge {{ $task->priorityBadgeClass() }}">{{ $task->priorityLabel() }}</span>
    </p>
  </div>

  <div class="sa-grid-2">
    <div class="sa-card" id="sa-edit-task">
      <h3>Modifier la tâche</h3>
      <form method="POST" action="{{ route('activites.admin.tasks.update', $task) }}">
        @csrf @method('PUT')
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Nom de la tâche</label>
            <input type="text" name="title" value="{{ old('title', $task->title) }}" required>
            @error('title') <div class="sa-field-error">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Projet</label>
            <select name="project_id" required>
              @foreach ($projects as $proj)
                <option value="{{ $proj->id }}" @selected(old('project_id', $task->project_id) == $proj->id)>{{ $proj->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="sa-field">
            <label>Assigner à</label>
            <select name="assigned_to" required>
              @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('assigned_to', $task->assigned_to) == $employee->id)>{{ $employee->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Description</label>
            <textarea name="description" rows="3">{{ old('description', $task->description) }}</textarea>
          </div>
        </div>
        <div class="sa-form-grid">
          <div class="sa-field sa-w-sm">
            <label>Priorité</label>
            <select name="priority">
              @foreach (\App\Models\Task::PRIORITIES as $p)
                <option value="{{ $p }}" @selected(old('priority', $task->priority) === $p)>{{ \App\Models\Task::PRIORITY_LABELS[$p] }}</option>
              @endforeach
            </select>
          </div>
          <div class="sa-field sa-w-sm">
            <label>Statut</label>
            <select name="status">
              @foreach (\App\Models\Task::STATUSES as $s)
                <option value="{{ $s }}" @selected(old('status', $task->status) === $s)>{{ \App\Models\Task::STATUS_LABELS[$s] }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="sa-form-grid">
          <div class="sa-field sa-w-sm">
            <label>Date de début</label>
            <input type="date" name="start_date" value="{{ old('start_date', optional($task->start_date)->format('Y-m-d')) }}">
          </div>
          <div class="sa-field sa-w-sm">
            <label>Échéance</label>
            <input type="date" name="due_date" value="{{ old('due_date', optional($task->due_date)->format('Y-m-d')) }}">
          </div>
          <div class="sa-field sa-w-sm">
            <label>Estimation</label>
            <input type="text" name="estimated_duration" placeholder="ex: 4h" value="{{ old('estimated_duration', $task->estimated_minutes ? \App\Support\Duration::toHuman($task->estimated_minutes) : '') }}">
            @error('estimated_duration') <div class="sa-field-error">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="sa-form-foot">
          <button type="submit" class="sa-btn sa-btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>

    <div>
      <div class="sa-card" style="margin-bottom:16px;">
        <h3>Avancement</h3>
        <div class="sa-progress-wrap">
          <div class="sa-progress-track"><div class="sa-progress-fill" style="width:{{ $task->percent_complete }}%;"></div></div>
          <strong>{{ $task->percent_complete }}%</strong>
        </div>
        <ul class="sa-meta-list">
          <li><span>Créée par</span><span>{{ $task->owner->name ?? '—' }}</span></li>
          <li><span>Temps loggé</span><span>{{ \App\Support\Duration::toHuman($task->logged_minutes) }}</span></li>
          <li><span>Temps estimé</span><span>{{ $task->estimated_minutes ? \App\Support\Duration::toHuman($task->estimated_minutes) : '—' }}</span></li>
          <li><span>Échéance</span><span style="{{ $task->isLate() ? 'color:var(--sa-red);' : '' }}">{{ $task->due_date ? $task->due_date->format('d/m/Y') : '—' }}{{ $task->isLate() ? ' (dépassée)' : '' }}</span></li>
        </ul>
        @if ($task->employee_comment)
          <div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--sa-line); font-size:12.5px;">
            <div style="color:var(--sa-ink-2); margin-bottom:4px;">Commentaire de l'employé :</div>
            {{ $task->employee_comment }}
          </div>
        @endif
      </div>

      <div class="sa-card">
        <h3>Supprimer cette tâche</h3>
        <form method="POST" action="{{ route('activites.admin.tasks.destroy', $task) }}" onsubmit="return confirm('Supprimer définitivement cette tâche ?');">
          @csrf @method('DELETE')
          <button type="submit" class="sa-btn sa-btn-danger">Supprimer la tâche</button>
        </form>
      </div>
    </div>
  </div>

  <div class="sa-card">
    <h3>Saisies de temps</h3>
    @if ($task->activities->isEmpty())
      <div class="sa-empty-hint">Aucune saisie de temps sur cette tâche pour l'instant.</div>
    @else
      <div class="sa-table-wrap">
        <table class="sa-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Employé</th>
              <th>Durée</th>
              <th>Description</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($task->activities as $activity)
              <tr>
                <td class="sa-cell-muted">{{ $activity->activity_date->format('d/m/Y') }}</td>
                <td>{{ $activity->user->name ?? '—' }}</td>
                <td class="sa-cell-muted">{{ \App\Support\Duration::toHuman($activity->duration_minutes) }}</td>
                <td class="sa-cell-muted">{{ \Illuminate\Support\Str::limit($activity->comment, 60) ?: '—' }}</td>
                <td><span class="sa-badge {{ $activity->statusBadgeClass() }}">{{ $activity->statusLabel() }}</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

</div>
@endsection
