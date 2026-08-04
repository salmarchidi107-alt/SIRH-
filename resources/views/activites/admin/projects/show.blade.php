@extends('layouts.app')

@section('title', "Suivi d'activité")
@section('page-title', "Suivi d'activité — {$project->name}")

@section('content')
<div id="sa-app">

  <a href="{{ route('activites.admin.projects.index') }}" class="sa-back">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Retour aux projets
  </a>

  <div class="sa-page-head">
    <div>
      <h1>{{ $project->name }}</h1>
      <p class="sa-sub">
        <span class="sa-badge {{ $project->statusBadgeClass() }}">{{ $project->statusLabel() }}</span>
        · Créé le {{ $project->created_at->format('d/m/Y') }}
        · {{ $project->tasks->count() }} tâche(s)
      </p>
    </div>
  </div>

  <div class="sa-grid-2">
    <div class="sa-card" id="sa-edit-project">
      <h3>Modifier le projet</h3>
      <form method="POST" action="{{ route('activites.admin.projects.update', $project) }}">
        @csrf @method('PUT')
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Nom du projet</label>
            <input type="text" name="name" value="{{ old('name', $project->name) }}" required>
            @error('name') <div class="sa-field-error">{{ $message }}</div> @enderror
          </div>
          <div class="sa-field sa-w-sm">
            <label>Statut</label>
            <select name="status">
              @foreach (\App\Models\Project::STATUSES as $status)
                <option value="{{ $status }}" @selected(old('status', $project->status) === $status)>{{ \App\Models\Project::STATUS_LABELS[$status] }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Description</label>
            <textarea name="description" rows="3">{{ old('description', $project->description) }}</textarea>
          </div>
        </div>
        <div class="sa-form-foot">
          <button type="submit" class="sa-btn sa-btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>

    <div class="sa-card">
      <h3>Supprimer ce projet</h3>
      <p class="sa-sub" style="margin-bottom:14px;">Cette action supprime définitivement le projet ainsi que toutes ses tâches et saisies de temps associées.</p>
      <form method="POST" action="{{ route('activites.admin.projects.destroy', $project) }}" onsubmit="return confirm('Supprimer définitivement ce projet et toutes ses tâches ?');">
        @csrf @method('DELETE')
        <button type="submit" class="sa-btn sa-btn-danger">Supprimer le projet</button>
      </form>
    </div>
  </div>

  <div class="sa-card">
    <h3>Tâches du projet</h3>
    @if ($project->tasks->isEmpty())
      <div class="sa-empty-hint">Aucune tâche pour l'instant. Ajoute-en une depuis l'onglet « Tâches ».</div>
    @else
      <div class="sa-table-wrap">
        <table class="sa-table">
          <thead>
            <tr>
              <th>Tâche</th>
              <th>Assignée à</th>
              <th>Priorité</th>
              <th>Échéance</th>
              <th>Temps</th>
              <th>Statut</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($project->tasks as $task)
              <tr>
                <td class="sa-cell-title">{{ $task->title }}</td>
                <td>
                  <span style="display:flex; align-items:center; gap:6px;">
                    <span class="sa-avatar">{{ strtoupper(substr($task->assignee->name ?? '?', 0, 1)) }}</span>
                    {{ $task->assignee->name ?? '—' }}
                  </span>
                </td>
                <td><span class="sa-badge {{ $task->priorityBadgeClass() }}">{{ $task->priorityLabel() }}</span></td>
                <td class="sa-cell-muted" style="{{ $task->isLate() ? 'color:var(--sa-red); font-weight:700;' : '' }}">
                  {{ $task->due_date ? $task->due_date->format('d/m/Y') : '—' }}
                </td>
                <td class="sa-cell-muted">{{ \App\Support\Duration::toHuman($task->logged_minutes) }}</td>
                <td><span class="sa-badge {{ $task->statusBadgeClass() }}">{{ $task->statusLabel() }}</span></td>
                <td><a href="{{ route('activites.admin.tasks.show', $task) }}" class="sa-btn" style="padding:5px 10px;">Voir</a></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

</div>
@endsection