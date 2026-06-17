@extends('layouts.app')

@section('title', 'Catalogue Équipements')
@section('page-title', 'Catalogue des Équipements')

@push('styles')
<style>
.eq-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-bottom: 20px; }
.eq-stat { background: #fff; border: 1px solid var(--border, #e2e8f0); border-radius: 10px; padding: 16px 18px; }
.eq-stat-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
.eq-stat-value { font-size: 22px; font-weight: 600; color: var(--text-primary, #1e293b); }
.eq-filters { display: flex; gap: 10px; align-items: center; margin-bottom: 16px; flex-wrap: wrap; }
.eq-filters select { min-width: 180px; }
.tag { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 6px; font-size: 11px; font-weight: 500; }
.t-blue  { background: #E6F1FB; color: #0C447C; }
.t-green { background: #EAF3DE; color: #27500A; }
.t-amber { background: #FAEEDA; color: #633806; }
.t-red   { background: #FCEBEB; color: #791F1F; }
.t-gray  { background: #F1EFE8; color: #444441; }
table.eq-table { width: 100%; border-collapse: collapse; font-size: 13px; }
table.eq-table th { text-align: left; font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; padding: 9px 12px; border-bottom: 1px solid var(--border, #e2e8f0); }
table.eq-table td { padding: 9px 12px; border-bottom: 1px solid var(--border, #e2e8f0); vertical-align: middle; }
table.eq-table tr:hover td { background: #f8fafc; }
.mono { font-family: monospace; font-size: 12px; color: #475569; }
</style>
@endpush

@section('content')

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="page-header">
    <div class="page-header-left">
        <h1>Catalogue des Équipements</h1>
        <p>{{ $stats['total'] }} équipement(s) référencé(s) — valeur du parc : {{ number_format($stats['valeur_parc'], 0, ',', ' ') }} MAD</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="document.getElementById('add-eq-form').style.display='block'">
        + Ajouter un équipement
    </button>
</div>

<div class="eq-stats">
    <div class="eq-stat"><div class="eq-stat-label">Total</div><div class="eq-stat-value">{{ $stats['total'] }}</div></div>
    <div class="eq-stat"><div class="eq-stat-label">Affectés</div><div class="eq-stat-value" style="color:#185FA5">{{ $stats['affectes'] }}</div></div>
    <div class="eq-stat"><div class="eq-stat-label">Disponibles</div><div class="eq-stat-value" style="color:#3B6D11">{{ $stats['disponibles'] }}</div></div>
    <div class="eq-stat"><div class="eq-stat-label">Maintenance</div><div class="eq-stat-value" style="color:#854F0B">{{ $stats['maintenance'] }}</div></div>
    <div class="eq-stat"><div class="eq-stat-label">Valeur parc</div><div class="eq-stat-value">{{ number_format($stats['valeur_parc']/1000000, 2) }}M MAD</div></div>
</div>

{{-- Formulaire d'ajout (masqué par défaut) --}}
<div class="card mb-4" id="add-eq-form" style="display:none">
    <div class="card-header"><div class="card-title">Nouvel équipement</div></div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('equipment.catalogue.store') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label>Référence *</label>
                    <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" required placeholder="PC-00126">
                </div>
                <div class="form-group">
                    <label>Désignation *</label>
                    <input type="text" name="designation" class="form-control" value="{{ old('designation') }}" required placeholder="Dell XPS 15">
                </div>
                <div class="form-group">
                    <label>Catégorie *</label>
                    <select name="equipment_category_id" class="form-control" required>
                        <option value="">Sélectionner...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('equipment_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Marque</label>
                    <input type="text" name="brand" class="form-control" value="{{ old('brand') }}">
                </div>
                <div class="form-group">
                    <label>N° de série</label>
                    <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number') }}">
                </div>
                <div class="form-group">
                    <label>État *</label>
                    <select name="condition" class="form-control" required>
                        <option value="neuf">Neuf</option>
                        <option value="bon_etat">Bon état</option>
                        <option value="usure_normale">Usure normale</option>
                        <option value="endommage">Endommagé</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Valeur (MAD)</label>
                    <input type="number" name="value" class="form-control" value="{{ old('value') }}" min="0" step="50">
                </div>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:14px">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('add-eq-form').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <div class="card-title">Liste des équipements</div>
        <form method="GET" class="eq-filters" style="margin:0">
            <select name="category" class="form-control" onchange="this.form.submit()">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">Tous statuts</option>
                <option value="disponible" {{ request('status') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                <option value="affecte" {{ request('status') == 'affecte' ? 'selected' : '' }}>Affecté</option>
                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="hors_service" {{ request('status') == 'hors_service' ? 'selected' : '' }}>Hors service</option>
            </select>
        </form>
    </div>
    <div class="card-body" style="padding:0">
        <table class="eq-table">
            <thead>
                <tr>
                    <th>Référence</th><th>Désignation</th><th>Catégorie</th><th>Marque</th>
                    <th>État</th><th>Statut</th><th style="text-align:right">Valeur</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipments as $eq)
                <tr>
                    <td class="mono">{{ $eq->reference }}</td>
                    <td style="font-weight:500">{{ $eq->designation }}</td>
                    <td><span class="tag t-blue">{{ $eq->category->name }}</span></td>
                    <td>{{ $eq->brand ?? '—' }}</td>
                    <td>
                        @php
                            $condTags = ['neuf' => 't-green', 'bon_etat' => 't-amber', 'usure_normale' => 't-gray', 'endommage' => 't-red', 'perdu' => 't-red'];
                            $condLabels = ['neuf' => 'Neuf', 'bon_etat' => 'Bon état', 'usure_normale' => 'Usure normale', 'endommage' => 'Endommagé', 'perdu' => 'Perdu'];
                        @endphp
                        <span class="tag {{ $condTags[$eq->condition] ?? 't-gray' }}">{{ $condLabels[$eq->condition] ?? $eq->condition }}</span>
                    </td>
                    <td>
                        @php
                            $statusTags = ['disponible' => 't-green', 'affecte' => 't-blue', 'maintenance' => 't-amber', 'hors_service' => 't-red'];
                            $statusLabels = ['disponible' => 'Disponible', 'affecte' => 'Affecté', 'maintenance' => 'Maintenance', 'hors_service' => 'Hors service'];
                        @endphp
                        <span class="tag {{ $statusTags[$eq->status] ?? 't-gray' }}">{{ $statusLabels[$eq->status] ?? $eq->status }}</span>
                    </td>
                    <td style="text-align:right">{{ number_format($eq->value, 0, ',', ' ') }}</td>
                    <td>
                        @if($eq->status === 'disponible')
                            <a href="{{ route('equipment.assign') }}" class="btn btn-ghost" style="padding:4px 10px;font-size:12px">Affecter</a>
                        @elseif($eq->currentAssignment())
                            <a href="{{ route('equipment.employee', $eq->currentAssignment()->employee_id) }}" class="btn btn-ghost" style="padding:4px 10px;font-size:12px">Voir</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:24px">Aucun équipement trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body" style="border-top:1px solid var(--border, #e2e8f0)">
        {{ $equipments->links() }}
    </div>
</div>

@endsection
