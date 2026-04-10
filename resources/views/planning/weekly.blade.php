@extends('layouts.app')

@section('title', 'Planning')
@section('page-title', 'Planning')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        @if(isset($isEmployee) && $isEmployee)
            <h1>Votre Planning Personnel</h1>
            <p>Semaine du {{ $startOfWeek->format('d') }} au {{ $endOfWeek->format('d M Y') }}</p>
        @else
            <h1>Planning</h1>
            <p>Semaine du {{ $startOfWeek->format('d') }} au {{ $endOfWeek->format('d M Y') }}</p>
        @endif
    </div>
    @if(!(isset($isEmployee) && $isEmployee))
    <div class="page-header-right" style="display:flex;gap:8px">
        <a href="{{ route('planning.monthly') }}" class="btn btn-outline">Vue Mensuelle</a>
        <a href="{{ route('planning.weekly.pdf', request()->query()) }}" class="btn btn-outline" target="_blank">Exporter PDF</a>
        <a href="{{ route('planning.templates.index') }}" class="btn btn-outline">Semaines Types</a>
        <a href="{{ route('planning.templates.apply') }}" class="btn btn-outline">➕ Appliquer Semaine Type</a>
        <button type="button" class="btn btn-primary" onclick="openPlanningModal()">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Créer un planning
        </button>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════
     MODAL — CRÉER UN PLANNING
══════════════════════════════════════ --}}
<div id="planningModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5)">
    <div style="background:white;margin:5% auto;padding:24px;border-radius:12px;width:90%;max-width:500px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2 style="margin:0;font-size:1.25rem">Créer un planning</h2>
            <button type="button" onclick="closePlanningModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted)">×</button>
        </div>
        <form method="POST" action="{{ route('planning.store') }}">
            @csrf
            <div style="margin-bottom:16px">
                @if(!isset($isEmployee) || !$isEmployee)
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Employé</label>
                <select name="employee_id" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                    <option value="">Sélectionner un employé</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }} - {{ $emp->department }}</option>
                    @endforeach
                </select>
                @else
                <input type="hidden" name="employee_id" value="{{ $employees->first()->id }}">
                @endif
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Date</label>
                <input type="date" name="date" id="createDate" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Type de shift</label>
                <select name="shift_type" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                    <option value="matin">Matin</option>
                    <option value="apres_midi">Après-midi</option>
                    <option value="journee">Journée complète</option>
                    <option value="nuit">Nuit</option>
                    <option value="garde">Garde</option>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de début</label>
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
            <div style="display:flex;gap:12px;justify-content:flex-end">
                <button type="button" onclick="closePlanningModal()" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════
     MODAL — MODIFIER / SUPPRIMER UN SHIFT
══════════════════════════════════════ --}}
<div id="editShiftModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5)">
    <div style="background:white;margin:5% auto;padding:24px;border-radius:12px;width:90%;max-width:480px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2 style="margin:0;font-size:1.25rem" id="editShiftTitle">Modifier le shift</h2>
            <button type="button" onclick="closeEditShiftModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted)">×</button>
        </div>
        <form id="editShiftForm" method="POST">
            @csrf
            @method('PUT')
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Type de shift</label>
                <select name="shift_type" id="editShiftType" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                    <option value="matin">Matin</option>
                    <option value="apres_midi">Après-midi</option>
                    <option value="journee">Journée complète</option>
                    <option value="nuit">Nuit</option>
                    <option value="garde">Garde</option>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de début</label>
                    <input type="time" name="shift_start" id="editShiftStart" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                </div>
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de fin</label>
                    <input type="time" name="shift_end" id="editShiftEnd" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                </div>
            </div>
            <div style="margin-bottom:20px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Notes</label>
                <textarea name="notes" id="editShiftNotes" rows="3" placeholder="Ajouter une note..." style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;resize:vertical"></textarea>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center">
                {{-- Supprimer à gauche --}}
                <button type="button" id="deleteShiftBtn" onclick="deleteShift()"
                    style="padding:8px 16px;border:1px solid #ef4444;border-radius:8px;background:white;color:#ef4444;font-size:0.875rem;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:6px">
                    🗑 Supprimer
                </button>
                {{-- Annuler + Enregistrer à droite --}}
                <div style="display:flex;gap:10px">
                    <button type="button" onclick="closeEditShiftModal()" class="btn btn-outline">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════
     MODAL — QUICK ADD (cellule vide)
══════════════════════════════════════ --}}
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
                    <option value="apres_midi">Après-midi</option>
                    <option value="journee">Journée complète</option>
                    <option value="nuit">Nuit</option>
                    <option value="garde">Garde</option>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de début</label>
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
            <div style="display:flex;gap:12px;justify-content:flex-end">
                <button type="button" onclick="closeQuickAddModal()" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

@if(!(isset($isEmployee) && $isEmployee))
<!-- Filters Bar -->
<div class="filters-bar" style="margin-bottom:20px">
    <form method="GET" action="{{ route('planning.weekly') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
        <div style="display:flex;align-items:center;gap:8px">
            <a href="{{ route('planning.weekly', ['week' => $week - 1, 'year' => $year, 'search' => $search, 'department' => $department]) }}" class="btn btn-sm btn-outline">← Semaine précédente</a>
            <select name="week" onchange="this.form.submit()" style="min-width:120px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                @for($w = 1; $w <= 52; $w++)
                    <option value="{{ $w }}" {{ $week == $w ? 'selected' : '' }}>Semaine {{ $w }}</option>
                @endfor
            </select>
            <select name="year" onchange="this.form.submit()" style="min-width:100px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <a href="{{ route('planning.weekly', ['week' => $week + 1, 'year' => $year, 'search' => $search, 'department' => $department]) }}" class="btn btn-sm btn-outline">Semaine suivante →</a>
        </div>
        <div style="display:flex;gap:8px;margin-left:auto">
            <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher par nom..." style="min-width:180px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
            <select name="department" style="min-width:150px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                <option value="">Tous les services</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ $department == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Rechercher</button>
            @if($search || $department)
                <a href="{{ route('planning.weekly', ['week' => $week, 'year' => $year]) }}" class="btn btn-outline">Réinitialiser</a>
            @endif
        </div>
    </form>
</div>

<div style="background:var(--surface-2);padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:0.85rem;color:var(--text-muted)">
    💡 <strong>Glisser-Déposer :</strong> Déplacez un shift d'un employé vers un autre. &nbsp;|&nbsp; 🖊 <strong>Cliquez</strong> sur un shift pour le modifier ou le supprimer.
</div>
@endif

{{-- ══════════════════════════════════════
     TABLEAU PLANNING HEBDOMADAIRE
══════════════════════════════════════ --}}
<div class="card" style="overflow-x:auto">
    <div class="card-body" style="padding:0">
        <table style="width:100%;border-collapse:collapse;font-size:0.85rem">
            <thead>
                <tr style="background:var(--surface-2)">
                    <th style="padding:16px 12px;text-align:left;min-width:200px;position:sticky;left:0;background:var(--surface-2);z-index:10">
                        Employé
                    </th>
                    @foreach($weekDays as $date => $day)
                    <th style="padding:12px 8px;text-align:center;min-width:140px;white-space:nowrap">
                        <div style="font-weight:600;color:var(--primary)">{{ ucfirst($day['day_name']) }}</div>
                        <div style="font-size:0.75rem;color:var(--text-muted)">{{ $day['day_number'] }} {{ $day['date']->locale('fr')->monthName }}</div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                @php
                    $empPlannings = $plannings->get($emp->id, collect());
                @endphp
                <tr style="border-bottom:1px solid var(--border)" data-employee-id="{{ $emp->id }}">

                    {{-- Colonne employé --}}
                    <td style="padding:12px;position:sticky;left:0;background:white;z-index:5;box-shadow:2px 0 4px rgba(0,0,0,0.05)">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg, var(--primary), #1a8fa5);color:white;font-weight:600;font-size:0.75rem;display:flex;align-items:center;justify-content:center">
                                {{ strtoupper(substr($emp->first_name, 0, 1)) }}{{ strtoupper(substr($emp->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <a href="{{ route('planning.show', ['employee' => $emp->id, 'week' => $week, 'year' => $year]) }}"
                                   style="font-weight:600;color:var(--primary);text-decoration:none">
                                    {{ $emp->full_name }}
                                </a>
                                <div style="font-size:0.7rem;color:var(--text-muted)">{{ $emp->department }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Cellules jours --}}
                    @foreach($weekDays as $date => $day)
                    @php
                        $dayPlanning = $empPlannings->firstWhere('date', $day['date']);
                    @endphp
                    <td style="padding:6px 8px;text-align:center;vertical-align:top;min-height:60px"
                        data-date="{{ $day['date']->format('Y-m-d') }}"
                        @if(!(isset($isEmployee) && $isEmployee))
                        ondragover="allowDrop(event)"
                        ondrop="drop(event, '{{ $day['date']->format('Y-m-d') }}', {{ $emp->id }})"
                        @endif>

                        @if($dayPlanning)
                        {{-- SHIFT EXISTANT — cliquable + draggable --}}
                        <div
                            @if(!(isset($isEmployee) && $isEmployee))
                            draggable="true"
                            ondragstart="drag(event, {{ $dayPlanning->id }})"
                            @endif
                            data-planning-id="{{ $dayPlanning->id }}"
                            onclick="openEditShiftModal(
                                {{ $dayPlanning->id }},
                                '{{ $dayPlanning->shift_type }}',
                                '{{ substr($dayPlanning->shift_start ?? '', 0, 5) }}',
                                '{{ substr($dayPlanning->shift_end ?? '', 0, 5) }}',
                                @js($dayPlanning->notes ?? '')
                            )"
                            style="display:flex;flex-direction:column;gap:4px;cursor:pointer;transition:transform 0.15s,opacity 0.15s"
                            onmouseover="this.style.transform='scale(1.03)';this.style.opacity='0.9'"
                            onmouseout="this.style.transform='scale(1)';this.style.opacity='1'">

                            {{-- Bloc Matin --}}
                            @if(in_array($dayPlanning->shift_type, ['matin', 'journee']))
                            <div style="background:linear-gradient(135deg,#0ea5e9,#38bdf8);color:white;padding:6px 8px;border-radius:6px;font-size:0.72rem;position:relative">
                                <div style="font-weight:700">Matin</div>
                                <div>{{ substr($dayPlanning->shift_start ?? '', 0, 5) }}</div>
                                @if($dayPlanning->notes)
                                <div style="position:absolute;top:4px;right:5px;font-size:0.6rem;opacity:0.9" title="{{ $dayPlanning->notes }}">📝</div>
                                @endif
                            </div>
                            @endif

                            {{-- Bloc Après-midi --}}
                            @if(in_array($dayPlanning->shift_type, ['apres_midi', 'journee']))
                            <div style="background:linear-gradient(135deg,#f59e0b,#fbbf24);color:white;padding:6px 8px;border-radius:6px;font-size:0.72rem;position:relative">
                                <div style="font-weight:700">Après-midi</div>
                                <div>{{ substr($dayPlanning->shift_end ?? '', 0, 5) }}</div>
                                @if($dayPlanning->notes && $dayPlanning->shift_type === 'apres_midi')
                                <div style="position:absolute;top:4px;right:5px;font-size:0.6rem;opacity:0.9" title="{{ $dayPlanning->notes }}">📝</div>
                                @endif
                            </div>
                            @endif

                            {{-- Bloc Nuit --}}
                            @if($dayPlanning->shift_type === 'nuit')
                            <div style="background:linear-gradient(135deg,#6366f1,#818cf8);color:white;padding:6px 8px;border-radius:6px;font-size:0.72rem;position:relative">
                                <div style="font-weight:700">Nuit</div>
                                <div>{{ substr($dayPlanning->shift_start ?? '', 0, 5) }} - {{ substr($dayPlanning->shift_end ?? '', 0, 5) }}</div>
                                @if($dayPlanning->notes)
                                <div style="position:absolute;top:4px;right:5px;font-size:0.6rem;opacity:0.9" title="{{ $dayPlanning->notes }}">📝</div>
                                @endif
                            </div>
                            @endif

                            {{-- Bloc Garde --}}
                            @if($dayPlanning->shift_type === 'garde')
                            <div style="background:linear-gradient(135deg,#ef4444,#f87171);color:white;padding:6px 8px;border-radius:6px;font-size:0.72rem;position:relative">
                                <div style="font-weight:700">Garde</div>
                                <div>{{ substr($dayPlanning->shift_start ?? '', 0, 5) }} - {{ substr($dayPlanning->shift_end ?? '', 0, 5) }}</div>
                                @if($dayPlanning->notes)
                                <div style="position:absolute;top:4px;right:5px;font-size:0.6rem;opacity:0.9" title="{{ $dayPlanning->notes }}">📝</div>
                                @endif
                            </div>
                            @endif

                        </div>

                        @else
                        {{-- CELLULE VIDE — clic pour ajouter --}}
                        @if(!(isset($isEmployee) && $isEmployee))
                        <div onclick="openQuickAddModal('{{ $day['date']->format('Y-m-d') }}', {{ $emp->id }})"
                             style="color:var(--text-muted);font-size:0.75rem;min-height:48px;display:flex;align-items:center;justify-content:center;border:2px dashed var(--border);border-radius:6px;cursor:pointer;transition:all 0.2s"
                             onmouseover="this.style.background='rgba(14,165,233,0.07)';this.style.borderColor='var(--primary)';this.style.color='var(--primary)'"
                             onmouseout="this.style.background='';this.style.borderColor='var(--border)';this.style.color='var(--text-muted)'">
                            + Créer shift
                        </div>
                        @endif
                        @endif
                    </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:40px;text-align:center;color:var(--text-muted)">
                        <div style="font-size:2rem;margin-bottom:8px">📅</div>
                        <div>Aucun employé trouvé</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Légende --}}
<div style="display:flex;gap:20px;margin-top:16px;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:8px">
        <div style="width:16px;height:16px;background:linear-gradient(135deg,#0ea5e9,#38bdf8);border-radius:4px"></div>
        <span style="font-size:0.8rem">Matin</span>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
        <div style="width:16px;height:16px;background:linear-gradient(135deg,#f59e0b,#fbbf24);border-radius:4px"></div>
        <span style="font-size:0.8rem">Après-midi</span>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
        <div style="width:16px;height:16px;background:linear-gradient(135deg,#6366f1,#818cf8);border-radius:4px"></div>
        <span style="font-size:0.8rem">Nuit</span>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
        <div style="width:16px;height:16px;background:linear-gradient(135deg,#ef4444,#f87171);border-radius:4px"></div>
        <span style="font-size:0.8rem">Garde</span>
    </div>
    

{{-- ══════════════════════════════════════
     SCRIPTS
══════════════════════════════════════ --}}
<script>
// ── Drag & Drop ──────────────────────────────────
let draggedPlanningId = null;

function drag(event, planningId) {
    console.log('Drag start', planningId);
    draggedPlanningId = planningId;
    event.dataTransfer.setData("text/plain", planningId);
    event.target.closest('[data-planning-id]').style.opacity = '0.5';
}

function allowDrop(event) {
    event.preventDefault();
    console.log('Allow drop');
    event.target.closest('td').style.background = 'rgba(14,165,233,0.08)';
}

function drop(event, newDate, newEmployeeId) {
    event.preventDefault();
    console.log('Drop:', {planningId: draggedPlanningId, newDate, newEmployeeId});
    event.target.closest('td').style.background = '';
    if (!draggedPlanningId) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    fetch('{{ route("planning.dragDrop") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ 
            planning_id: draggedPlanningId, 
            new_date: newDate, 
            new_employee_id: newEmployeeId,
            _token: csrfToken 
        })
    })

    .then(r => {
        console.log('Response status', r.status);
        return r.json();
    })
    .then(data => { 
        console.log('Response data', data);
        if (data.success) location.reload(); else alert('Erreur: ' + (data.error || 'Inconnue')); 
    })
    .catch(err => { 
        console.error('Fetch error', err);
        alert('Erreur réseau'); 
    });

    draggedPlanningId = null;
}


document.addEventListener('dragend', e => {
    const el = e.target.closest('[data-planning-id]');
    if (el) el.style.opacity = '1';
});

// ── Modal Créer ──────────────────────────────────
function openPlanningModal() {
    document.getElementById('planningModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function closePlanningModal() {
    document.getElementById('planningModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ── Modal Modifier shift ─────────────────────────
let currentEditPlanningId = null;

function openEditShiftModal(id, shiftType, shiftStart, shiftEnd, notes) {
    currentEditPlanningId = id;
    document.getElementById('editShiftForm').action = '/planning/' + id;
    document.getElementById('editShiftType').value  = shiftType;
    document.getElementById('editShiftStart').value = shiftStart;
    document.getElementById('editShiftEnd').value   = shiftEnd;
    document.getElementById('editShiftNotes').value = notes || '';
    document.getElementById('editShiftModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function closeEditShiftModal() {
    document.getElementById('editShiftModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}
function deleteShift() {
    if (!confirm('Supprimer ce shift ?')) return;
    fetch('/planning/' + currentEditPlanningId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }).then(r => {
        if (r.ok) {
            location.reload();
        } else {
            r.json().then(data => {
                alert('Erreur suppression: ' + (data.message || data.error || 'Erreur serveur inconnue'));
            }).catch(() => alert('Erreur suppression (réseau)'));
        }
    }).catch(e => alert('Erreur réseau: ' + e.message));
}

// ── Modal Quick Add ──────────────────────────────
function openQuickAddModal(date, employeeId) {
    document.getElementById('qaDate').value       = date;
    document.getElementById('qaEmployeeId').value = employeeId;
    document.getElementById('quickAddModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function closeQuickAddModal() {
    document.getElementById('quickAddModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ── Fermer en cliquant hors modal ────────────────
window.onclick = function(e) {
    ['planningModal','editShiftModal','quickAddModal'].forEach(id => {
        const m = document.getElementById(id);
        if (e.target === m) {
            m.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
};
</script>

@endsection