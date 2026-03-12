@extends('layouts.app')

@section('title', 'Planning')
@section('page-title', 'Planning du Personnel')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Planning</h1>
        <p>Gestion des horaires et shifts</p>
    </div>
</div>

<div class="filters-bar">
    <form method="GET" action="{{ route('planning.index') }}" class="filters-bar" style="margin:0;flex-wrap:wrap;gap:12px">
        <select name="employee_id" class="filter-select" onchange="this.form.submit()">
            <option value="">Tous les employés</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
            @endforeach
        </select>
        <select name="month" class="filter-select" onchange="this.form.submit()">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m)->locale('fr')->monthName }}</option>
            @endfor
        </select>
        <select name="year" class="filter-select" onchange="this.form.submit()">
            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </form>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Date</th>
                    <th>Shift</th>
                    <th>Horaire</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plannings as $planning)
                <tr>
                    <td>
                        <div class="table-employee">
                            <div class="table-avatar">{{ strtoupper(substr($planning->employee->first_name,0,1)) }}</div>
                            <div>
                                <div class="table-name">{{ $planning->employee->full_name }}</div>
                                <div class="table-sub">{{ $planning->employee->department }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $planning->date->format('d/m/Y') }}</td>
                    <td>
                        <span class="shift-pill shift-{{ $planning->shift_type }}">
                            {{ \App\Models\Planning::SHIFT_TYPES[$planning->shift_type] }}
                        </span>
                    </td>
                    <td>{{ $planning->shift_start }} - {{ $planning->shift_end }}</td>
                    <td class="text-sm text-muted">{{ $planning->notes ?: '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:48px;color:var(--text-muted)">
                        <div style="font-size:2.5rem;margin-bottom:12px">📅</div>
                        <div>Aucun planning trouvé</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
