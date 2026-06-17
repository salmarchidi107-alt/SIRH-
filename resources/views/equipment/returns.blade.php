@extends('layouts.app')

@section('title', 'Retours / Départs')
@section('page-title', 'Retours d\'équipements — Départs')

@push('styles')
<style>
.check-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid var(--border, #e2e8f0); border-radius: 8px; margin-bottom: 8px; }
.check-item input[type=checkbox] { width: 16px; height: 16px; cursor: pointer; }
.tag { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 6px; font-size: 11px; font-weight: 500; }
.t-red { background: #FCEBEB; color: #791F1F; }
.t-green { background: #EAF3DE; color: #27500A; }
.t-amber { background: #FAEEDA; color: #633806; }
.avatar { width: 34px; height: 34px; border-radius: 50%; background: #E6F1FB; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px; color: #0C447C; flex-shrink: 0; }
.dep-row { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 8px; cursor: pointer; }
</style>
@endpush

@section('content')

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="page-header">
    <div class="page-header-left">
        <h1>Retours d'équipements — Départs</h1>
        <p>Gestion des restitutions de matériel pour les salariés en fin de contrat</p>
    </div>
</div>

@if($departing->isEmpty())
    <div class="card">
        <div class="card-body" style="text-align:center;color:#94a3b8;padding:30px">
            Aucun salarié en fin de contrat avec du matériel non restitué actuellement.
        </div>
    </div>
@else

<div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 20px">

    <div>
        @foreach($departing as $employee)
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title">Checklist de départ — {{ $employee->full_name }}</div>
            </div>
            <div class="card-body">
                <div style="margin-bottom:10px;font-size:13px;color:#64748b">
                    {{ $employee->activeEquipmentAssignments->count() }} équipement(s) à restituer
                </div>

                @foreach($employee->activeEquipmentAssignments as $assignment)
                    <div class="check-item">
                        <div style="flex:1">
                            <div style="font-weight:500;font-size:13px">{{ $assignment->equipment->designation }} ({{ $assignment->equipment->reference }})</div>
                            <div style="font-size:11px;color:#94a3b8">Affecté depuis le {{ $assignment->assigned_at->format('d/m/Y') }} — Valeur : {{ number_format($assignment->equipment->value, 0, ',', ' ') }} MAD</div>
                        </div>

                        <form action="{{ route('equipment.returns.store', $assignment) }}" method="POST" style="display:flex;gap:6px;align-items:center" onsubmit="return confirm('Confirmer la restitution de cet équipement ?')">
                            @csrf
                            <input type="hidden" name="returned_at" value="{{ date('Y-m-d') }}">
                            <select name="condition_at_return" class="form-control" style="height:30px;font-size:12px;padding:0 6px" required>
                                <option value="bon_etat">Bon état</option>
                                <option value="usure_normale">Usure normale</option>
                                <option value="endommage">Endommagé</option>
                                <option value="perdu">Perdu</option>
                            </select>
                            <button type="submit" class="btn btn-primary" style="padding:4px 10px;font-size:12px">Restituer</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <div>
        <div class="card">
            <div class="card-header"><div class="card-title">Salariés concernés</div></div>
            <div class="card-body">
                @foreach($departing as $employee)
                    <div class="dep-row">
                        <div class="avatar">{{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}</div>
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:500">{{ $employee->full_name }}</div>
                            <div style="font-size:11px;color:#94a3b8">{{ $employee->activeEquipmentAssignments->count() }} équipement(s) en attente de retour</div>
                        </div>
                        <span class="tag t-red">Urgent</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

@endif

@endsection
