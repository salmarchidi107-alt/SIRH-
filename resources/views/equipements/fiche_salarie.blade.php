@extends('layouts.app')

@section('title', 'Fiche patrimoine — ' . $employee->first_name . ' ' . $employee->last_name)
@section('page-title', 'Fiche salarié')

@section('content')

<style>
.fs-header {
    background: #fff;
    border-radius: 14px;
    padding: 22px 24px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    border: 1px solid #f1f5f9;
    flex-wrap: wrap;
    gap: 16px;
}
.fs-identity { display: flex; align-items: center; gap: 16px; }
.fs-avatar {
    width: 56px; height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #14b8a6, #0ea5e9);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 20px; color: #fff;
    flex-shrink: 0;
}
.fs-name { font-size: 17px; font-weight: 700; color: #0f172a; }
.fs-sub { font-size: 12.5px; color: #64748b; margin-top: 3px; }

.fs-grid4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 20px; }
.fs-stat {
    background: #fff;
    border-radius: 14px;
    padding: 16px 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    border: 1px solid #f1f5f9;
}
.fs-stat .v { font-size: 22px; font-weight: 700; color: #0f172a; line-height: 1; }
.fs-stat .l { font-size: 11.5px; color: #64748b; font-weight: 500; margin-top: 5px; }

@media (max-width: 768px) {
    .fs-grid4 { grid-template-columns: 1fr 1fr; }
}

/* ── Classes partagées avec equipements/index.blade.php ── */
.eq-card {
    background: #fff;
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    border: 1px solid #f1f5f9;
}
.eq-card-title {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 14px;
}
.eq-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.eq-table th {
    font-size: 11px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    text-align: left;
    white-space: nowrap;
    background: #fafafa;
}
.eq-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f8fafc;
    color: #1e293b;
    vertical-align: middle;
}
.eq-table tbody tr:last-child td { border-bottom: none; }
.eq-table tbody tr:hover td { background: #f8fafc; }
.eq-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 99px;
    font-size: 11px;
    font-weight: 600;
}
.b-teal  { background: #f0fdfa; color: #0f766e; }
.b-blue  { background: #eff6ff; color: #1d4ed8; }
.b-green { background: #f0fdf4; color: #166534; }
.b-amber { background: #fffbeb; color: #92400e; }
.b-red   { background: #fef2f2; color: #991b1b; }
.b-gray  { background: #f8fafc; color: #475569; }
.mono { font-family: 'JetBrains Mono', 'Courier New', monospace; font-size: 11px; }
</style>

<div class="fs-header">
    <div class="fs-identity">
        @php $ini = mb_strtoupper(mb_substr($employee->first_name ?? 'X', 0, 1) . mb_substr($employee->last_name ?? 'X', 0, 1)); @endphp
        <div class="fs-avatar">{{ $ini }}</div>
        <div>
            <div class="fs-name">{{ $employee->first_name }} {{ $employee->last_name }}</div>
            <div class="fs-sub">
                {{ $employee->employee_number ?? $employee->matricule ?? '—' }}
                — {{ $employee->position ?? $employee->poste ?? '—' }}
                — {{ $employee->department ?? $employee->departement ?? '—' }}
            </div>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="{{ route('equipements.fiche_salarie.pdf', $employee->id) }}"
           style="height:38px;padding:0 16px;background:#0f172a;color:#fff;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;gap:7px">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
            Exporter en PDF
        </a>
        <a href="{{ route('equipements.index', ['tab' => 'salarie']) }}"
           style="height:38px;padding:0 16px;background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;text-decoration:none;display:inline-flex;align-items:center">
            ← Retour
        </a>
    </div>
</div>

<div class="fs-grid4">
    <div class="fs-stat">
        <div class="v">{{ $metrics_salarie['equipements_actuels'] }}</div>
        <div class="l">Équipements actuels</div>
    </div>
    <div class="fs-stat">
        <div class="v">{{ number_format($metrics_salarie['valeur_confiee'], 0, ',', ' ') }} MAD</div>
        <div class="l">Valeur confiée</div>
    </div>
    <div class="fs-stat">
        <div class="v">{{ $metrics_salarie['derniere_affectation'] ? \Carbon\Carbon::parse($metrics_salarie['derniere_affectation'])->format('d/m/Y') : '—' }}</div>
        <div class="l">Dernière affectation</div>
    </div>
    <div class="fs-stat">
        <div class="v">{{ $metrics_salarie['decharges_signees'] }} / {{ $metrics_salarie['equipements_actuels'] }}</div>
        <div class="l">Décharges signées</div>
    </div>
</div>

<div class="eq-card">
    <div class="eq-card-title">Équipements actuellement en sa possession</div>
    <table class="eq-table">
        <thead>
            <tr>
                <th>Équipement</th>
                <th>Référence</th>
                <th>Pris le</th>
                <th>Retour prévu</th>
                <th>État remise</th>
                <th style="text-align:center">Décharge</th>
            </tr>
        </thead>
        <tbody>
            @forelse($affectations_actives as $aff)
            <tr>
                <td style="font-weight:500">{{ $aff->equipement->designation ?? '—' }}</td>
                <td class="mono">{{ $aff->equipement->reference ?? '—' }}</td>
                <td style="color:#64748b;font-size:12px">{{ optional($aff->date_affectation)->format('d/m/Y') }}</td>
                <td style="color:#64748b;font-size:12px">{{ $aff->date_retour_prevue ? optional($aff->date_retour_prevue)->format('d/m/Y') : 'Non défini' }}</td>
                <td style="color:#64748b">{{ $aff->etat_remise }}</td>
                <td style="text-align:center">
                    @if($aff->decharge_signee)
                        <span class="eq-badge b-green">Signée</span>
                    @else
                        <span class="eq-badge b-amber">En attente</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;color:#64748b;padding:24px;font-size:13px">
                    Aucun équipement actuellement affecté
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="eq-card">
    <div class="eq-card-title">Historique complet</div>
    <table class="eq-table">
        <thead>
            <tr>
                <th>Équipement</th>
                <th>Référence</th>
                <th>Pris le</th>
                <th>Rendu le</th>
                <th>État remise</th>
                <th>État retour</th>
                <th style="text-align:center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historique as $h)
            <tr>
                <td style="font-weight:500">{{ $h->equipement->designation ?? '—' }}</td>
                <td class="mono">{{ $h->equipement->reference ?? '—' }}</td>
                <td style="color:#64748b;font-size:12px">{{ optional($h->date_affectation)->format('d/m/Y') }}</td>
                <td style="color:#64748b;font-size:12px">{{ $h->date_retour_effectif ? optional($h->date_retour_effectif)->format('d/m/Y') : '—' }}</td>
                <td style="color:#64748b">{{ $h->etat_remise }}</td>
                <td style="color:#64748b">{{ $h->etat_retour ?? '—' }}</td>
                <td style="text-align:center">
                    @php
                        $badgeClass = match($h->statut) {
                            'Actif'     => 'b-blue',
                            'Restitué'  => 'b-green',
                            'Perdu'     => 'b-red',
                            default     => 'b-gray',
                        };
                    @endphp
                    <span class="eq-badge {{ $badgeClass }}">{{ $h->statut }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;color:#64748b;padding:24px;font-size:13px">
                    Aucun historique pour ce salarié
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
