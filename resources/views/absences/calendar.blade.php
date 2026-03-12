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
    
    /* Quick Stats */
    .quick-stats {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    .quick-stat {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: var(--surface);
        border-radius: 8px;
        border: 1px solid var(--border);
    }
    .quick-stat-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }
    
    /* Monthly Calendar Grid - Employee Rows × Date Columns */
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
    .monthly-calendar td.employee-cell:hover {
        background: #f8f9fa;
    }
    .monthly-calendar td.today-cell {
        background: #eff6ff;
    }
    .monthly-calendar td.weekend-cell {
        background: #fef9f0;
    }
    
    /* Absence Cell Styling */
    .absence-cell {
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
    .absence-cell:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        z-index: 1;
    }
    .absence-cell.approved {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }
    .absence-cell.pending {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }
    .absence-cell.rejected {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }
    .absence-cell.empty {
        background: transparent;
        cursor: default;
    }
    .absence-cell.empty:hover {
        transform: none;
        box-shadow: none;
    }
    
    /* Employee Info in Cell */
    .employee-mini {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .employee-mini-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .employee-mini-info {
        min-width: 0;
    }
    .employee-mini-name {
        font-weight: 600;
        font-size: 0.8rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .employee-mini-dept {
        font-size: 0.65rem;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Absence Card for List View */
    .absence-card {
        border-left: 4px solid;
        transition: all 0.2s;
    }
    .absence-card:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .absence-card.approved { border-left-color: #10b981; }
    .absence-card.pending { border-left-color: #f59e0b; }
    .absence-card.rejected { border-left-color: #ef4444; }
    
    /* Modal Styles - Project Color Theme */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }
    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .modal-content {
        background: white;
        border-radius: 16px;
        width: 90%;
        max-width: 420px;
        max-height: 90vh;
        overflow-y: auto;
        transform: scale(0.9) translateY(20px);
        transition: all 0.3s;
    }
    .modal-overlay.active .modal-content {
        transform: scale(1) translateY(0);
    }
    .modal-header {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--primary);
        border-radius: 16px 16px 0 0;
        color: white;
    }
    .modal-header h3 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
    }
    .modal-close {
        background: rgba(255,255,255,0.2);
        border: none;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        color: white;
        cursor: pointer;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .modal-close:hover {
        background: rgba(255,255,255,0.3);
    }
    .modal-body {
        padding: 18px;
    }
    .modal-employee-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border);
    }
    .modal-employee-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 700;
    }
    .modal-employee-info h4 {
        margin: 0 0 2px 0;
        font-size: 0.95rem;
    }
    .modal-employee-info p {
        margin: 0;
        color: var(--text-muted);
        font-size: 0.75rem;
    }
    .modal-detail-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 6px 0;
        border-bottom: 1px solid var(--border);
    }
    .modal-detail-row:last-child {
        border-bottom: none;
    }
    .modal-detail-label {
        font-weight: 500;
        color: var(--text-muted);
        font-size: 0.75rem;
    }
    .modal-detail-value {
        font-weight: 600;
        font-size: 0.8rem;
        text-align: right;
    }
    .modal-status-badge {
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .modal-status-badge.approved {
        background: #d1fae5;
        color: #065f46;
    }
    .modal-status-badge.pending {
        background: #fef3c7;
        color: #92400e;
    }
    .modal-status-badge.rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    .modal-type-badge {
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 500;
        background: #e0e7ff;
        color: #4338ca;
    }
</style>
@endpush

@section('content')
@php
    $firstDay = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
    $daysInMonth = $firstDay->daysInMonth;
    $today = \Carbon\Carbon::today();
    
    // Get employees - filter by department if selected
    $filteredEmployees = $employees;
    if (request('department')) {
        $filteredEmployees = $employees->where('department', request('department'));
    }
    if (request('employee_id')) {
        $filteredEmployees = $employees->where('id', request('employee_id'));
    }
    
    // Build absence lookup: $absenceMap[employee_id][day] = absence object
    $absenceMap = [];
    foreach ($absences as $absence) {
        $empId = $absence->employee_id;
        if (!isset($absenceMap[$empId])) {
            $absenceMap[$empId] = [];
        }
        $start = \Carbon\Carbon::parse($absence->start_date);
        $end = \Carbon\Carbon::parse($absence->end_date);
        for ($d = $start; $d->lte($end); $d->addDay()) {
            if ($d->month == $month && $d->year == $year) {
                $absenceMap[$empId][$d->day] = $absence;
            }
        }
    }
@endphp

<div class="page-header">
    <div class="page-header-left">
        <h1>État Visuel des Absences</h1>
        <p>Vue mensuelle — {{ $firstDay->translatedFormat('F Y') }}</p>
    </div>
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <!-- View Toggle (Calendar and List only - no Timeline) -->
        <div class="view-toggle">
            <button class="{{ (!isset($viewMode) || $viewMode == 'calendar') ? 'active' : '' }}" onclick="switchView('calendar')">
                📅 Calendrier
            </button>
            <button class="{{ $viewMode == 'list' ? 'active' : '' }}" onclick="switchView('list')">
                📋 Liste
            </button>
        </div>
        
        <div style="display:flex;gap:8px;align-items:center">
            @php
                $prev = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth();
                $next = \Carbon\Carbon::createFromDate($year, $month, 1)->addMonth();
            @endphp
            <a href="{{ route('absences.calendar', array_merge(request()->query(), ['month' => $prev->month, 'year' => $prev->year])) }}" class="btn btn-ghost btn-sm">←</a>
            <a href="{{ route('absences.calendar', array_merge(request()->query(), ['month' => now()->month, 'year' => now()->year])) }}" class="btn btn-secondary btn-sm">Aujourd'hui</a>
            <a href="{{ route('absences.calendar', array_merge(request()->query(), ['month' => $next->month, 'year' => $next->year])) }}" class="btn btn-ghost btn-sm">→</a>
        </div>
    </div>
</div>

{{-- Quick Stats --}}
<div class="quick-stats" style="margin-bottom:24px">
    <div class="quick-stat">
        <div class="quick-stat-dot" style="background:#10b981"></div>
        <span><strong>{{ $absences->where('status', 'approved')->count() }}</strong> Approuvées</span>
    </div>
    <div class="quick-stat">
        <div class="quick-stat-dot" style="background:#f59e0b"></div>
        <span><strong>{{ $absences->where('status', 'pending')->count() }}</strong> En attente</span>
    </div>
    <div class="quick-stat">
        <div class="quick-stat-dot" style="background:#ef4444"></div>
        <span><strong>{{ count($conflicts) }}</strong> Conflits</span>
    </div>
    <div class="quick-stat">
        <div class="quick-stat-dot" style="background:#8b5cf6"></div>
        <span><strong>{{ $replacements->count() }}</strong> Remplacements</span>
    </div>
    <div class="quick-stat">
        <div class="quick-stat-dot" style="background:#3b82f6"></div>
        <span><strong>{{ $absences->sum('days') }}</strong> Jours total</span>
    </div>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:24px">
    <div style="padding:16px">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
            <div style="font-weight:600;font-size:0.875rem">Filtrer par:</div>
            
            <select class="filter-select" style="padding:8px 12px" onchange="applyFilter('department', this.value)">
                <option value="">Tous les services</option>
                @foreach($employees->pluck('department')->filter()->unique() as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
            
            <select class="filter-select" style="padding:8px 12px" onchange="applyFilter('employee_id', this.value)">
                <option value="">Tous les employés</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
                @endforeach
            </select>
            
            <select class="filter-select" style="padding:8px 12px" onchange="applyFilter('status', this.value)">
                <option value="">Tous les statuts</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvées</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
            </select>
            
            @if(request()->anyFilled(['department', 'employee_id', 'status']))
                <a href="{{ route('absences.calendar', ['month' => $month, 'year' => $year]) }}" class="btn btn-ghost btn-sm">✕ Réinitialiser</a>
            @endif
        </div>
    </div>
</div>

{{-- Alerts --}}
@if(count($conflicts) > 0)
<div class="alert alert-danger" style="margin-bottom:20px">
    <strong>⚠️ {{ count($conflicts) }} conflit(s) détecté(s)</strong> — Des absences approuvées se chevauchent. 
    <a href="#conflicts" style="color:inherit;text-decoration:underline">Voir les détails ↓</a>
</div>
@endif

{{-- CALENDAR VIEW (Monthly Grid: Employees × Dates) --}}
@if(!isset($viewMode) || $viewMode == 'calendar')
<div class="card">
    <div class="monthly-calendar-container">
        <table class="monthly-calendar">
            <thead>
                <tr>
                    <th class="employee-col">Collaborateur</th>
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $date = \Carbon\Carbon::createFromDate($year, $month, $day);
                            $isToday = $date->isSameDay($today);
                            $isWeekend = $date->dayOfWeek == 0 || $date->dayOfWeek == 6;
                        @endphp
                        <th class="day-header {{ $isToday ? 'today-header' : '' }}">
                            {{ $day }}
                            @if($isToday)
                                <div style="font-size:0.5rem;color:var(--primary)">AUJ</div>
                            @endif
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @forelse($filteredEmployees as $emp)
                    @php
                        $empAbsences = $absenceMap[$emp->id] ?? [];
                    @endphp
                    <tr>
                        <td class="employee-cell">
                            <div class="employee-mini">
                                <div class="employee-mini-avatar">
                                    {{ strtoupper(substr($emp->first_name, 0, 1)) }}{{ strtoupper(substr($emp->last_name, 0, 1)) }}
                                </div>
                                <div class="employee-mini-info">
                                    <div class="employee-mini-name">{{ $emp->full_name }}</div>
                                    <div class="employee-mini-dept">{{ $emp->department ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $date = \Carbon\Carbon::createFromDate($year, $month, $day);
                                $isToday = $date->isSameDay($today);
                                $isWeekend = $date->dayOfWeek == 0 || $date->dayOfWeek == 6;
                                $absence = $empAbsences[$day] ?? null;
                            @endphp
                            <td class="{{ $isToday ? 'today-cell' : '' }} {{ $isWeekend ? 'weekend-cell' : '' }}">
                                @if($absence)
                                    <div class="absence-cell {{ $absence->status }}" 
                                         onclick="showAbsenceModal({{ $absence->id }}, '{{ $emp->full_name }}', '{{ $emp->department ?? '' }}', '{{ $emp->position ?? '' }}', '{{ $absence->start_date->format('d/m/Y') }}', '{{ $absence->end_date->format('d/m/Y') }}', '{{ \App\Models\Absence::TYPES[$absence->type] ?? $absence->type }}', '{{ $absence->status }}', {{ $absence->days }}, '{{ $absence->reason ?? '' }}')"
                                         title="{{ \App\Models\Absence::TYPES[$absence->type] ?? $absence->type }}">
                                        <span style="font-size:0.7rem">●</span>
                                    </div>
                                @else
                                    <div class="absence-cell empty"></div>
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

{{-- LIST VIEW --}}
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
                <tr class="absence-card {{ $absence->status }}" onclick="showAbsenceModal({{ $absence->id }}, '{{ $absence->employee->full_name }}', '{{ $absence->employee->department ?? '' }}', '{{ $absence->employee->position ?? '' }}', '{{ $absence->start_date->format('d/m/Y') }}', '{{ $absence->end_date->format('d/m/Y') }}', '{{ \App\Models\Absence::TYPES[$absence->type] ?? $absence->type }}', '{{ $absence->status }}', {{ $absence->days }}, '{{ $absence->reason ?? '' }}')" style="cursor:pointer">
                    <td>
                        <div class="employee-mini">
                            <div class="employee-mini-avatar" style="width:36px;height:36px;font-size:0.75rem">
                                {{ strtoupper(substr($absence->employee->first_name, 0, 1)) }}{{ strtoupper(substr($absence->employee->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600">{{ $absence->employee->full_name }}</div>
                                <div style="font-size:0.75rem;color:var(--text-muted)">{{ $absence->employee->department }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-weight:500">{{ \App\Models\Absence::TYPES[$absence->type] ?? $absence->type }}</span>
                    </td>
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
                            <a href="{{ route('absences.show', $absence) }}" class="btn btn-ghost btn-sm btn-icon" title="Voir">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            @if($absence->status == 'pending')
                                <form action="{{ route('absences.approve', $absence) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Approuver">✓</button>
                                </form>
                                <form action="{{ route('absences.reject', $absence) }}" method="POST">
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

{{-- Conflicts Details --}}
@if(count($conflicts) > 0)
<div class="card" id="conflicts" style="margin-top:24px;border:2px solid #fee2e2">
    <div class="card-header" style="background:#fef2f2">
        <h3 class="card-title" style="color:#991b1b">⚠️ Détails des conflits ({{ count($conflicts) }})</h3>
    </div>
    <div style="padding:16px">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px">
            @foreach($conflicts as $i => $conflict)
            <div style="background:#fff;border:1px solid #fecaca;border-radius:12px;padding:16px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <div style="font-weight:600;color:#991b1b">{{ $conflict['a']->employee->full_name }}</div>
                    <span style="background:#fee2e2;color:#991b1b;padding:4px 8px;border-radius:4px;font-size:0.75rem;font-weight:600">CONFLIT</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div style="background:#fef2f2;padding:12px;border-radius:8px">
                        <div style="font-size:0.7rem;color:#dc2626;font-weight:600;margin-bottom:4px">ABSENCE 1</div>
                        <div style="font-weight:600">{{ \App\Models\Absence::TYPES[$conflict['a']->type] ?? $conflict['a']->type }}</div>
                        <div style="font-size:0.85rem">{{ $conflict['a']->start_date->format('d/m') }} → {{ $conflict['a']->end_date->format('d/m/Y') }}</div>
                        <div style="font-size:0.85rem;color:#dc2626;font-weight:600">{{ $conflict['a']->days }} jours</div>
                    </div>
                    <div style="background:#fef2f2;padding:12px;border-radius:8px">
                        <div style="font-size:0.7rem;color:#dc2626;font-weight:600;margin-bottom:4px">ABSENCE 2</div>
                        <div style="font-weight:600">{{ \App\Models\Absence::TYPES[$conflict['b']->type] ?? $conflict['b']->type }}</div>
                        <div style="font-size:0.85rem">{{ $conflict['b']->start_date->format('d/m') }} → {{ $conflict['b']->end_date->format('d/m/Y') }}</div>
                        <div style="font-size:0.85rem;color:#dc2626;font-weight:600">{{ $conflict['b']->days }} jours</div>
                    </div>
                </div>
                <div style="margin-top:12px;padding-top:12px;border-top:1px solid #fecaca">
                    <span style="font-size:0.8rem;color:#991b1b">Chevauchement: </span>
                    <strong style="color:#dc2626">
                        {{ max($conflict['a']->start_date, $conflict['b']->start_date)->format('d/m') }} → 
                        {{ min($conflict['a']->end_date, $conflict['b']->end_date)->format('d/m/Y') }}
                    </strong>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Legend --}}
<div class="card" style="margin-top:24px;background:var(--bg-secondary)">
    <div style="padding:16px">
        <div style="font-weight:600;margin-bottom:12px">Légende</div>
        <div style="display:flex;gap:24px;flex-wrap:wrap;font-size:0.85rem">
            <div style="display:flex;align-items:center;gap:6px">
                <div style="width:16px;height:16px;background:linear-gradient(135deg, #10b981, #059669);border-radius:4px"></div>
                <span style="color:#065f46;font-weight:500">Approuvée</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
                <div style="width:16px;height:16px;background:linear-gradient(135deg, #f59e0b, #d97706);border-radius:4px"></div>
                <span style="color:#92400e;font-weight:500">En attente</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
                <div style="width:16px;height:16px;background:linear-gradient(135deg, #ef4444, #dc2626);border-radius:4px"></div>
                <span style="color:#991b1b;font-weight:500">Rejetée</span>
            </div>
        </div>
    </div>
</div>

{{-- Modal for Absence Details --}}
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
                <span class="modal-detail-value" id="modalStartDate">01/01/2024</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Fin</span>
                <span class="modal-detail-value" id="modalEndDate">05/01/2024</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Motif</span>
                <span class="modal-detail-value"><span class="modal-type-badge" id="modalType">Congé Annuel</span></span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Jours</span>
                <span class="modal-detail-value" id="modalDays">5 jours</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Statut</span>
                <span class="modal-detail-value"><span class="modal-status-badge" id="modalStatus">Approuvé</span></span>
            </div>
            <div class="modal-detail-row" id="modalReasonRow" style="display:none">
                <span class="modal-detail-label">Détails</span>
                <span class="modal-detail-value" id="modalReason" style="max-width:180px;text-align:right">-</span>
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
    function switchView(view) {
        const url = new URL(window.location.href);
        url.searchParams.set('view', view);
        window.location.href = url.toString();
    }
    
    function applyFilter(key, value) {
        const url = new URL(window.location.href);
        if (value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
        window.location.href = url.toString();
    }
    
    function showAbsenceModal(id, name, dept, position, startDate, endDate, type, status, days, reason) {
        // Set employee info
        const nameParts = name.split(' ');
        const initials = nameParts.length >= 2 
            ? nameParts[0][0] + nameParts[nameParts.length - 1][0] 
            : name[0];
        document.getElementById('modalAvatar').textContent = initials.toUpperCase();
        document.getElementById('modalEmployeeName').textContent = name;
        document.getElementById('modalEmployeeDept').textContent = dept + (position ? ' - ' + position : '');
        
        // Set absence details
        document.getElementById('modalStartDate').textContent = startDate;
        document.getElementById('modalEndDate').textContent = endDate;
        document.getElementById('modalType').textContent = type;
        document.getElementById('modalDays').textContent = days + ' jour(s)';
        
        // Set status with appropriate class
        const statusEl = document.getElementById('modalStatus');
        statusEl.textContent = status === 'approved' ? 'Approuvé' : status === 'pending' ? 'En attente' : 'Rejeté';
        statusEl.className = 'modal-status-badge ' + status;
        
        // Set reason if available
        const reasonRow = document.getElementById('modalReasonRow');
        if (reason && reason.trim()) {
            reasonRow.style.display = 'flex';
            document.getElementById('modalReason').textContent = reason;
        } else {
            reasonRow.style.display = 'none';
        }
        
        // Set detail link
        document.getElementById('modalDetailLink').href = '/absences/' + id;
        
        // Show modal
        document.getElementById('absenceModal').classList.add('active');
    }
    
    function closeAbsenceModal() {
        document.getElementById('absenceModal').classList.remove('active');
    }
    
    function closeModal(event) {
        if (event.target.classList.contains('modal-overlay')) {
            closeAbsenceModal();
        }
    }
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAbsenceModal();
        }
    });
</script>
@endpush
@endsection

