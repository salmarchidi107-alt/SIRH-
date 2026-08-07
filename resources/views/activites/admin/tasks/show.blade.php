@extends('layouts.app')

@section('title', "Suivi d'activité")
@section('page-title', "Suivi d'activité — {$task->title}")

@section('content')
<div id="sa-app">

  <a href="{{ route('activites.admin.tasks.index') }}" class="sa-back">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Retour aux tâches
  </a>

  <div class="sa-page-head">
    <div>
      <h1>{{ $task->title }}</h1>
      <p class="sa-sub">
        Projet <a href="{{ route('activites.admin.projects.show', $task->project) }}">{{ $task->project->name }}</a>
        · <span class="sa-badge {{ $task->statusBadgeClass() }}">{{ $task->statusLabel() }}</span>
        · <span class="sa-badge {{ $task->priorityBadgeClass() }}">{{ $task->priorityLabel() }}</span>
      </p>
    </div>
  </div>

  <div class="sa-grid-2 sa-grid-2-wide">
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
