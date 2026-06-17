@extends('layouts.app')

@section('title', 'Fiche Équipements — ' . $employee->full_name)
@section('page-title', 'Équipements de ' . $employee->full_name)

@push('styles')
<style>
.tag { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 6px; font-size: 11px; font-weight: 500; }
.t-blue  { background: #E6F1FB; color: #0C447C; }
.t-green { background: #EAF3DE; color: #27500A; }
.t-amber { background: #FAEEDA; color: #633806; }
.t-red   { background: #FCEBEB; color: #791F1F; }
.eq-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid var(--border, #e2e8f0); border-radius: 10px; margin-bottom: 8px; }
.eq-item-icon { width: 38px; height: 38px; border-radius: 50%; background: #E6F1FB; display: flex; align-items: center; justify-content: center; color: #0C447C; font-weight: 600; flex-shrink: 0; }
.mono { font-family: monospace; font-size: 12px; color: #475569; }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $employee->full_name }}</h1>
        <p>{{ $employee->matricule }} — {{ $employee->position }} — {{ $employee->department }}</p>
    </div>
    @unless($readonly)
        <a href="{{ route('equipment.assign') }}" class="btn btn-primary">+ Affecter un équipement</a>
    @endunless
</div>

<div class="card">
    <div class="card-header"><div class="card-title">Équipements actuellement affectés</div></div>
    <div class="card-body">
        @php $active = $employee->equipmentAssignments->where('status', 'active'); @endphp

        @forelse($active as $assignment)
            <div class="eq-item">
                <div class="eq-item-icon">{{ substr($assignment->equipment->designation, 0, 2) }}</div>
                <div style="flex:1">
                    <div style="font-weight:500;font-size:14px">{{ $assignment->equipment->designation }}</div>
                    <div class="mono">{{ $assignment->equipment->reference }} — Affecté depuis le {{ $assignment->assigned_at->format('d/m/Y') }}</div>
                </div>
                <span class="tag t-blue">{{ number_format($assignment->equipment->value, 0, ',', ' ') }} MAD</span>
                @if($assignment->discharges->isNotEmpty())
                    <a href="{{ route('equipment.discharge', $assignment->discharges->first()) }}" class="btn btn-ghost" style="padding:4px 10px;font-size:12px">Décharge</a>
                @endif
            </div>
        @empty
            <p style="color:#94a3b8;text-align:center;padding:20px 0">Aucun équipement actuellement affecté.</p>
        @endforelse
    </div>
</div>

<div class="card mt-4">
    <div class="card-header"><div class="card-title">Historique complet</div></div>
    <div class="card-body" style="padding:0">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr>
                    <th style="text-align:left;padding:9px 12px;font-size:11px;color:#94a3b8;text-transform:uppercase;border-bottom:1px solid var(--border,#e2e8f0)">Date</th>
                    <th style="text-align:left;padding:9px 12px;font-size:11px;color:#94a3b8;text-transform:uppercase;border-bottom:1px solid var(--border,#e2e8f0)">Équipement</th>
                    <th style="text-align:left;padding:9px 12px;font-size:11px;color:#94a3b8;text-transform:uppercase;border-bottom:1px solid var(--border,#e2e8f0)">Statut</th>
                    <th style="text-align:left;padding:9px 12px;font-size:11px;color:#94a3b8;text-transform:uppercase;border-bottom:1px solid var(--border,#e2e8f0)">Retour</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employee->equipmentAssignments->sortByDesc('assigned_at') as $assignment)
                <tr>
                    <td style="padding:9px 12px;border-bottom:1px solid var(--border,#e2e8f0);color:#64748b">{{ $assignment->assigned_at->format('d/m/Y') }}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid var(--border,#e2e8f0)">{{ $assignment->equipment->designation }} <span class="mono">({{ $assignment->equipment->reference }})</span></td>
                    <td style="padding:9px 12px;border-bottom:1px solid var(--border,#e2e8f0)">
                        @if($assignment->status === 'active')
                            <span class="tag t-blue">Actif</span>
                        @elseif($assignment->status === 'returned')
                            <span class="tag t-green">Restitué</span>
                        @else
                            <span class="tag t-red">Perdu</span>
                        @endif
                    </td>
                    <td style="padding:9px 12px;border-bottom:1px solid var(--border,#e2e8f0);color:#64748b">{{ $assignment->returned_at?->format('d/m/Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">Aucun historique.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
