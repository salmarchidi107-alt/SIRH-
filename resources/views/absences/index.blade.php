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
            <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>En attente</option>
            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvées</option>
            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejetées</option>
        </select>
        <select name="employee_id" class="filter-select" onchange="this.form.submit()">
            <option value="">Tous les employés</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                    {{ $emp->full_name }}
                </option>
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
                    <th>Traité par</th>{{-- ← nouvelle colonne --}}
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absences as $absence)
                <tr>
                    {{-- Employé --}}
                    <td>
                        <div class="table-employee">
                            <div class="table-avatar">
                                {{ strtoupper(substr($absence->employee->first_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="table-name">{{ $absence->employee->full_name }}</div>
                                <div class="table-sub">{{ $absence->employee->department }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Type --}}
                    <td>{{ \App\Models\Absence::TYPES[$absence->type] ?? $absence->type }}</td>

                    {{-- Période --}}
                    <td class="text-sm">
                        {{ $absence->start_date->format('d/m') }} → {{ $absence->end_date->format('d/m/Y') }}
                    </td>

                    {{-- Jours --}}
                    <td><span class="font-semibold">{{ $absence->days }}</span></td>

                    {{-- Créé le --}}
                    <td>
                        <span class="text-xs text-muted block">
                            {{ $absence->created_at->format('d/m/Y') }}
                            <time class="text-[0.6875rem]">{{ $absence->created_at->format('H:i') }}</time>
                        </span>
                    </td>

                    {{-- Statut --}}
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

                    {{-- ── Traité par (nouveau) ──────────────────────────────────── --}}
                    <td>
                        @if($absence->approvedByUser && in_array($absence->status, ['approved', 'rejected']))
                            <div class="flex items-center gap-1.5">
                                {{-- Icône selon action --}}
                                @if($absence->status === 'approved')
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2.5"
                                         class="text-success shrink-0">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                @else
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2.5"
                                         class="text-danger shrink-0">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6"  y1="6" x2="18" y2="18"/>
                                    </svg>
                                @endif
                                <div>
                                    <div class="text-xs font-medium">{{ $absence->approvedByUser->name }}</div>
                                    @if($absence->approved_at)
                                        <div class="text-[0.6875rem] text-muted">
                                            {{ \Carbon\Carbon::parse($absence->approved_at)->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif($absence->status === 'pending')
                            <span class="text-xs text-muted">—</span>
                        @else
                            <span class="text-xs text-muted">—</span>
                        @endif
                    </td>
                    {{-- ── Fin Traité par ─────────────────────────────────────────── --}}

                    {{-- Actions --}}
                    <td>
                        <div class="flex gap-1.5">
                            <a href="{{ route('absences.show', $absence) }}"
                               class="btn btn-ghost btn-sm btn-icon" title="Voir">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            <a href="{{ route('absences.pdf', $absence) }}"
                               class="btn btn-ghost btn-sm btn-icon" target="_blank" title="PDF">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                            </a>
                            @if($absence->status == 'pending' && in_array(auth()->user()->role, ['admin', 'rh']))
                                <form action="{{ route('absences.approve', $absence) }}"
                                      method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Approuver">✓</button>
                                </form>
                                <form action="{{ route('absences.reject', $absence) }}"
                                      method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" title="Rejeter">✗</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center p-12 text-muted-foreground">
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
