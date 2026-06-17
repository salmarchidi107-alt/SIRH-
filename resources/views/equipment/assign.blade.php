@extends('layouts.app')

@section('title', 'Affectation Équipement')
@section('page-title', 'Affecter un Équipement')

@section('content')

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="page-header">
    <div class="page-header-left">
        <h1>Affecter un Équipement</h1>
        <p>Attribuer un équipement disponible à un salarié — une décharge sera générée automatiquement</p>
    </div>
    <a href="{{ route('equipment.catalogue') }}" class="btn btn-ghost">← Catalogue</a>
</div>

<div class="card mb-4">
    <div class="card-header"><div class="card-title">Nouvelle affectation</div></div>
    <div class="card-body">
        <form action="{{ route('equipment.assign.store') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label>Équipement disponible *</label>
                    <select name="equipment_id" class="form-control" required>
                        <option value="">Sélectionner un équipement...</option>
                        @foreach($equipments as $eq)
                            <option value="{{ $eq->id }}" {{ old('equipment_id') == $eq->id ? 'selected' : '' }}>
                                {{ $eq->reference }} — {{ $eq->designation }} ({{ $eq->category->name }})
                            </option>
                        @endforeach
                    </select>
                    @if($equipments->isEmpty())
                        <small style="color:#f59e0b;font-size:.75rem">⚠️ Aucun équipement disponible actuellement.</small>
                    @endif
                </div>
                <div class="form-group">
                    <label>Salarié *</label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">Sélectionner un salarié...</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->matricule }} — {{ $emp->full_name }} ({{ $emp->position }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Date d'affectation *</label>
                    <input type="date" name="assigned_at" class="form-control" value="{{ old('assigned_at', date('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label>État à la remise *</label>
                    <select name="condition_at_assignment" class="form-control" required>
                        <option value="neuf">Neuf</option>
                        <option value="bon_etat">Bon état</option>
                        <option value="usure_normale">Usure normale</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:18px">
                <a href="{{ route('equipment.catalogue') }}" class="btn btn-ghost">Annuler</a>
                <button type="submit" class="btn btn-primary">Affecter et générer la décharge</button>
            </div>
        </form>
    </div>
</div>

@endsection
