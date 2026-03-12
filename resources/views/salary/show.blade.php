@extends('layouts.app')

@section('title', 'Salaire - '.$employee->full_name)
@section('page-title', 'Fiche de Paie')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Salaire - {{ $employee->full_name }}</h1>
        <p>{{ $employee->department }} — {{ $employee->position }}</p>
    </div>
    <a href="{{ route('salary.index') }}" class="btn btn-ghost">← Retour</a>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:20px">
    <div>
        <div class="salary-card mb-4">
            <div class="salary-label">Salaire de Base</div>
            <div class="salary-net">{{ number_format($employee->base_salary, 0, ',', ' ') }} MAD</div>
            <div style="font-size:0.75rem;opacity:0.5">Brut mensuel</div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Générer une paie</div>
            </div>
            <div class="card-body">
                <form action="{{ route('salary.update', $employee) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label>Mois</label>
                        <select name="month" class="form-control">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m)->locale('fr')->monthName }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Année</label>
                        <select name="year" class="form-control">
                            @for($y = now()->year; $y >= now()->year - 2; $y--)
                                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Salaire de base</label>
                        <input type="number" name="base_salary" class="form-control" value="{{ $employee->base_salary }}">
                    </div>
                    <div class="form-group mb-3">
                        <label>Heures supplémentaires</label>
                        <input type="number" name="overtime_hours" class="form-control" value="0">
                    </div>
                    <div class="form-group mb-3">
                        <label>Primes</label>
                        <input type="number" name="bonuses" class="form-control" value="0">
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Générer</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Historique des paie</div>
        </div>
        <div class="card-body" style="padding:0">
            @if($salaries->isEmpty())
                <div style="padding:48px;text-align:center;color:var(--text-muted)">
                    <div style="font-size:2.5rem;margin-bottom:12px">💰</div>
                    <div>Aucune fiche de paie</div>
                </div>
            @else
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Période</th>
                            <th>Base</th>
                            <th>Primes</th>
                            <th>CNSS</th>
                            <th>IR</th>
                            <th>Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salaries as $salary)
                        <tr>
                            <td class="font-semibold">{{ $salary->month }}/{{ $salary->year }}</td>
                            <td>{{ number_format($salary->base_salary, 0, ',', ' ') }}</td>
                            <td class="bonus">{{ number_format($salary->bonuses, 0, ',', ' ') }}</td>
                            <td class="deduction">-{{ number_format($salary->cnss_deduction, 0, ',', ' ') }}</td>
                            <td class="deduction">-{{ number_format($salary->ir_deduction, 0, ',', ' ') }}</td>
                            <td class="font-semibold" style="color:var(--success)">{{ number_format($salary->net_salary, 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
