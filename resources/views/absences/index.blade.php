@extends('layouts.app')

@section('title', 'Absences & Congés')
@section('page-title', 'Absences & Congés')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Absences & Congés</h1>
        <p>{{ $absences->total() }} demandes d'absence</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('absences.create') }}" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nouvelle demande
        </a>
    </div>
</div>

<div class="filters-bar">
    <form method="GET" action="{{ route('absences.index') }}" class="filters-bar flex-wrap gap-3">
        <div class="search-bar">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
        </div>
        <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvées</option>
            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejetées</option>
        </select>
        <select name="employee_id" class="filter-select" onchange="this.form.submit()">
            <option value="">Tous les employés</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
            @endforeach
        </select>
    </form>
</div>

@if($pending_count > 0)
<div class="alert alert-warning mb-5">
    ⚠️ <strong>{{ $pending_count }}</strong> demande(s) en attente d'approbation
</div>
@endif

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Type</th>
                    <th>Période</th>
                    <th>Jours</th>
                    <th>Créé le</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absences as $absence)
                <tr>
                    <td>
                        <div class="table-employee">
                            <div class="table-avatar">{{ strtoupper(substr($absence->employee->first_name,0,1)) }}</div>
                            <div>
                                <div class="table-name">{{ $absence->employee->full_name }}</div>
                                <div class="table-sub">{{ $absence->employee->department }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ \App\Models\Absence::TYPES[$absence->type] ?? $absence->type }}</td>
                    <td class="text-sm">
                        {{ $absence->start_date->format('d/m') }} → {{ $absence->end_date->format('d/m/Y') }}
                    </td>
                    <td><span class="font-semibold">{{ $absence->days }}</span></td>
                    <td>
                        <span class="text-xs text-muted block">
                            {{ $absence->created_at->format('d/m/Y') }}
                            <time class="text-[0.6875rem]">{{ $absence->created_at->format('H:i') }}</time>
                        </span>
                    </td>
                    <td>
                        @if($absence->status == 'pending')
                            <span class="badge badge-warning">En attente</span>
                        @elseif($absence->status == 'approved')
                            <span class="badge badge-success">Approuvé</span>
                        @elseif($absence->status == 'rejected')
                            <span class="badge badge-danger">Rejeté</span>
                        @else
                            <span class="badge badge-neutral">Annulé</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex gap-1.5">
                            <a href="{{ route('absences.show', $absence) }}" class="btn btn-ghost btn-sm btn-icon" title="Voir">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            @if($absence->status == 'pending' && in_array(auth()->user()->role, ['admin', 'rh']))
                                <form action="{{ route('absences.approve', $absence) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Approuver">✓</button>
                                </form>
                                <form action="{{ route('absences.reject', $absence) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" title="Rejeter">✗</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center p-12 text-muted-foreground">
                        <div class="text-6xl mb-3">📅</div>
                        <div>Aucune absence trouvée</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4">{{ $absences->withQueryString()->links() }}</div>
</div>
@endsection

