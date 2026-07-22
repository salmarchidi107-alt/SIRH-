@extends('layouts.app')

@section('title', "Suivi d'activité")
@section('page-title', "Suivi d'activité — Équipe")

@section('content')
<style>
  .sa-stats-row{display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; font-family:'Plus Jakarta Sans', inherit;}
  .sa-stat{flex:1; min-width:140px; background:#fff; border:1px solid var(--border, #e2e8f0); border-radius:16px; padding:16px 18px; box-shadow:0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03);}
  .sa-stat .sa-label{font-size:11.5px; color:var(--text-muted, #64748b); margin-bottom:6px; font-weight:600;}
  .sa-stat .sa-value{font-size:20px; font-weight:800; color:#0f172a;}
  .sa-stat.sa-warn .sa-value{color:var(--danger, #ef4444);}
  .sa-stat.sa-good .sa-value{color:var(--primary, #0f6b7c);}

  .sa-section-head{display:flex; align-items:center; justify-content:space-between; margin:26px 0 12px;}
  .sa-section-head h3{font-size:15px; font-weight:700; margin:0; color:#0f172a; font-family:'Plus Jakarta Sans', inherit;}

  .sa-filters{display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap;}
  .sa-filters select{border:1px solid var(--border, #e2e8f0); border-radius:8px; padding:7px 10px; font-size:12.5px; background:#fff; font-family:inherit; transition:border-color .15s ease;}
  .sa-filters select:focus{outline:none; border-color:var(--primary, #0f6b7c);}

  .sa-task-row{display:flex; align-items:center; gap:14px; background:#fff; border:1px solid var(--border, #e2e8f0); border-radius:16px; padding:14px 18px; margin-bottom:10px; flex-wrap:wrap; box-shadow:0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03); transition:transform .2s ease, box-shadow .2s ease;}
  .sa-task-row:hover{transform:translateY(-2px); box-shadow:0 10px 15px -3px rgba(0,0,0,.08), 0 4px 6px -2px rgba(0,0,0,.05);}
  .sa-task-row .sa-dot{width:9px;height:9px;border-radius:50%; flex-shrink:0;}
  .sa-dot-a_faire{background:#c6cdd6;} .sa-dot-en_cours{background:var(--primary, #0f6b7c);} .sa-dot-en_pause{background:var(--warning, #f59e0b);}
  .sa-dot-terminee{background:var(--success, #22c55e);} .sa-dot-annulee{background:var(--danger, #ef4444);}

  .sa-task-main{flex:1; min-width:180px;}
  .sa-task-main .sa-title{font-weight:600; font-size:13.5px; margin-bottom:3px; color:#0f172a;}
  .sa-task-main .sa-meta{font-size:11.5px; color:var(--text-muted, #64748b);}
  .sa-task-main .sa-meta b{color:#0f172a;}

  .sa-badge{padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap;}
  .sa-b-a_faire{background:#eef0f3; color:#51586b;}
  .sa-b-en_cours{background:#e6f3f5; color:var(--primary, #0f6b7c);}
  .sa-b-en_pause{background:#fff3e0; color:var(--warning, #f59e0b);}
  .sa-b-terminee{background:#e7f9ee; color:var(--success, #22c55e);}
  .sa-b-annulee{background:#fdeaea; color:var(--danger, #ef4444);}

  .sa-task-time{font-size:12px; color:var(--text-muted, #64748b); width:70px; text-align:right; flex-shrink:0;}
  .sa-task-time b{color:#0f172a; display:block; font-size:13px;}

  .sa-empty-hint{font-size:12px; color:var(--text-muted, #64748b); text-align:center; padding:18px 0;}

  @media (max-width:700px){ .sa-stat{flex:1 1 45%;} }
</style>

  <p style="font-size:12.5px; color:var(--text-muted, #64748b); margin-bottom:20px;">Vue d'ensemble des tâches et du temps de l'équipe</p>
  <div class="sa-stats-row">
    <div class="sa-stat"><div class="sa-label">Tâches en cours</div><div class="sa-value">{{ $stats['active_tasks'] }}</div></div>
    <div class="sa-stat sa-good"><div class="sa-label">Temps aujourd'hui</div><div class="sa-value">{{ \App\Support\Duration::toHuman($stats['today_minutes']) }}</div></div>
    <div class="sa-stat sa-warn"><div class="sa-label">En retard</div><div class="sa-value">{{ $stats['late_tasks'] }}</div></div>
    <div class="sa-stat"><div class="sa-label">Employés actifs</div><div class="sa-value">{{ $stats['active_employees'] }} / {{ $stats['total_employees'] }}</div></div>
  </div>
  <div class="sa-section-head"><h3>Tâches</h3></div>
  <form method="GET" class="sa-filters">
    <select name="user_id" onchange="this.form.submit()">
      <option value="">Tous les employés</option>
      @foreach ($employees as $employee)
        <option value="{{ $employee->id }}" @selected(request('user_id') == $employee->id)>{{ $employee->name }}</option>
      @endforeach
    </select>
    <select name="project_id" onchange="this.form.submit()">
      <option value="">Tous les projets</option>
      @foreach ($projects as $proj)
        <option value="{{ $proj->id }}" @selected(request('project_id') == $proj->id)>{{ $proj->name }}</option>
      @endforeach
    </select>
    <select name="status" onchange="this.form.submit()">
      <option value="">Tous les statuts</option>
      @foreach (\App\Models\Task::STATUSES as $status)
        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_',' ', ucfirst($status)) }}</option>
      @endforeach
    </select>
    <select name="priority" onchange="this.form.submit()">
      <option value="">Toutes les priorités</option>
      @foreach (\App\Models\Task::PRIORITIES as $priority)
        <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ ucfirst($priority) }}</option>
      @endforeach
    </select>
  </form>
  @forelse ($tasks as $task)
    <div class="sa-task-row">
      <div class="sa-dot sa-dot-{{ $task->status }}"></div>
      <div class="sa-task-main">
        <div class="sa-title">{{ $task->title }}</div>
        <div class="sa-meta">
          Projet <b>{{ $task->project->name }}</b> · Créée par <b>{{ $task->owner->name }}</b> · Priorité {{ $task->priority }}
          @if ($task->due_date)
            · Échéance {{ $task->due_date->format('d/m/Y') }}
            @if ($task->isLate())
              · <span style="color:var(--danger, #ef4444); font-weight:600;">Échéance dépassée</span>
            @endif
          @endif
        </div>
      </div>
      <span class="sa-badge sa-b-{{ $task->status }}">{{ str_replace('_',' ', ucfirst($task->status)) }}</span>
      <div class="sa-task-time"><b>{{ \App\Support\Duration::toHuman($task->logged_minutes) }}</b>{{ $task->estimated_minutes ? ' / '.\App\Support\Duration::toHuman($task->estimated_minutes) : '' }}</div>
    </div>
  @empty
    <div class="sa-empty-hint">Aucune tâche ne correspond à ces filtres.</div>
  @endforelse
  <div style="margin-top:16px;">{{ $tasks->links() }}</div>
@endsection
