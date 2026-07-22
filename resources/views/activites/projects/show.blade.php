@extends('layouts.app')

@section('title', $project->name)
@section('page-title', $project->name)

@section('content')
<style>
  .sa-stats-row{display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; font-family:'Plus Jakarta Sans', inherit;}
  .sa-stat{flex:1; min-width:140px; background:#fff; border:1px solid var(--border, #e2e8f0); border-radius:16px; padding:16px 18px; box-shadow:0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03);}
  .sa-stat .sa-label{font-size:11.5px; color:var(--text-muted, #64748b); margin-bottom:6px; font-weight:600;}
  .sa-stat .sa-value{font-size:20px; font-weight:800; color:#0f172a;}
  .sa-stat.sa-good .sa-value{color:var(--primary, #0f6b7c);}

  .sa-section-head{display:flex; align-items:center; justify-content:space-between; margin:26px 0 12px;}
  .sa-section-head h3{font-size:15px; font-weight:700; margin:0; color:#0f172a; font-family:'Plus Jakarta Sans', inherit;}

  .sa-task-row{display:flex; align-items:center; gap:14px; background:#fff; border:1px solid var(--border, #e2e8f0); border-radius:16px; padding:14px 18px; margin-bottom:10px; flex-wrap:wrap; text-decoration:none; color:inherit; box-shadow:0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03); transition:transform .2s ease, box-shadow .2s ease;}
  .sa-task-row:hover{transform:translateY(-2px); box-shadow:0 10px 15px -3px rgba(0,0,0,.08), 0 4px 6px -2px rgba(0,0,0,.05);}
  .sa-task-row .sa-dot{width:9px;height:9px;border-radius:50%; flex-shrink:0;}
  .sa-dot-a_faire{background:#c6cdd6;} .sa-dot-en_cours{background:var(--primary, #0f6b7c);} .sa-dot-en_pause{background:var(--warning, #f59e0b);}
  .sa-dot-terminee{background:var(--success, #22c55e);} .sa-dot-annulee{background:var(--danger, #ef4444);}

  .sa-task-main{flex:1; min-width:180px;}
  .sa-task-main .sa-title{font-weight:600; font-size:13.5px; margin-bottom:3px; color:#0f172a;}
  .sa-task-main .sa-meta{font-size:11.5px; color:var(--text-muted, #64748b);}

  .sa-badge{padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap;}
  .sa-b-a_faire{background:#eef0f3; color:#51586b;}
  .sa-b-en_cours{background:#e6f3f5; color:var(--primary, #0f6b7c);}
  .sa-b-en_pause{background:#fff3e0; color:var(--warning, #f59e0b);}
  .sa-b-terminee{background:#e7f9ee; color:var(--success, #22c55e);}
  .sa-b-annulee{background:#fdeaea; color:var(--danger, #ef4444);}

  .sa-task-time{font-size:12px; color:var(--text-muted, #64748b); width:70px; text-align:right; flex-shrink:0;}
  .sa-task-time b{color:#0f172a; display:block; font-size:13px;}

  .sa-form-card{background:#fff; border:1px solid var(--border, #e2e8f0); border-radius:16px; padding:18px 20px; box-shadow:0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03);}
  .sa-form-row{display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap;}
  .sa-field{flex:1; min-width:140px; display:flex; flex-direction:column; gap:5px;}
  .sa-field.sa-small{flex:0 0 140px;}
  .sa-field label{font-size:11.5px; color:var(--text-muted, #64748b); font-weight:600;}
  .sa-field input, .sa-field select, .sa-field textarea{border:1px solid var(--border, #e2e8f0); border-radius:8px; padding:9px 10px; font-size:13px; font-family:inherit; background:#fff; transition:border-color .15s ease;}
  .sa-field input:focus, .sa-field select:focus, .sa-field textarea:focus{outline:none; border-color:var(--primary, #0f6b7c);}
  .sa-field-error{color:var(--danger, #ef4444); font-size:11px; margin-top:2px;}
  .sa-form-foot{display:flex; justify-content:flex-end; gap:8px; margin-top:4px;}

  .sa-empty-hint{font-size:12px; color:var(--text-muted, #64748b); text-align:center; padding:18px 0;}

  @media (max-width:700px){ .sa-stat{flex:1 1 45%;} .sa-form-row{flex-direction:column;} }
</style>

  <p style="font-size:12.5px; margin-bottom:8px;"><a href="{{ route('activites.projects.index') }}" style="color:var(--text-muted, #64748b);">← Mes projets</a></p>

  @if ($project->description)
    <p style="font-size:12.5px; color:var(--text-muted, #64748b); margin-bottom:20px;">{{ $project->description }}</p>
  @endif

  <div class="sa-stats-row">
    <div class="sa-stat sa-good"><div class="sa-label">Temps total projet</div><div class="sa-value">{{ \App\Support\Duration::toHuman($project->logged_minutes) }}</div></div>
    <div class="sa-stat"><div class="sa-label">Tâches terminées</div><div class="sa-value">{{ $project->tasks_count_label }}</div></div>
  </div>

  <div class="sa-section-head"><h3>Nouvelle tâche</h3></div>
  <div class="sa-form-card">
    <form method="POST" action="{{ route('activites.tasks.store', $project) }}">
      @csrf
      <div class="sa-form-row">
        <div class="sa-field">
          <label>Titre</label>
          <input type="text" name="title" value="{{ old('title') }}" placeholder="Ex : Rédiger le rapport mensuel" required>
          @error('title') <div class="sa-field-error">{{ $message }}</div> @enderror
        </div>
        <div class="sa-field sa-small">
          <label>Priorité</label>
          <select name="priority">
            @foreach (\App\Models\Task::PRIORITIES as $p)
              <option value="{{ $p }}" @selected(old('priority') === $p)>{{ ucfirst($p) }}</option>
            @endforeach
          </select>
        </div>
        <div class="sa-field sa-small">
          <label>Échéance</label>
          <input type="date" name="due_date" value="{{ old('due_date') }}">
        </div>
        <div class="sa-field sa-small">
          <label>Estimation</label>
          <input type="text" name="estimated_duration" placeholder="ex: 4h" value="{{ old('estimated_duration') }}">
          @error('estimated_duration') <div class="sa-field-error">{{ $message }}</div> @enderror
        </div>
      </div>
      <div class="sa-form-row">
        <div class="sa-field">
          <label>Description (optionnel)</label>
          <textarea name="description" rows="2" placeholder="Détails de la tâche...">{{ old('description') }}</textarea>
        </div>
      </div>
      <div class="sa-form-foot">
        <button class="btn btn-primary" type="submit">+ Créer la tâche</button>
      </div>
    </form>
  </div>

  <div class="sa-section-head"><h3>Tâches du projet</h3></div>

  @forelse ($project->tasks as $task)
    <a href="{{ route('activites.tasks.show', [$project, $task]) }}" class="sa-task-row">
      <div class="sa-dot sa-dot-{{ $task->status }}"></div>
      <div class="sa-task-main">
        <div class="sa-title">{{ $task->title }}</div>
        <div class="sa-meta">
          Priorité {{ $task->priority }}
          @if ($task->due_date)
            · Échéance {{ $task->due_date->format('d/m/Y') }}
            @if ($task->isLate())
              · <span style="color:var(--danger, #ef4444); font-weight:600;">En retard</span>
            @endif
          @endif
        </div>
      </div>
      <span class="sa-badge sa-b-{{ $task->status }}">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span>
      <div class="sa-task-time"><b>{{ \App\Support\Duration::toHuman($task->logged_minutes) }}</b>{{ $task->estimated_minutes ? ' / '.\App\Support\Duration::toHuman($task->estimated_minutes) : '' }}</div>
    </a>
  @empty
    <div class="sa-empty-hint">Aucune tâche dans ce projet pour l'instant — crée-en une ci-dessus.</div>
  @endforelse
@endsection
