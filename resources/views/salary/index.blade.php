@extends('layouts.app')

@section('title', 'Salaires')
@section('page-title', 'Dossiers Salaires')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Dossiers Salaires</h1>
        <p>Gestion de la paie</p>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Service</th>
                    <th>Poste</th>
                    <th>Salaire de base</th>
                    <th>Dernière paie</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                <tr>
                    <td>
                        <div class="table-employee">
                            <div class="table-avatar">{{ strtoupper(substr($employee->first_name,0,1)) }}</div>
                            <div>
                                <div class="table-name">{{ $employee->full_name }}</div>
                                <div class="table-sub">{{ $employee->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm">{{ $employee->department }}</td>
                    <td class="text-sm">{{ $employee->position }}</td>
                    <td class="font-semibold">{{ number_format($employee->base_salary, 0, ',', ' ') }} MAD</td>
                    <td class="text-sm text-muted">
                        @if($employee->salaries->first())
                            {{ $employee->salaries->first()->month }}/{{ $employee->salaries->first()->year }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('salary.show', $employee) }}" class="btn btn-outline btn-sm">
                            Voir dossier
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:48px;color:var(--text-muted)">
                        <div style="font-size:2.5rem;margin-bottom:12px">💰</div>
                        <div>Aucun employé trouvé</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:0 16px">{{ $employees->links() }}</div>
</div>
@endsection
