@extends('layouts.app')

@section('title', 'État Visuel des Absences')
@section('page-title', 'État Visuel des Absences')

@push('styles')
<style>
    .view-toggle {
        display: flex;
        gap: 4px;
        background: var(--bg-secondary);
        padding: 4px;
        border-radius: 8px;
    }
    .view-toggle button {
        padding: 8px 16px;
        border: none;
        background: transparent;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s;
        color: var(--text-muted);
    }
    .view-toggle button.active {
        background: white;
        color: var(--primary);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* ===== BADGES STATS CLIQUABLES ===== */
    .quick-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 24px;
        align-items: center;
    }

    .quick-stat {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 999px;
        border: 1.5px solid transparent;
        background: var(--surface, #fff);
        font-family: inherit;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all .18s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,.07);
        user-select: none;
        text-align: left;
        /* reset button styles */
        -webkit-appearance: none;
        appearance: none;
    }

    .quick-stat--info {
        cursor: default;
        pointer-events: none;
    }

    .quick-stat-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .quick-stat strong {
        font-weight: 700;
    }

    /* Couleurs par type */
    .quick-stat--approved  { border-color: #bbf7d0; background: #f0fdf4; }
    .quick-stat--approved  .quick-stat-dot { background: #22c55e; }
    .quick-stat--approved  strong { color: #16a34a; }

    .quick-stat--pending   { border-color: #fde68a; background: #fffbeb; }
    .quick-stat--pending   .quick-stat-dot { background: #f59e0b; }
    .quick-stat--pending   strong { color: #d97706; }

    .quick-stat--conflict  { border-color: #fecaca; background: #fef2f2; }
    .quick-stat--conflict  .quick-stat-dot { background: #ef4444; }
    .quick-stat--conflict  strong { color: #dc2626; }

    .quick-stat--replacement { border-color: #ddd6fe; background: #f5f3ff; }
    .quick-stat--replacement .quick-stat-dot { background: #8b5cf6; }
    .quick-stat--replacement strong { color: #7c3aed; }

    .quick-stat--days      { border-color: #bfdbfe; background: #eff6ff; }
    .quick-stat--days      .quick-stat-dot { background: #3b82f6; }
    .quick-stat--days      strong { color: #2563eb; }

    .quick-stat--reset     { border-color: #e5e7eb; background: #f9fafb; color: #6b7280; font-size:13px; }

    /* Hover */
    .quick-stat:not(.quick-stat--info):not(.quick-stat--reset):not(.active):hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,.1);
    }

    /* État actif */
    .quick-stat--approved.active  { background: #16a34a; border-color: #16a34a; box-shadow: 0 2px 8px rgba(22,163,74,.35); }
    .quick-stat--pending.active   { background: #d97706; border-color: #d97706; box-shadow: 0 2px 8px rgba(217,119,6,.35); }
    .quick-stat--conflict.active  { background: #dc2626; border-color: #dc2626; box-shadow: 0 2px 8px rgba(220,38,38,.35); }
    .quick-stat--replacement.active { background: #7c3aed; border-color: #7c3aed; box-shadow: 0 2px 8px rgba(124,58,237,.35); }

    .quick-stat.active .quick-stat-dot { background: rgba(255,255,255,.65) !important; }
    .quick-stat.active strong           { color: #fff !important; }
    .quick-stat.active span             { color: #fff !important; }

    /* Notification filtre */
    #filterNotice {
        display: none;
        margin-bottom: 12px;
        padding: 8px 14px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        font-size: 13px;
        color: #166534;
        align-items: center;
        gap: 8px;
    }

    /* ===== GRILLE CALENDRIER ===== */
    .monthly-calendar-container {
        overflow-x: auto;
        background: white;
        border-radius: 12px;
        border: 1px solid var(--border);
    }
    .monthly-calendar {
        width: 100%;
        min-width: 1200px;
        border-collapse: collapse;
    }
    .monthly-calendar th {
        background: var(--bg-secondary);
        padding: 12px 4px;
        text-align: center;
        font-weight: 600;
        font-size: 0.7rem;
        border: 1px solid var(--border);
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .monthly-calendar th.employee-col {
        position: sticky;
        left: 0;
        z-index: 20;
        min-width: 180px;
        text-align: left;
        background: var(--bg-secondary);
    }
    .monthly-calendar th.day-header {
        min-width: 32px;
        width: 32px;
    }
    .monthly-calendar th.today-header {
        background: #eff6ff;
        color: var(--primary);
    }
    .monthly-calendar td {
        border: 1px solid var(--border);
        padding: 2px;
        min-height: 40px;
        text-align: center;
        vertical-align: middle;
    }
    .monthly-calendar td.employee-cell {
        position: sticky;
        left: 0;
        z-index: 5;
        background: white;
        text-align: left;
        padding: 6px 10px;
    }
    .monthly-calendar td.employee-cell:hover { background: #f8f9fa; }
    .monthly-calendar td.today-cell   { background: #eff6ff; }
    .monthly-calendar td.weekend-cell { background: #fef9f0; }

    /* Lignes masquées par le filtre */
    .employee-row.row-hidden { display: none !important; }

    /* Absence Cell */
    .absence-dot {
        width: 100%;
        height: 100%;
        min-height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.6rem;
        font-weight: 500;
    }
    .absence-dot:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        z-index: 1;
    }
    .absence-dot.approved    { background: linear-gradient(135deg, #10b981, #059669); color: white; }
    .absence-dot.pending     { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
    .absence-dot.rejected    { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
    .absence-dot.empty       { background: transparent; cursor: default; }
    .absence-dot.empty:hover { transform: none; box-shadow: none; }

    /* Grisé quand filtre actif et cellule non concernée */
    td.cell-dimmed .absence-dot {
        opacity: .15 !important;
        pointer-events: none;
    }

    @keyframes calFlash {
        0%   { box-shadow: inset 0 0 0 2px transparent; }
        40%  { box-shadow: inset 0 0 0 2px rgba(0,0,0,.22); }
        100% { box-shadow: inset 0 0 0 2px transparent; }
    }
    td.cell-highlight .absence-dot { animation: calFlash .55s ease forwards; }

    /* Employee Info */
    .employee-mini { display: flex; align-items: center; gap: 8px; }
    .employee-mini-avatar {
        width: 28px; height: 28px; border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white; display: flex; align-items: center; justify-content: center;
        font-size: 0.65rem; font-weight: 700; flex-shrink: 0;
    }
    .employee-mini-info { min-width: 0; }
    .employee-mini-name { font-weight: 600; font-size: 0.8rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .employee-mini-dept { font-size: 0.65rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* List view */
    .absence-card { border-left: 4px solid; transition: all 0.2s; }
    .absence-card:hover { transform: translateX(4px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .absence-card.approved  { border-left-color: #10b981; }
    .absence-card.pending   { border-left-color: #f59e0b; }
    .absence-card.rejected  { border-left-color: #ef4444; }

    /* Modal */
    .modal-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex; align-items: center; justify-content: center;
        z-index: 1000; opacity: 0; visibility: hidden; transition: all 0.3s;
    }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    .modal-content {
        background: white; border-radius: 16px;
        width: 90%; max-width: 420px; max-height: 90vh; overflow-y: auto;
        transform: scale(0.9) translateY(20px); transition: all 0.3s;
    }
    .modal-overlay.active .modal-content { transform: scale(1) translateY(0); }
    .modal-header {
        padding: 14px 18px; border-bottom: 1px solid var(--border);
        display: flex; justify-content: space-between; align-items: center;
        background: var(--primary); border-radius: 16px 16px 0 0; color: white;
    }
    .modal-header h3 { margin: 0; font-size: 0.95rem; font-weight: 600; }
    .modal-close {
        background: rgba(255,255,255,0.2); border: none;
        width: 26px; height: 26px; border-radius: 50%; color: white;
        cursor: pointer; font-size: 0.9rem;
        display: flex; align-items: center; justify-content: center; transition: all 0.2s;
    }
    .modal-close:hover { background: rgba(255,255,255,0.3); }
    .modal-body { padding: 18px; }
    .modal-employee-header {
        display: flex; align-items: center; gap: 10px;
        margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid var(--border);
    }
    .modal-employee-avatar {
        width: 42px; height: 42px; border-radius: 50%;
        background: var(--primary); color: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem; font-weight: 700;
    }
    .modal-employee-info h4 { margin: 0 0 2px 0; font-size: 0.95rem; }
    .modal-employee-info p  { margin: 0; color: var(--text-muted); font-size: 0.75rem; }
    .modal-detail-row {
        display: flex; justify-content: space-between; align-items: flex-start;
        padding: 6px 0; border-bottom: 1px solid var(--border);
    }
    .modal-detail-row:last-child { border-bottom: none; }
    .modal-detail-label { font-weight: 500; color: var(--text-muted); font-size: 0.75rem; }
    .modal-detail-value { font-weight: 600; font-size: 0.8rem; text-align: right; }
    .modal-status-badge { padding: 2px 8px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
    .modal-status-badge.approved  { background: #d1fae5; color: #065f46; }
    .modal-status-badge.pending   { background: #fef3c7; color: #92400e; }
    .modal-status-badge.rejected  { background: #fee2e2; color: #991b1b; }
    .modal-type-badge { padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 500; background: #e0e7ff; color: #4338ca; }
</style>
@endpush

@section('content')

{{-- Conflicts Modal --}}
<div class="modal-overlay" id="conflictsModal" onclick="closeConflictsModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header" style="background:linear-gradient(135deg,#ef4444,#dc2626);color:white">
            <h3>⚠️ Conflits détectés</h3>
            <button class="modal-close" onclick="closeConflictsModal()">×</button>
        </div>
        <div class="modal-body">
            <ul style="list-style:none;padding:0;margin:0">
                <li style="text-align:center;color:var(--text-muted);padding:32px">Chargement...</li>
            </ul>
        </div>
    </div>
</div>

<div class="page-header">
    <div class="page-header-left">
        <h1>État Visuel des Absences</h1>
<p>Vue mensuelle — {{ $firstDay->locale('fr')->translatedFormat('F Y') }}</p>    </div>
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <div class="view-toggle">
            <button class="{{ (!isset($viewMode) || $viewMode == 'calendar') ? 'active' : '' }}" onclick="switchView('calendar')">Calendrier</button>
            <button class="{{ $viewMode == 'list' ? 'active' : '' }}" onclick="switchView('list')">Liste</button>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <a href="{{ $prevMonthUrl }}" class="btn btn-ghost btn-sm">←</a>
            <a href="{{ $todayUrl }}" class="btn btn-secondary btn-sm">Aujourd'hui</a>
            <a href="{{ $nextMonthUrl }}" class="btn btn-ghost btn-sm">→</a>
        </div>
    </div>
</div>

{{-- ===== BADGES STATS CLIQUABLES ===== --}}
@php
    $conflictEmpIds = collect($conflicts)->pluck('employee_id')->unique()->toArray();
    $replacementEmpIds = $absences->whereNotNull('replacement_id')->pluck('employee_id')->unique()->toArray();
@endphp

<div class="quick-stats" id="quickStats">

    <button type="button"
        class="quick-stat quick-stat--approved"
        data-filter="approved"
        onclick="filterCalendar('approved')"
        title="Filtrer les absences approuvées">
        <div class="quick-stat-dot"></div>
        <span><strong>{{ $stats['approved_count'] }}</strong> Approuvée{{ $stats['approved_count'] > 1 ? 's' : '' }}</span>
    </button>

    <button type="button"
        class="quick-stat quick-stat--pending"
        data-filter="pending"
        onclick="filterCalendar('pending')"
        title="Filtrer les absences en attente">
        <div class="quick-stat-dot"></div>
        <span><strong>{{ $stats['pending_count'] }}</strong> En attente</span>
    </button>

    <button type="button"
        class="quick-stat quick-stat--conflict"
        data-filter="conflict"
        onclick="filterCalendar('conflict')"
        title="Filtrer les conflits">
        <div class="quick-stat-dot"></div>
        <span><strong>{{ $stats['conflicts_count'] }}</strong> Conflit{{ $stats['conflicts_count'] > 1 ? 's' : '' }}</span>
    </button>

    <button type="button"
        class="quick-stat quick-stat--replacement"
        data-filter="replacement"
        onclick="filterCalendar('replacement')"
        title="Filtrer les remplacements">
        <div class="quick-stat-dot"></div>
        <span><strong>{{ $stats['replacements_count'] }}</strong> Remplacement{{ $stats['replacements_count'] > 1 ? 's' : '' }}</span>
    </button>

    <div class="quick-stat quick-stat--days quick-stat--info">
        <div class="quick-stat-dot"></div>
        <span><strong>{{ $stats['total_days'] }}</strong> Jours total</span>
    </div>

    <button type="button"
        class="quick-stat quick-stat--reset"
        id="badgeResetBtn"
        onclick="filterCalendar(null)"
        style="display:none"
        title="Tout afficher">
        <span>✕ Tout afficher</span>
    </button>
</div>

{{-- Bandeau filtre actif --}}
<div id="filterNotice" style="display:none;margin-bottom:12px;padding:8px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:13px;color:#166534;align-items:center;gap:8px;">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
    <span id="filterNoticeText">Filtre actif</span>
    <button onclick="filterCalendar(null)" style="margin-left:auto;background:none;border:none;cursor:pointer;font-size:12px;color:#166534;font-weight:bold;text-decoration:underline;">Réinitialiser</button>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:24px">
    <div style="padding:16px">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
            <div style="font-weight:600;font-size:0.875rem">Filtrer par:</div>

            <select class="filter-select" style="padding:8px 12px" onchange="applyFilter('department', this.value)">
                <option value="">Tous les services</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>

            <div class="search-bar" style="position:relative">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:var(--text-muted)">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" placeholder="Rechercher employé..." value="{{ $search ?? '' }}"
                    style="padding:10px 12px 10px 40px;border:1px solid var(--border);border-radius:8px;min-width:220px">
            </div>

           

            @if(request()->anyFilled(['department', 'employee_id', 'status']))
                <a href="{{ $resetUrl }}" class="btn btn-ghost btn-sm">✕ Réinitialiser</a>
            @endif
        </div>
    </div>
</div>

{{-- ===== VUE CALENDRIER ===== --}}
@if(!isset($viewMode) || $viewMode == 'calendar')

{{-- Données conflict/replacement pour le JS --}}
<script>
    var CONFLICT_EMP_IDS    = @json($conflictEmpIds);
    var REPLACEMENT_EMP_IDS = @json($replacementEmpIds);
</script>

<div class="card">
    <div class="monthly-calendar-container">
        <table class="monthly-calendar">
            <thead>
                <tr>
                    <th class="employee-col">Collaborateur</th>
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $dateH = \Carbon\Carbon::createFromDate($year, $month, $day);
                            $isTodayH   = $dateH->isSameDay($today);
                            $isWeekendH = $dateH->dayOfWeek == 0 || $dateH->dayOfWeek == 6;
                        @endphp
                        <th class="day-header {{ $isTodayH ? 'today-header' : '' }}">
                            {{ $day }}
                            @if($isTodayH)
                                <div style="font-size:0.5rem;color:var(--primary)">AUJ</div>
                            @endif
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
            @forelse($filteredEmployees as $emp)
                @php $empAbsences = $absenceMap[$emp->id] ?? []; @endphp
                <tr class="employee-row" data-employee-id="{{ $emp->id }}">
                    <td class="employee-cell">
                        <div class="employee-mini">
                            <div class="employee-mini-avatar">
                                {{ strtoupper(substr($emp->first_name,0,1)) }}{{ strtoupper(substr($emp->last_name,0,1)) }}
                            </div>
                            <div class="employee-mini-info">
                                <div class="employee-mini-name">{{ $emp->full_name }}</div>
                                <div class="employee-mini-dept">{{ $emp->department ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>

                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $date      = \Carbon\Carbon::createFromDate($year, $month, $day);
                            $isToday   = $date->isSameDay($today);
                            $isWeekend = $date->dayOfWeek == 0 || $date->dayOfWeek == 6;
                            $absence   = $empAbsences[$day] ?? null;

                            $tdData = '';
                            if ($absence) {
                                $hasReplacement = $absence->replacement_id ? '1' : '0';
                                $isConflict     = in_array($emp->id, $conflictEmpIds) ? '1' : '0';
                                $tdData = 'data-status="'.$absence->status.'" data-has-replacement="'.$hasReplacement.'" data-is-conflict="'.$isConflict.'" data-absence-id="'.$absence->id.'"';
                            }
                        @endphp

                        <td
                            class="{{ $isToday ? 'today-cell' : '' }} {{ $isWeekend ? 'weekend-cell' : '' }} {{ $absence ? 'absence-td' : '' }}"
                            {!! $absence ? $tdData : '' !!}
                        >
                            @if($absence)
                                <div class="absence-dot {{ $absence->status }}"
                                     onclick="showAbsenceModal(
                                         {{ $absence->id }},
                                         '{{ addslashes($emp->full_name) }}',
                                         '{{ addslashes($emp->department ?? '') }}',
                                         '{{ addslashes($emp->position ?? '') }}',
                                         '{{ $absence->start_date->format('d/m/Y') }}',
                                         '{{ $absence->end_date->format('d/m/Y') }}',
                                         '{{ addslashes(\App\Models\Absence::TYPES[$absence->type] ?? $absence->type) }}',
                                         '{{ $absence->status }}',
                                         {{ $absence->days }},
                                         '{{ addslashes($absence->reason ?? '') }}'
                                     )"
                                     title="{{ \App\Models\Absence::TYPES[$absence->type] ?? $absence->type }}">
                                    <span style="font-size:0.7rem">●</span>
                                </div>
                            @else
                                <div class="absence-dot empty"></div>
                            @endif
                        </td>
                    @endfor
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $daysInMonth + 1 }}" style="text-align:center;padding:48px;color:var(--text-muted)">
                        <div style="font-size:2.5rem;margin-bottom:12px">👥</div>
                        <div>Aucun employé trouvé</div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ===== VUE LISTE ===== --}}
@if($viewMode == 'list')
<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Type</th>
                    <th>Période</th>
                    <th>Jours</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absences->sortByDesc('created_at') as $absence)
                <tr class="absence-card {{ $absence->status }}"
                    onclick="showAbsenceModal({{ $absence->id }}, '{{ addslashes($absence->employee->full_name) }}', '{{ addslashes($absence->employee->department ?? '') }}', '{{ addslashes($absence->employee->position ?? '') }}', '{{ $absence->start_date->format('d/m/Y') }}', '{{ $absence->end_date->format('d/m/Y') }}', '{{ addslashes(\App\Models\Absence::TYPES[$absence->type] ?? $absence->type) }}', '{{ $absence->status }}', {{ $absence->days }}, '{{ addslashes($absence->reason ?? '') }}')"
                    style="cursor:pointer">
                    <td>
                        <div class="employee-mini">
                            <div class="employee-mini-avatar" style="width:36px;height:36px;font-size:0.75rem">
                                {{ strtoupper(substr($absence->employee->first_name,0,1)) }}{{ strtoupper(substr($absence->employee->last_name,0,1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600">{{ $absence->employee->full_name }}</div>
                                <div style="font-size:0.75rem;color:var(--text-muted)">{{ $absence->employee->department }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span style="font-weight:500">{{ \App\Models\Absence::TYPES[$absence->type] ?? $absence->type }}</span></td>
                    <td>
                        <div style="font-size:0.875rem">{{ $absence->start_date->format('d/m/Y') }} → {{ $absence->end_date->format('d/m/Y') }}</div>
                        <div style="font-size:0.75rem;color:var(--text-muted)">{{ $absence->start_date->diffInDays($absence->end_date) + 1 }} jour(s)</div>
                    </td>
                    <td><span class="badge badge-primary">{{ $absence->days }} j</span></td>
                    <td>
                        @if($absence->status == 'pending')
                            <span class="badge badge-warning">En attente</span>
                        @elseif($absence->status == 'approved')
                            <span class="badge badge-success">Approuvé</span>
                        @else
                            <span class="badge badge-danger">Rejeté</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="{{ route('absences.show', $absence) }}" class="btn btn-ghost btn-sm btn-icon" title="Voir" onclick="event.stopPropagation()">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            @if($absence->status == 'pending')
                                <form action="{{ route('absences.approve', $absence) }}" method="POST" onclick="event.stopPropagation()">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Approuver">✓</button>
                                </form>
                                <form action="{{ route('absences.reject', $absence) }}" method="POST" onclick="event.stopPropagation()">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" title="Rejeter">✗</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:48px;color:var(--text-muted)">
                        <div style="font-size:2.5rem;margin-bottom:12px">📅</div>
                        <div>Aucune absence trouvée</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif


    

{{-- Modal détail absence --}}
<div class="modal-overlay" id="absenceModal" onclick="closeModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Détails de l'absence</h3>
            <button class="modal-close" onclick="closeAbsenceModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="modal-employee-header">
                <div class="modal-employee-avatar" id="modalAvatar">JD</div>
                <div class="modal-employee-info">
                    <h4 id="modalEmployeeName">John Doe</h4>
                    <p id="modalEmployeeDept">Département</p>
                </div>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Début</span>
                <span class="modal-detail-value" id="modalStartDate">—</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Fin</span>
                <span class="modal-detail-value" id="modalEndDate">—</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Motif</span>
                <span class="modal-detail-value"><span class="modal-type-badge" id="modalType">—</span></span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Jours</span>
                <span class="modal-detail-value" id="modalDays">—</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Statut</span>
                <span class="modal-detail-value"><span class="modal-status-badge" id="modalStatus">—</span></span>
            </div>
            <div class="modal-detail-row" id="modalReasonRow" style="display:none">
                <span class="modal-detail-label">Détails</span>
                <span class="modal-detail-value" id="modalReason" style="max-width:180px;text-align:right">—</span>
            </div>
            <div style="margin-top:16px;display:flex;gap:10px;justify-content:flex-end">
                <button class="btn btn-ghost btn-sm" onclick="closeAbsenceModal()">Fermer</button>
                <a href="#" id="modalDetailLink" class="btn btn-primary btn-sm">Voir plus</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// =========================================================================
// VUE / FILTRES URL
// =========================================================================
function switchView(view) {
    var url = new URL(window.location.href);
    url.searchParams.set('view', view);
    window.location.href = url.toString();
}

function applyFilter(key, value) {
    var url = new URL(window.location.href);
    if (value) { url.searchParams.set(key, value); }
    else        { url.searchParams.delete(key); }
    window.location.href = url.toString();
}

// =========================================================================
// FILTRAGE DES BADGES — tout en JS, sans rechargement
// =========================================================================
var currentFilter = null;

var FILTER_LABELS = {
    approved:    'Affichage : absences approuvées uniquement',
    pending:     'Affichage : absences en attente uniquement',
    conflict:    'Affichage : employés avec conflits uniquement',
    replacement: 'Affichage : absences avec remplacement uniquement',
};

window.filterCalendar = function(filter) {
    // Toggle : recliquer désactive
    if (currentFilter === filter) { filter = null; }
    currentFilter = filter;

    // Mise à jour visuels badges
    document.querySelectorAll('.quick-stat[data-filter]').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.filter === filter);
    });

    var resetBtn = document.getElementById('badgeResetBtn');
    if (resetBtn) { resetBtn.style.display = filter ? '' : 'none'; }

    // Bandeau notification
    var notice     = document.getElementById('filterNotice');
    var noticeText = document.getElementById('filterNoticeText');
    if (notice) {
        if (filter) {
            notice.style.display = 'flex';
            if (noticeText) { noticeText.textContent = FILTER_LABELS[filter] || 'Filtre actif'; }
        } else {
            notice.style.display = 'none';
        }
    }

    // Application du filtre sur la grille
    if (!filter)                  { showAll(); }
    else if (filter === 'conflict')     { filterByData('conflict'); }
    else if (filter === 'replacement')  { filterByData('replacement'); }
    else                                { filterByStatus(filter); }
};

// Remet tout à l'état initial
function showAll() {
    document.querySelectorAll('.employee-row').forEach(function(row) {
        row.classList.remove('row-hidden');
    });
    document.querySelectorAll('.absence-td').forEach(function(td) {
        td.classList.remove('cell-dimmed', 'cell-highlight');
    });
}

// Filtre par statut : approved | pending
function filterByStatus(status) {
    document.querySelectorAll('.employee-row').forEach(function(row) {
        var allTds    = row.querySelectorAll('.absence-td');
        var matchTds  = row.querySelectorAll('.absence-td[data-status="' + status + '"]');

        if (allTds.length === 0) {
            // Ligne sans aucune absence → masquer
            row.classList.add('row-hidden');
            return;
        }

        if (matchTds.length === 0) {
            row.classList.add('row-hidden');
            allTds.forEach(function(td) { td.classList.remove('cell-dimmed', 'cell-highlight'); });
            return;
        }

        row.classList.remove('row-hidden');
        allTds.forEach(function(td) {
            if (td.dataset.status === status) {
                td.classList.remove('cell-dimmed');
                td.classList.add('cell-highlight');
            } else {
                td.classList.add('cell-dimmed');
                td.classList.remove('cell-highlight');
            }
        });
    });
}

// Filtre générique par data-attribute (conflict | replacement)
function filterByData(type) {
    var attr = type === 'conflict' ? 'data-is-conflict' : 'data-has-replacement';

    document.querySelectorAll('.employee-row').forEach(function(row) {
        var allTds   = row.querySelectorAll('.absence-td');
        var matchTds = row.querySelectorAll('.absence-td[' + attr + '="1"]');

        if (matchTds.length === 0) {
            row.classList.add('row-hidden');
            allTds.forEach(function(td) { td.classList.remove('cell-dimmed', 'cell-highlight'); });
            return;
        }

        row.classList.remove('row-hidden');
        allTds.forEach(function(td) {
            if (td.getAttribute(attr) === '1') {
                td.classList.remove('cell-dimmed');
                td.classList.add('cell-highlight');
            } else {
                td.classList.add('cell-dimmed');
                td.classList.remove('cell-highlight');
            }
        });
    });
}

// =========================================================================
// MODAL ABSENCE DETAIL
// =========================================================================
function showAbsenceModal(id, name, dept, position, startDate, endDate, type, status, days, reason) {
    var initials = name.split(' ').map(function(n){ return n[0] || ''; }).join('').toUpperCase().substring(0, 2);
    document.getElementById('modalAvatar').textContent = initials;
    document.getElementById('modalEmployeeName').textContent = name;
    document.getElementById('modalEmployeeDept').textContent = (position ? position + ' — ' : '') + dept;
    document.getElementById('modalStartDate').textContent = startDate;
    document.getElementById('modalEndDate').textContent   = endDate;
    document.getElementById('modalType').textContent      = type;
    document.getElementById('modalDays').textContent      = days + ' jour' + (days > 1 ? 's' : '');

    var statusBadge = document.getElementById('modalStatus');
    var statusLabels = { approved: 'Approuvé', pending: 'En attente', rejected: 'Rejeté' };
    statusBadge.textContent  = statusLabels[status] || status;
    statusBadge.className    = 'modal-status-badge ' + status;

    var reasonRow = document.getElementById('modalReasonRow');
    if (reason && reason.trim()) {
        document.getElementById('modalReason').textContent = reason;
        reasonRow.style.display = 'flex';
    } else {
        reasonRow.style.display = 'none';
    }

    document.getElementById('modalDetailLink').href = '/absences/' + id;
    document.getElementById('absenceModal').classList.add('active');
}

function closeAbsenceModal() {
    document.getElementById('absenceModal').classList.remove('active');
}

function closeModal(e) {
    if (e.target === document.getElementById('absenceModal')) {
        closeAbsenceModal();
    }
}

// =========================================================================
// MODAL CONFLITS
// =========================================================================
function loadConflictsModal() {
    var baseUrl = '{{ route("absences.conflicts.json") }}';
    var params  = new URLSearchParams(window.location.search);
    fetch(baseUrl + '?' + params.toString())
        .then(function(r){ return r.json(); })
        .then(function(conflicts) {
            var modal = document.getElementById('conflictsModal');
            var body  = modal.querySelector('.modal-body ul');
            body.innerHTML = '';
            if (conflicts.length === 0) {
                body.innerHTML = '<li style="text-align:center;color:var(--text-muted);padding:32px">Aucun conflit détecté</li>';
            } else {
                conflicts.forEach(function(conflict) {
                    var li = document.createElement('li');
                    li.style.cssText = 'padding:12px 0;border-bottom:1px solid #fee2e2;';
                    li.innerHTML =
                        '<div style="font-weight:600;margin-bottom:4px">' + conflict.employee + '</div>' +
                        '<div style="display:flex;gap:8px;font-size:0.85rem">' +
                            '<span style="background:#fef2f2;padding:4px 8px;border-radius:4px;color:#dc2626">' + conflict.absence1 + '</span>' +
                            '<span>vs</span>' +
                            '<span style="background:#fef2f2;padding:4px 8px;border-radius:4px;color:#dc2626">' + conflict.absence2 + '</span>' +
                        '</div>' +
                        '<div style="font-size:0.8rem;color:#991b1b;margin-top:4px">' + conflict.start + ' → ' + conflict.end + '</div>';
                    body.appendChild(li);
                });
            }
        })
        .catch(function(err){ console.error('Conflicts load error', err); });
}

function closeConflictsModal() {
    document.getElementById('conflictsModal').classList.remove('active');
}

// =========================================================================
// INIT
// =========================================================================
document.addEventListener('DOMContentLoaded', function() {
    // Echap ferme les modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAbsenceModal();
            closeConflictsModal();
        }
    });
});
</script>
@endpush
@endsection