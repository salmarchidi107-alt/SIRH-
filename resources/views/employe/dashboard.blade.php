@extends('layouts.app')

@section('title', 'Mon Tableau de Bord')
@section('page-title', 'Mon espace — Employé')

@section('content')
<div class="emp-greeting">
    <strong>Bonjour {{ $employee->first_name }} 👋</strong>
    <span>{{ now()->isoFormat('dddd D MMMM YYYY') }} @if($employee->department) — {{ $employee->department }} @endif @if($employee->position) · {{ $employee->position }} @endif</span>
</div>

@if($myTasks->isNotEmpty())
<div class="emp-alert-tasks {{ $myTasksLate > 0 ? 'is-late' : 'is-info' }}">
    <div class="emp-alert-head">
        <span class="emp-alert-title">
            @if($myTasksLate > 0)
                ⚠️ {{ $myTasksLate }} tâche(s) en retard sur {{ $myTasks->count() }} en attente
            @else
                📋 {{ $myTasks->count() }} tâche(s) à réaliser
            @endif
        </span>
        <a href="{{ route('activites.my-tasks.index') }}" class="emp-alert-link">Voir mes tâches →</a>
    </div>
    @foreach($myTasks->take(3) as $task)
        <div class="emp-alert-row">
            <div>
                <div class="emp-alert-task-name">{{ $task->title }}</div>
                <div class="emp-alert-task-meta">
                    {{ $task->project->name }}
                    @if($task->due_date)
                        · Échéance {{ $task->due_date->format('d/m/Y') }}
                    @endif
                </div>
            </div>
            @if($task->isLate())
                <span class="emp-alert-late-badge">En retard</span>
            @else
                <span class="emp-alert-task-meta">{{ \App\Models\Task::PRIORITY_LABELS[$task->priority] }}</span>
            @endif
        </div>
    @endforeach
</div>
@endif

<div class="emp-kpi-row">
    <div class="card">
        <div class="card-body">
            <div class="emp-kpi-label">Congés restants</div>
            <div class="emp-kpi-num">{{ $congesRestants }}</div>
            <div class="emp-kpi-sub" style="color: var(--emp-green);">jours</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="emp-kpi-label">Demandes en attente</div>
            <div class="emp-kpi-num">{{ $demandesEnAttente }}</div>
            <div class="emp-kpi-sub" style="color: var(--emp-amber);">En cours</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="emp-kpi-label">Heures ce mois</div>
            <div class="emp-kpi-num">{{ $heuresMois }}h</div>
            <div class="emp-kpi-sub" style="color: var(--text-muted);">/ {{ $heuresPrevues }}h</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    @if($demandesEnAttente === 0)
        <div class="emp-empty">
            <div class="emp-empty-icon">✓</div>
            <span class="emp-empty-txt">Aucune demande</span>
        </div>
    @else
        <div class="card-header">
            <div class="card-title">⏳ Demandes</div>
        </div>
        <div class="card-body">
            @foreach($absences->take(3) as $abs)
            <div class="emp-req-row">
                <div>
                    <div class="emp-req-name">{{ \App\Models\Absence::TYPES[$abs->type] ?? $abs->type }}</div>
                    <div class="emp-req-date">{{ $abs->start_date->format('d M Y') }}</div>
                </div>
                @if($abs->status === 'approved')
                    <span class="emp-chip emp-chip-ok">OK</span>
                @elseif($abs->status === 'rejected')
                    <span class="emp-chip emp-chip-refuse">KO</span>
                @else
                    <span class="emp-chip emp-chip-wait">Attente</span>
                @endif
            </div>
            @endforeach
            <div style="margin-top:12px">
                <a href="{{ route('absences.create') }}?employee_id={{ $employee->id }}" class="btn btn-outline w-full">+ Nouvelle</a>
            </div>
        </div>
    @endif
</div>

    <div class="card mb-4">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <div class="card-title">📅 Planning semaine</div>
            <a href="{{ route('planning.weekly') }}" class="btn btn-ghost btn-sm" style="font-size:0.8rem">Planning complet →</a>
        </div>
        <div class="card-body">
            @forelse($planningSemaine as $jour)
            <div class="emp-plan-row">
                <div class="emp-plan-left">
                    <div class="emp-dot {{ $jour->absence ? 'emp-dot-red' : ($jour->planning ? 'emp-dot-blue' : 'emp-dot-amber') }}"></div>
                    <div>
                        <div class="emp-plan-name">{{ $jour->date->isoFormat('dddd D') }}</div>
                        <div class="emp-plan-sub">{{ $jour->absence ? \App\Models\Absence::TYPES[$jour->absence->type] ?? 'Absence' : ($jour->planning ? $jour->heure_debut.'–'.$jour->heure_fin : 'Repos') }}</div>
                    </div>
                </div>
                <span class="emp-chip {{ $jour->absence ? 'emp-chip-refuse' : ($jour->planning ? 'emp-chip-matin' : 'emp-chip-repos') }}">{{ $jour->absence ? 'Absence' : $jour->periode }}</span>
            </div>
            @empty
            <div class="emp-empty">
                <div class="emp-empty-icon">📅</div>
                <span class="emp-empty-txt">Aucun planning cette semaine</span>
            </div>
            @endforelse
        </div>
    </div>

<div class="emp-two-col">
    <div class="card">
        <div class="card-header">
            <div class="card-title">📊 Absences</div>
        </div>
        <div class="card-body">
            <div class="emp-abs-row">
                <div class="emp-abs-top">
                    <span>Congés</span>
                    <span>{{ $absencesData['conges_utilises'] }}/{{ $absencesData['conges_total'] }}</span>
                </div>
                <div class="emp-abs-bar">
                    <div class="emp-abs-fill" style="width:{{ $absencesData['conges_pct'] }}%;background:var(--emp-green);"></div>
                </div>
            </div>
            <div class="emp-abs-row">
                <div class="emp-abs-top">
                    <span>Maladie</span>
                    <span>{{ $absencesData['maladie_utilises'] }}/{{ $absencesData['maladie_total'] }}</span>
                </div>
                <div class="emp-abs-bar">
                    <div class="emp-abs-fill" style="width:{{ $absencesData['maladie_pct'] }}%;background:var(--emp-amber);"></div>
                </div>
            </div>
            <div class="emp-abs-row">
                <div class="emp-abs-top">
                    <span>RTT</span>
                    <span>{{ $absencesData['rtt_utilises'] }}/{{ $absencesData['rtt_total'] }}</span>
                </div>
                <div class="emp-abs-bar">
                    <div class="emp-abs-fill" style="width:{{ $absencesData['rtt_pct'] }}%;background:var(--emp-blue);"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">🔔 Actualités</div>
        </div>
        <div class="card-body">
            @forelse($evenements as $ev)
            <div class="emp-ev-row">
                <div class="emp-ev-tag ev-tag-event">Événement</div>
                <span class="emp-ev-name">{{ $ev->title }}</span>
                <span class="emp-ev-date">{{ $ev->event_date->format('d M') }}</span>
            </div>
            @empty
            <div class="emp-empty">
                <span class="emp-empty-txt">Aucune actu</span>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- News Flyers Section -->
@if($upcomingNews->isNotEmpty())
<div class="card mt-6" style="border: none; box-shadow: none; background: transparent; padding: 0;">
    <div class="card-header" style="background: transparent; padding: 0 0 16px 0;">
        <div class="card-title" style="font-size: 1.25rem; font-weight: 700;">📰 Événements à venir</div>
        <a href="{{ route('news.index') }}" class="btn btn-ghost btn-sm">Voir tout →</a>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        @foreach($upcomingNews->take(4) as $news)
        <a href="{{ route('news.show', $news) }}" class="news-flyer-card">
            @if($news->image)
            <div class="news-flyer-image">
                <img src="{{ asset($news->image) }}" alt="{{ $news->title }}">
            </div>
            @else
            <div class="news-flyer-image news-flyer-placeholder">
                <div style="font-size: 3rem;">📰</div>
            </div>
            @endif
            <div class="news-flyer-content">
                <div class="news-flyer-badges">
                    <span class="badge bg-primary">
                        {{ \App\Models\News::TYPES[$news->type] ?? $news->type }}
                    </span>
                </div>
                <h3 class="news-flyer-title">{{ $news->title }}</h3>
                <div class="news-flyer-date">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    {{ $news->event_date->format('d F Y') }}
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

@endsection
