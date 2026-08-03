@extends('layouts.app')

@section('title', 'Rapport RH')
@section('page-title', 'Rapport RH')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Rapport</h1>
        <p>Indicateurs opérationnels et financiers</p>
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" action="{{ route('reporting.index') }}" id="rptForm">
    <div class="rpt-filters">

        <div class="rpt-filter-group">
            <label>Période</label>
            <div class="rpt-period-pills">
                @foreach(['month' => 'Ce mois', 'quarter' => 'Trimestre', 'year' => 'Année', 'custom' => 'Période'] as $val => $lbl)
                    <a href="#" class="rpt-period-pill {{ $periode === $val ? 'active' : '' }}"
                       onclick="setPeriode('{{ $val }}'); return false;">{{ $lbl }}</a>
                @endforeach
            </div>
            <input type="hidden" name="periode" id="periodeInput" value="{{ $periode }}">
        </div>

        <div class="rpt-filter-group" id="date-debut" style="{{ $periode !== 'custom' ? 'display:none' : '' }}">
            <label>Du</label>
            <input type="date" name="date_debut" value="{{ $dateDebut ?? $startDate->format('Y-m-d') }}">
        </div>
        <div class="rpt-filter-group" id="date-fin" style="{{ $periode !== 'custom' ? 'display:none' : '' }}">
            <label>Au</label>
            <input type="date" name="date_fin" value="{{ $dateFin ?? $endDate->format('Y-m-d') }}">
        </div>

        <div class="rpt-filter-group">
            <label>Département</label>
            <select name="departement">
                <option value="all" {{ $departement === 'all' ? 'selected' : '' }}>Tous les départements</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $departement == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="rpt-filter-group" style="min-width:auto">
            <label>Devise</label>
            <div class="rpt-currency-switcher">
                <div class="cur-btn-group">
                    <button type="button" id="btn-mad" class="cur-btn active-mad" onclick="setCurrency('MAD')">MAD</button>
                    <button type="button" id="btn-mru" class="cur-btn" onclick="setCurrency('MRU')">MRU</button>
                </div>
                <span id="cur-badge" class="cur-badge cur-badge-mad">Dirham marocain</span>
            </div>
        </div>

        <button type="submit" class="btn-rpt-apply">Appliquer</button>

        <button type="button" class="btn-rpt-pdf" onclick="checkAndExport()">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export PDF
        </button>
    </div>
</form>

{{-- ═══════════════════════════════════════
     MODAL VALIDATION AVANT EXPORT
═══════════════════════════════════════ --}}
<div class="export-modal-overlay" id="exportModal">
    <div class="export-modal">

        @if($validation['isReady'])
        <div style="text-align:center;padding:8px 0 20px;">
            <div style="width:60px;height:60px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 style="font-size:1.1rem;font-weight:700;color:#111827;margin:0 0 8px;">Tout est prêt !</h3>
            <p style="font-size:0.875rem;color:#6b7280;margin:0 0 24px;">
                Toutes les données sont validées.<br>Le rapport PDF sera complet et fiable.
            </p>
            <div style="display:flex;gap:10px;justify-content:center;">
                <button onclick="closeExportModal()"
                        style="padding:9px 20px;border:1px solid #e5e7eb;border-radius:8px;background:white;cursor:pointer;font-size:0.875rem;font-family:inherit;color:#6b7280;">
                    Annuler
                </button>
                <a href="{{ route('reporting.export-pdf', request()->query()) }}" target="_blank"
                   onclick="closeExportModal()"
                   style="padding:9px 24px;background:linear-gradient(135deg,#2dd4bf,#0f766e);color:white;border:none;border-radius:8px;font-size:0.875rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter le PDF
                </a>
            </div>
        </div>

        @else
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
            <div style="width:48px;height:48px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <div>
                <h3 style="font-size:1rem;font-weight:700;color:#111827;margin:0 0 4px;">Données incomplètes</h3>
                <p style="font-size:0.82rem;color:#6b7280;margin:0;">
                    {{ count($validation['problems']) }} point(s) nécessitent votre attention avant l'export.
                </p>
            </div>
        </div>

        <div style="margin-bottom:20px;">
            @foreach($validation['problems'] as $problem)
            <div class="problem-row">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="problem-count">{{ $problem['count'] }}</div>
                    <div>
                        <div style="font-size:0.875rem;font-weight:600;color:#111827;">{{ $problem['label'] }}</div>
                        <div style="font-size:0.72rem;color:#9ca3af;margin-top:1px;">{{ $problem['detail'] ?? '' }}</div>
                    </div>
                </div>
                <a href="{{ $problem['url'] }}" target="_blank"
                   style="font-size:0.75rem;color:#0ea5e9;font-weight:600;text-decoration:none;padding:5px 12px;border:1px solid #bae6fd;border-radius:6px;background:#f0f9ff;white-space:nowrap;flex-shrink:0;">
                    Corriger →
                </a>
            </div>
            @endforeach
        </div>

        <div style="border-top:1px solid #f3f4f6;padding-top:16px;">
            <p style="font-size:0.78rem;color:#9ca3af;margin:0 0 14px;text-align:center;">
                Corrigez ces points pour un rapport complet, ou exportez quand même avec les données disponibles.
            </p>
            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                <button onclick="closeExportModal()"
                        style="padding:9px 16px;border:1px solid #e5e7eb;border-radius:8px;background:white;cursor:pointer;font-size:0.875rem;font-family:inherit;color:#6b7280;">
                    Annuler
                </button>
                <a href="{{ route('reporting.export-pdf', request()->query()) }}" target="_blank"
                   onclick="closeExportModal()"
                   style="padding:9px 16px;border:1px solid #fecaca;border-radius:8px;background:#fef2f2;color:#ef4444;font-size:0.875rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter quand même
                </a>
                <button onclick="closeExportModal()"
                        style="padding:9px 16px;background:linear-gradient(135deg,#2dd4bf,#0f766e);color:white;border:none;border-radius:8px;font-size:0.875rem;font-weight:700;cursor:pointer;font-family:inherit;">
                    Corriger d'abord
                </button>
            </div>
        </div>
        @endif

    </div>
</div>

<script>
var currentCurrency = 'MAD';

function setCurrency(cur) {
    currentCurrency = cur;
    localStorage.setItem('rpt_currency', cur);
    document.getElementById('btn-mad').className = 'cur-btn' + (cur==='MAD' ? ' active-mad' : '');
    document.getElementById('btn-mru').className = 'cur-btn' + (cur==='MRU' ? ' active-mru' : '');
    var badge = document.getElementById('cur-badge');
    badge.textContent = cur === 'MAD' ? 'Dirham marocain' : 'Ouguiya mauritanien';
    badge.className   = 'cur-badge ' + (cur==='MAD' ? 'cur-badge-mad' : 'cur-badge-mru');
    document.querySelectorAll('.cur-label').forEach(function(el) { el.textContent = cur; });
    var lbl = document.getElementById('cur-section-label');
    if (lbl) lbl.textContent = cur === 'MAD' ? 'Dirham marocain (MAD)' : 'Ouguiya mauritanien (MRU)';
    var recapBadge = document.getElementById('cur-recap-badge');
    if (recapBadge) {
        recapBadge.textContent = cur;
        recapBadge.className   = 'cur-badge ' + (cur === 'MAD' ? 'cur-badge-mad' : 'cur-badge-mru');
    }
}

function checkAndExport() {
    document.getElementById('exportModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeExportModal() {
    document.getElementById('exportModal').classList.remove('open');
    document.body.style.overflow = '';
}

document.getElementById('exportModal').addEventListener('click', function(e) {
    if (e.target === this) closeExportModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeExportModal();
});
</script>

<p class="rpt-period-info">
    Période analysée :
    <strong>{{ $startDate->locale('fr')->translatedFormat('d M Y') }}</strong>
    &rarr;
    <strong>{{ $endDate->locale('fr')->translatedFormat('d M Y') }}</strong>
    &nbsp;·&nbsp; {{ $joursOuvrables }} jours ouvrables
</p>

@if($tauxAbsenteisme > 5)
<div class="rpt-alert warn">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    Taux d'absentéisme à {{ $tauxAbsenteisme }}% — seuil critique dépassé (5%). Intervention recommandée.
</div>
@endif

@if(!$validation['isReady'])
<div class="rpt-alert warn" style="cursor:pointer;" onclick="checkAndExport()">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <span>
        <strong>{{ count($validation['problems']) }} point(s) incomplet(s)</strong> —
        @foreach($validation['problems'] as $i => $p){{ $p['label'] }}{{ $i < count($validation['problems'])-1 ? ', ' : '' }}@endforeach.
        <span style="text-decoration:underline;">Cliquez pour voir le détail.</span>
    </span>
</div>
@endif

{{-- ══ SECTION 1 — OPÉRATIONNEL ══ --}}
<div class="rpt-section-header">
    <div style="width:32px;height:32px;border-radius:8px;background:#e0f2fe;display:flex;align-items:center;justify-content:center;">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#0369a1" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
    </div>
    <div>
        <h2>Indicateurs Opérationnels</h2>
        <span class="sub">Effectifs · Absences · Temps de travail</span>
    </div>
</div>

<div class="rpt-grid-2">
    <div class="rpt-list-card">
        <div class="rpt-list-card-header"><h3>Effectifs &amp; présences</h3></div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Salariés actifs</span>
            <span class="rpt-list-value">{{ number_format($nbrSalaries) }}</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Présences</span>
            <div style="display:flex;align-items:center;gap:8px">
                <span class="rpt-list-value">{{ number_format($nbrSalaries - $nbrAbsences) }}</span>
                <span class="badge-ok">{{ $tauxPresence }}%</span>
            </div>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Absences approuvées</span>
            <div style="display:flex;align-items:center;gap:8px">
                <span class="rpt-list-value">{{ number_format($nbrAbsences) }}</span>
                <span class="{{ $tauxAbsenteisme > 5 ? 'badge-bad' : 'badge-warn' }}">{{ $tauxAbsenteisme }}%</span>
            </div>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Jours d'absence</span>
            <span class="rpt-list-value">{{ $joursAbsence }} j</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Employés sans planning</span>
            <span class="rpt-list-value" style="color:{{ $empSansPlanning > 0 ? '#ef4444' : '#15803d' }}">{{ $empSansPlanning }}</span>
        </div>
    </div>

    <div class="rpt-list-card">
        <div class="rpt-list-card-header"><h3>Temps de travail</h3></div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Heures planifiées</span>
            <span class="rpt-list-value" style="font-family:monospace">{{ number_format($heurePlanifiees, 0) }} h</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Heures pointées</span>
            <span class="rpt-list-value" style="font-family:monospace">{{ number_format($heuresPointees, 0) }} h</span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Heures supplémentaires</span>
            <div style="display:flex;align-items:center;gap:8px">
                <span class="rpt-list-value" style="font-family:monospace;color:{{ $heuresSupp > 0 ? '#15803d' : '#6b7280' }}">+{{ number_format($heuresSupp, 0) }} h</span>
                @if($heuresSupp > 0)<span class="badge-ok">Surplus</span>@endif
            </div>
        </div>
        <div class="rpt-list-row" style="background:{{ $heuresGarde > 0 ? '#faf5ff' : 'transparent' }}">
            <span class="rpt-list-label">Heures de garde</span>
            <div style="display:flex;align-items:center;gap:8px">
                <span class="rpt-list-value" style="font-family:monospace;color:{{ $heuresGarde > 0 ? '#6d28d9' : '#6b7280' }}">{{ number_format($heuresGarde, 1) }} h</span>
                @if($nbGardes > 0)<span class="badge-purple">{{ $nbGardes }} garde(s)</span>@endif
            </div>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Taux de présence</span>
            <div class="mini-bar-wrap" style="flex:1;max-width:200px;margin-left:auto">
                <div class="mini-bar"><div class="mini-bar-fill" style="width:{{ min($tauxPresence,100) }}%;background:{{ $tauxPresence >= 90 ? '#22c55e' : ($tauxPresence >= 70 ? '#f59e0b' : '#ef4444') }}"></div></div>
                <span class="mini-bar-pct">{{ $tauxPresence }}%</span>
            </div>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Taux d'absentéisme</span>
            <div class="mini-bar-wrap" style="flex:1;max-width:200px;margin-left:auto">
                <div class="mini-bar"><div class="mini-bar-fill" style="width:{{ min($tauxAbsenteisme * 5, 100) }}%;background:{{ $tauxAbsenteisme > 5 ? '#ef4444' : '#f59e0b' }}"></div></div>
                <span class="mini-bar-pct">{{ $tauxAbsenteisme }}%</span>
            </div>
        </div>
    </div>
</div>

@if($repartitionDept->isNotEmpty())
<div class="rpt-list-card" style="margin-bottom:16px">
    <div class="rpt-list-card-header">
        <h3>Répartition par département</h3>
        <span style="font-size:0.78rem;color:var(--text-muted,#6b7280)">{{ $nbrSalaries }} salariés au total</span>
    </div>
    @foreach($repartitionDept as $dept)
    @php $pct = $nbrSalaries > 0 ? round(($dept->total / $nbrSalaries) * 100) : 0; @endphp
    <div class="rpt-list-row">
        <span class="rpt-list-label" style="font-weight:600;color:var(--text,#111827)">{{ $dept->dept }}</span>
        <div class="mini-bar-wrap" style="flex:1;max-width:260px;margin-left:24px">
            <div class="mini-bar"><div class="mini-bar-fill" style="width:{{ $pct }}%;background:linear-gradient(90deg,#2dd4bf,#0ea5e9)"></div></div>
            <span class="mini-bar-pct">{{ $pct }}%</span>
        </div>
    </div>
    @endforeach
</div>
@endif

@if($absencesParType->isNotEmpty())
<div class="rpt-list-card" style="margin-bottom:16px">
    <div class="rpt-list-card-header"><h3>Absences par type</h3></div>
    <table class="rpt-table">
        <thead>
            <tr>
                <th>Type</th>
                <th style="text-align:right">Demandes</th>
                <th style="text-align:right">Jours totaux</th>
                <th style="text-align:right">Moy. j/demande</th>
            </tr>
        </thead>
        <tbody>
            @php $typeColors = ['conge_paye'=>'badge-ok','maladie'=>'badge-bad','sans_solde'=>'badge-warn','maternite'=>'badge-purple','autre'=>'badge-gray']; @endphp
            @foreach($absencesParType as $abs)
            <tr>
                <td><span class="{{ $typeColors[$abs->type] ?? 'badge-gray' }}">{{ ucfirst(str_replace('_', ' ', $abs->type)) }}</span></td>
                <td style="text-align:right;font-weight:700;font-family:monospace">{{ $abs->count }}</td>
                <td style="text-align:right;font-family:monospace">{{ $abs->jours }} j</td>
                <td style="text-align:right;color:var(--text-muted,#6b7280);font-family:monospace">{{ $abs->count > 0 ? round($abs->jours / $abs->count, 1) : 0 }} j</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ══ SECTION 2 — FINANCIER ══ --}}
<div class="rpt-section-header">
    <div style="width:32px;height:32px;border-radius:8px;background:#dcfce7;display:flex;align-items:center;justify-content:center;">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#15803d" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <div>
        <h2>Indicateurs Financiers</h2>
        <span class="sub">Paie · Charges · DGI · Bulletins · Gardes &nbsp;—&nbsp; <span id="cur-section-label" style="font-weight:600;color:#0f766e">Dirham marocain (MAD)</span></span>
    </div>
</div>

<div class="rpt-grid-2" style="margin-bottom:16px">
    <div class="rpt-list-card">
        <div class="rpt-list-card-header"><h3>Masse salariale</h3></div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Masse salariale brute</span>
            <span class="rpt-list-value" style="font-family:monospace">
                <span id="val-masseBrute">{{ number_format($masseSalarialeBrute, 2, ',', ' ') }}</span>
                <span class="cur-label">MAD</span>
            </span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Net à payer total</span>
            <span class="rpt-list-value" style="font-family:monospace;color:#15803d">
                <span id="val-netTotal">{{ number_format($netTotal, 2, ',', ' ') }}</span>
                <span class="cur-label">MAD</span>
            </span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Coût employeur total</span>
            <span class="rpt-list-value" style="font-family:monospace">
                <span id="val-coutEmployeur">{{ number_format($coutEmployeur, 2, ',', ' ') }}</span>
                <span class="cur-label">MAD</span>
            </span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Salaire moyen brut</span>
            <span class="rpt-list-value" style="font-family:monospace">
                <span id="val-salMoyBrut">{{ number_format($salaireMoyenBrut, 2, ',', ' ') }}</span>
                <span class="cur-label">MAD</span>
            </span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">Salaire moyen net</span>
            <span class="rpt-list-value" style="font-family:monospace;color:#0f766e">
                <span id="val-salMoyNet">{{ number_format($salaireMoyenNet, 2, ',', ' ') }}</span>
                <span class="cur-label">MAD</span>
            </span>
        </div>
        <div class="rpt-list-row" style="background:#faf5ff;border-top:1px solid #ede9fe">
            <span class="rpt-list-label">Heures de garde (bulletins)</span>
            <span class="rpt-list-value" style="font-family:monospace;color:#6d28d9">{{ number_format($gardeHeures, 1) }} h</span>
        </div>
        <div class="rpt-list-row" style="background:#faf5ff">
            <span class="rpt-list-label">Paiement gardes</span>
            <span class="rpt-list-value" style="font-family:monospace;color:#6d28d9;font-weight:700">
                <span id="val-gardeTotal">{{ number_format($gardeTotal, 2, ',', ' ') }}</span>
                <span class="cur-label">MAD</span>
            </span>
        </div>
    </div>

    <div class="rpt-list-card">
        <div class="rpt-list-card-header"><h3>Charges &amp; déclarations</h3></div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">CNSS salariale <span class="badge-blue">4.48%</span></span>
            <span class="rpt-list-value" style="font-family:monospace">
                <span id="val-cnssEmp">{{ number_format($cnssEmployee, 2, ',', ' ') }}</span>
                <span class="cur-label">MAD</span>
            </span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">CNSS patronale <span class="badge-blue">8.98%</span></span>
            <span class="rpt-list-value" style="font-family:monospace">
                <span id="val-cnssPatron">{{ number_format($cnssPatron, 2, ',', ' ') }}</span>
                <span class="cur-label">MAD</span>
            </span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">AMO salariale <span class="badge-teal">2.26%</span></span>
            <span class="rpt-list-value" style="font-family:monospace">
                <span id="val-amoEmp">{{ number_format($amoEmployee, 2, ',', ' ') }}</span>
                <span class="cur-label">MAD</span>
            </span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">AMO patronale <span class="badge-teal">2.26%</span></span>
            <span class="rpt-list-value" style="font-family:monospace">
                <span id="val-amoPatron">{{ number_format($amoPatron, 2, ',', ' ') }}</span>
                <span class="cur-label">MAD</span>
            </span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">IR retenu à la source</span>
            <span class="rpt-list-value" style="font-family:monospace;color:#b91c1c">
                <span id="val-ir">{{ number_format($irRetenu, 2, ',', ' ') }}</span>
                <span class="cur-label">MAD</span>
            </span>
        </div>
        <div class="rpt-list-row">
            <span class="rpt-list-label">DGI mensuelle <span class="badge-gray">IR + 1.6%</span></span>
            <span class="rpt-list-value" style="font-family:monospace;color:#b91c1c">
                <span id="val-dgi">{{ number_format($dgiMensuelle, 2, ',', ' ') }}</span>
                <span class="cur-label">MAD</span>
            </span>
        </div>
    </div>
</div>

<div class="rpt-list-card" style="margin-bottom:16px">
    <div class="rpt-list-card-header">
        <h3>État des bulletins de paie</h3>
        <span class="{{ $bulletinsTotal > 0 && round($bulletinsValides/$bulletinsTotal*100) == 100 ? 'badge-ok' : 'badge-warn' }}">
            {{ $bulletinsTotal > 0 ? round($bulletinsValides / $bulletinsTotal * 100) : 0 }}% validés
        </span>
    </div>
    <div class="rpt-list-row">
        <span class="rpt-list-label">Bulletins générés</span>
        <span class="rpt-list-value">{{ $bulletinsTotal }}</span>
    </div>
    <div class="rpt-list-row">
        <span class="rpt-list-label">Bulletins validés</span>
        <div style="display:flex;align-items:center;gap:10px;flex:1;max-width:300px;margin-left:auto">
            <div class="mini-bar" style="flex:1">
                <div class="mini-bar-fill" style="width:{{ $bulletinsTotal > 0 ? round($bulletinsValides/$bulletinsTotal*100) : 0 }}%;background:#22c55e"></div>
            </div>
            <span class="rpt-list-value" style="min-width:60px;text-align:right">{{ $bulletinsValides }} / {{ $bulletinsTotal }}</span>
        </div>
    </div>
    <div class="rpt-list-row">
        <span class="rpt-list-label">En attente de validation</span>
        <span class="rpt-list-value" style="color:{{ ($bulletinsTotal - $bulletinsValides) > 0 ? '#d97706' : '#15803d' }}">
            {{ $bulletinsTotal - $bulletinsValides }}
        </span>
    </div>
</div>

<div class="rpt-list-card" style="margin-bottom:24px">
    <div class="rpt-list-card-header">
        <h3>Récapitulatif financier complet</h3>
        <span id="cur-recap-badge" class="cur-badge cur-badge-mad">MAD</span>
    </div>
    <table class="rpt-table">
        <thead>
            <tr>
                <th>Indicateur</th>
                <th style="text-align:right">Montant (<span class="cur-label">MAD</span>)</th>
                <th style="text-align:right">% masse brute</th>
                <th>Nature</th>
            </tr>
        </thead>
        <tbody>
            @php
                $rows = [
                    ['Masse salariale brute',      'val-recap-masseBrute', $masseSalarialeBrute, 100,                                                                      'Base',    'badge-gray',   false],
                    ['Net à payer total',           'val-recap-netTotal',   $netTotal,            $masseSalarialeBrute ? round($netTotal/$masseSalarialeBrute*100,1):0,       'Débit',   'badge-ok',     false],
                    ['CNSS salariale (4.48%)',      'val-recap-cnssEmp',    $cnssEmployee,        4.5,                                                                       'Retenue', 'badge-blue',   false],
                    ['AMO salariale (2.26%)',        'val-recap-amoEmp',     $amoEmployee,         2.3,                                                                       'Retenue', 'badge-blue',   false],
                    ['IR retenu à la source',        'val-recap-ir',         $irRetenu,            $masseSalarialeBrute ? round($irRetenu/$masseSalarialeBrute*100,1):0,       'Retenue', 'badge-bad',    false],
                    ['CNSS patronale (8.98%)',       'val-recap-cnssPatron', $cnssPatron,          9.0,                                                                       'Charge',  'badge-warn',   false],
                    ['AMO patronale (2.26%)',        'val-recap-amoPatron',  $amoPatron,           2.3,                                                                       'Charge',  'badge-warn',   false],
                    ['Paiement gardes',              'val-recap-gardeTotal', $gardeTotal,          $masseSalarialeBrute ? round($gardeTotal/$masseSalarialeBrute*100,1):0,     'Garde',   'badge-purple', true],
                    ['DGI — déclaration mensuelle',  'val-recap-dgi',        $dgiMensuelle,        $masseSalarialeBrute ? round($dgiMensuelle/$masseSalarialeBrute*100,1):0,   'Fiscal',  'badge-purple', false],
                    ['Coût employeur total',         'val-recap-coutTotal',  $coutEmployeur,       $masseSalarialeBrute ? round($coutEmployeur/$masseSalarialeBrute*100,1):0,  'Total',   'badge-teal',   false],
                ];
            @endphp
            @foreach($rows as [$label, $valId, $montant, $pct, $nature, $badgeClass, $isGarde])
            <tr class="{{ $label === 'Coût employeur total' ? 'highlight-row' : ($isGarde ? 'garde-row' : '') }}">
                <td style="font-weight:{{ $label === 'Coût employeur total' ? '700' : '400' }}">
                    @if($isGarde)
                        {{ $label }} <span style="font-size:0.72rem;color:#8b5cf6;font-weight:400">({{ $gardeHeures }} h)</span>
                    @else
                        {{ $label }}
                    @endif
                </td>
                <td style="text-align:right;font-family:monospace;font-weight:600;{{ $isGarde ? 'color:#6d28d9' : '' }}">
                    <span id="{{ $valId }}">{{ number_format($montant, 2, ',', ' ') }}</span>
                </td>
                <td style="text-align:right">
                    <span class="mini-bar-pct">{{ $pct }}%</span>
                </td>
                <td><span class="{{ $badgeClass }}">{{ $nature }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script>
function setPeriode(val) {
    document.getElementById('periodeInput').value = val;
    var show = val === 'custom';
    document.getElementById('date-debut').style.display = show ? '' : 'none';
    document.getElementById('date-fin').style.display   = show ? '' : 'none';
    document.querySelectorAll('.rpt-period-pill').forEach(function(p) {
        p.classList.toggle('active', p.getAttribute('onclick') === "setPeriode('" + val + "'); return false;");
    });
    if (!show) document.getElementById('rptForm').submit();
}

document.querySelectorAll('.mini-bar-fill').forEach(function(el) {
    var w = el.style.width;
    el.style.width = '0';
    setTimeout(function() { el.style.width = w; }, 250);
});

document.addEventListener('DOMContentLoaded', function() {
    var saved = localStorage.getItem('rpt_currency') || 'MAD';
    setCurrency(saved);
});
</script>
@endpush
