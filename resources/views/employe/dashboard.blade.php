@extends('layouts.app')

@section('title', 'Mon Tableau de Bord')
@section('page-title', 'Mon espace — Employé')

@push('styles')
<style>
    :root {
        --emp-teal: #1a9e7a;
        --emp-teal-dark: #0d6e5a;
        --emp-blue: #3b82f6;
        --emp-blue-light: #dbeafe;
        --emp-amber: #f59e0b;
        --emp-amber-light: #fef9c3;
        --emp-green: #10b981;
        --emp-green-light: #dcfce7;
        --emp-red: #ef4444;
        --emp-gray: #f3f4f6;
        --emp-radius: 10px;
    }

    .emp-greeting {
        background: linear-gradient(90deg, var(--emp-teal-dark), var(--emp-teal));
        border-radius: var(--emp-radius);
        padding: 16px 20px;
        margin-bottom: 20px;
    }
    .emp-greeting strong {
        font-size: 15px;
        font-weight: 600;
        color: #fff;
        display: block;
        margin-bottom: 4px;
    }
    .emp-greeting span {
        font-size: 12px;
        color: rgba(255,255,255,0.85);
    }

    .emp-kpi-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    .emp-kpi-label {
        font-size: 11px;
        color: var(--text-muted);
        margin-bottom: 6px;
    }
    .emp-kpi-num {
        font-size: 28px;
        font-weight: 700;
        color: var(--text);
    }
    .emp-kpi-sub {
        font-size: 11px;
        margin-top: 3px;
        font-weight: 600;
    }

    .emp-plan-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 11px 0;
        border-bottom: 1px solid var(--border);
    }
    .emp-plan-row:last-child { border-bottom: none; }
    .emp-plan-left {
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .emp-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-top: 3px;
        flex-shrink: 0;
    }
    .emp-dot-blue { background: var(--emp-blue); }
    .emp-dot-amber { background: var(--emp-amber); }
    .emp-dot-red { background: var(--emp-red); }
    .emp-plan-name { font-size: 13px; font-weight: 600; color: var(--text); }
    .emp-plan-sub { font-size: 11px; color: var(--text-muted); margin-top: 1px; }

    .emp-chip {
        font-size: 11px;
        font-weight: 500;
        padding: 4px 12px;
        border-radius: 6px;
        white-space: nowrap;
    }
    .emp-chip-matin { background: var(--emp-blue-light); color: #1e40af; }
    .emp-chip-aprem { background: #fef3c7; color: #92400e; }
    .emp-chip-wait { background: var(--emp-amber-light); color: #854d0e; }
    .emp-chip-repos { background: var(--emp-gray); color: #6b7280; }
    .emp-chip-ok { background: var(--emp-green-light); color: #166534; }
    .emp-chip-refuse { background: #fee2e2; color: #991b1b; }

    .emp-two-col {
        display: grid;
        grid-template-columns: minmax(0,1fr) minmax(0,1fr);
        gap: 16px;
        margin-bottom: 20px;
    }

    .emp-req-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
    }
    .emp-req-row:last-child { border-bottom: none; }
    .emp-req-name { font-size: 13px; color: var(--text); }
    .emp-req-date { font-size: 11px; color: var(--text-muted); margin-top: 1px; }

    .emp-abs-row { padding: 8px 0; border-bottom: 1px solid var(--border); }
    .emp-abs-row:last-child { border-bottom: none; }
    .emp-abs-top {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: var(--text-muted);
        margin-bottom: 5px;
    }
    .emp-abs-bar { height: 5px; border-radius: 3px; background: var(--emp-gray); }
    .emp-abs-fill { height: 100%; border-radius: 3px; }

    .emp-ev-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
    }
    .emp-ev-row:last-child { border-bottom: none; }
    .emp-ev-tag {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 99px;
        margin-right: 8px;
    }
    .ev-tag-event { background: var(--emp-blue-light); color: #1e40af; }
    .ev-tag-actu { background: var(--emp-green-light); color: #166534; }
    .emp-ev-name { font-size: 13px; font-weight: 500; color: var(--text); }
    .emp-ev-date { font-size: 12px; color: var(--text-muted); white-space: nowrap; }

    .emp-note {
        font-size: 11px;
        color: var(--text-muted);
        font-style: italic;
        padding-top: 10px;
        border-top: 1px dashed var(--border);
        margin-top: 8px;
    }

    .emp-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 24px;
        gap: 8px;
    }
    .emp-empty-icon {
        width: 34px;
        height: 34px;
        background: #22c55e;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 18px;
    }
    .emp-empty-txt { font-size: 13px; color: var(--text-muted); }

    @media (max-width: 640px) {
        .emp-kpi-row { grid-template-columns: repeat(2, 1fr); }
        .emp-two-col { grid-template-columns: 1fr; }
    }

    /* News Cards Styles */
    .news-flyer-card {
        display: block;
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        color: inherit;
    }
    .news-flyer-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .news-flyer-image {
        width: 100%;
        height: 180px;
        overflow: hidden;
        position: relative;
    }
    .news-flyer-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .news-flyer-card:hover .news-flyer-image img {
        transform: scale(1.05);
    }
    .news-flyer-placeholder {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }
    .news-flyer-content {
        padding: 20px;
    }
    .news-flyer-badges {
        margin-bottom: 12px;
    }
    .news-flyer-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0 0 12px 0;
        color: #1e293b;
        line-height: 1.4;
    }
    .news-flyer-date {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #64748b;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
<div class="emp-greeting">
    <strong>Bonjour {{ $employee->first_name }} 👋</strong>
    <span>{{ now()->isoFormat('dddd D MMMM YYYY') }} @if($employee->department) — {{ $employee->department }} @endif @if($employee->position) · {{ $employee->position }} @endif</span>
</div>

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