@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Notifications</h1>
        <p>Gestion des demandes et actualites</p>
    </div>
</div>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #fbbf24); padding: 20px; border-radius: 12px; color: white;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div>
                <div style="font-size: 0.85rem; opacity: 0.9;">Demandes en attente</div>
                <div style="font-size: 1.8rem; font-weight: 700;">{{ $pendingCount }}</div>
            </div>
        </div>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, #0ea5e9, #38bdf8); padding: 20px; border-radius: 12px; color: white;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </div>
            <div>
                <div style="font-size: 0.85rem; opacity: 0.9;">Actualites</div>
                <div style="font-size: 1.8rem; font-weight: 700;">{{ $newsCount }}</div>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Demandes de congés en attente -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid var(--border);">
            <h3 style="margin: 0; font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <span style="color: #f59e0b;">📅</span>
                Demandes de congés en attente
            </h3>
            <span style="background: #fef3c7; color: #f59e0b; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                {{ $pendingCount }}
            </span>
        </div>
        <div class="card-body" style="padding: 0;">
            @if($pendingAbsences->count() > 0)
                <div style="display: flex; flex-direction: column;">
                    @foreach($pendingAbsences as $absence)
                    <div style="display: flex; align-items: center; gap: 12px; padding: 14px 20px; border-bottom: 1px solid #f0f0f0; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #fbbf24); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.85rem; flex-shrink: 0;">
                            {{ strtoupper(substr($absence->employee->first_name ?? 'E', 0, 1)) }}{{ strtoupper(substr($absence->employee->last_name ?? '', 0, 1)) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--text);">
                                {{ $absence->employee->full_name ?? 'Employe' }}
                            </div>
                            <div style="font-size: 0.8rem; color: #666;">
                                {{ \Carbon\Carbon::parse($absence->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($absence->end_date)->format('d/m/Y') }}
                            </div>
                            <div style="font-size: 0.75rem; color: #999;">
                                Demandé le {{ $absence->created_at->format('d/m/Y') }}
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px; flex-shrink: 0;">
                            <form method="POST" action="{{ route('absences.approve', $absence) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm" style="background: #10b981; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 4px; font-size: 0.8rem;">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    Accepter
                                </button>
                            </form>
                            <form method="POST" action="{{ route('absences.reject', $absence) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm" style="background: #ef4444; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 4px; font-size: 0.8rem;">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                    Refuser
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div style="padding: 40px 20px; text-align: center; color: #999;">
                    <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" style="margin: 0 auto 12px; opacity: 0.5;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <div style="font-size: 0.9rem;">Aucune demande en attente</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Actualites recentes -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid var(--border);">
            <h3 style="margin: 0; font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <span style="color: #0ea5e9;">📰</span>
                Actualites recentes
            </h3>
            <span style="background: #e0f2fe; color: #0ea5e9; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                {{ $newsCount }}
            </span>
        </div>
        <div class="card-body" style="padding: 0;">
            @if($recentNews->count() > 0)
                <div style="display: flex; flex-direction: column;">
                    @foreach($recentNews as $news)
                    <a href="{{ route('news.show', $news) }}" style="display: flex; align-items: center; gap: 12px; padding: 14px 20px; border-bottom: 1px solid #f0f0f0; text-decoration: none; color: inherit; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                        @if($news->image)
                        <div style="width: 44px; height: 44px; border-radius: 8px; background: url('{{ asset($news->image) }}'); background-size: cover; background-position: center; flex-shrink: 0;"></div>
                        @else
                        <div style="width: 44px; height: 44px; border-radius: 8px; background: linear-gradient(135deg, #0ea5e9, #38bdf8); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                            📰
                        </div>
                        @endif
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $news->title }}
                            </div>
                            <div style="font-size: 0.8rem; color: #666; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ Str::limit($news->content, 60) }}
                            </div>
                            <div style="font-size: 0.75rem; color: #999;">
                                Publie le {{ $news->created_at->format('d/m/Y') }}
                            </div>
                        </div>
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: #999; flex-shrink: 0;">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                    @endforeach
                </div>
            @else
                <div style="padding: 40px 20px; text-align: center; color: #999;">
                    <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" style="margin: 0 auto 12px; opacity: 0.5;">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <div style="font-size: 0.9rem;">Aucune actualite</div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
}
.card-header {
    background: var(--surface-2);
}
.card-body {
    padding: 0;
}
@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection

