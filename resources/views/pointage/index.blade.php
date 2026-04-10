{{-- resources/views/pointage/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Pointage — Badgeuse')

@push('styles')
<style>
    /* ── Variables thème clair ──────────────────────────── */
    :root {
        --p-bg:          #f8fafc;
        --p-surface:     #ffffff;
        --p-border:      #e2e8f0;
        --p-border-soft: #f1f5f9;
        --p-text:        #0f172a;
        --p-text-muted:  #64748b;
        --p-text-light:  #94a3b8;
        --p-teal:        #0d9488;
        --p-teal-bg:     #f0fdfa;
        --p-teal-light:  #ccfbf1;
        --p-blue:        #1d4ed8;
        --p-blue-bg:     #eff6ff;
        --p-purple:      #7c3aed;
        --p-purple-bg:   #f5f3ff;
        --p-amber:       #d97706;
        --p-amber-bg:    #fffbeb;
        --p-red:         #dc2626;
        --p-red-bg:      #fef2f2;
        --p-green:       #16a34a;
        --p-green-bg:    #f0fdf4;
        --p-gray-bg:     #f8fafc;
    }

    .pointage-wrap { display: flex; flex-direction: column; height: calc(100vh - 64px); background: var(--p-bg); }

    /* ── Topbar ─────────────────────────────────────────── */
    .pt-topbar {
        background: var(--p-surface);
        border-bottom: 1px solid var(--p-border);
        padding: 0 1.5rem;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .pt-topbar-left { display: flex; align-items: center; gap: 1rem; }
    .pt-title { font-size: 15px; font-weight: 600; color: var(--p-text); }
    .pt-tabs { display: flex; background: var(--p-bg); border: 1px solid var(--p-border); border-radius: 8px; overflow: hidden; }
    .pt-tab {
        padding: 5px 16px; font-size: 12px; font-weight: 500; cursor: pointer;
        color: var(--p-text-muted); background: transparent; border: none; transition: all .15s;
        text-decoration: none; display: flex; align-items: center;
    }
    .pt-tab.active, .pt-tab:hover { background: var(--p-teal); color: #fff; }
    .pt-topbar-right { display: flex; align-items: center; gap: .75rem; }
    .pt-sync {
        display: flex; align-items: center; gap: 6px;
        font-size: 11px; color: var(--p-text-muted);
        background: var(--p-bg); border: 1px solid var(--p-border);
        padding: 4px 10px; border-radius: 20px;
    }
    .pt-sync-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--p-teal); animation: blink 2s infinite; }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
    .pt-btn-validate {
        background: var(--p-teal); color: #fff; border: none;
        padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
        cursor: pointer; transition: background .15s;
    }
    .pt-btn-validate:hover { background: #0f766e; }

    /* ── Week nav ───────────────────────────────────────── */
    .pt-weeknav {
        background: var(--p-surface); border-bottom: 1px solid var(--p-border);
        padding: .75rem 1.5rem; display: flex; align-items: center; gap: .75rem; flex-shrink: 0;
    }
    .pt-weeknav-btn {
        background: var(--p-bg); border: 1px solid var(--p-border);
        width: 28px; height: 28px; border-radius: 6px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; color: var(--p-text-muted); transition: all .15s;
    }
    .pt-weeknav-btn:hover { border-color: var(--p-teal); color: var(--p-teal); }
    .pt-week-label { font-size: 13px; font-weight: 500; color: var(--p-text); }
    .pt-week-badge {
        background: var(--p-teal-light); color: var(--p-teal);
        font-size: 11px; font-weight: 600; padding: 2px 10px; border-radius: 20px;
    }

    /* ── Body (day sidebar + table) ─────────────────────── */
    .pt-body { display: flex; flex: 1; overflow: hidden; }

    /* Day sidebar */
    .pt-days {
        width: 155px; flex-shrink: 0; background: var(--p-surface);
        border-right: 1px solid var(--p-border); overflow-y: auto;
    }
    .pt-day {
        display: flex; align-items: center; justify-content: space-between;
        padding: 11px 14px; cursor: pointer; border-left: 3px solid transparent;
        text-decoration: none; transition: all .12s;
    }
    .pt-day:hover { background: var(--p-teal-bg); }
    .pt-day.active { background: var(--p-teal-bg); border-left-color: var(--p-teal); }
    .pt-day-name { font-size: 12px; font-weight: 600; color: var(--p-text); }
    .pt-day-date { font-size: 11px; color: var(--p-text-muted); margin-top: 1px; }
    .pt-day-check {
        width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 10px;
    }
    .pt-day-check.ok { background: var(--p-teal); color: #fff; }
    .pt-day-check.pending { border: 1.5px solid var(--p-border); color: var(--p-text-light); }

    /* Table */
    .pt-table-wrap { flex: 1; overflow: auto; }
    .pt-table { width: 100%; border-collapse: collapse; min-width: 860px; }
    .pt-table thead th {
        position: sticky; top: 0; z-index: 2;
        background: var(--p-gray-bg); border-bottom: 1px solid var(--p-border);
        padding: 10px 12px; text-align: left; white-space: nowrap;
        font-size: 11px; font-weight: 600; text-transform: uppercase;
        letter-spacing: .05em; color: var(--p-text-muted);
    }
    .pt-table td { padding: 10px 12px; border-bottom: 1px solid var(--p-border-soft); vertical-align: middle; }
    .pt-table tbody tr:hover td { background: var(--p-teal-bg); }

    /* Badges heures */
    .pt-time-pill {
        display: inline-block; padding: 3px 9px; border-radius: 6px;
        font-size: 12px; font-weight: 700; letter-spacing: .02em;
    }
    .pt-pill-start  { background: var(--p-blue-bg);   color: var(--p-blue); }
    .pt-pill-end    { background: var(--p-purple-bg);  color: var(--p-purple); }
    .pt-pill-midnight { background: #ede9fe; color: #6d28d9; }
    .pt-time-sep { color: var(--p-text-light); font-size: 12px; margin: 0 2px; }

    /* Badge pause */
    .pt-pause {
        display: inline-block; padding: 3px 9px; border-radius: 6px;
        font-size: 12px; font-weight: 600;
    }
    .pt-pause-on  { background: var(--p-amber-bg); color: var(--p-amber); }
    .pt-pause-off { background: var(--p-green-bg); color: var(--p-green); }

    /* Statuts */
    .pt-badge {
        display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 500;
    }
    .pt-badge-absent { background: var(--p-red-bg); color: var(--p-red); }
    .pt-badge-nobadge { background: var(--p-gray-bg); color: var(--p-text-muted); border: 1px solid var(--p-border); }

    /* Shift */
    .pt-shift { font-size: 12px; color: var(--p-text-muted); white-space: nowrap; }
    .pt-total { font-size: 13px; font-weight: 700; color: var(--p-teal); }
    .pt-total.long { color: var(--p-amber); }

    /* Checkbox validé */
    .pt-check {
        width: 22px; height: 22px; border-radius: 50%; display: flex;
        align-items: center; justify-content: center; cursor: pointer;
        border: none; transition: all .15s;
    }
    .pt-check.ok { background: var(--p-teal); color: #fff; }
    .pt-check.pending { background: transparent; border: 1.5px solid var(--p-border); color: var(--p-text-light); }

    /* Action buttons */
    .pt-action-btn {
        font-size: 11px; font-weight: 500; padding: 4px 10px; border-radius: 6px;
        cursor: pointer; border: 1px solid var(--p-border); background: var(--p-surface);
        color: var(--p-text-muted); transition: all .15s; white-space: nowrap;
    }
    .pt-action-btn:hover { border-color: var(--p-teal); color: var(--p-teal); }
    .pt-action-btn.keep { background: var(--p-teal-bg); border-color: var(--p-teal); color: var(--p-teal); }

    /* ── Status bar ─────────────────────────────────────── */
    .pt-statusbar {
        background: var(--p-surface); border-top: 1px solid var(--p-border);
        padding: .5rem 1.5rem; display: flex; align-items: center;
        justify-content: space-between; flex-shrink: 0;
    }
    .pt-stat { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--p-text-muted); }
    .pt-stat-dot { width: 8px; height: 8px; border-radius: 50%; }
    .pt-stat strong { font-weight: 600; }

    /* Employee avatar */
    .pt-avatar {
        width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700;
        background: var(--p-teal-light); color: var(--p-teal);
    }

    /* Dimmed row (ex: shift très court) */
    .pt-row-dimmed td { opacity: .55; }
</style>
@endpush

@section('content')
<div class="pointage-wrap">

    {{-- ── Topbar ──────────────────────────────────────────── --}}
    <div class="pt-topbar">
        <div class="pt-topbar-left">
            <span class="pt-title">Pointage — Badgeuse</span>
            <div class="pt-tabs">
<<<<<<< HEAD
                <a href="{{ route('pointage.index', ['date' => $currentDate->toDateString(), 'vue' => 'journee']) }}"
                   class="pt-tab {{ request('vue', 'journee') === 'journee' ? 'active' : '' }}">
                    Journée
                </a>
                <a href="{{ route('pointage.index', ['date' => $currentDate->toDateString(), 'vue' => 'employes']) }}"
                   class="pt-tab {{ request('vue') === 'employes' ? 'active' : '' }}">
                    Employés
=======
                <a href="{{ route('pointage.index', array_merge(request()->only(['search', 'department']), ['date' => $currentDate->toDateString(), 'vue' => 'tous'])) }}"
                   class="pt-tab {{ ($vue ?? request('vue', 'tous')) === 'tous' ? 'active' : '' }}">
                    Tous
                </a>
                <a href="{{ route('pointage.index', array_merge(request()->only(['search', 'department']), ['date' => $currentDate->toDateString(), 'vue' => 'pointe'])) }}"
                   class="pt-tab {{ ($vue ?? request('vue')) === 'pointe' ? 'active' : '' }}">
                    Pointe
                </a>
                <a href="{{ route('pointage.index', array_merge(request()->only(['search', 'department']), ['date' => $currentDate->toDateString(), 'vue' => 'non_pointe'])) }}"
                   class="pt-tab {{ ($vue ?? request('vue')) === 'non_pointe' ? 'active' : '' }}">
                    Non pointe
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
                </a>
            </div>
        </div>
        <div class="pt-topbar-right">
            @if($dernierSync)
            <div class="pt-sync">
                <div class="pt-sync-dot"></div>
                <span>Sync tablette <strong id="sync-ago">—</strong></span>
            </div>
            @endif
<<<<<<< HEAD
=======

            {{-- Export PDF Button --}}
            <div class="pdf-export-dropdown">
                <a href="{{ route('pointage.pdf', request()->only(['date', 'department', 'search', 'vue'])) }}"
                   class="pt-btn-export" title="Exporter PDF (filtres actuels)"
                   style="background: var(--p-primary): #22c55e;; padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; transition: background .15s; white-space: nowrap;">
                    PDF
                </a>
            </div>
        
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
            <button class="pt-btn-validate" id="btn-validate"
                    data-date="{{ $currentDate->toDateString() }}"
                    data-url="{{ route('pointage.valider-journee') }}">
                ✓ Valider la journée
            </button>
        </div>
    </div>

<<<<<<< HEAD
    {{-- ── Week nav ─────────────────────────────────────────── --}}
    <div class="pt-weeknav">
        @php
            $prevDate = $currentDate->copy()->subWeek();
            $nextDate = $currentDate->copy()->addWeek();
        @endphp
        <a href="{{ route('pointage.index', ['date' => $prevDate->toDateString()]) }}" class="pt-weeknav-btn">&#8249;</a>
=======
    {{-- ── FILTERS BAR - NOUVEAU ────────────────────────────── --}}
    <div style="background: var(--p-surface); border-bottom: 1px solid var(--p-border); padding: 0.75rem 1.5rem; display: flex; gap: 0.75rem; align-items: center; font-size: 13px;">
         <strong>Filtrer:</strong> 
        <form method="GET" action="{{ route('pointage.index') }}" style="display: flex; gap: 0.5rem; align-items: center; flex: 1;">
            <input type="hidden" name="date" value="{{ $currentDate->toDateString() }}">
            <input type="hidden" name="vue" value="{{ $vue ?? request('vue', 'tous') }}">

            <input type="text" name="search" placeholder="Nom employé..." value="{{ request('search') }}" onchange="this.form.submit()" style="flex: 1; padding: 0.5rem; border: 1px solid var(--p-border); border-radius: 6px;">
            <select name="department" onchange="this.form.submit()" style="padding: 0.5rem; border: 1px solid var(--p-border); border-radius: 6px;">
                <option value=""> Tous départements</option>
                @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
@if(request()->hasAny(['search', 'department']))
                <a href="{{ route('pointage.index', ['date' => $currentDate->toDateString(), 'vue' => request('vue')]) }}" style="padding: 0.5rem 1rem; background: var(--p-red-bg); color: var(--p-red); border-radius: 6px; text-decoration: none; font-weight: 500;">✕ Reset</a>
            @endif
        </form>
    </div>

    {{-- ── Week nav ─────────────────────────────────────────── --}}
    <div class="pt-weeknav">

@php
            $prevDate = $currentDate->copy()->subWeek();
            $nextDate = $currentDate->copy()->addWeek();
            $filterParams = request()->only(['search', 'department']);
        @endphp
        <a href="{{ route('pointage.index', array_merge($filterParams, ['date' => $prevDate->toDateString()])) }}" class="pt-weeknav-btn">&#8249;</a>
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
        <span class="pt-week-label">
            {{ $startOfWeek->translatedFormat('d M') }} – {{ $endOfWeek->translatedFormat('d M Y') }}
        </span>
        <span class="pt-week-badge">Semaine {{ $currentDate->weekOfYear }}</span>
<<<<<<< HEAD
        <a href="{{ route('pointage.index', ['date' => $nextDate->toDateString()]) }}" class="pt-weeknav-btn">&#8250;</a>
        <a href="{{ route('pointage.index', ['date' => today()->toDateString()]) }}"
=======
        <a href="{{ route('pointage.index', array_merge($filterParams, ['date' => $nextDate->toDateString()])) }}" class="pt-weeknav-btn">&#8250;</a>
        <a href="{{ route('pointage.index', array_merge($filterParams, ['date' => today()->toDateString()])) }}"
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
           class="pt-weeknav-btn" title="Aujourd'hui" style="font-size:11px;width:auto;padding:0 10px;">
            Aujourd'hui
        </a>
    </div>

    {{-- ── Body ─────────────────────────────────────────────── --}}
    <div class="pt-body">

        {{-- Day sidebar --}}
        <div class="pt-days">
<<<<<<< HEAD
            @foreach($weekDays as $day)
            <a href="{{ route('pointage.index', ['date' => $day['date']->toDateString()]) }}"
=======
@foreach($weekDays as $day)
            <a href="{{ route('pointage.index', array_merge($filterParams, ['date' => $day['date']->toDateString()])) }}"
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
               class="pt-day {{ $day['isSelected'] ? 'active' : '' }}">
                <div>
                    <div class="pt-day-name">{{ $day['label'] }}</div>
                    <div class="pt-day-date">{{ $day['short'] }}</div>
                </div>
                <div class="pt-day-check {{ $day['valide'] ? 'ok' : 'pending' }}">
                    {{ $day['valide'] ? '✓' : '○' }}
                </div>
            </a>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="pt-table-wrap">
            <table class="pt-table">
                <thead>
                    <tr>
                        <th style="width:44px">Validé</th>
                        <th>Employé</th>
                        <th>Absence</th>
<<<<<<< HEAD
                        <th>Heures travaillées</th>
                        <th>Pause</th>
                        <th>Shifts rémunérés</th>
                        <th style="width:72px;text-align:center">Pause (min)</th>
                        <th style="width:80px">Total</th>
                        <th>Action</th>
=======
                    <th>Heures travaillées</th>
                    <th>Pause total</th>
                    <th>Pause début / fin</th>
                    <th style="width:80px">Total travaillé</th>
                    <th>Action</th>

>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
                    </tr>
                </thead>
                <tbody>
                @foreach($employees as $emp)
                @php
                    $p       = $emp['pointage'];
                    $statut  = $p?->statut ?? 'pas_de_badge';
                    $valide  = $p?->valide ?? false;
                    $isDimmed = $p && $p->total_heures && $p->total_heures < 1;
<<<<<<< HEAD
                    $isAbsent = in_array($statut, ['absent','absence_injustifiee']);
=======
                    $isAbsent = in_array($statut, ['absent','absence']);
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
                    $isNoBadge = $statut === 'pas_de_badge' && !$p?->heure_entree;
                    $isMidnight = $p?->heure_sortie === '00:00:00' || $p?->heure_sortie === '00:00';
                @endphp
                <tr class="{{ $isDimmed ? 'pt-row-dimmed' : '' }}" id="row-emp-{{ $emp['id'] }}">

                    {{-- Validé --}}
                    <td>
                        @if($p)
                        <button class="pt-check {{ $valide ? 'ok' : 'pending' }}"
                                data-id="{{ $p->id }}"
                                data-url="{{ route('pointage.toggle-valider', $p->id) }}"
                                onclick="toggleValider(this)"
                                title="{{ $valide ? 'Validé – cliquer pour annuler' : 'Cliquer pour valider' }}">
                            {{ $valide ? '✓' : '○' }}
                        </button>
                        @else
                        <div class="pt-check pending">○</div>
                        @endif
                    </td>

                    {{-- Employé --}}
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="pt-avatar">{{ $emp['avatar'] }}</div>
                            <span style="font-size:13px;font-weight:500;color:var(--p-text);">{{ $emp['nom'] }}</span>
                        </div>
                    </td>

                    {{-- Absence --}}
<<<<<<< HEAD
                    <td>
                        @if($isAbsent)
                        <span class="pt-badge pt-badge-absent">Absence injustifiée</span>
                        @else
                        <input type="checkbox" style="accent-color:var(--p-teal);width:15px;height:15px;" {{ $isAbsent ? 'checked' : '' }} disabled>
                        @endif
                    </td>
=======
            <td>
    <input type="checkbox"
           class="absent-checkbox"
           data-employee="{{ $emp['id'] }}"
           data-date="{{ $currentDate->toDateString() }}"
           data-url="{{ route('pointage.toggle-absence') }}"
           style="accent-color:var(--p-teal);width:15px;height:15px;"
           {{ $isAbsent ? 'checked' : '' }}
           onchange="toggleAbsence(this)">

    <span class="pt-badge pt-badge-absent"
          style="{{ !$isAbsent ? 'display:none;' : '' }}">
        Absence 
    </span>
</td>
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06

                    {{-- Heures travaillées --}}
                    <td>
                        @if($p && $p->heure_entree)
                        <div style="display:flex;align-items:center;gap:4px;">
                            <span class="pt-time-pill pt-pill-start">
                                {{ \Carbon\Carbon::parse($p->heure_entree)->format('H:i') }}
                            </span>
                            <span class="pt-time-sep">–</span>
                            @if($p->heure_sortie)
                            <span class="pt-time-pill {{ $isMidnight ? 'pt-pill-midnight' : 'pt-pill-end' }}">
                                {{ \Carbon\Carbon::parse($p->heure_sortie)->format('H:i') }}{{ $isMidnight ? '*' : '' }}
                            </span>
                            @else
                            <span style="font-size:11px;color:var(--p-text-light)">En cours…</span>
                            @endif
                        </div>
                        @elseif($isAbsent)
                        <span style="color:var(--p-text-light)">—</span>
                        @elseif($isNoBadge)
                        <span class="pt-badge pt-badge-nobadge">Pas de badge</span>
                        @else
                        <span style="color:var(--p-text-light)">—</span>
                        @endif
                    </td>

<<<<<<< HEAD
                    {{-- Pause --}}
                    <td>
                        @if($p && !$isAbsent && !$isNoBadge)
                        <span class="pt-pause {{ $p->pause_minutes > 0 ? 'pt-pause-on' : 'pt-pause-off' }}">
                            {{ $p->pause_minutes }} mn
=======
                    {{-- Pause total --}}
                    <td>
                        @if($p && !$isAbsent && !$isNoBadge)
                        <span class="pt-pause {{ $p->pause_minutes > 0 ? 'pt-pause-on' : 'pt-pause-off' }}">
                            {{ $p->pause_formatee }}
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
                        </span>
                        @else
                        <span style="color:var(--p-text-light)">—</span>
                        @endif
                    </td>

<<<<<<< HEAD
                    {{-- Shifts rémunérés --}}
                    <td class="pt-shift">
                        @if($p && $p->heure_entree && $p->heure_sortie)
                        {{ \Carbon\Carbon::parse($p->heure_entree)->format('H:i') }}
                        – {{ \Carbon\Carbon::parse($p->heure_sortie)->format('H:i') }}
                        @else
                        <span style="color:var(--p-border)">—</span>
                        @endif
                    </td>

                    {{-- Pause min --}}
                    <td style="text-align:center;font-size:12px;color:var(--p-text-muted);">
                        {{ $p ? $p->pause_minutes : 0 }}
                    </td>

                    {{-- Total --}}
                    <td>
                        @if($p && $p->total_heures)
                        <span class="pt-total {{ $p->total_heures > 10 ? 'long' : '' }}">
                            {{ number_format($p->total_heures, 2) }}h
=======
                    {{-- Pause début / fin --}}
                    <td>
                        @if($p && $p->pause_debut && $p->pause_fin)
                        <span class="pt-time-pill pt-pill-start">{{ $p->pause_debut }}</span>
                        <span class="pt-time-sep">–</span>
                        <span class="pt-time-pill pt-pill-end">{{ $p->pause_fin }}</span>
@elseif($p?->pause_debut)
                        <span class="pt-time-pill pt-pill-start">{{ $p->pause_debut }}</span> <span style="color:var(--p-text-light);font-size:11px;">en cours</span>
                        @else
                        <span style="color:var(--p-text-light)">—</span>
                        @endif
                    </td>

                    {{-- Total travaillé --}}
                    <td>
                        @if($p && $p->total_heures)
                        <span class="pt-total {{ $p->total_heures > 10 ? 'long' : '' }}">
                            {{ $p->total_heures_formate }}
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
                        </span>
                        @else
                        <span style="color:var(--p-border)">—</span>
                        @endif
                    </td>

                    {{-- Action --}}
<<<<<<< HEAD
=======

>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
                    <td>
                        @if($p)
                        <button class="pt-action-btn {{ $p->ignore_badge ? '' : 'keep' }}"
                                data-id="{{ $p->id }}"
                                data-url="{{ route('pointage.toggle-ignore', $p->id) }}"
                                onclick="toggleIgnore(this)">
                            {{ $p->ignore_badge ? '⊘ Ignorer' : '👁 Garder' }}
                        </button>
                        @else
                        <button class="pt-action-btn" disabled style="opacity:.4;cursor:default;">⊘ Ignorer</button>
                        @endif
                    </td>

                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Status bar ───────────────────────────────────────── --}}
    <div class="pt-statusbar">
        <div style="display:flex;gap:1.5rem;">
            <div class="pt-stat">
                <div class="pt-stat-dot" style="background:var(--p-teal)"></div>
                Validés : <strong style="color:var(--p-teal)">{{ $stats['valides'] }}</strong>
            </div>
            <div class="pt-stat">
                <div class="pt-stat-dot" style="background:var(--p-amber)"></div>
                En attente : <strong style="color:var(--p-amber)">{{ $stats['en_attente'] }}</strong>
            </div>
            <div class="pt-stat">
                <div class="pt-stat-dot" style="background:var(--p-red)"></div>
                Absents : <strong style="color:var(--p-red)">{{ $stats['absents'] }}</strong>
            </div>
            <div class="pt-stat" style="margin-left:1rem;color:var(--p-text-muted);">
                Total employés : <strong>{{ $stats['total'] }}</strong>
            </div>
        </div>
        @if($dernierSync)
        <div class="pt-stat">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="var(--p-text-light)" stroke-width="1.5">
                <rect x="2" y="2" width="5" height="12" rx="1"/>
                <rect x="9" y="2" width="5" height="5" rx="1"/>
                <rect x="9" y="9" width="5" height="5" rx="1"/>
            </svg>
            Tablette : <strong style="color:var(--p-teal)">{{ $dernierSync->nom }}</strong>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

<<<<<<< HEAD
/* ── Sync temps affiché ─────────────────────────────── */
=======
 /* ── Sync temps affiché ─────────────────────────────── */
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
@if($dernierSync)
(function() {
    const syncedAt = new Date('{{ $dernierSync->derniere_connexion?->toIso8601String() }}');
    function updateSyncLabel() {
        const diff = Math.floor((Date.now() - syncedAt) / 1000);
        let label;
        if (diff < 60)       label = 'à l\'instant';
        else if (diff < 3600) label = `il y a ${Math.floor(diff/60)} min`;
        else                  label = `il y a ${Math.floor(diff/3600)}h`;
        const el = document.getElementById('sync-ago');
        if (el) el.textContent = label;
    }
    updateSyncLabel();
<<<<<<< HEAD
    setInterval(updateSyncLabel, 30000);
})();
@endif

/* ── Polling auto-refresh toutes les 60s ────────────── */
setTimeout(() => location.reload(), 60000);
=======
    setInterval(updateSyncLabel, 3000000);
})();
@endif



>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06

/* ── Valider la journée ─────────────────────────────── */
document.getElementById('btn-validate').addEventListener('click', async function() {
    const btn  = this;
    const date = btn.dataset.date;
    const url  = btn.dataset.url;

    btn.disabled = true;
    btn.textContent = '…';

    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ date })
        });
        const data = await res.json();
        btn.textContent = '✓ ' + data.message;
        btn.style.background = '#0f766e';
<<<<<<< HEAD
        setTimeout(() => { btn.textContent = '✓ Valider la journée'; btn.style.background = ''; btn.disabled = false; }, 3000);
=======
        setTimeout(() => { btn.textContent = '✓ Valider la journée'; btn.style.background = ''; btn.disabled = false; }, 300000);
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
    } catch(e) {
        btn.textContent = 'Erreur !';
        btn.style.background = '#dc2626';
        btn.disabled = false;
    }
});

<<<<<<< HEAD
/* ── Toggle validé ──────────────────────────────────── */
=======
 /* ── Toggle validé ──────────────────────────────────── */
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
async function toggleValider(btn) {
    const url = btn.dataset.url;
    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        btn.classList.toggle('ok',      data.valide);
        btn.classList.toggle('pending', !data.valide);
        btn.textContent = data.valide ? '✓' : '○';
    } catch(e) { console.error(e); }
}

/* ── Toggle ignorer/garder ──────────────────────────── */
async function toggleIgnore(btn) {
    const url = btn.dataset.url;
    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        btn.classList.toggle('keep', !data.ignore_badge);
        btn.textContent = data.ignore_badge ? '⊘ Ignorer' : '👁 Garder';
    } catch(e) { console.error(e); }
}
<<<<<<< HEAD
</script>
@endpush
=======
async function toggleAbsence(checkbox) {
    const url = checkbox.dataset.url;
    const employeeId = checkbox.dataset.employee;
    const date = checkbox.dataset.date;
    const badge = checkbox.parentElement.querySelector('.pt-badge-absent');

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                employee_id: employeeId,
                date: date,
                absent: checkbox.checked
            })
        });

        const data = await res.json();

        // UI update
        badge.style.display = checkbox.checked ? 'inline-block' : 'none';

    } catch (e) {
        console.error(e);
        checkbox.checked = !checkbox.checked;
    }
}

</script>
@endpush

>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06
