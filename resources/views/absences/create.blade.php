@extends('layouts.app')

@section('title', 'Nouvelle Absence')
@section('page-title', 'Nouvelle Demande d\'Absence')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Nouvelle Absence</h1>
        <p>Soumettre une demande d'absence ou de congé</p>
    </div>
    <a href="{{ route('absences.index') }}" class="btn btn-ghost">← Retour</a>
</div>

<form action="{{ route('absences.store') }}" method="POST">
    @csrf

    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">📋 Informations de la Demande</div>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Employé *</label>
@if(isset($employee))
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                    <div style="padding:16px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:24px">
                        <h3 style="margin:0 0 8px 0;color:var(--primary);font-size:1.1rem">{{ $employee->full_name }}</h3>
                        <div style="color:var(--text-muted);font-size:0.875rem">{{ $employee->department }} — {{ $employee->position }}</div>
                    </div>
@else
                    <select name="employee_id" class="form-control" required>
                        <option value="">Sélectionner un employé</option>
                        @if($employees ?? [])
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id', request('employee_id')) == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name }} — {{ $emp->department }}
                                </option>
                            @endforeach
                        @endif
                    </select>
@endif
                </div>
                <div class="form-group">
                    <label>Type d'absence *</label>
                    <select name="type" class="form-control" required>
                        <option value="">Sélectionner le type</option>
                        @foreach(array_keys(\App\Models\Absence::TYPES) as $type)
                            <option value="{{ $type }}">{{ \App\Models\Absence::TYPES[$type] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Date de début *</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Date de fin *</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
                <div class="form-group full">
                    <label>Motif</label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="Raison de l'absence..."></textarea>
                </div>
                <div class="form-group">
                    <label>Employé de remplacement</label>
                    <select name="replacement_id" class="form-control">
                        <option value="">Aucun</option>
                        @if($employees ?? [])
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group full">
                    <label>Notes supplémentaires</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
        <a href="{{ route('absences.index') }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary">Soumettre la demande</button>
    </div>
</form>
@endsection

