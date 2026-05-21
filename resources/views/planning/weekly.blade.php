@extends('layouts.app')

@section('title', 'Planning')
@section('page-title', 'Planning')

@php
    $isEmployee = isset($isEmployee) && $isEmployee;
    $filterAbsence = ($shift_type ?? '') === 'absence';
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-left">
        @if($isEmployee)
            <h1>Votre Planning Personnel</h1>
            @isset($startOfWeek, $endOfWeek)
                <p>Semaine du {{ $startOfWeek->format('d') }} au {{ $endOfWeek->format('d M Y') }}</p>
            @else
                <p>Semaine du ? au ?</p>
            @endisset
        @else
            <h1>Planning</h1>
            @isset($startOfWeek, $endOfWeek)
                <p>Semaine du {{ $startOfWeek->format('d') }} au {{ $endOfWeek->format('d M Y') }}</p>
            @else
                <p>Semaine du ? au ?</p>
            @endisset
        @endif
    </div>

    @if(!$isEmployee)
    <div class="page-header-right" style="display:flex;gap:8px">
        <a href="{{ route('planning.monthly') }}" class="btn btn-outline">Vue Mensuelle</a>
        <a href="{{ route('planning.weekly.pdf', request()->query()) }}" class="btn btn-outline" target="_blank">Exporter PDF</a>
        <a href="{{ route('planning.templates.index') }}" class="btn btn-outline">Semaines Types</a>
        <button type="button" class="btn btn-primary" onclick="openPlanningModal()">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Creer un planning
        </button>
    </div>
    @endif
</div>

{{-- MODAL CREER --}}
@if(!$isEmployee)
<div id="planningModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5)">
    <div style="background:white;margin:5% auto;padding:24px;border-radius:12px;width:90%;max-width:500px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2 style="margin:0;font-size:1.25rem">Creer un planning</h2>
            <button type="button" onclick="closePlanningModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted)">×</button>
        </div>
        <form method="POST" action="{{ route('planning.store') }}">
            @csrf
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Employe</label>
                <select name="employee_id" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                    <option value="">Selectionner un employe</option>
                    @isset($employees)
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} - {{ $emp->department }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Date</label>
                <input type="date" name="date" id="createDate" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Type de shift</label>
                <select name="shift_type" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                    <option value="matin">Matin</option>
                    <option value="apres_midi">Apres-midi</option>
                    <option value="journee">Journee complete</option>
                    <option value="garde">Garde</option>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de debut</label>
                    <input type="time" name="shift_start" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                </div>
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de fin</label>
                    <input type="time" name="shift_end" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                </div>
            </div>
            <div style="margin-bottom:20px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Notes (optionnel)</label>
                <textarea name="notes" rows="2" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;resize:vertical"></textarea>
            </div>
            <div style="margin-bottom:20px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Salle (optionnel)</label>
                <input type="text" name="room" placeholder="Salle" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end">
                <button type="button" onclick="closePlanningModal()" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- MODAL MODIFIER/SUPPRIMER --}}
<div id="editShiftModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5)">
    <div style="background:white;margin:5% auto;padding:24px;border-radius:12px;width:90%;max-width:480px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2 style="margin:0;font-size:1.25rem" id="editShiftTitle">{{ $isEmployee ? 'Detail du shift' : 'Modifier le shift' }}</h2>
            <button type="button" onclick="closeEditShiftModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted)">×</button>
        </div>
        <form id="editShiftForm" method="POST">
            @csrf
            @method('PUT')
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Type de shift</label>
                <select name="shift_type" id="editShiftType" required {{ $isEmployee ? 'disabled' : '' }}
                    style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:{{ $isEmployee ? '#f9fafb' : 'white' }}">
                    <option value="matin">Matin</option>
                    <option value="apres_midi">Apres-midi</option>
                    <option value="journee">Journee complete</option>
                    <option value="garde">Garde</option>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de debut</label>
                    <input type="time" name="shift_start" id="editShiftStart" required {{ $isEmployee ? 'readonly' : '' }}
                        style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:{{ $isEmployee ? '#f9fafb' : 'white' }}">
                </div>
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de fin</label>
                    <input type="time" name="shift_end" id="editShiftEnd" required {{ $isEmployee ? 'readonly' : '' }}
                        style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:{{ $isEmployee ? '#f9fafb' : 'white' }}">
                </div>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Salle</label>
                <input type="text" name="room" id="editShiftRoom" placeholder="Salle (optionnel)" {{ $isEmployee ? 'readonly' : '' }}
                    style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:{{ $isEmployee ? '#f9fafb' : 'white' }}">
            </div>
            <div style="margin-bottom:20px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Notes</label>
                <textarea name="notes" id="editShiftNotes" rows="3" placeholder="Ajouter une note..." {{ $isEmployee ? 'readonly' : '' }}
                    style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;resize:vertical;background:{{ $isEmployee ? '#f9fafb' : 'white' }}"></textarea>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center">
                @if(!$isEmployee)
                <button type="button" id="deleteShiftBtn" onclick="deleteShift()"
                    style="padding:8px 16px;border:1px solid #ef4444;border-radius:8px;background:white;color:#ef4444;font-size:0.875rem;cursor:pointer;font-weight:600">
                    Supprimer
                </button>
                @else
                <div></div>
                @endif
                <div style="display:flex;gap:10px">
                    <button type="button" onclick="closeEditShiftModal()" class="btn btn-outline">{{ $isEmployee ? 'Fermer' : 'Annuler' }}</button>
                    @if(!$isEmployee)
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL QUICK ADD --}}
@if(!$isEmployee)
<div id="quickAddModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5)">
    <div style="background:white;margin:5% auto;padding:24px;border-radius:12px;width:90%;max-width:460px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2 style="margin:0;font-size:1.25rem">Ajouter un shift</h2>
            <button type="button" onclick="closeQuickAddModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted)">×</button>
        </div>
        <form method="POST" action="{{ route('planning.store') }}">
            @csrf
            <input type="hidden" name="employee_id" id="qaEmployeeId">
            <input type="hidden" name="date" id="qaDate">
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Type de shift</label>
                <select name="shift_type" id="qaShiftType" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                    <option value="matin">Matin</option>
                    <option value="apres_midi">Apres-midi</option>
                    <option value="journee">Journee complete</option>
                    <option value="garde">Garde</option>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de debut</label>
                    <input type="time" name="shift_start" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                </div>
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de fin</label>
                    <input type="time" name="shift_end" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                </div>
            </div>
            <div style="margin-bottom:20px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Notes (optionnel)</label>
                <textarea name="notes" rows="2" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;resize:vertical"></textarea>
            </div>
            <div style="margin-bottom:20px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Salle (optionnel)</label>
                <input type="text" name="room" placeholder="Salle" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end">
                <button type="button" onclick="closeQuickAddModal()" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- BARRE DE FILTRES --}}
@if(!$isEmployee)
@isset($week, $year)
<div class="filters-bar" style="margin-bottom:20px">
    <form method="GET" action="{{ route('planning.weekly') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
        <div style="display:flex;align-items:center;gap:8px">
            <a href="{{ route('planning.weekly', ['week' => $week - 1, 'year' => $year, 'search' => $search ?? '', 'department' => $department ?? '', 'shift_type' => $shift_type ?? '']) }}"
               class="btn btn-sm btn-outline">← Semaine precedente</a>
            <select name="week" onchange="this.form.submit()" style="min-width:120px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                @for($w = 1; $w <= 52; $w++)
                    <option value="{{ $w }}" {{ ($week ?? 0) == $w ? 'selected' : '' }}>Semaine {{ $w }}</option>
                @endfor
            </select>
            <select name="year" onchange="this.form.submit()" style="min-width:100px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ ($year ?? 0) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <a href="{{ route('planning.weekly', ['week' => $week + 1, 'year' => $year, 'search' => $search ?? '', 'department' => $department ?? '', 'shift_type' => $shift_type ?? '']) }}"
               class="btn btn-sm btn-outline">Semaine suivante →</a>
        </div>
        <div style="display:flex;gap:8px;margin-left:auto;flex-wrap:wrap;align-items:center">
            <input type="text" name="search" value="{{ $search ?? '' }}"
                   placeholder="Rechercher par nom..."
                   style="min-width:180px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
            <select name="department" style="min-width:150px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                <option value="">Departements</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept }}" {{ ($department ?? '') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
            <select name="shift_type" style="min-width:160px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                <option value="">Tous les shifts</option>
                <option value="matin"      {{ ($shift_type ?? '') === 'matin'      ? 'selected' : '' }}>Matin</option>
                <option value="apres_midi" {{ ($shift_type ?? '') === 'apres_midi' ? 'selected' : '' }}>Après-midi</option>
                <option value="journee"    {{ ($shift_type ?? '') === 'journee'    ? 'selected' : '' }}>Journée complète</option>
                <option value="garde"      {{ ($shift_type ?? '') === 'garde'      ? 'selected' : '' }}>Garde</option>
                {{-- NOUVEAU : filtre absence --}}
                <option value="absence"    {{ ($shift_type ?? '') === 'absence'    ? 'selected' : '' }}
                    style="color:#ef4444;font-weight:700">
                     Absences uniquement
                </option>
            </select>
            <select name="room_id" style="min-width:120px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                <option value="">Salles</option>
                @foreach($rooms ?? [] as $room)
                    <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                        {{ $room->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Rechercher</button>
            @if(($search ?? '') || ($department ?? '') || ($shift_type ?? ''))
                <a href="{{ route('planning.weekly', ['week' => $week, 'year' => $year]) }}" class="btn btn-outline">Reinitialiser</a>
            @endif
        </div>
    </form>
</div>



@endisset

@else
@isset($week, $year)
<div style="margin-bottom:20px;display:flex;align-items:center;gap:8px">
    <a href="{{ route('planning.weekly', ['week' => $week - 1, 'year' => $year]) }}" class="btn btn-sm btn-outline">← Semaine precedente</a>
    <span style="padding:8px 16px;background:var(--surface-2);border-radius:8px;font-size:0.85rem;font-weight:600">Semaine {{ $week }} — {{ $year }}</span>
    <a href="{{ route('planning.weekly', ['week' => $week + 1, 'year' => $year]) }}" class="btn btn-sm btn-outline">Semaine suivante →</a>
</div>
@endisset
@endif

{{-- TABLEAU HEBDOMADAIRE --}}
<div class="card" style="overflow-x:auto">
    <div class="card-body" style="padding:0">
        <table style="width:100%;border-collapse:collapse;font-size:0.85rem">
            <thead>
                <tr style="background:var(--surface-2)">
                    <th style="padding:16px 12px;text-align:left;min-width:200px;position:sticky;left:0;background:var(--surface-2);z-index:10">Employe</th>
                    <th style="padding:16px 12px;text-align:left;min-width:120px;">Salle</th>
                    @foreach($weekDays as $date => $day)
                    <th style="padding:12px 8px;text-align:center;min-width:140px;white-space:nowrap">
                        <div style="font-weight:600;color:var(--primary)">{{ ucfirst($day['day_name']) }}</div>
                        <div style="font-size:0.75rem;color:var(--text-muted)">{{ $day['day_number'] }} {{ $day['date']->locale('fr')->monthName }}</div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    //  Filtre absence : ne garder que les employés avec au moins 1 absence cette semaine
                    $displayEmployees = $employees;
                    if ($filterAbsence) {
                        $displayEmployees = $employees->filter(function($emp) use ($weekDays) {
                            foreach ($weekDays as $day) {
                                if ($emp->hasApprovedAbsenceOn($day['date'])) return true;
                            }
                            return false;
                        });
                    }
                @endphp

                @forelse($displayEmployees as $emp)
                @php
                    $empPlannings = $plannings->get($emp->id, collect());
                    $shiftFilter  = ($shift_type ?? '');
                    // Pour les filtres shifts normaux (pas absence), filtrer les plannings
                    if (!$isEmployee && $shiftFilter && $shiftFilter !== 'absence') {
                        $empPlannings = $empPlannings->filter(fn($p) => $p->shift_type === $shiftFilter);
                    }
                    $roomCounts     = $empPlannings->whereNotNull('room')->groupBy('room')->map->count();
                    $mostCommonRoom = $roomCounts->isNotEmpty() ? $roomCounts->sortDesc()->keys()->first() : null;
                @endphp
                <tr style="border-bottom:1px solid var(--border)" data-employee-id="{{ $emp->id }}">

                    <td style="padding:12px;position:sticky;left:0;background:white;z-index:5;box-shadow:2px 0 4px rgba(0,0,0,0.05)">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg, var(--primary), #1a8fa5);color:white;font-weight:600;font-size:0.75rem;display:flex;align-items:center;justify-content:center">
                                {{ strtoupper(substr($emp->first_name, 0, 1)) }}{{ strtoupper(substr($emp->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <span style="font-weight:600;color:var(--text-primary)">{{ $emp->full_name }}</span>
                                <div style="font-size:0.7rem;color:var(--text-muted)">{{ $emp->department }}</div>
                            </div>
                        </div>
                    </td>

                    <td style="padding:12px;">
                        @if(!$isEmployee)
                            <select onchange="updateRoom(this)" data-employee="{{ $emp->id }}"
                                data-start="{{ $startOfWeek->format('Y-m-d') }}"
                                data-end="{{ $endOfWeek->format('Y-m-d') }}"
                                style="padding:6px;border-radius:8px;border:1px solid var(--border);font-size:0.85rem;">
                                <option value="">Choisir salle</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ $room->name == $mostCommonRoom ? 'selected' : '' }}>{{ $room->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <span style="font-size:0.85rem;color:var(--text-muted)">{{ $mostCommonRoom ?? '—' }}</span>
                        @endif
                    </td>

                    @foreach($weekDays as $date => $day)
                    @php
                        $dayPlannings = $empPlannings->filter(function($p) use ($day) {
                            return \Carbon\Carbon::parse($p->date)->format('Y-m-d') === $day['date']->format('Y-m-d');
                        })->values();
                        $isAbsent = $emp->hasApprovedAbsenceOn($day['date']);
                    @endphp
                    <td style="padding:6px 8px;text-align:center;vertical-align:top;min-height:60px"
                        data-date="{{ $day['date']->format('Y-m-d') }}"
                        data-employee="{{ $emp->id }}"
                        @if(!$isAbsent && !$isEmployee)
                            ondragover="allowDrop(event)"
                            ondrop="drop(event, '{{ $day['date']->format('Y-m-d') }}', {{ $emp->id }})"
                        @endif>

                       @if($isAbsent)
    <div style="background:linear-gradient(135deg,#ef4444,#f87171);color:white;padding:8px 12px;border-radius:8px;font-size:0.75rem;font-weight:700;min-height:48px;display:flex;align-items:center;justify-content:center;">
        ABS
    </div>

@elseif($filterAbsence)
    <div style="min-height:48px"></div>

@elseif($dayPlannings->isNotEmpty())
                            <div style="display:flex;flex-direction:column;gap:4px">
                                @foreach($dayPlannings as $dayPlanning)
                                <div
                                    @if(!$isEmployee)
                                        draggable="true"
                                        ondragstart="drag(event, {{ $dayPlanning->id }})"
                                    @endif
                                    data-planning-id="{{ $dayPlanning->id }}"
                                    onclick="openEditShiftModal({{ $dayPlanning->id }},'{{ $dayPlanning->shift_type }}','{{ substr($dayPlanning->shift_start ?? '', 0, 5) }}','{{ substr($dayPlanning->shift_end ?? '', 0, 5) }}',@js($dayPlanning->notes ?? ''),@js($dayPlanning->room ?? ''))"
                                    style="cursor:pointer;transition:transform 0.15s,opacity 0.15s"
                                    onmouseover="this.style.transform='scale(1.03)'"
                                    onmouseout="this.style.transform='scale(1)'">

                                    @if($dayPlanning->shift_type === 'journee')
                                    <div style="background:linear-gradient(135deg,#10b981,#34d399);color:white;padding:6px 8px;border-radius:6px;font-size:0.72rem;position:relative">
                                        <div style="font-weight:700">Journée</div>
                                        <div>{{ substr($dayPlanning->shift_start ?? '', 0, 5) }} – {{ substr($dayPlanning->shift_end ?? '', 0, 5) }}</div>
                                        @if($dayPlanning->notes)<div style="position:absolute;top:4px;right:5px;font-size:0.6rem" title="{{ $dayPlanning->notes }}"></div>@endif
                                    </div>
                                    @elseif($dayPlanning->shift_type === 'matin')
                                    <div style="background:linear-gradient(135deg,#0ea5e9,#38bdf8);color:white;padding:6px 8px;border-radius:6px;font-size:0.72rem;position:relative">
                                        <div style="font-weight:700">Matin</div>
                                        <div>{{ substr($dayPlanning->shift_start ?? '', 0, 5) }} – {{ substr($dayPlanning->shift_end ?? '', 0, 5) }}</div>
                                        @if($dayPlanning->notes)<div style="position:absolute;top:4px;right:5px;font-size:0.6rem" title="{{ $dayPlanning->notes }}"></div>@endif
                                    </div>
                                    @elseif($dayPlanning->shift_type === 'apres_midi')
                                    <div style="background:linear-gradient(135deg,#f59e0b,#fbbf24);color:white;padding:6px 8px;border-radius:6px;font-size:0.72rem;position:relative">
                                        <div style="font-weight:700">Apres-midi</div>
                                        <div>{{ substr($dayPlanning->shift_start ?? '', 0, 5) }} – {{ substr($dayPlanning->shift_end ?? '', 0, 5) }}</div>
                                        @if($dayPlanning->notes)<div style="position:absolute;top:4px;right:5px;font-size:0.6rem" title="{{ $dayPlanning->notes }}">📝</div>@endif
                                    </div>
                                    @elseif($dayPlanning->shift_type === 'garde')
                                    <div style="background:linear-gradient(135deg,#d766cd,#ef9be8);color:white;padding:6px 8px;border-radius:6px;font-size:0.72rem;position:relative">
                                        <div style="font-weight:700">Garde</div>
                                        <div>{{ substr($dayPlanning->shift_start ?? '', 0, 5) }} - {{ substr($dayPlanning->shift_end ?? '', 0, 5) }}</div>
                                        @if($dayPlanning->notes)<div style="position:absolute;top:4px;right:5px;font-size:0.6rem" title="{{ $dayPlanning->notes }}">📝</div>@endif
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>

                        @else
                            @if(!$isEmployee)
                            <div onclick="openQuickAddModal('{{ $day['date']->format('Y-m-d') }}', {{ $emp->id }})"
                                 style="color:var(--text-muted);font-size:0.75rem;min-height:48px;display:flex;align-items:center;justify-content:center;border:2px dashed var(--border);border-radius:6px;cursor:pointer;transition:all 0.2s"
                                 onmouseover="this.style.background='rgba(14,165,233,0.07)';this.style.borderColor='var(--primary)';this.style.color='var(--primary)'"
                                 onmouseout="this.style.background='';this.style.borderColor='var(--border)';this.style.color='var(--text-muted)'">
                                + Creer shift
                            </div>
                            @endif
                        @endif
                    </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="padding:40px;text-align:center;color:var(--text-muted)">
                        @if($filterAbsence)
                        <div style="font-size:2rem;margin-bottom:8px"></div>
                        <div>Aucune absence cette semaine</div>
                        @else
                        <div style="font-size:2rem;margin-bottom:8px"></div>
                        <div>Aucun employe trouve</div>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
const IS_EMPLOYEE = {{ $isEmployee ? 'true' : 'false' }};
let draggedPlanningId = null;

function drag(event, planningId) {
    if (IS_EMPLOYEE) return;
    draggedPlanningId = planningId;
    event.dataTransfer.setData("text/plain", planningId);
    event.target.closest('[data-planning-id]').style.opacity = '0.5';
}
function allowDrop(event) { if (IS_EMPLOYEE) return; event.preventDefault(); event.target.closest('td').style.background = 'rgba(14,165,233,0.08)'; }
function drop(event, newDate, newEmployeeId) {
    if (IS_EMPLOYEE) return;
    event.preventDefault();
    event.target.closest('td').style.background = '';
    if (!draggedPlanningId) return;
    fetch('{{ route("planning.dragDrop") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        body: JSON.stringify({ planning_id: draggedPlanningId, new_date: newDate, new_employee_id: newEmployeeId })
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); else alert('Erreur: ' + (data.error || 'Inconnue')); }).catch(() => alert('Erreur réseau'));
    draggedPlanningId = null;
}
document.addEventListener('dragend', e => { const el = e.target.closest('[data-planning-id]'); if (el) el.style.opacity = '1'; });

function openPlanningModal() { if (IS_EMPLOYEE) return; document.getElementById('planningModal').style.display = 'block'; document.body.style.overflow = 'hidden'; }
function closePlanningModal() { const m = document.getElementById('planningModal'); if (m) { m.style.display = 'none'; document.body.style.overflow = 'auto'; } }

let currentEditPlanningId = null;
function openEditShiftModal(id, shiftType, shiftStart, shiftEnd, notes, room) {
    currentEditPlanningId = id;
    document.getElementById('editShiftForm').action = '/planning/' + id;
    document.getElementById('editShiftType').value  = shiftType;
    document.getElementById('editShiftStart').value = shiftStart;
    document.getElementById('editShiftEnd').value   = shiftEnd;
    document.getElementById('editShiftNotes').value = notes || '';
    document.getElementById('editShiftRoom').value  = room || '';
    document.getElementById('editShiftModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function closeEditShiftModal() { document.getElementById('editShiftModal').style.display = 'none'; document.body.style.overflow = 'auto'; }
function deleteShift() {
    if (IS_EMPLOYEE) return;
    if (!confirm('Supprimer ce shift ?')) return;
    fetch('/planning/' + currentEditPlanningId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' }
    }).then(r => { if (r.ok) location.reload(); else r.json().then(d => alert('Erreur: ' + (d.message || d.error || 'Inconnue'))).catch(() => alert('Erreur suppression')); }).catch(e => alert('Erreur réseau: ' + e.message));
}

function openQuickAddModal(date, employeeId) {
    if (IS_EMPLOYEE) return;
    document.getElementById('qaDate').value = date;
    document.getElementById('qaEmployeeId').value = employeeId;
    document.getElementById('quickAddModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function closeQuickAddModal() { const m = document.getElementById('quickAddModal'); if (m) { m.style.display = 'none'; document.body.style.overflow = 'auto'; } }

function updateRoom(select) {
    if (IS_EMPLOYEE) return;
    fetch('/planning/update-room', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ employee_id: select.dataset.employee, room_id: select.value, start: select.dataset.start, end: select.dataset.end })
    }).then(res => res.json()).then(() => console.log('Salle mise a jour')).catch(err => console.error(err));
}

window.onclick = function(e) {
    ['planningModal','editShiftModal','quickAddModal'].forEach(id => {
        const m = document.getElementById(id);
        if (m && e.target === m) { m.style.display = 'none'; document.body.style.overflow = 'auto'; }
    });
};
</script>

@endsection
