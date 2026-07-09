@extends('layouts.app')

@section('title', 'Appliquer une Semaine Type')
@section('page-title', 'Appliquer un Modèle de Semaine')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1> Appliquer une Semaine Type</h1>
        <p>Appliquez un modèle de semaine à un employé ou un département entier</p>
    </div>
</div>

{{-- ── Erreurs de validation ── --}}
@if($errors->any())
<div style="margin-bottom:20px;padding:14px 18px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:12px;color:#b91c1c;font-size:0.9rem">
    <strong>⚠ Veuillez corriger les erreurs suivantes :</strong>
    <ul style="margin:8px 0 0 18px;padding:0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('planning.templates.apply') }}" id="applyForm">
    @csrf

    <div class="card">
        <div class="card-header">
            <div class="card-title">Sélection</div>
        </div>
        <div class="card-body">
            <div style="display:grid;gap:20px;max-width:600px">

                {{-- Modèle --}}
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">
                        Modèle de semaine <span style="color:#ef4444">*</span>
                    </label>
                    <select name="template_id" required
                        style="width:100%;padding:10px 12px;border:1px solid {{ $errors->has('template_id') ? '#ef4444' : 'var(--border)' }};border-radius:8px;font-size:0.9rem;background:white">
                        <option value="">Sélectionner un modèle</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}"
                                {{ old('template_id', request('template_id')) == $template->id || ($selectedTemplate && $selectedTemplate->id == $template->id) ? 'selected' : '' }}>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('template_id')
                        <p style="color:#ef4444;font-size:0.8rem;margin-top:4px">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Cible : département auto ou sélection manuelle --}}
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">
                        {{ $selectedTemplate?->department ? 'Département ciblé' : 'Cible' }}
                        <span style="color:#ef4444">*</span>
                    </label>

                    @if($selectedTemplate?->department)
                        {{-- Auto department depuis le template --}}
                        <div style="padding:12px;background:var(--surface-2);border-radius:8px;border:1px solid var(--border);font-weight:500">
                             {{ $selectedTemplate->department }}
                            <span style="font-size:0.85rem;color:var(--text-muted);font-weight:400">
                                ({{ \App\Models\Employee::where('department', $selectedTemplate->department)->where('status', 'active')->count() }} employés actifs)
                            </span>
                            <input type="hidden" name="department_target" value="{{ $selectedTemplate->department }}">
                        </div>
                    @else
                        {{-- Sélection manuelle : département OU employé --}}
                        <div style="padding:14px;background:var(--surface-2);border-radius:10px;border:1px solid {{ $errors->has('employee_id') ? '#ef4444' : 'var(--border)' }};display:grid;gap:10px">
                            <div>
                                <label style="font-size:0.8rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;display:block">
                                    Option 1 — Département entier
                                </label>
                                <select name="department_target" id="deptSelect"
                                    style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white"
                                    onchange="onTargetChange()">
                                    <option value="">Sélectionner un département</option>
                                    @foreach(\App\Models\Department::orderBy('name')->pluck('name') as $dept)
                                        <option value="{{ $dept }}" {{ old('department_target') == $dept ? 'selected' : '' }}>
                                            {{ $dept }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="text-align:center;font-size:0.8rem;color:var(--text-muted);font-weight:600">— OU —</div>

                            <div>
                                <label style="font-size:0.8rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;display:block">
                                    Option 2 — Employé spécifique
                                </label>
                                <select name="employee_id" id="empSelect"
                                    style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white"
                                    onchange="onTargetChange()">
                                    <option value="">Sélectionner un employé</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->full_name }} — {{ $emp->department }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @error('employee_id')
                            <p style="color:#ef4444;font-size:0.8rem;margin-top:6px">{{ $message }}</p>
                        @enderror

                        {{-- Message d'erreur JS côté client --}}
                        <p id="targetError" style="color:#ef4444;font-size:0.8rem;margin-top:6px;display:none">
                            ⚠ Veuillez sélectionner un département ou un employé.
                        </p>
                    @endif
                </div>

                {{-- Date de début --}}
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">
                        Date de début de semaine <span style="color:#ef4444">*</span>
                    </label>
                    <input type="date" name="start_date" required
                        value="{{ old('start_date', date('Y-m-d')) }}"
                        style="width:100%;padding:10px 12px;border:1px solid {{ $errors->has('start_date') ? '#ef4444' : 'var(--border)' }};border-radius:8px;font-size:0.9rem">
                    @error('start_date')
                        <p style="color:#ef4444;font-size:0.8rem;margin-top:4px">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;margin-top:20px">
        <a href="{{ route('planning.templates.index') }}" class="btn btn-outline">Annuler</a>
        <button type="submit" class="btn btn-primary" id="submitBtn">Appliquer le modèle</button>
    </div>
</form>

<script>
function onTargetChange() {
    var dept = document.getElementById('deptSelect');
    var emp  = document.getElementById('empSelect');
    if (!dept || !emp) return;

    /* Si département choisi → vider employé, et vice-versa */
    if (dept.value) {
        emp.value = '';
    } else if (emp.value) {
        dept.value = '';
    }

    document.getElementById('targetError').style.display = 'none';
}

document.getElementById('applyForm').addEventListener('submit', function(e) {
    var dept = document.getElementById('deptSelect');
    var emp  = document.getElementById('empSelect');

    /* Si les deux selects existent (mode manuel) et les deux sont vides → bloquer */
    if (dept && emp && !dept.value && !emp.value) {
        e.preventDefault();
        document.getElementById('targetError').style.display = 'block';
        dept.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
@endsection
