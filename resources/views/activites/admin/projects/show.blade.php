@extends('layouts.app')

@section('title', "Suivi d'activité")
@section('page-title', "Suivi d'activité — {$project->name}")

@section('content')
<style>
  #sa-app{
    --sa-teal:#14b8a6; --sa-teal-dark:#0d9488; --sa-teal-light:#e6f7f5;
    --sa-ink:#0f1720; --sa-ink-2:#6b7684; --sa-line:#e8eaee; --sa-card:#ffffff;
    --sa-amber:#f59e0b; --sa-red:#ef4444; --sa-blue:#3b82f6; --sa-green:#22c55e;
    font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;
  }
  #sa-app .sa-page-head{display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:16px; flex-wrap:wrap;}
  #sa-app .sa-back{display:inline-flex; align-items:center; gap:6px; font-size:12px; color:var(--sa-ink-2); text-decoration:none; margin-bottom:8px;}
  #sa-app .sa-back svg{width:14px; height:14px;}
  #sa-app .sa-page-head h1{font-size:19px; font-weight:800; margin:0 0 4px; color:var(--sa-ink);}
  #sa-app .sa-sub{font-size:12.5px; color:var(--sa-ink-2); margin:0;}

  #sa-app .sa-btn{display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; font-family:inherit; color:var(--sa-ink); background:#fff; border:1px solid var(--sa-line); border-radius:8px; padding:9px 14px; cursor:pointer; text-decoration:none; line-height:1;}
  #sa-app .sa-btn-primary{background:var(--sa-teal); border-color:var(--sa-teal); color:#fff;}
  #sa-app .sa-btn-danger{color:var(--sa-red); border-color:#f6c9c9;}

  #sa-app .sa-grid-2{display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:22px;}
  @media (max-width:900px){ #sa-app .sa-grid-2{grid-template-columns:1fr;} }

  #sa-app .sa-card{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; padding:18px 20px;}
  #sa-app .sa-card h3{font-size:13.5px; font-weight:700; margin:0 0 14px; color:var(--sa-ink);}

  #sa-app .sa-form-grid{display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap;}
  #sa-app .sa-field{flex:1; min-width:160px; display:flex; flex-direction:column; gap:5px;}
  #sa-app .sa-field.sa-w-sm{flex:0 0 160px;}
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

  #sa-app .sa-table-wrap{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; overflow:hidden; overflow-x:auto;}
  #sa-app .sa-table{width:100%; border-collapse:collapse; font-size:12.5px;}
  #sa-app .sa-table th{text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:var(--sa-ink-2); padding:10px 16px; border-bottom:1px solid var(--sa-line); background:#fafbfc; white-space:nowrap;}
  #sa-app .sa-table td{padding:12px 16px; border-bottom:1px solid var(--sa-line); color:var(--sa-ink); vertical-align:middle;}
  #sa-app .sa-table tr:last-child td{border-bottom:none;}
  #sa-app .sa-cell-title{font-weight:600; color:var(--sa-ink);}
  #sa-app .sa-cell-muted{color:var(--sa-ink-2); font-size:12px;}
  #sa-app .sa-avatar{width:22px; height:22px; border-radius:50%; background:var(--sa-teal-light); color:var(--sa-teal-dark); font-size:10.5px; font-weight:700; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;}
  #sa-app .sa-empty-hint{font-size:12.5px; color:var(--sa-ink-2); text-align:center; padding:20px 0;}
</style>

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
