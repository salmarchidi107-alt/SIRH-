@extends('layouts.app')

@section('title', 'Mes projets')
@section('page-title', 'Mes projets')

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

  .sa-task-row{display:flex; align-items:center; gap:14px; background:#fff; border:1px solid var(--border, #e2e8f0); border-radius:16px; padding:14px 18px; margin-bottom:10px; flex-wrap:wrap; text-decoration:none; color:inherit; box-shadow:0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03); transition:transform .2s ease, box-shadow .2s ease;}
  .sa-task-row:hover{transform:translateY(-2px); box-shadow:0 10px 15px -3px rgba(0,0,0,.08), 0 4px 6px -2px rgba(0,0,0,.05);}
  .sa-task-row .sa-dot{width:9px;height:9px;border-radius:50%; flex-shrink:0;}
  .sa-dot-a_faire{background:#c6cdd6;} .sa-dot-en_cours{background:var(--primary, #0f6b7c);}

  .sa-task-main{flex:1; min-width:180px;}
  .sa-task-main .sa-title{font-weight:600; font-size:13.5px; margin-bottom:3px; color:#0f172a;}
  .sa-task-main .sa-meta{font-size:11.5px; color:var(--text-muted, #64748b);}

  .sa-badge{padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap;}
  .sa-b-a_faire{background:#eef0f3; color:#51586b;}
  .sa-b-en_cours{background:#e6f3f5; color:var(--primary, #0f6b7c);}

  .sa-form-card{background:#fff; border:1px solid var(--border, #e2e8f0); border-radius:16px; padding:18px 20px; box-shadow:0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03);}
  .sa-form-row{display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap;}
  .sa-field{flex:1; min-width:140px; display:flex; flex-direction:column; gap:5px;}
  .sa-field label{font-size:11.5px; color:var(--text-muted, #64748b); font-weight:600;}
  .sa-field input, .sa-field select, .sa-field textarea{border:1px solid var(--border, #e2e8f0); border-radius:8px; padding:9px 10px; font-size:13px; font-family:inherit; background:#fff; transition:border-color .15s ease;}
  .sa-field input:focus, .sa-field select:focus, .sa-field textarea:focus{outline:none; border-color:var(--primary, #0f6b7c);}
  .sa-field-error{color:var(--danger, #ef4444); font-size:11px; margin-top:2px;}
  .sa-form-foot{display:flex; justify-content:flex-end; gap:8px; margin-top:4px;}

  .sa-empty-hint{font-size:12px; color:var(--text-muted, #64748b); text-align:center; padding:18px 0;}

  @media (max-width:700px){ .sa-stat{flex:1 1 45%;} .sa-form-row{flex-direction:column;} }
</style>

  <p style="font-size:12.5px; color:var(--text-muted, #64748b); margin-bottom:20px;">Crée un projet, puis ajoute-y tes tâches et suis le temps que tu y consacres</p>

  <div class="sa-stats-row">
    <div class="sa-stat sa-good"><div class="sa-label">Temps aujourd'hui</div><div class="sa-value">{{ \App\Support\Duration::toHuman($stats['today_minutes']) }}</div></div>
    <div class="sa-stat sa-good"><div class="sa-label">Temps cette semaine</div><div class="sa-value">{{ \App\Support\Duration::toHuman($stats['week_minutes']) }}</div></div>
    <div class="sa-stat"><div class="sa-label">Projets actifs</div><div class="sa-value">{{ $stats['active_projects'] }}</div></div>
  </div>

  <div class="sa-section-head"><h3>Nouveau projet</h3></div>
  <div class="sa-form-card">
    <form method="POST" action="{{ route('activites.projects.store') }}">
      @csrf
      <div class="sa-form-row">
        <div class="sa-field">
          <label>Nom du projet</label>
          <input type="text" name="name" value="{{ old('name') }}" placeholder="Ex : Migration RH 2026" required>
          @error('name') <div class="sa-field-error">{{ $message }}</div> @enderror
        </div>
      </div>
      <div class="sa-form-row">
        <div class="sa-field">
          <label>Description (optionnel)</label>
          <textarea name="description" rows="2" placeholder="À quoi correspond ce projet...">{{ old('description') }}</textarea>
        </div>
      </div>
      <div class="sa-form-foot">
        <button class="btn btn-primary" type="submit">+ Créer le projet</button>
      </div>
    </form>
  </div>

  <div class="sa-section-head"><h3>Mes projets</h3></div>

  @forelse ($projects as $project)
    <a href="{{ route('activites.projects.show', $project) }}" class="sa-task-row">
      <div class="sa-dot {{ $project->status === 'actif' ? 'sa-dot-en_cours' : 'sa-dot-a_faire' }}"></div>
      <div class="sa-task-main">
        <div class="sa-title">{{ $project->name }}</div>
        <div class="sa-meta">{{ $project->tasks_count }} tâche(s)</div>
      </div>
      <span class="sa-badge {{ $project->status === 'actif' ? 'sa-b-en_cours' : 'sa-b-a_faire' }}">{{ ucfirst($project->status) }}</span>
    </a>
  @empty
    <div class="sa-empty-hint">Aucun projet pour l'instant — crée ton premier projet ci-dessus.</div>
  @endforelse
@endsection
