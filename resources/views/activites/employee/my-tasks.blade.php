@extends('layouts.app')

@section('title', "Suivi d'activité")
@section('page-title', "Suivi d'activité — Mes tâches")

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

  #sa-app .sa-stats{display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;}
  #sa-app .sa-stat{flex:1; min-width:150px; background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; padding:14px 16px;}
  #sa-app .sa-stat-label{font-size:11.5px; color:var(--sa-ink-2); margin-bottom:6px;}
  #sa-app .sa-stat-value{font-size:20px; font-weight:800; color:var(--sa-ink);}
  #sa-app .sa-stat.sa-warn .sa-stat-value{color:var(--sa-red);}

  #sa-app .sa-filters{display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap;}
  #sa-app .sa-filters select{border:1px solid var(--sa-line); border-radius:8px; padding:7px 10px; font-size:12.5px; background:#fff; font-family:inherit; color:var(--sa-ink);}

  #sa-app .sa-task-card{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; padding:16px 18px; margin-bottom:12px;}
  #sa-app .sa-task-head{display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:10px;}
  #sa-app .sa-task-title{font-weight:700; font-size:14px; color:var(--sa-ink); margin-bottom:4px;}
  #sa-app .sa-task-meta{font-size:12px; color:var(--sa-ink-2);}
  #sa-app .sa-task-meta b{color:var(--sa-ink);}

  #sa-app .sa-badge{padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap; display:inline-block;}
  #sa-app .sa-badge-gray{background:#eef0f3; color:#51586b;}
  #sa-app .sa-badge-blue{background:#e6f0ff; color:var(--sa-blue);}
  #sa-app .sa-badge-amber{background:#fff3e0; color:var(--sa-amber);}
  #sa-app .sa-badge-green{background:#e7f9ee; color:var(--sa-green);}
  #sa-app .sa-badge-red{background:#fdeaea; color:var(--sa-red);}

  #sa-app .sa-disclosure{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:14px; margin-bottom:20px; overflow:hidden;}
  #sa-app .sa-disclosure summary{list-style:none; cursor:pointer; padding:14px 20px; display:flex; align-items:center; gap:10px; font-weight:700; font-size:13.5px; color:var(--sa-ink);}
  #sa-app .sa-disclosure summary::-webkit-details-marker{display:none;}
  #sa-app .sa-plus-icon{width:20px; height:20px; border-radius:50%; background:var(--sa-teal); color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; flex-shrink:0;}
  #sa-app .sa-chevron{margin-left:auto; color:var(--sa-ink-2); transition:transform .15s ease;}
  #sa-app .sa-disclosure[open] .sa-chevron{transform:rotate(180deg);}
  #sa-app .sa-disclosure-body{padding:16px 20px 20px; border-top:1px solid var(--sa-line);}
  #sa-app .sa-form-grid{display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap;}
  #sa-app .sa-field.sa-w-sm{flex:0 0 150px;}
  #sa-app .sa-field-error{color:var(--sa-red); font-size:11px; margin-top:2px;}
  #sa-app .sa-form-foot{display:flex; justify-content:flex-end; margin-top:4px;}
  #sa-app textarea{border:1px solid var(--sa-line); border-radius:8px; padding:9px 10px; font-size:13px; font-family:inherit; background:#fff; color:var(--sa-ink); width:100%;}

  #sa-app .sa-update-form{display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; padding-top:12px; border-top:1px solid var(--sa-line);}
  #sa-app .sa-field{display:flex; flex-direction:column; gap:5px;}
  #sa-app .sa-field label{font-size:11px; color:var(--sa-ink-2); font-weight:600;}
  #sa-app .sa-field select, #sa-app .sa-field input{border:1px solid var(--sa-line); border-radius:8px; padding:8px 10px; font-size:12.5px; font-family:inherit; background:#fff; color:var(--sa-ink);}
  #sa-app .sa-field.sa-grow{flex:1; min-width:180px;}
  #sa-app .sa-field.sa-percent{width:100px;}
  #sa-app .sa-btn{display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; font-family:inherit; color:#fff; background:var(--sa-teal); border:1px solid var(--sa-teal); border-radius:8px; padding:9px 14px; cursor:pointer;}

  #sa-app .sa-empty-hint{font-size:12.5px; color:var(--sa-ink-2); text-align:center; padding:30px 0; background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px;}
</style>

<div id="sa-app">

  <div class="sa-page-head">
    <h1>Suivi d'activité</h1>
    <p class="sa-sub">Les tâches qui te sont assignées. Tu peux mettre à jour le statut, l'avancement et ajouter un commentaire.</p>
  </div>

  <div class="sa-tabs">
    <a href="{{ route('activites.my-tasks.index') }}" class="sa-tab {{ request()->routeIs('activites.my-tasks.*') ? 'active' : '' }}">Mes tâches</a>
    <a href="{{ route('activites.time-entries.index') }}" class="sa-tab {{ request()->routeIs('activites.time-entries.*') ? 'active' : '' }}">Saisie de temps</a>
  </div>

  <div class="sa-stats">
    <div class="sa-stat"><div class="sa-stat-label">Tâches en cours</div><div class="sa-stat-value">{{ $stats['remaining'] }}</div></div>
    <div class="sa-stat sa-warn"><div class="sa-stat-label">En retard</div><div class="sa-stat-value">{{ $stats['late'] }}</div></div>
  </div>

  <details class="sa-disclosure">
    <summary>
      <span class="sa-plus-icon">+</span>
      Nouvelle tâche
      <svg class="sa-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
    </summary>
    <div class="sa-disclosure-body">
      @if ($projects->isEmpty())
        <p class="sa-sub">Aucun projet actif pour l'instant : demande à un admin d'en créer un.</p>
      @else
        <form method="POST" action="{{ route('activites.my-tasks.store') }}">
          @csrf
          <div class="sa-form-grid">
            <div class="sa-field sa-grow">
              <label>Titre de la tâche</label>
              <input type="text" name="title" value="{{ old('title') }}" placeholder="Ex : Préparer le compte-rendu" required>
              @error('title') <div class="sa-field-error">{{ $message }}</div> @enderror
            </div>
            <div class="sa-field sa-grow">
              <label>Projet</label>
              <select name="project_id" required>
                <option value="" disabled selected>Choisir un projet</option>
                @foreach ($projects as $proj)
                  <option value="{{ $proj->id }}" @selected((int) old('project_id') === $proj->id)>{{ $proj->name }}</option>
                @endforeach
              </select>
              @error('project_id') <div class="sa-field-error">{{ $message }}</div> @enderror
            </div>
          </div>
          <div class="sa-form-grid">
            <div class="sa-field sa-w-sm">
              <label>Priorité</label>
              <select name="priority">
                @foreach (\App\Models\Task::PRIORITIES as $p)
                  <option value="{{ $p }}" @selected(old('priority') === $p)>{{ \App\Models\Task::PRIORITY_LABELS[$p] }}</option>
                @endforeach
              </select>
            </div>
            <div class="sa-field sa-w-sm">
              <label>Échéance</label>
              <input type="date" name="due_date" value="{{ old('due_date') }}">
            </div>
            <div class="sa-field sa-w-sm">
              <label>Durée estimée *</label>
              <input type="text" name="estimated_duration" placeholder="ex: 4h" value="{{ old('estimated_duration') }}" required>
              @error('estimated_duration') <div class="sa-field-error">{{ $message }}</div> @enderror
            </div>
          </div>
          <div class="sa-form-grid">
            <div class="sa-field sa-grow">
              <label>Description</label>
              <textarea name="description" rows="2" placeholder="Optionnel">{{ old('description') }}</textarea>
            </div>
          </div>
          <div class="sa-form-foot">
            <button type="submit" class="sa-btn">Créer la tâche</button>
          </div>
        </form>
      @endif
    </div>
  </details>

  <form method="GET" class="sa-filters">
    <select name="status" onchange="this.form.submit()">
      <option value="">Tous les statuts</option>
      @foreach (\App\Models\Task::STATUSES as $status)
        <option value="{{ $status }}" @selected(request('status') === $status)>{{ \App\Models\Task::STATUS_LABELS[$status] }}</option>
      @endforeach
    </select>
  </form>

  @forelse ($tasks as $task)
    <div class="sa-task-card">
      <div class="sa-task-head">
        <div>
          <div class="sa-task-title">{{ $task->title }}</div>
          <div class="sa-task-meta">
            Projet <b>{{ $task->project->name }}</b>
            · Priorité <b>{{ $task->priorityLabel() }}</b>
            @if ($task->due_date)
              · Échéance <b>{{ $task->due_date->format('d/m/Y') }}</b>
              @if ($task->isLate())
                · <span style="color:var(--sa-red); font-weight:700;">En retard</span>
              @endif
            @endif
            @if ($task->estimated_minutes)
              · Temps <b>{{ \App\Support\Duration::toHuman($task->logged_minutes) }} / {{ \App\Support\Duration::toHuman($task->estimated_minutes) }}</b> estimé
            @endif
          </div>
          @if ($task->description)
            <div class="sa-task-meta" style="margin-top:6px;">{{ $task->description }}</div>
          @endif
        </div>
        <span class="sa-badge {{ $task->statusBadgeClass() }}">{{ $task->statusLabel() }}</span>
      </div>

      <form method="POST" action="{{ route('activites.my-tasks.update', $task) }}" class="sa-update-form">
        @csrf @method('PATCH')
        <div class="sa-field">
          <label>Statut</label>
          <select name="status">
            @foreach (\App\Models\Task::STATUSES as $status)
              <option value="{{ $status }}" @selected($task->status === $status)>{{ \App\Models\Task::STATUS_LABELS[$status] }}</option>
            @endforeach
          </select>
        </div>
        <div class="sa-field sa-percent">
          <label>Avancement (%)</label>
          <input type="number" name="percent_complete" min="0" max="100" value="{{ $task->percent_complete }}">
        </div>
        <div class="sa-field sa-grow">
          <label>Commentaire</label>
          <input type="text" name="employee_comment" value="{{ $task->employee_comment }}" placeholder="Optionnel">
        </div>
        <button type="submit" class="sa-btn">Mettre à jour</button>
      </form>
    </div>
  @empty
    <div class="sa-empty-hint">Aucune tâche ne t'est assignée pour l'instant.</div>
  @endforelse

</div>
@endsection
