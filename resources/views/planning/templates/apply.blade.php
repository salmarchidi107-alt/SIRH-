@extends('layouts.app')

@section('title', 'Appliquer une Semaine Type')
@section('page-title', 'Appliquer un Modèle de Semaine')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1> Appliquer une Semaine Type</h1>
        <p>Appliquez un modèle de semaine à un employé</p>
    </div>
</div>

<form method="POST" action="{{ route('planning.templates.apply') }}">
    @csrf
    
    <div class="card">
        <div class="card-header">
            <div class="card-title">Sélection</div>
        </div>
        <div class="card-body">
            <div style="display:grid;gap:16px;max-width:600px">
                
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Modèle de semaine</label>
                    <select name="template_id" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                        <option value="">Sélectionner un modèle</option>
                        @foreach($templates as $template)
                    <option value="{{ $template->id }}" {{ request('template_id') == $template->id || ($selectedTemplate && $selectedTemplate->id == $template->id) ? 'selected' : '' }}>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">
                        {{ $selectedTemplate?->department ? 'Département ciblé' : 'Employé / Département' }}
                    </label>
                    @if($selectedTemplate?->department)
                        {{-- Auto department --}}
                        <div style="padding:12px;background:var(--surface-2);border-radius:8px;border:1px solid var(--border);font-weight:500">
                            📍 {{ $selectedTemplate->department }} 
                            <span style="font-size:0.85rem;color:var(--text-muted);font-weight:400">
                                ({{ \App\Models\Employee::where('department', $selectedTemplate->department)->where('status', 'active')->count() }} employés)
                            </span>
                            <input type="hidden" name="department_target" value="{{ $selectedTemplate->department }}">
                        </div>
                    @else
                        {{-- Manual select --}}
                        <select name="department_target" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white;margin-bottom:12px">
                            <option value="">Sélectionner un département (mass apply)</option>
                            @foreach(\App\Models\Department::orderBy('name')->pluck('name') as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                        <select name="employee_id" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                            <option value="">OU un employé spécifique</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} - {{ $emp->department }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Date de début de semaine</label>
                    <input type="date" name="start_date" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                </div>

            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;margin-top:20px">
        <a href="{{ route('planning.templates.index') }}" class="btn btn-outline">Annuler</a>
        <button type="submit" class="btn btn-primary">Appliquer le modèle</button>
    </div>
</form>
@endsection
