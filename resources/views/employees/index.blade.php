@extends('layouts.app')

@section('title', 'Liste du Personnel')
@section('page-title', 'Liste du Personnel')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Employés</h1>
        <p>{{ $employees->total() }} collaborateurs enregistrés</p>
    </div>
    <a href="{{ route('employees.create') }}" class="btn btn-primary">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Nouvel Employé
    </a>
</div>

<!-- Filter Buttons: Tous / Actifs -->
<div class="filters-bar">
    <div style="display:flex;gap:8px">
        <a href="{{ route('employees.index', ['filter' => 'all']) }}"
           class="btn {{ $filter == 'all' ? 'btn-primary' : 'btn-outline' }}">
            Tous
        </a>
        <a href="{{ route('employees.index', ['filter' => 'active']) }}"
           class="btn {{ $filter == 'active' ? 'btn-primary' : 'btn-outline' }}">
            Actifs
        </a>
    </div>
    <form method="GET" action="{{ route('employees.index') }}" class="filters-bar" style="margin:0;flex-wrap:wrap;gap:12px">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <div class="search-bar">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" placeholder="Rechercher un employé..." value="{{ request('search') }}">
        </div>
        <select name="department" class="filter-select" onchange="this.form.submit()">
            <option value="">Tous les services</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['search','department']))
            <a href="{{ route('employees.index', ['filter' => $filter]) }}" class="btn btn-ghost btn-sm">✕ Réinitialiser</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
               
                    <th>Matricule</th>
                    <th>Collaborateur</th>
                    <th>Fonction</th>
                    <th>Contrat</th>
                    <th>Statut</th>
                    <th>Entrée</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                <tr>
                    <td>
                        <span style="font-family:monospace;font-size:0.8rem;background:var(--surface-2);padding:2px 8px;border-radius:4px;border:1px solid var(--border)">
                            {{ $employee->matricule }}
                        </span>
                    </td>
                    <td>
                        <div class="table-employee">
                            <div class="table-avatar">
                            @if($employee->photo)
                                    <img src="{{ $employee->photo_url }}" alt="">
                                @else
                                    {{ strtoupper(substr($employee->first_name,0,1).substr($employee->last_name,0,1)) }}
                                @endif
                            </div>
                            <div>
                                <div class="table-name">{{ $employee->full_name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm">{{ $employee->position }}</td>
                    <td>
                        <span class="badge {{ $employee->contract_type == 'CDI' ? 'badge-success' : ($employee->contract_type == 'CDD' ? 'badge-warning' : 'badge-neutral') }}">
                            {{ $employee->contract_type }}
                        </span>
                    </td>
                    <td>
                        @if($employee->status == 'active')
                            <span class="badge badge-success">● Actif</span>
                        @elseif($employee->status == 'leave')
                            <span class="badge badge-warning">◐ En congé</span>
                        @else
                            <span class="badge badge-neutral">○ Inactif</span>
                        @endif
                    </td>
                    <td class="text-sm text-muted">{{ $employee->hire_date->format('d/m/Y') }}</td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-ghost btn-sm btn-icon" title="Voir">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline btn-sm btn-icon" title="Modifier">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            <form action="{{ route('employees.destroy', $employee) }}" method="POST"
                                onsubmit="return confirm('Supprimer cet employé ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Supprimer">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:48px;color:var(--text-muted)">
                        <div style="font-size:2.5rem;margin-bottom:12px">👥</div>
                        <div style="font-weight:600;margin-bottom:4px">Aucun employé trouvé</div>
                        <div style="font-size:0.875rem">Modifiez vos critères de recherche ou ajoutez un employé</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:0 16px">{{ $employees->withQueryString()->links() }}</div>
</div>
@endsection
