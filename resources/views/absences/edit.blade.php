@extends('layouts.app')

@section('title', 'Modifier Absence')
@section('page-title', 'Modifier une Demande d\'Absence')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Modifier l'Absence</h1>
    </div>
    <a href="{{ route('absences.index') }}" class="btn btn-ghost">← Retour</a>
</div>

<form action="{{ route('absences.update', $absence) }}" method="POST">
    @csrf @method('PUT')

    <div class="card mb-4">
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Type *</label>
                    <select name="type" class="form-control" required>
                        @foreach(array_keys(\App\Models\Absence::TYPES) as $type)
                            <option value="{{ $type }}" {{ old('type', $absence->type) == $type ? 'selected' : '' }}>
                                {{ \App\Models\Absence::TYPES[$type] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Date de début *</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $absence->start_date->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label>Date de fin *</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $absence->end_date->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label>Motif</label>
                    <textarea name="reason" class="form-control" rows="2">{{ old('reason', $absence->reason) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
        <a href="{{ route('absences.index') }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection
