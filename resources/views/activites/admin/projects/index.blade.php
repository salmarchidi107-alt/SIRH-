@extends('layouts.app')

@section('title', "Suivi d'activité")
@section('page-title', "Suivi d'activité — Projets")

@section('content')
<div id="sa-app">

  <div class="sa-page-head">
    <div>
      <h1>Suivi d'activité — Équipe</h1>
      <p class="sa-sub">Gestion complète des projets de l'entreprise.</p>
    </div>
    <div class="sa-head-actions">
      <a href="{{ route('activites.admin.projects.export-pdf', request()->query()) }}" class="sa-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export PDF
      </a>
      <a href="{{ route('activites.admin.projects.export-excel', request()->query()) }}" class="sa-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export Excel
      </a>
      <button type="button" class="sa-btn sa-btn-primary" onclick="document.getElementById('sa-new-project').open = true; document.getElementById('sa-new-project').scrollIntoView({behavior:'smooth'});">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        Nouveau projet
      </button>
    </div>
  </div>

  <div class="sa-tabs">
    <a href="{{ route('activites.admin.dashboard') }}" class="sa-tab {{ request()->routeIs('activites.admin.dashboard') ? 'active' : '' }}">État d'avancement</a>
    <a href="{{ route('activites.admin.projects.index') }}" class="sa-tab {{ request()->routeIs('activites.admin.projects.*') ? 'active' : '' }}">Projets</a>
    <a href="{{ route('activites.admin.tasks.index') }}" class="sa-tab {{ request()->routeIs('activites.admin.tasks.*') ? 'active' : '' }}">Tâches</a>
  </div>

  <details id="sa-new-project" class="sa-disclosure">
    <summary>
      <span class="sa-plus-icon">+</span>
      Créer un projet
      <svg class="sa-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
    </summary>
    <div class="sa-disclosure-body">
      <form method="POST" action="{{ route('activites.admin.projects.store') }}">
        @csrf
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Nom du projet</label>
            <input type="text" name="name" value="{{ old('name') }}"  required>
            @error('name') <div class="sa-field-error">{{ $message }}</div> @enderror
          </div>
          <div class="sa-field sa-w-sm">
            <label>Statut</label>
            <select name="status">
              @foreach (\App\Models\Project::STATUSES as $status)
                <option value="{{ $status }}" @selected(old('status') === $status)>{{ \App\Models\Project::STATUS_LABELS[$status] }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Description</label>
            <textarea name="description" rows="2" >{{ old('description') }}</textarea>
          </div>
        </div>
        <div class="sa-form-foot">
          <button type="submit" class="sa-btn sa-btn-primary">Créer le projet</button>
        </div>
      </form>
    </div>
  </details>

  <form method="GET" class="sa-filters">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un projet...">
    <select name="status" onchange="this.form.submit()">
      <option value="">Tous les statuts</option>
      @foreach (\App\Models\Project::STATUSES as $status)
        <option value="{{ $status }}" @selected(request('status') === $status)>{{ \App\Models\Project::STATUS_LABELS[$status] }}</option>
      @endforeach
    </select>
    <input type="date" name="date_from" value="{{ request('date_from') }}" title="Créé depuis le">
    <input type="date" name="date_to" value="{{ request('date_to') }}" title="Créé jusqu'au">
    <button type="submit" class="sa-btn sa-btn-sm">Filtrer</button>
    @if (request()->anyFilled(['q','status','date_from','date_to']))
      <a href="{{ route('activites.admin.projects.index') }}" class="sa-btn sa-btn-sm">Réinitialiser</a>
    @endif
  </form>

  @if ($projects->isEmpty())
    <div class="sa-table-wrap">
      <div class="sa-empty">
        <div class="sa-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4zm0 0v10l9 4 9-4V7"/></svg></div>
        <div class="sa-empty-title">Aucun projet ne correspond à ces filtres.</div>
      </div>
    </div>
  @else
    <div class="sa-table-wrap">
      <table class="sa-table">
        <thead>
          <tr>
            <th>Projet</th>
            <th>Statut</th>
            <th>Description du projet</th>
            <th>Créé le</th>
            <th style="width:120px;"></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($projects as $project)
            <tr>
              <td class="sa-cell-title">{{ $project->name }}</td>
              <td><span class="sa-badge {{ $project->statusBadgeClass() }}">{{ $project->statusLabel() }}</span></td>
              <td class="sa-cell-muted">
                @if ($project->description)
                  {{ \Illuminate\Support\Str::limit($project->description, 90) }}
                @else
                  <em>Aucune description</em>
                @endif
              </td>
              <td class="sa-cell-muted">{{ $project->created_at->format('d/m/Y') }}</td>
              <td>
                <div class="sa-row-actions">
                  <a href="{{ route('activites.admin.projects.show', $project) }}" class="sa-icon-btn" title="Voir">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                  </a>
                  <a href="{{ route('activites.admin.projects.show', $project) }}#sa-edit-project" class="sa-icon-btn" title="Modifier">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </a>
                  <form method="POST" action="{{ route('activites.admin.projects.destroy', $project) }}" onsubmit="return confirm('Supprimer ce projet et toutes ses tâches ?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="sa-icon-btn sa-icon-danger" title="Supprimer">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div style="margin-top:16px;">{{ $projects->links() }}</div>
  @endif

</div>
@endsection