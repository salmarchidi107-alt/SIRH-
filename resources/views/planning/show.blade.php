@extends('layouts.app')

@section('title', 'Planning - '.$employee->full_name)
@section('page-title', 'Planning de '.$employee->full_name)

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <div style="display:flex;align-items:center;gap:16px">
            <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg, var(--primary), #1a8fa5);color:white;font-weight:700;font-size:1.2rem;display:flex;align-items:center;justify-content:center">
                {{ strtoupper(substr($employee->first_name, 0, 1)) }}{{ strtoupper(substr($employee->last_name, 0, 1)) }}
            </div>
            <div>
                <h1 style="font-size:1.5rem;margin:0">{{ $employee->full_name }}</h1>
                <p style="margin:4px 0 0;color:var(--text-muted)">{{ $employee->department }} - {{ $employee->position }}</p>
            </div>
        </div>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('planning.weekly', ['search' => $employee->full_name]) }}" class="btn btn-outline">← Planning</a>
    </div>
</div>

<!-- Week Navigation -->
<div class="filters-bar" style="margin-bottom: 20px;">
    <div style="display:flex;align-items:center;gap:8px">
        <a href="{{ route('planning.show', ['employee' => $employee->id, 'week' => $week - 1, 'year' => $year]) }}" class="btn btn-sm btn-outline">← Semaine précédente</a>
        <span style="font-weight:600;padding:0 16px">Semaine {{ $week }} - {{ $startOfWeek->format('d') }} au {{ $endOfWeek->format('d M Y') }}</span>
        <a href="{{ route('planning.show', ['employee' => $employee->id, 'week' => $week + 1, 'year' => $year]) }}" class="btn btn-sm btn-outline">Semaine suivante →</a>
    </div>
    <div style="display:flex;gap:8px;margin-left:auto">
        <a href="{{ route('planning.show', ['employee' => $employee->id, 'week' => now()->weekOfYear, 'year' => now()->year]) }}" class="btn btn-primary">Cette semaine</a>
    </div>
</div>

<!-- Individual Weekly Planning Table -->
<div class="card" style="overflow-x:auto">
    <div class="card-body" style="padding:0">
        <table style="width:100%;border-collapse:collapse;font-size:0.9rem">
            <thead>
                <tr style="background:var(--surface-2)">
                    <th style="padding:16px 12px;text-align:left;min-width:150px;position:sticky;left:0;background:var(--surface-2);z-index:10">
                        Horaire
                    </th>
                    @foreach($weekDays as $date => $day)
                    <th style="padding:12px 8px;text-align:center;min-width:160px;white-space:nowrap">
                        <div style="font-weight:600;color:var(--primary);font-size:1rem">{{ ucfirst($day['day_name']) }}</div>
                        <div style="font-size:0.8rem;color:var(--text-muted)">{{ $day['day_number'] }} {{ $day['date']->locale('fr')->monthName }}</div>
                        <div style="font-size:0.7rem;color:var(--text-muted)">{{ $day['date']->format('Y') }}</div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <!-- Morning Row -->
                <tr style="border-bottom:1px solid var(--border);background:rgba(14, 165, 233, 0.03)">
                    <td style="padding:16px;position:sticky;left:0;background:rgba(14, 165, 233, 0.05);z-index:5;font-weight:600;color:#0ea5e9">
                         MATIN
                    </td>
                    @foreach($weekDays as $date => $day)
                    @php 
                        $dayPlanning = $plannings->get($date);
                        $isMatin = $dayPlanning && in_array($dayPlanning->shift_type, ['matin', 'journee']);
                    @endphp
                    <td style="padding:12px;text-align:center;vertical-align:middle">
                        @if($isMatin)
                        <div onclick="openEditModal({{ $dayPlanning->id }}, '{{ $dayPlanning->shift_type }}', '{{ $dayPlanning->shift_start }}', '{{ $dayPlanning->shift_end }}', '{{ $dayPlanning->notes ?? '' }}')" 
                             style="background:linear-gradient(135deg, #0ea5e9, #38bdf8);color:white;padding:16px 12px;border-radius:10px;box-shadow:0 4px 12px rgba(14, 165, 233, 0.3);cursor:pointer;position:relative" title="Cliquez pour modifier">
                            <div style="font-weight:700;font-size:1.1rem">{{ $dayPlanning->shift_start }}</div>
                            <div style="font-size:0.75rem;opacity:0.9">Debut</div>
                            @if($dayPlanning->notes)
                            <div style="margin-top:8px;font-size:0.7rem;opacity:0.8;border-top:1px solid rgba(255,255,255,0.3);padding-top:8px">
                                {{ $dayPlanning->notes }}
                            </div>
                            @endif
                        </div>
                        @else
                        <div onclick="openAddModal('{{ $date }}', 'matin')" style="color:var(--text-muted);font-size:0.8rem;cursor:pointer;padding:8px;border:2px dashed var(--border);border-radius:8px" title="Cliquez pour ajouter">+ Ajouter</div>
                        @endif
                    </td>
                    @endforeach
                </tr>
                
                <!-- Afternoon Row -->
                <tr style="border-bottom:1px solid var(--border);background:rgba(245, 158, 11, 0.03)">
                    <td style="padding:16px;position:sticky;left:0;background:rgba(245, 158, 11, 0.05);z-index:5;font-weight:600;color:#f59e0b">
                         APRÈS-MIDI
                    </td>
                    @foreach($weekDays as $date => $day)
                    @php 
                        $dayPlanning = $plannings->get($date);
                        $isApresMidi = $dayPlanning && in_array($dayPlanning->shift_type, ['apres_midi', 'journee']);
                    @endphp
                    <td style="padding:12px;text-align:center;vertical-align:middle">
                        @if($isApresMidi)
                        <div onclick="openEditModal({{ $dayPlanning->id }}, '{{ $dayPlanning->shift_type }}', '{{ $dayPlanning->shift_start }}', '{{ $dayPlanning->shift_end }}', '{{ $dayPlanning->notes ?? '' }}')" 
                             style="background:linear-gradient(135deg, #f59e0b, #fbbf24);color:white;padding:16px 12px;border-radius:10px;box-shadow:0 4px 12px rgba(245, 158, 11, 0.3);cursor:pointer;position:relative" title="Cliquez pour modifier">
                            <div style="font-weight:700;font-size:1.1rem">{{ $dayPlanning->shift_end }}</div>
                            <div style="font-size:0.75rem;opacity:0.9">Fin</div>
                            @if($dayPlanning->notes)
                            <div style="margin-top:8px;font-size:0.7rem;opacity:0.8;border-top:1px solid rgba(255,255,255,0.3);padding-top:8px">
                                {{ $dayPlanning->notes }}
                            </div>
                            @endif
                        </div>
                        @else
                        <div onclick="openAddModal('{{ $date }}', 'apres_midi')" style="color:var(--text-muted);font-size:0.8rem;cursor:pointer;padding:8px;border:2px dashed var(--border);border-radius:8px" title="Cliquez pour ajouter">+ Ajouter</div>
                        @endif
                    </td>
                    @endforeach
                </tr>
                
                <!-- Night Row -->
                <tr style="border-bottom:1px solid var(--border);background:rgba(99, 102, 241, 0.03)">
                    <td style="padding:16px;position:sticky;left:0;background:rgba(99, 102, 241, 0.05);z-index:5;font-weight:600;color:#6366f1">
                        NUIT
                    </td>
                    @foreach($weekDays as $date => $day)
                    @php 
                        $dayPlanning = $plannings->get($date);
                        $isNuit = $dayPlanning && $dayPlanning->shift_type === 'nuit';
                    @endphp
                    <td style="padding:12px;text-align:center;vertical-align:middle">
                        @if($isNuit)
                        <div onclick="openEditModal({{ $dayPlanning->id }}, '{{ $dayPlanning->shift_type }}', '{{ $dayPlanning->shift_start }}', '{{ $dayPlanning->shift_end }}', '{{ $dayPlanning->notes ?? '' }}')" 
                             style="background:linear-gradient(135deg, #6366f1, #818cf8);color:white;padding:16px 12px;border-radius:10px;box-shadow:0 4px 12px rgba(99, 102, 241, 0.3);cursor:pointer;position:relative" title="Cliquez pour modifier">
                            <div style="font-weight:700;font-size:1rem">{{ $dayPlanning->shift_start }} - {{ $dayPlanning->shift_end }}</div>
                            <div style="font-size:0.75rem;opacity:0.9">Nuit complète</div>
                            @if($dayPlanning->notes)
                            <div style="margin-top:8px;font-size:0.7rem;opacity:0.8;border-top:1px solid rgba(255,255,255,0.3);padding-top:8px">
                                {{ $dayPlanning->notes }}
                            </div>
                            @endif
                        </div>
                        @else
                        <div onclick="openAddModal('{{ $date }}', 'nuit')" style="color:var(--text-muted);font-size:0.8rem;cursor:pointer;padding:8px;border:2px dashed var(--border);border-radius:8px" title="Cliquez pour ajouter">+ Ajouter</div>
                        @endif
                    </td>
                    @endforeach
                </tr>
                
                <!-- Garde Row -->
                <tr style="background:rgba(239, 68, 68, 0.03)">
                    <td style="padding:16px;position:sticky;left:0;background:rgba(239, 68, 68, 0.05);z-index:5;font-weight:600;color:#ef4444">
                         GARDE
                    </td>
                    @foreach($weekDays as $date => $day)
                    @php 
                        $dayPlanning = $plannings->get($date);
                        $isGarde = $dayPlanning && $dayPlanning->shift_type === 'garde';
                    @endphp
                    <td style="padding:12px;text-align:center;vertical-align:middle">
                        @if($isGarde)
                        <div onclick="openEditModal({{ $dayPlanning->id }}, '{{ $dayPlanning->shift_type }}', '{{ $dayPlanning->shift_start }}', '{{ $dayPlanning->shift_end }}', '{{ $dayPlanning->notes ?? '' }}')" 
                             style="background:linear-gradient(135deg, #ef4444, #f87171);color:white;padding:16px 12px;border-radius:10px;box-shadow:0 4px 12px rgba(239, 68, 68, 0.3);cursor:pointer;position:relative" title="Cliquez pour modifier">
                            <div style="font-weight:700;font-size:1rem">{{ $dayPlanning->shift_start }} - {{ $dayPlanning->shift_end }}</div>
                            <div style="font-size:0.75rem;opacity:0.9">Garde</div>
                            @if($dayPlanning->notes)
                            <div style="margin-top:8px;font-size:0.7rem;opacity:0.8;border-top:1px solid rgba(255,255,255,0.3);padding-top:8px">
                                {{ $dayPlanning->notes }}
                            </div>
                            @endif
                        </div>
                        @else
                        <div onclick="openAddModal('{{ $date }}', 'garde')" style="color:var(--text-muted);font-size:0.8rem;cursor:pointer;padding:8px;border:2px dashed var(--border);border-radius:8px" title="Cliquez pour ajouter">+ Ajouter</div>
                        @endif
                    </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5)">
    <div class="modal-content" style="background-color:white;margin:5% auto;padding:24px;border-radius:12px;width:90%;max-width:450px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2 style="margin:0;font-size:1.25rem">Modifier le shift</h2>
            <button type="button" onclick="closeEditModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted)">×</button>
        </div>
        
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            <input type="hidden" name="date" id="editDate">
            
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Type de shift</label>
                <select name="shift_type" id="editShiftType" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                    <option value="matin"> Matin</option>
                    <option value="apres_midi"> Après-midi</option>
                    <option value="journee"> Journée complète</option>
                    <option value="nuit"> Nuit</option>
                    <option value="garde"> Garde</option>
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
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Notes (optionnel)</label>
                <textarea name="notes" id="editNotes" rows="2" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;resize:vertical"></textarea>
            </div>
            
            <div style="display:flex;gap:12px;justify-content:space-between">
                <button type="button" onclick="deletePlanning()" class="btn btn-outline" style="color:var(--danger);border-color:var(--danger)">Supprimer</button>
                <div style="display:flex;gap:12px">
                    <button type="button" onclick="closeEditModal()" class="btn btn-outline">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5)">
    <div class="modal-content" style="background-color:white;margin:5% auto;padding:24px;border-radius:12px;width:90%;max-width:450px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2 style="margin:0;font-size:1.25rem">Ajouter un shift</h2>
            <button type="button" onclick="closeAddModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted)">×</button>
        </div>
        
        <form method="POST" action="{{ route('planning.store') }}">
            @csrf
            
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            <input type="hidden" name="date" id="addDate">
            <input type="hidden" name="shift_type" id="addShiftType">
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de début</label>
                    <input type="time" name="shift_start" id="addShiftStart" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                </div>
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de fin</label>
                    <input type="time" name="shift_end" id="addShiftEnd" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                </div>
            </div>
            
            <div style="margin-bottom:20px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Notes (optionnel)</label>
                <textarea name="notes" rows="2" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;resize:vertical"></textarea>
            </div>
            
            <div style="display:flex;gap:12px;justify-content:flex-end">
                <button type="button" onclick="closeAddModal()" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentPlanningId = null;

function openEditModal(id, shiftType, shiftStart, shiftEnd, notes) {
    currentPlanningId = id;
    document.getElementById('editForm').action = '/planning/' + id;
    document.getElementById('editShiftType').value = shiftType;
    document.getElementById('editShiftStart').value = shiftStart;
    document.getElementById('editShiftEnd').value = shiftEnd;
    document.getElementById('editNotes').value = notes || '';
    document.getElementById('editModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function deletePlanning() {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce shift?')) {
        fetch('/planning/' + currentPlanningId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}

function openAddModal(date, shiftType) {
    document.getElementById('addDate').value = date;
    document.getElementById('addShiftType').value = shiftType;
    document.getElementById('addModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

window.onclick = function(event) {
    var editModal = document.getElementById('editModal');
    var addModal = document.getElementById('addModal');
    if (event.target == editModal) {
        closeEditModal();
    }
    if (event.target == addModal) {
        closeAddModal();
    }
}
</script>

<!-- Summary Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:20px">
    @php
        $shiftCounts = $plannings->groupBy('shift_type')->map->count();
    @endphp
    <div class="stat-card" style="padding:16px;background:var(--surface-2);border-radius:var(--radius-sm);border:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg, #0ea5e9, #38bdf8);color:white;display:flex;align-items:center;justify-content:center;font-size:1.2rem"></div>
            <div>
                <div style="font-size:1.5rem;font-weight:700">{{ $shiftCounts->get('matin', 0) + $shiftCounts->get('journee', 0) }}</div>
                <div style="font-size:0.75rem;color:var(--text-muted)">Jours Matin</div>
            </div>
        </div>
    </div>
    <div class="stat-card" style="padding:16px;background:var(--surface-2);border-radius:var(--radius-sm);border:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg, #f59e0b, #fbbf24);color:white;display:flex;align-items:center;justify-content:center;font-size:1.2rem"></div>
            <div>
                <div style="font-size:1.5rem;font-weight:700">{{ $shiftCounts->get('apres_midi', 0) + $shiftCounts->get('journee', 0) }}</div>
                <div style="font-size:0.75rem;color:var(--text-muted)">Jours Apres-midi</div>
            </div>
        </div>
    </div>
    <div class="stat-card" style="padding:16px;background:var(--surface-2);border-radius:var(--radius-sm);border:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg, #6366f1, #818cf8);color:white;display:flex;align-items:center;justify-content:center;font-size:1.2rem"></div>
            <div>
                <div style="font-size:1.5rem;font-weight:700">{{ $shiftCounts->get('nuit', 0) }}</div>
                <div style="font-size:0.75rem;color:var(--text-muted)">Jours Nuit</div>
            </div>
        </div>
    </div>
    <div class="stat-card" style="padding:16px;background:var(--surface-2);border-radius:var(--radius-sm);border:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg, #ef4444, #f87171);color:white;display:flex;align-items:center;justify-content:center;font-size:1.2rem"></div>
            <div>
                <div style="font-size:1.5rem;font-weight:700">{{ $shiftCounts->get('garde', 0) }}</div>
                <div style="font-size:0.75rem;color:var(--text-muted)">Jours Garde</div>
            </div>
        </div>
    </div>
</div>

@endsection
