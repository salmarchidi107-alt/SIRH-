@extends('layouts.app')

@section('title', 'Compteurs et droits absences')
@section('page-title', 'Compteurs et droits absences')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Compteurs de Congés</h1>
        <p>
            Droits acquis sur 24 mois glissants depuis l'embauche
            {{ $search ? ' | Recherche: ' . $search : '' }}
            {{ $department ? ' | Service: ' . $department : '' }}
        </p>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">

        {{-- Filters Form --}}
        <form method="GET" action="{{ route('absences.counters') }}" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">

            {{-- Search --}}
            <div class="search-bar" style="position:relative">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:var(--text-muted)">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" placeholder="Rechercher employé..." value="{{ $search ?? '' }}" style="padding:10px 12px 10px 40px;border:1px solid var(--border);border-radius:8px;min-width:220px">
            </div>

            {{-- Department --}}
            <select name="department" style="padding:10px 12px;border:1px solid var(--border);border-radius:8px;min-width:160px">
                <option value="">Departements</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ $department == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary" style="padding:10px 24px">Filtrer</button>

            @if($search || $department)
                <a href="{{ route('absences.counters') }}" class="btn btn-ghost">✕ Réinitialiser</a>
            @endif
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:24px">
    <div class="card" style="background:linear-gradient(135deg, #10b981, #059669);color:white;padding:24px;border-radius:12px">
        <div style="font-size:0.875rem;opacity:0.9">Droits acquis</div>
        <div style="font-size:2.5rem;font-weight:700">{{ number_format(array_sum(array_column($countersData, 'acquis')), 0, ',', '') }} <span style="font-size:1rem">jours</span></div>
        <div style="font-size:0.8rem;opacity:0.8">Pour {{ count($countersData) }} employés</div>
    </div>
    <div class="card" style="background:linear-gradient(135deg, #f59e0b, #d97706);color:white;padding:24px;border-radius:12px">
        <div style="font-size:0.875rem;opacity:0.9">Congés pris</div>
        <div style="font-size:2.5rem;font-weight:700">{{ number_format(array_sum(array_column($countersData, 'taken')), 0, ',', '') }} <span style="font-size:1rem">jours</span></div>
        <div style="font-size:0.8rem;opacity:0.8">Sur la période en cours</div>
    </div>
    <div class="card" style="background:linear-gradient(135deg, #3b82f6, #1d4ed8);color:white;padding:24px;border-radius:12px">
        <div style="font-size:0.875rem;opacity:0.9">En attente</div>
        <div style="font-size:2.5rem;font-weight:700">{{ number_format(array_sum(array_column($countersData, 'pending')), 0, ',', '') }} <span style="font-size:1rem">jours</span></div>
        <div style="font-size:0.8rem;opacity:0.8">Demandes en cours</div>
    </div>
    <div class="card" style="background:linear-gradient(135deg, #8b5cf6, #7c3aed);color:white;padding:24px;border-radius:12px">
        <div style="font-size:0.875rem;opacity:0.9">Solde total</div>
        <div style="font-size:2.5rem;font-weight:700">{{ number_format(array_sum(array_column($countersData, 'solde')), 0, ',', '') }} <span style="font-size:1rem">jours</span></div>
        <div style="font-size:0.8rem;opacity:0.8">Restants sur la période</div>
    </div>
</div>

{{-- Info Box --}}
<div class="card" style="background:linear-gradient(90deg, #f0fdf4, #ecfdf5);border-left:4px solid #10b981;margin-bottom:24px">
    <div style="padding:16px">
        <div style="font-weight:600;color:#065f46;margin-bottom:8px">Règle de calcul</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;font-size:0.875rem;color:#047857">
            <div>✓ <strong>1,5 jour</strong> acquis par mois travaillé</div>
            <div>✓ <strong>36 jours</strong> maximum par période de <strong>24 mois</strong></div>
            <div>✓ La période se réinitialise automatiquement tous les 24 mois depuis la date d'embauche</div>
        </div>
    </div>
</div>

{{-- Main Table --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Détail par employé</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Service</th>
                    <th style="text-align:center">Période</th>
                    <th style="text-align:center">Mois</th>
                    <th style="text-align:center">Droits</th>
                    <th style="text-align:center">Pris</th>
                    <th style="text-align:center">En attente</th>
                    <th style="text-align:center">Solde</th>
                </tr>
            </thead>
            <tbody>
                @forelse($countersData as $row)
                @php
                    $emp        = $row['employee'];
                    $progress   = $row['acquis'] > 0 ? min(100, round($row['taken'] / $row['acquis'] * 100)) : 0;
                    $soldeColor = $row['solde'] < 0 ? '#dc2626' : ($row['solde'] < 3 ? '#f59e0b' : '#10b981');
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg, #6366f1, #8b5cf6);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.8rem">
                                {{ strtoupper(substr($emp->first_name, 0, 1)) }}{{ strtoupper(substr($emp->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600">{{ $emp->full_name }}</div>
                                @if($emp->hire_date)
                                <div style="font-size:0.75rem;color:var(--text-muted)">Embauché le {{ \Carbon\Carbon::parse($emp->hire_date)->format('d/m/Y') }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="background:var(--bg-secondary);padding:4px 10px;border-radius:20px;font-size:0.75rem">{{ $emp->department ?? 'N/A' }}</span>
                    </td>
                    <td style="text-align:center">
                        <div style="font-size:0.7rem;color:var(--text-muted)">{{ $row['period_start']->format('d/m/Y') }} → {{ $row['period_end']->format('d/m/Y') }}</div>
                    </td>
                    <td style="text-align:center">
                        <span style="font-weight:600">{{ number_format($row['months_worked'], 0, ',', '') }} / 24</span>
                    </td>
                    <td style="text-align:center">
                        <span style="color:#10b981;font-weight:700;font-size:1.1rem">{{ number_format($row['acquis'], 0, ',', '') }}</span>
                        <div style="margin-top:4px;background:#e5e7eb;border-radius:4px;height:6px;width:60px;margin-left:auto;margin-right:auto">
                            <div style="background:linear-gradient(90deg, #10b981, #059669);border-radius:4px;height:6px;width:{{ $progress }}%"></div>
                        </div>
                    </td>
                    <td style="text-align:center">
                        <span style="color:#dc2626;font-weight:600">{{ number_format($row['taken'], 0, ',', '') }} j</span>
                    </td>
                    <td style="text-align:center">
                        @if($row['pending'] > 0)
                            <span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:10px;font-size:0.8rem;font-weight:600">{{ number_format($row['pending'], 0, ',', '') }} j</span>
                        @else
                            <span style="color:var(--text-muted)">—</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        <span style="font-weight:700;font-size:1.1rem;color:{{ $soldeColor }}">{{ number_format($row['solde'], 0, ',', '') }} j</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:48px;color:var(--text-muted)">
                        <div style="font-size:3rem;margin-bottom:12px">👥</div>
                        <div>Aucun collaborateur actif trouvé</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
