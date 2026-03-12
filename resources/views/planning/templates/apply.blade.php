@extends('layouts.app')

@section('title', 'Appliquer une Semaine Type')
@section('page-title', 'Appliquer un Modèle de Semaine')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>📋 Appliquer une Semaine Type</h1>
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
                            <option value="{{ $template->id }}" {{ request('template_id') == $template->id ? 'selected' : '' }}>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Employé</label>
                    <select name="employee_id" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                        <option value="">Sélectionner un employé</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} - {{ $emp->department }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Date de début de semaine</label>
                    <input type="date" name="start_date" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                    <p style="font-size:0.75rem;color:var(--text-muted);margin-top:4px">La semaine débutera le lundi de cette date</p>
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
