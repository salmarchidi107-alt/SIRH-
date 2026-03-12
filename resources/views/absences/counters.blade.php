@extends('layouts.app')

@section('title', 'Compteurs et droits absences')
@section('page-title', 'Compteurs et droits absences')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1> Compteurs de Congés</h1>
        <p>Année {{ $year }} — Calcul des droits acquis</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <form method="GET" action="{{ route('absences.counters') }}">
            <select name="year" class="filter-select" onchange="this.form.submit()" style="padding:8px 12px;border-radius:6px;border:1px solid var(--border)">
                @for($y = now()->year + 1; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:24px">
    <div class="card" style="background:linear-gradient(135deg, #10b981, #059669);color:white;padding:24px;border-radius:12px">
        <div style="font-size:0.875rem;opacity:0.9">Droits acquis (tous)</div>
        <div style="font-size:2.5rem;font-weight:700">{{ number_format(array_sum(array_column($countersData, 'acquis')), 0, ',', '') }} <span style="font-size:1rem">jours</span></div>
        <div style="font-size:0.8rem;opacity:0.8">Pour {{ count($countersData) }} employés</div>
    </div>
    <div class="card" style="background:linear-gradient(135deg, #f59e0b, #d97706);color:white;padding:24px;border-radius:12px">
        <div style="font-size:0.875rem;opacity:0.9">Congés pris</div>
        <div style="font-size:2.5rem;font-weight:700">{{ array_sum(array_column($countersData, 'taken')) }} <span style="font-size:1rem">jours</span></div>
        <div style="font-size:0.8rem;opacity:0.8">Approuvés cette année</div>
    </div>
    <div class="card" style="background:linear-gradient(135deg, #3b82f6, #1d4ed8);color:white;padding:24px;border-radius:12px">
        <div style="font-size:0.875rem;opacity:0.9">En attente</div>
        <div style="font-size:2.5rem;font-weight:700">{{ array_sum(array_column($countersData, 'pending')) }} <span style="font-size:1rem">jours</span></div>
        <div style="font-size:0.8rem;opacity:0.8">Demandes en cours</div>
    </div>
    <div class="card" style="background:linear-gradient(135deg, #8b5cf6, #7c3aed);color:white;padding:24px;border-radius:12px">
        <div style="font-size:0.875rem;opacity:0.9">Solde total</div>
        <div style="font-size:2.5rem;font-weight:700">{{ array_sum(array_column($countersData, 'solde')) }} <span style="font-size:1rem">jours</span></div>
        <div style="font-size:0.8rem;opacity:0.8">Restants à prendre</div>
    </div>
</div>

{{-- Info Box --}}
<div class="card" style="background:linear-gradient(90deg, #f0fdf4, #ecfdf5);border-left:4px solid #10b981;margin-bottom:24px">
    <div style="padding:16px">
        <div style="font-weight:600;color:#065f46;margin-bottom:8px"> Règle de calcul</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;font-size:0.875rem;color:#047857">
            <div>✓ <strong>1,5 jour</strong> acquis par mois travaillé</div>
            <div>✓ <strong>18 jours</strong> maximum par an (12 mois)</div>
            <div>✓ Congés décomptés: annuel, maladie, sans solde</div>
        </div>
    </div>
</div>

{{-- Main Table --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"> Détail par employé</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Service</th>
                    <th style="text-align:center">Mois</th>
                    <th style="text-align:center">Droits</th>
                    <th style="text-align:center">Pris</th>
                    <th style="text-align:center">En attente</th>
                    <th style="text-align:center">Solde</th>
                    <th style="text-align:center">Si approved</th>
                </tr>
            </thead>
            <tbody>
                @forelse($countersData as $row)
                @php
                    $emp = $row['employee'];
                    $progress = $row['acquis'] > 0 ? min(100, round($row['taken'] / $row['acquis'] * 100)) : 0;
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
<span style="font-weight:600">{{ number_format($row['months_worked'], 0, ',', '') }}</span>
                    </td>
                    <td style="text-align:center">
                        <span style="color:#10b981;font-weight:700;font-size:1.1rem">{{ number_format($row['acquis'], 0, ',', '') }}</span>
                        <div style="margin-top:4px;background:#e5e7eb;border-radius:4px;height:6px;width:60px;margin-left:auto;margin-right:auto">
                            <div style="background:linear-gradient(90deg, #10b981, #059669);border-radius:4px;height:6px;width:100%"></div>
                        </div>
                    </td>
                    <td style="text-align:center">
                        <span style="color:#dc2626;font-weight:600">{{ $row['taken'] }} j</span>
                    </td>
                    <td style="text-align:center">
                        @if($row['pending'] > 0)
                            <span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:10px;font-size:0.8rem;font-weight:600">{{ $row['pending'] }} j</span>
                        @else
                            <span style="color:var(--text-muted)">—</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        <span style="font-weight:700;font-size:1.1rem;color:{{ $soldeColor }}">{{ number_format($row['solde'], 0, ',', '') }} j</span>
                    </td>
                    <td style="text-align:center">
                        @if($row['pending'] > 0)
                            @php $ifColor = $row['solde_if_pending'] < 0 ? '#dc2626' : '#6b7280'; @endphp
                            <span style="font-weight:600;color:{{ $ifColor }}">{{ number_format($row['solde_if_pending'], 0, ',', '') }} j</span>
                        @else
                            <span style="color:var(--text-muted)">—</span>
                        @endif
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

{{-- Legend --}}
<div class="card" style="margin-top:24px;background:var(--bg-secondary)">
    <div style="padding:16px">
        <div style="font-weight:600;margin-bottom:12px"> Légende</div>
        <div style="display:flex;gap:24px;flex-wrap:wrap;font-size:0.85rem;color:var(--text-muted)">
            <div><span style="color:#10b981;font-weight:700">Vert</span> — Solde positif</div>
            <div><span style="color:#f59e0b;font-weight:700">Orange</span> — Solde faible (<3 jours)</div>
            <div><span style="color:#dc2626;font-weight:700">Rouge</span> — Solde négatif (dépassement)</div>
            <div><strong>Pris</strong> = Congés annuels, maladie, sans solde approuvés</div>
            <div><strong>En attente</strong> = Demandes non encore traitées</div>
        </div>
    </div>
</div>
@endsection
