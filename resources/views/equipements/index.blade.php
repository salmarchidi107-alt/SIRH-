@extends('layouts.app')

@section('title', 'Gestion des équipements')
@section('page-title', 'Équipements')

@section('content')

<style>
/* ══════════════════════════════════════════════════════
 ÉQUIPEMENTS — thème aligné sur le projet HospitalRH
 Couleurs : teal #14b8a6, fond #f8fafc, cartes blanches
══════════════════════════════════════════════════════ */

/* ── Navigation onglets ── */
.eq-nav {
    display: flex;
    border-bottom: 1px solid #e2e8f0;
    background: #fff;
    padding: 0;
    gap: 0;
    overflow-x: auto;
    margin: -16px -16px 24px;
    scrollbar-width: none;
}
.eq-nav::-webkit-scrollbar { display: none; }
.eq-tab {
    padding: 14px 18px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    flex-shrink: 0;
    text-decoration: none;
    transition: color .15s, border-color .15s;
}
.eq-tab:hover { color: #14b8a6; background: #f8fafc; }
.eq-tab.active { border-bottom-color: #14b8a6; color: #14b8a6; }
.eq-tab i { font-size: 15px; }

/* ── Pages ── */
.eq-page { display: none; }
.eq-page.active { display: block; }

/* ── Cartes métriques ── */
.eq-stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px 22px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    border: 1px solid #f1f5f9;
    position: relative;
    overflow: hidden;
}
.eq-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 16px 16px 0 0;
}
.eq-stat-card.teal::before  { background: #14b8a6; }
.eq-stat-card.blue::before  { background: #3b82f6; }
.eq-stat-card.green::before { background: #22c55e; }
.eq-stat-card.amber::before { background: #f59e0b; }
.eq-stat-card.red::before   { background: #ef4444; }
.eq-stat-card .stat-val {
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
    margin-bottom: 4px;
}
.eq-stat-card .stat-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

/* ── Cartes métriques CLIQUABLES ── */
a.eq-stat-card {
    cursor: pointer;
    text-decoration: none;
    display: block;
    color: inherit;
    transition: transform .15s, box-shadow .15s, border-color .15s;
}
a.eq-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,.10);
    border-color: #e2e8f0;
}
a.eq-stat-card .stat-label::after {
    content: ' →';
    font-size: 10px;
    color: #cbd5e1;
    margin-left: 3px;
    transition: color .15s;
}
a.eq-stat-card:hover .stat-label::after { color: #14b8a6; }

/* ── Filtre dashboard ── */
.dash-filter {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    background: #fff;
    border: 1px solid #f1f5f9;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.dash-filter select,
.dash-filter input[type=date] {
    height: 34px;
    font-size: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0 10px;
    background: #fff;
    color: #374151;
    min-width: 150px;
}
.dash-filter select:focus,
.dash-filter input[type=date]:focus {
    outline: none;
    border-color: #14b8a6;
    box-shadow: 0 0 0 3px rgba(20,184,166,.10);
}
.dash-filter label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    white-space: nowrap;
}
.dash-filter-btn {
    height: 34px;
    padding: 0 14px;
    background: #14b8a6;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.dash-filter-btn:hover { background: #0d9488; }
.dash-filter-reset {
    height: 34px;
    padding: 0 12px;
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.dash-filter-reset:hover { background: #e2e8f0; }
.dash-filter-active-badge {
    margin-left: auto;
    font-size: 11px;
    background: #f0fdfa;
    border: 1px solid #ccfbf1;
    border-radius: 6px;
    padding: 3px 10px;
    color: #0f766e;
    font-weight: 500;
}

/* ── Panneau détail affectation ── */
.dash-detail-panel {
    display: none;
    margin-bottom: 16px;
    animation: slideDown .2s ease;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Grilles ── */
.eq-grid4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 20px; }
.eq-grid3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; }
.eq-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

/* ── Cartes contenu ── */
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
    display: flex;
    align-items: center;
    gap: 7px;
}
.eq-card-title i { color: #14b8a6; font-size: 15px; }

/* ── Barres de progression ── */
.eq-bar { height: 6px; border-radius: 99px; background: #f1f5f9; overflow: hidden; margin: 4px 0 0; }
.eq-bar-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg, #14b8a6, #0ea5e9); }

/* ── Tableau ── */
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
.eq-table tbody tr.clickable-row { cursor: pointer; }
.eq-table tbody tr.clickable-row:hover td { background: #f0fdfa; }

/* ── Badges ── */
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
.t-blue  { background: #eff6ff; color: #1d4ed8; border-radius: 99px; padding: 3px 9px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; }
.t-green { background: #f0fdf4; color: #166534; border-radius: 99px; padding: 3px 9px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; }
.t-amber { background: #fffbeb; color: #92400e; border-radius: 99px; padding: 3px 9px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; }
.t-red   { background: #fef2f2; color: #991b1b; border-radius: 99px; padding: 3px 9px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; }
.t-gray  { background: #f8fafc; color: #475569; border-radius: 99px; padding: 3px 9px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; }
.t-teal  { background: #f0fdfa; color: #0f766e; border-radius: 99px; padding: 3px 9px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; }

/* ── Alertes ── */
.eq-alert {
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 13px;
    margin-bottom: 12px;
    display: flex;
    align-items: flex-start;
    gap: 9px;
}
.eq-alert i { font-size: 15px; flex-shrink: 0; margin-top: 1px; }
.eq-alert.info  { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
.eq-alert.warn  { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.eq-alert.ok    { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.eq-alert.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

/* ── Formulaires ── */
.fgroup { display: flex; flex-direction: column; gap: 4px; }
.fgroup label { font-size: 12px; font-weight: 500; color: #374151; }
.fgroup input,
.fgroup select,
.fgroup textarea {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0 12px;
    height: 36px;
    font-size: 13px;
    background: #fff;
    color: #1e293b;
    transition: border-color .15s, box-shadow .15s;
}
.fgroup textarea { height: 70px; padding: 8px 12px; resize: none; }
.fgroup input:focus, .fgroup select:focus, .fgroup textarea:focus {
    outline: none;
    border-color: #14b8a6;
    box-shadow: 0 0 0 3px rgba(20,184,166,.12);
}
.fgroup input[readonly] { background: #f8fafc; color: #64748b; }

/* ── Steps workflow ── */
.eq-step { display: flex; align-items: flex-start; gap: 14px; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
.eq-step:last-child { border-bottom: none; }
.step-num {
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700;
    flex-shrink: 0;
    background: #f1f5f9; color: #64748b;
}
.step-num.done { background: #f0fdf4; color: #16a34a; }
.step-num.act  { background: #14b8a6; color: #fff; }

/* ── Avatar ── */
.eq-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #14b8a6, #0ea5e9);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 13px; color: #fff;
    flex-shrink: 0;
}

/* ── Checklist ── */
.eq-check-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}
.eq-check-item:last-child { border-bottom: none; }
.eq-check-item input[type=checkbox] { width: 16px; height: 16px; accent-color: #14b8a6; cursor: pointer; }

/* ── Décharge preview ── */
.decharge-preview {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 20px;
    background: #f8fafc;
    font-size: 12px;
}

/* ── Misc ── */
.mono { font-family: 'JetBrains Mono', 'Courier New', monospace; font-size: 11px; }
.btn-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-top: 12px; }

@media (max-width: 768px) {
    .eq-grid4 { grid-template-columns: 1fr 1fr; }
    .eq-grid2 { grid-template-columns: 1fr; }
}
</style>

{{-- ══ EN-TÊTE + ONGLETS ══ --}}
<div style="background:#fff;border-bottom:1px solid #e2e8f0;margin:-16px -16px 24px;padding:0">

    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px 0">
        <div>
            <h1 style="font-size:18px;font-weight:700;color:#0f172a;margin:0">Gestion des équipements</h1>
            <p style="font-size:12px;color:#64748b;margin:3px 0 0">Catalogue · Affectations · Décharges · Retours</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            @if($alertes_depart->isNotEmpty())
            <span style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:99px;padding:4px 12px;font-size:12px;font-weight:600">
                {{ $alertes_depart->count() }} alerte(s) départ
            </span>
            @endif
            <a href="{{ route('equipements.index', ['tab' => 'affecter']) }}"
               style="background:#14b8a6;color:#fff;border:none;display:inline-flex;align-items:center;gap:5px;border-radius:8px;padding:0 16px;height:36px;font-size:13px;font-weight:500;text-decoration:none">
                + Affecter un équipement
            </a>
        </div>
    </div>

    <div class="eq-nav" style="margin:0;border-bottom:none">
        <a href="{{ route('equipements.index', ['tab' => 'dash']) }}"
           class="eq-tab {{ $tab === 'dash' ? 'active' : '' }}">Tableau de bord</a>
        <a href="{{ route('equipements.index', ['tab' => 'catalogue']) }}"
           class="eq-tab {{ $tab === 'catalogue' ? 'active' : '' }}">Catalogue</a>
        <a href="{{ route('equipements.index', ['tab' => 'affecter']) }}"
           class="eq-tab {{ $tab === 'affecter' ? 'active' : '' }}">Affectation</a>
        <a href="{{ route('equipements.index', ['tab' => 'salarie']) }}"
           class="eq-tab {{ $tab === 'salarie' ? 'active' : '' }}">Fiche salarié</a>
        <a href="{{ route('equipements.index', ['tab' => 'decharge']) }}"
           class="eq-tab {{ $tab === 'decharge' ? 'active' : '' }}">Décharges</a>
        <a href="{{ route('equipements.index', ['tab' => 'retour']) }}"
           class="eq-tab {{ $tab === 'retour' ? 'active' : '' }}">Retours / Départ</a>
    </div>
</div>

@if(session('success'))
<div class="eq-alert ok" style="margin-bottom:16px">
    <div>{{ session('success') }}</div>
</div>
@endif
@if(session('error'))
<div class="eq-alert error" style="margin-bottom:16px">
    <div>{{ session('error') }}</div>
</div>
@endif


{{-- ═══════════════════════════════
     TABLEAU DE BORD
════════════════════════════════ --}}
<div class="eq-page {{ $tab === 'dash' ? 'active' : '' }}">

    {{-- ── Barre de filtre dashboard ── --}}
    <form method="GET" action="{{ route('equipements.index') }}" class="dash-filter">
        <input type="hidden" name="tab" value="dash">
        <label>Catégorie :</label>
        <select name="dash_cat">
            <option value="">Toutes les catégories</option>
            @foreach($liste_categories as $cat)
                <option value="{{ $cat }}" {{ request('dash_cat') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <label>Du :</label>
        <input type="date" name="dash_from" value="{{ request('dash_from') }}">
        <label>Au :</label>
        <input type="date" name="dash_to" value="{{ request('dash_to') }}">
        <button type="submit" class="dash-filter-btn">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18M7 12h10M11 18h2"/></svg>
            Filtrer
        </button>
        @if(request('dash_cat') || request('dash_from') || request('dash_to'))
            <a href="{{ route('equipements.index', ['tab' => 'dash']) }}" class="dash-filter-reset">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                Réinitialiser
            </a>
            <span class="dash-filter-active-badge">Filtre actif</span>
        @endif
    </form>

    {{-- ── 6 cartes métriques cliquables ── --}}
    @php
        $dashParams = array_filter(['dash_cat' => request('dash_cat'), 'dash_from' => request('dash_from'), 'dash_to' => request('dash_to')]);
    @endphp
    <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:14px;margin-bottom:20px">

        <a href="{{ route('equipements.index', array_merge($dashParams, ['tab' => 'catalogue'])) }}"
           class="eq-stat-card teal" title="Voir tout le catalogue">
            <div class="stat-val">{{ $metrics['total'] }}</div>
            <div class="stat-label">Total</div>
        </a>

        <a href="{{ route('equipements.index', array_merge($dashParams, ['tab' => 'catalogue', 'statut' => 'Affecté'])) }}"
           class="eq-stat-card blue" title="Voir les équipements affectés">
            <div class="stat-val">{{ $metrics['affectes'] }}</div>
            <div class="stat-label">Affectés</div>
        </a>

        <a href="{{ route('equipements.index', array_merge($dashParams, ['tab' => 'catalogue', 'statut' => 'Disponible'])) }}"
           class="eq-stat-card green" title="Voir les équipements disponibles">
            <div class="stat-val">{{ $metrics['disponibles'] }}</div>
            <div class="stat-label">Disponibles</div>
        </a>

        <a href="{{ route('equipements.index', array_merge($dashParams, ['tab' => 'catalogue', 'statut' => 'Maintenance'])) }}"
           class="eq-stat-card amber" title="Voir les équipements en maintenance">
            <div class="stat-val">{{ $metrics['maintenance'] }}</div>
            <div class="stat-label">Maintenance</div>
        </a>

        <a href="{{ route('equipements.index', array_merge($dashParams, ['tab' => 'catalogue', 'statut' => 'Perdu'])) }}"
           class="eq-stat-card red" title="Voir les équipements perdus">
            <div class="stat-val">{{ $metrics['perdus'] }}</div>
            <div class="stat-label">Perdus</div>
        </a>

        {{-- Valeur parc — non cliquable --}}
        <div class="eq-stat-card teal" style="border-top-color:#8b5cf6">
            <div class="stat-val" style="font-size:17px">{{ number_format($metrics['valeur_parc'], 0, ',', ' ') }}</div>
            <div class="stat-label">Valeur parc (MAD)</div>
        </div>
    </div>

    {{-- ── Panneau détail affectation (affiché au clic sur une ligne) ── --}}
    <div id="dash-detail-panel" class="dash-detail-panel">
        <div class="eq-card" style="border-left:3px solid #14b8a6;padding:16px 20px;margin-bottom:0">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:8px;height:8px;border-radius:50%;background:#14b8a6"></div>
                    <span id="dash-detail-title" style="font-size:13px;font-weight:600;color:#374151">Détail affectation</span>
                </div>
                <button onclick="closeDashDetail()"
                    style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;line-height:1;padding:0;width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center"
                    title="Fermer">×</button>
            </div>
            <div id="dash-detail-body"></div>
        </div>
    </div>

    <div class="eq-grid2">
        <div>
            {{-- Répartition catégories --}}
            <div class="eq-card">
                <div class="eq-card-title">Répartition par catégorie</div>
                @php $totalEq = max($metrics['total'], 1); @endphp
                @forelse($categories as $cat)
                <a href="{{ route('equipements.index', ['tab' => 'catalogue', 'categorie' => $cat->categorie]) }}"
                   style="text-decoration:none;display:block;margin-bottom:12px">
                    <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                        <span style="font-weight:500;color:#374151">{{ $cat->categorie }}</span>
                        <span style="color:#64748b">
                            {{ $cat->total }} / {{ $metrics['total'] }}
                            <span style="color:#14b8a6;font-size:10px;margin-left:4px">→ voir</span>
                        </span>
                    </div>
                    <div class="eq-bar">
                        <div class="eq-bar-fill" style="width:{{ round($cat->total / $totalEq * 100) }}%"></div>
                    </div>
                </a>
                @empty
                <p style="text-align:center;color:#64748b;font-size:13px;padding:16px 0">Aucun équipement enregistré</p>
                @endforelse
            </div>
        </div>

        <div>
            {{-- Alertes --}}
            @if($alertes_depart->isNotEmpty())
            <div class="eq-card" style="margin-bottom:16px">
                <div class="eq-card-title">Alertes actives</div>
                <div class="eq-alert error">
                    <div><strong>{{ $alertes_depart->count() }} contrat(s) terminé(s)</strong> — équipements toujours affectés non restitués</div>
                </div>
                <a href="{{ route('equipements.index', ['tab' => 'retour']) }}"
                   style="display:inline-flex;align-items:center;gap:5px;font-size:12px;color:#14b8a6;text-decoration:none;font-weight:500">
                    Voir les retours en attente
                </a>
            </div>
            @endif

            {{-- Dernières affectations avec détail au clic --}}
            <div class="eq-card">
                <div class="eq-card-title">
                    Dernières affectations
                    <span style="margin-left:auto;font-size:11px;color:#94a3b8;font-weight:400">Cliquer pour détail</span>
                </div>
                <table class="eq-table">
                    <thead>
                        <tr>
                            <th>Salarié</th>
                            <th>Matériel</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dernieres_affectations->take(6) as $aff)
                        @php
                            $detailData = json_encode([
                                'salarie'  => ($aff->employee->first_name ?? '') . ' ' . ($aff->employee->last_name ?? '—'),
                                'mat'      => $aff->employee->employee_number ?? $aff->employee->matricule ?? '—',
                                'fonction' => $aff->employee->position ?? $aff->employee->poste ?? '—',
                                'service'  => $aff->employee->department ?? $aff->employee->departement ?? '—',
                                'materiel' => $aff->equipement->designation ?? '—',
                                'ref'      => $aff->equipement->reference ?? '—',
                                'cat'      => $aff->equipement->categorie ?? '—',
                                'valeur'   => number_format($aff->equipement->valeur_acquisition ?? 0, 0, ',', ' '),
                                'date'     => optional($aff->date_affectation)->format('d/m/Y') ?? '—',
                                'date_retour' => optional($aff->date_retour_prevue)->format('d/m/Y') ?? '—',
                                'etat'     => $aff->etat_remise ?? '—',
                                'obs'      => $aff->observations ?? '—',
                                'decharge' => $aff->numero_decharge ?? '—',
                                'signed'   => $aff->decharge_signee ? 'Oui' : 'Non',
                            ], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT);
                        @endphp
                        <tr class="clickable-row" onclick="showAffDetail({{ $detailData }})" title="Voir le détail">
                            <td style="font-weight:500">{{ $aff->employee->first_name ?? '' }} {{ $aff->employee->last_name ?? '—' }}</td>
                            <td style="color:#64748b">{{ $aff->equipement->designation ?? '—' }}</td>
                            <td style="color:#64748b;font-size:12px">{{ optional($aff->date_affectation)->format('d/m/Y') }}</td>
                            <td><span class="eq-badge b-teal">Affecté</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center;color:#64748b;padding:20px">Aucune affectation récente</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════
     CATALOGUE
════════════════════════════════ --}}
<div class="eq-page {{ $tab === 'catalogue' ? 'active' : '' }}">

    <div class="eq-card" style="padding:14px 18px;margin-bottom:16px">
        <form method="GET" action="{{ route('equipements.index') }}"
              style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <input type="hidden" name="tab" value="catalogue">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="🔍 Rechercher"
                   style="height:36px;font-size:13px;border:1px solid #e2e8f0;border-radius:8px;padding:0 12px;width:220px;flex-shrink:0">
            <select name="categorie"
                    style="height:36px;font-size:13px;border:1px solid #e2e8f0;border-radius:8px;padding:0 10px;background:#fff;min-width:160px">
                <option value="">Toutes catégories</option>
                @foreach($liste_categories as $cat)
                <option value="{{ $cat }}" {{ request('categorie') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <select name="statut"
                    style="height:36px;font-size:13px;border:1px solid #e2e8f0;border-radius:8px;padding:0 10px;background:#fff;min-width:140px">
                <option value="">Tous statuts</option>
                <option value="Disponible"  {{ request('statut') === 'Disponible'  ? 'selected' : '' }}>Disponible</option>
                <option value="Affecté"     {{ request('statut') === 'Affecté'     ? 'selected' : '' }}>Affecté</option>
                <option value="Maintenance" {{ request('statut') === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="Perdu"       {{ request('statut') === 'Perdu'       ? 'selected' : '' }}>Perdu</option>
            </select>
            <button type="submit"
                    style="height:36px;padding:0 16px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer">
                Filtrer
            </button>
            @if(request('search') || request('categorie') || request('statut'))
            <a href="{{ route('equipements.index', ['tab' => 'catalogue']) }}"
               style="height:36px;padding:0 12px;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;text-decoration:none;display:inline-flex;align-items:center">
                ✕ Réinitialiser
            </a>
            @endif
            <div style="margin-left:auto">
                <button type="button" onclick="toggleForm('add-eq-form')"
                        style="height:36px;padding:0 16px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                    + Ajouter un équipement
                </button>
            </div>
        </form>
    </div>

    <div id="add-eq-form" style="display:none;margin-bottom:16px">
        <div class="eq-card">
            <div class="eq-card-title">Nouvel équipement</div>
            <form method="POST" action="{{ route('equipements.store') }}">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
                    <div class="fgroup" style="grid-column:1/-1">
                        <label>Désignation *</label>
                        <input type="text" name="designation" placeholder="Ex : Dell XPS 15 – Laptop Core i7" required>
                    </div>
                    <div class="fgroup">
                        <label>Catégorie *</label>
                        <select name="categorie" id="cat-select-add" onchange="toggleAutreAdd(this)" required>
                            @foreach($liste_categories as $cat)
                                @if($cat !== 'Autre')
                                <option value="{{ $cat }}">{{ $cat }}</option>
                                @endif
                            @endforeach
                            <option value="Autre">Autre (préciser…)</option>
                        </select>
                        <input type="text" name="categorie_autre" id="cat-autre-add"
                               placeholder="Nom de la nouvelle catégorie"
                               style="display:none;margin-top:6px;height:36px;border:1px solid #e2e8f0;border-radius:8px;padding:0 12px;font-size:13px;width:100%">
                    </div>
                    <div class="fgroup">
                        <label>Marque</label>
                        <input type="text" name="marque" placeholder="Dell, HP, Samsung…">
                    </div>
                    <div class="fgroup">
                        <label>Modèle</label>
                        <input type="text" name="modele" placeholder="XPS 15 9530">
                    </div>
                    <div class="fgroup">
                        <label>N° de série</label>
                        <input type="text" name="numero_serie" placeholder="SN…">
                    </div>
                    <div class="fgroup">
                        <label>Date d'acquisition</label>
                        <input type="date" name="date_acquisition">
                    </div>
                    <div class="fgroup">
                        <label>Fournisseur</label>
                        <input type="text" name="fournisseur">
                    </div>
                    <div class="fgroup">
                        <label>Valeur d'acquisition (MAD)</label>
                        <input type="number" name="valeur_acquisition" min="0" step="0.01" placeholder="0">
                    </div>
                    <div class="fgroup">
                        <label>État *</label>
                        <select name="etat" required>
                            <option>Neuf</option>
                            <option>Bon état</option>
                            <option>À réparer</option>
                            <option>Hors service</option>
                        </select>
                    </div>
                    <div class="fgroup">
                        <label>Localisation</label>
                        <input type="text" name="localisation" placeholder="Bâtiment A / Bureau 201">
                    </div>
                    <div class="fgroup">
                        <label>Statut initial *</label>
                        <select name="statut" required>
                            <option>Disponible</option>
                            <option>Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="btn-row">
                    <button type="submit"
                            style="height:36px;padding:0 18px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                        Enregistrer
                    </button>
                    <button type="button" onclick="toggleForm('add-eq-form')"
                            style="height:36px;padding:0 14px;background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;cursor:pointer">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="eq-card" style="padding:0;overflow:hidden">
        <div style="overflow-x:auto">
            <table class="eq-table" style="min-width:850px">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Désignation</th>
                        <th>Catégorie</th>
                        <th>Marque</th>
                        <th>N° série</th>
                        <th>État</th>
                        <th>Statut</th>
                        <th style="text-align:right">Valeur (MAD)</th>
                        <th style="text-align:center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipements as $eq)
                    <tr>
                        <td class="mono" style="font-weight:600;color:#0f172a">{{ $eq->reference }}</td>
                        <td style="font-weight:500">{{ $eq->designation }}</td>
                        <td><span class="{{ $eq->categorie_color }}">{{ $eq->categorie }}</span></td>
                        <td style="color:#64748b">{{ $eq->marque ?? '—' }}</td>
                        <td class="mono" style="color:#64748b">{{ $eq->numero_serie ?? '—' }}</td>
                        <td><span class="{{ $eq->etat_color }}">{{ $eq->etat }}</span></td>
                        <td><span class="{{ $eq->statut_color }}">{{ $eq->statut }}</span></td>
                        <td style="text-align:right;font-weight:600">{{ number_format($eq->valeur_acquisition, 0, ',', ' ') }}</td>
                        <td style="text-align:center">
                            @if($eq->statut === 'Disponible')
                            <a href="{{ route('equipements.index', ['tab' => 'affecter', 'equipement_id' => $eq->id]) }}"
                               title="Affecter cet équipement"
                               style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:#f0fdfa;color:#14b8a6;text-decoration:none;border:1px solid #ccfbf1">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                            </a>
                            @elseif($eq->affectationActive && $eq->affectationActive->employee_id)
                            <a href="{{ route('equipements.fiche_salarie', $eq->affectationActive->employee_id) }}"
                               title="Voir fiche salarié"
                               style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:#f8fafc;color:#64748b;text-decoration:none;border:1px solid #e2e8f0">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            @else
                            <span style="color:#cbd5e1;font-size:13px">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;color:#64748b;padding:32px;font-size:13px">
                            Aucun équipement trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-top:12px">{{ $equipements->appends(request()->query())->links() }}</div>
</div>


{{-- ═══════════════════════════════
     AFFECTATION
════════════════════════════════ --}}
<div class="eq-page {{ $tab === 'affecter' ? 'active' : '' }}">
    <div class="eq-grid2">
        <div>
            <div class="eq-card">
                <div class="eq-card-title">Nouvelle affectation</div>
                <form method="POST" action="{{ route('equipements.affecter') }}">
                    @csrf
                    <div style="display:flex;flex-direction:column;gap:12px">
                        <div class="fgroup">
                            <label>Salarié *</label>
                            <select name="employee_id" id="sel-salarie" onchange="updateSalarieInfo(this)" required>
                                <option value="">— Sélectionner un salarié —</option>
                                @foreach($employees_actifs as $emp)
                                <option value="{{ $emp->id }}"
                                    data-nom="{{ $emp->first_name }} {{ $emp->last_name }}"
                                    data-fonction="{{ $emp->position ?? $emp->poste ?? '' }}"
                                    data-service="{{ $emp->department ?? $emp->departement ?? '' }}"
                                    data-mat="{{ $emp->employee_number ?? $emp->matricule ?? '' }}"
                                    {{ (request('employee_id') == $emp->id) ? 'selected' : '' }}>
                                    {{ $emp->first_name }} {{ $emp->last_name }}
                                    @if($emp->employee_number ?? $emp->matricule ?? '')
                                        — {{ $emp->employee_number ?? $emp->matricule }}
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="sal-info" style="display:none;background:#f0fdfa;border-radius:10px;padding:12px;display:none;gap:12px;align-items:center;border:1px solid #ccfbf1">
                            <div class="eq-avatar" id="sal-avatar" style="width:40px;height:40px;font-size:14px">—</div>
                            <div>
                                <div style="font-weight:600;font-size:13px;color:#0f172a" id="sal-nom">—</div>
                                <div style="font-size:12px;color:#64748b;margin-top:2px" id="sal-detail">—</div>
                            </div>
                        </div>

                        <div class="fgroup">
                            <label>Équipement à affecter *</label>
                            <select name="equipement_id" required>
                                <option value="">— Sélectionner un équipement disponible —</option>
                                @foreach($equipements_disponibles as $eq)
                                <option value="{{ $eq->id }}" {{ (request('equipement_id') == $eq->id) ? 'selected' : '' }}>
                                    {{ $eq->reference }} — {{ $eq->designation }} ({{ $eq->etat }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                            <div class="fgroup">
                                <label>Date d'affectation *</label>
                                <input type="date" name="date_affectation" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="fgroup">
                                <label>Date de retour prévue</label>
                                <input type="date" name="date_retour_prevue">
                            </div>
                        </div>

                        <div class="fgroup">
                            <label>État au moment de la remise *</label>
                            <select name="etat_remise" required>
                                <option>Neuf</option>
                                <option>Bon état</option>
                                <option>État moyen</option>
                            </select>
                        </div>

                        <div class="fgroup">
                            <label>Observations</label>
                            <textarea name="observations" placeholder="Remarques sur l'état, accessoires remis…"></textarea>
                        </div>
                    </div>
                    <div class="btn-row">
                        <button type="submit"
                                style="height:36px;padding:0 18px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                            Valider et générer décharge
                        </button>
                        <a href="{{ route('equipements.index', ['tab' => 'catalogue']) }}"
                           style="height:36px;padding:0 14px;background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;text-decoration:none">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <div class="eq-card">
                <div class="eq-card-title">Workflow d'affectation</div>
                <div class="eq-step"><div class="step-num done">✓</div><div><div style="font-weight:600;font-size:13px;color:#0f172a">Sélection du salarié</div><div style="font-size:12px;color:#64748b;margin-top:2px">Vérification des équipements déjà en sa possession</div></div></div>
                <div class="eq-step"><div class="step-num done">✓</div><div><div style="font-weight:600;font-size:13px;color:#0f172a">Choix de l'équipement disponible</div><div style="font-size:12px;color:#64748b;margin-top:2px">Filtrage automatique sur statut = Disponible</div></div></div>
                <div class="eq-step"><div class="step-num act">3</div><div><div style="font-weight:600;font-size:13px;color:#0f172a">Saisie des détails</div><div style="font-size:12px;color:#64748b;margin-top:2px">Date, état, observations</div></div></div>
                <div class="eq-step"><div class="step-num">4</div><div><div style="font-weight:600;font-size:13px;color:#374151">Génération de la décharge</div><div style="font-size:12px;color:#64748b;margin-top:2px">Numéro DCH-AAAA-XXXXX attribué automatiquement</div></div></div>
                <div class="eq-step"><div class="step-num">5</div><div><div style="font-weight:600;font-size:13px;color:#374151">Signature du salarié</div><div style="font-size:12px;color:#64748b;margin-top:2px">Manuscrite ou confirmation RH</div></div></div>
                <div class="eq-step"><div class="step-num">6</div><div><div style="font-weight:600;font-size:13px;color:#374151">Archivage RH</div><div style="font-size:12px;color:#64748b;margin-top:2px">Statut équipement → Affecté</div></div></div>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════
     FICHE SALARIÉ
════════════════════════════════ --}}
<div class="eq-page {{ $tab === 'salarie' ? 'active' : '' }}">

    <div class="eq-card">
        <div class="eq-card-title">Consulter la fiche patrimoine d'un salarié</div>
        <div style="display:flex;gap:10px;align-items:flex-end">
            <div class="fgroup" style="flex:1">
                <label>Sélectionner un salarié</label>
                <select id="sel-salarie-view" style="height:36px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;padding:0 12px;background:#fff">
                    <option value="">— Choisir un salarié —</option>
                    @foreach($employees_actifs as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }} — {{ $emp->employee_number ?? $emp->matricule ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button"
                    style="height:36px;padding:0 18px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px"
                    onclick="var v=document.getElementById('sel-salarie-view').value; if(v) window.location='{{ url('equipements/salarie') }}/'+v;">
                Consulter la fiche
            </button>
        </div>
    </div>

    <div class="eq-card">
        <div class="eq-card-title">Salariés avec équipements affectés</div>
        <table class="eq-table">
            <thead>
                <tr>
                    <th>Salarié</th>
                    <th>Matricule</th>
                    <th>Nb équipements</th>
                    <th>Valeur confiée</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $salaries_eq = \App\Models\AffectationEquipement::where('tenant_id', auth()->user()->tenant_id)
                        ->where('statut', 'Actif')
                        ->with(['employee', 'equipement'])
                        ->get()
                        ->groupBy('employee_id');
                @endphp
                @forelse($salaries_eq as $empId => $affs)
                @php
                    $emp    = $affs->first()->employee;
                    $valeur = $affs->sum(fn($a) => $a->equipement->valeur_acquisition ?? 0);
                @endphp
                @if($emp)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            @php $ini = mb_strtoupper(mb_substr($emp->first_name ?? 'X', 0, 1) . mb_substr($emp->last_name ?? 'X', 0, 1)); @endphp
                            <div class="eq-avatar" style="width:32px;height:32px;font-size:12px">{{ $ini }}</div>
                            <span style="font-weight:500">{{ $emp->first_name }} {{ $emp->last_name }}</span>
                        </div>
                    </td>
                    <td class="mono">{{ $emp->employee_number ?? $emp->matricule ?? '—' }}</td>
                    <td><span class="eq-badge b-teal">{{ $affs->count() }}</span></td>
                    <td style="font-weight:600">{{ number_format($valeur, 0, ',', ' ') }} MAD</td>
                    <td style="text-align:center">
                        <a href="{{ route('equipements.fiche_salarie', $empId) }}"
                           style="display:inline-flex;align-items:center;gap:5px;padding:0 12px;height:30px;background:#f0fdfa;color:#14b8a6;border:1px solid #ccfbf1;border-radius:7px;font-size:12px;font-weight:500;text-decoration:none">
                            Voir fiche
                        </a>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#64748b;padding:28px;font-size:13px">
                        Aucun équipement affecté actuellement
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


{{-- ═══════════════════════════════
     DÉCHARGES
════════════════════════════════ --}}
<div class="eq-page {{ $tab === 'decharge' ? 'active' : '' }}">
    <div class="eq-grid2">
        <div>
            @if($decharges_en_attente->isNotEmpty())
            @php $first = $decharges_en_attente->first(); @endphp
            <div class="eq-card">
                <div class="eq-card-title">Aperçu — {{ $first->numero_decharge }}</div>
                <div class="decharge-preview">
                    <div style="text-align:center;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #e2e8f0">
                        <div style="font-size:15px;font-weight:700;color:#0f172a">Décharge de remise de matériel</div>
                        <div style="color:#64748b;font-size:12px;margin-top:4px">N° {{ $first->numero_decharge }} — {{ now()->format('d/m/Y') }}</div>
                    </div>
                    <div style="margin-bottom:12px">
                        <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Salarié</div>
                        <table style="font-size:12px;width:100%">
                            <tr><td style="color:#64748b;padding:3px 0;width:120px">Nom & prénom</td><td style="font-weight:600;color:#0f172a">{{ $first->employee->first_name ?? '' }} {{ $first->employee->last_name ?? '—' }}</td></tr>
                            <tr><td style="color:#64748b;padding:3px 0">Matricule</td><td>{{ $first->employee->employee_number ?? $first->employee->matricule ?? '—' }}</td></tr>
                            <tr><td style="color:#64748b;padding:3px 0">Fonction</td><td>{{ $first->employee->position ?? $first->employee->poste ?? '—' }}</td></tr>
                            <tr><td style="color:#64748b;padding:3px 0">Service</td><td>{{ $first->employee->department ?? $first->employee->departement ?? '—' }}</td></tr>
                        </table>
                    </div>
                    <div style="margin-bottom:12px;padding-top:12px;border-top:1px solid #e2e8f0">
                        <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Matériel remis</div>
                        <table style="font-size:12px;width:100%">
                            <thead>
                                <tr style="color:#64748b">
                                    <th style="text-align:left;padding:4px 0;font-weight:500">Désignation</th>
                                    <th style="text-align:left;padding:4px 0;font-weight:500">Réf.</th>
                                    <th style="text-align:left;padding:4px 0;font-weight:500">État</th>
                                    <th style="text-align:right;padding:4px 0;font-weight:500">Valeur</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding:4px 0;font-weight:500">{{ $first->equipement->designation ?? '—' }}</td>
                                    <td class="mono">{{ $first->equipement->reference ?? '—' }}</td>
                                    <td>{{ $first->etat_remise }}</td>
                                    <td style="text-align:right;font-weight:600">{{ number_format($first->equipement->valeur_acquisition ?? 0, 0, ',', ' ') }} MAD</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="padding-top:12px;border-top:1px solid #e2e8f0;font-size:11px;color:#64748b;line-height:1.7;margin-bottom:16px">
                        Je soussigné(e) <strong style="color:#0f172a">{{ $first->employee->first_name ?? '' }} {{ $first->employee->last_name ?? '' }}</strong>,
                        reconnais avoir reçu le matériel listé ci-dessus en bon état et m'engage à le restituer à ma sortie ou sur demande de la direction.
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div style="text-align:center">
                            <div style="font-size:11px;color:#64748b;margin-bottom:6px;font-weight:500">Signature du salarié</div>
                            <div style="height:52px;border:1px dashed #cbd5e1;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:11px;color:#94a3b8">En attente</div>
                        </div>
                        <div style="text-align:center">
                            <div style="font-size:11px;color:#64748b;margin-bottom:6px;font-weight:500">Cachet & signature RH</div>
                            <div style="height:52px;border:1px dashed #cbd5e1;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:11px;color:#94a3b8">En attente</div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="eq-card">
                <div class="eq-alert info">
                    <div>Aucune décharge en cours. Les décharges apparaissent ici après chaque affectation.</div>
                </div>
            </div>
            @endif
        </div>

        <div>
            @if($decharges_en_attente->isNotEmpty())
            <div class="eq-card" style="margin-bottom:16px">
                <div class="eq-card-title">Actions</div>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <button onclick="window.print()"
                            style="height:38px;background:#0f172a;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px">
                        Imprimer la décharge
                    </button>
                    <form method="POST" action="{{ route('equipements.signer_decharge', $decharges_en_attente->first()->id) }}">
                        @csrf
                        <button type="submit"
                                style="width:100%;height:38px;background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px">
                            Marquer comme signée
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <div class="eq-card">
                <div class="eq-card-title">Décharges en attente de signature</div>
                <div style="display:flex;flex-direction:column;gap:8px">
                    @forelse($decharges_en_attente as $dch)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9">
                        <div>
                            <div style="font-size:13px;font-weight:600;color:#0f172a">{{ $dch->numero_decharge }}</div>
                            <div style="font-size:11px;color:#64748b;margin-top:2px">
                                {{ $dch->employee->first_name ?? '' }} {{ $dch->employee->last_name ?? '' }}
                                — {{ $dch->equipement->reference ?? '' }}
                            </div>
                        </div>
                        <span class="eq-badge b-amber">En attente</span>
                    </div>
                    @empty
                    <p style="text-align:center;color:#64748b;font-size:13px;padding:16px 0">Aucune décharge en attente</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════
     RETOURS / DÉPART
════════════════════════════════ --}}
<div class="eq-page {{ $tab === 'retour' ? 'active' : '' }}">

    @if($alertes_depart->isNotEmpty())
    <div class="eq-alert error" style="margin-bottom:16px">
        <div>
            <strong>Alerte départ :</strong>
            {{ $alertes_depart->count() }} salarié(s) avec contrat terminé ont des équipements non restitués.
        </div>
    </div>
    @endif

    <div class="eq-grid2">
        <div>
            @if($employes_depart->isNotEmpty())
            @php $emp_dep = $employes_depart->first(); @endphp
            <div class="eq-card">
                <div class="eq-card-title">
                    Checklist — {{ $emp_dep->first_name }} {{ $emp_dep->last_name }}
                </div>
                <div style="border:1px solid #fee2e2;border-radius:8px;overflow:hidden;margin-bottom:12px">
                    <div style="padding:10px 14px;background:#fef2f2;font-size:12px;font-weight:600;display:flex;justify-content:space-between;color:#991b1b">
                        <span>Équipements à restituer</span>
                        <span>{{ $emp_dep->affectationsEquipements->count() }} en attente</span>
                    </div>
                    @foreach($emp_dep->affectationsEquipements as $aff)
                    <div class="eq-check-item">
                        <input type="checkbox" id="ck-{{ $aff->id }}" onchange="checkAll()">
                        <div style="flex:1">
                            <div style="font-weight:600;font-size:13px;color:#0f172a">
                                {{ $aff->equipement->designation ?? '—' }}
                                <span class="mono" style="color:#64748b">({{ $aff->equipement->reference ?? '' }})</span>
                            </div>
                            <div style="font-size:11px;color:#64748b;margin-top:2px">
                                Affecté depuis le {{ optional($aff->date_affectation)->format('d/m/Y') }}
                                — {{ number_format($aff->equipement->valeur_acquisition ?? 0, 0, ',', ' ') }} MAD
                            </div>
                        </div>
                        <span class="eq-badge b-red">Manquant</span>
                    </div>
                    @endforeach
                </div>

                <div id="blocage-msg" class="eq-alert error">
                    <div><strong>Processus bloqué</strong> — Cochez les équipements restitués pour débloquer.</div>
                </div>
                <div id="ok-msg" class="eq-alert ok" style="display:none">
                    <div><strong>Tous les équipements restitués</strong> — La sortie peut être validée.</div>
                </div>

                <div class="btn-row">
                    <form method="POST" action="{{ route('equipements.declarer_perte', $emp_dep->affectationsEquipements->first()->id ?? 0) }}">
                        @csrf
                        <button type="submit"
                                style="height:34px;padding:0 14px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;font-size:12px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:5px">
                            Déclarer perte
                        </button>
                    </form>
                    <form method="POST" action="{{ route('equipements.valider_sortie', $emp_dep->id) }}">
                        @csrf
                        <button type="submit" id="btn-valider-sortie" disabled
                                style="height:34px;padding:0 16px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:5px;opacity:.4">
                            Valider la sortie
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="eq-card">
                <div class="eq-alert ok">
                    <div>Aucun départ en cours nécessitant une action.</div>
                </div>
            </div>
            @endif
        </div>

        <div>
            <div class="eq-card" style="margin-bottom:16px">
                <div class="eq-card-title">Saisie de restitution rapide</div>
                @if($employes_depart->isNotEmpty())
                <form method="POST" action="{{ route('equipements.restituer', $employes_depart->first()->affectationsEquipements->first()->id ?? 0) }}">
                    @csrf
                    <div style="display:flex;flex-direction:column;gap:10px">
                        <div class="fgroup">
                            <label>Équipement restitué</label>
                            <select name="affectation_id">
                                @foreach($employes_depart as $e_dep)
                                @foreach($e_dep->affectationsEquipements as $aff)
                                <option value="{{ $aff->id }}">
                                    {{ $aff->equipement->reference ?? '' }} — {{ $aff->equipement->designation ?? '' }}
                                    ({{ $e_dep->first_name }} {{ $e_dep->last_name }})
                                </option>
                                @endforeach
                                @endforeach
                            </select>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                            <div class="fgroup">
                                <label>Date de restitution *</label>
                                <input type="date" name="date_retour_effectif" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="fgroup">
                                <label>État à la restitution *</label>
                                <select name="etat_retour" required>
                                    <option>Bon état</option>
                                    <option>Usure normale</option>
                                    <option>Endommagé</option>
                                    <option>Perdu</option>
                                </select>
                            </div>
                        </div>
                        <div class="fgroup">
                            <label>Observations</label>
                            <textarea name="observations_retour" placeholder="Accessoires restitués, dommages constatés…"></textarea>
                        </div>
                    </div>
                    <div class="btn-row">
                        <button type="submit"
                                style="height:36px;padding:0 18px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                            Enregistrer restitution
                        </button>
                    </div>
                </form>
                @else
                <p style="font-size:13px;color:#64748b;margin:0">Aucun équipement en attente de restitution.</p>
                @endif
            </div>

            <div class="eq-card">
                <div class="eq-card-title">Salariés avec équipements non restitués</div>
                <div style="display:flex;flex-direction:column;gap:8px">
                    @forelse($employes_depart as $e_dep)
                    @php $ini2 = mb_strtoupper(mb_substr($e_dep->first_name ?? 'X', 0, 1) . mb_substr($e_dep->last_name ?? 'X', 0, 1)); @endphp
                    <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:#fef2f2;border-radius:8px;border:1px solid #fecaca">
                        <div class="eq-avatar" style="width:34px;height:34px;font-size:12px;background:linear-gradient(135deg,#ef4444,#f97316)">{{ $ini2 }}</div>
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:600;color:#0f172a">{{ $e_dep->first_name }} {{ $e_dep->last_name }}</div>
                            <div style="font-size:11px;color:#64748b;margin-top:1px">{{ $e_dep->affectationsEquipements->count() }} équipement(s) en attente</div>
                        </div>
                        <span class="eq-badge b-red">Urgent</span>
                    </div>
                    @empty
                    <p style="text-align:center;color:#64748b;font-size:13px;padding:16px 0">Aucune alerte de départ</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>


<script>
/* ── Utilitaires généraux ── */
function toggleForm(id) {
    var el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function toggleAutreAdd(sel) {
    var inp = document.getElementById('cat-autre-add');
    if (!inp) return;
    if (sel.value === 'Autre') {
        inp.style.display = 'block';
        inp.required = true;
        sel.name = '_categorie_orig';
        inp.name = 'categorie';
    } else {
        inp.style.display = 'none';
        inp.required = false;
        sel.name = 'categorie';
        inp.name = 'categorie_autre';
    }
}

function updateSalarieInfo(sel) {
    var opt  = sel.options[sel.selectedIndex];
    var info = document.getElementById('sal-info');
    if (!sel.value) { info.style.display = 'none'; return; }
    info.style.display = 'flex';
    var nom = opt.getAttribute('data-nom') || '';
    var ini = nom.split(' ').map(function(p) { return p.charAt(0); }).slice(0, 2).join('').toUpperCase();
    document.getElementById('sal-avatar').textContent  = ini;
    document.getElementById('sal-nom').textContent     = nom;
    document.getElementById('sal-detail').textContent  =
        (opt.getAttribute('data-fonction') || '') + ' — ' +
        (opt.getAttribute('data-service')  || '') + ' — ' +
        (opt.getAttribute('data-mat')      || '');
}

function checkAll() {
    var cks = document.querySelectorAll('[id^="ck-"]');
    var all = true;
    cks.forEach(function(c) { if (!c.checked) all = false; });
    var bMsg = document.getElementById('blocage-msg');
    var oMsg = document.getElementById('ok-msg');
    var btn  = document.getElementById('btn-valider-sortie');
    if (bMsg) bMsg.style.display = all ? 'none' : 'flex';
    if (oMsg) oMsg.style.display = all ? 'flex' : 'none';
    if (btn)  { btn.disabled = !all; btn.style.opacity = all ? '1' : '.4'; }
}

/* ── Panneau détail affectation (dashboard) ── */
function showAffDetail(d) {
    var panel = document.getElementById('dash-detail-panel');
    document.getElementById('dash-detail-title').textContent = 'Détail affectation — ' + d.decharge;

    var signedBadge = d.signed === 'Oui'
        ? '<span style="background:#f0fdf4;color:#166534;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:600">Signée</span>'
        : '<span style="background:#fffbeb;color:#92400e;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:600">En attente</span>';

    document.getElementById('dash-detail-body').innerHTML =
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;font-size:13px">' +

        /* ── Salarié ── */
        '<div>' +
        '<div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:10px">Salarié</div>' +
        '<table style="width:100%;border-collapse:collapse">' +
        '<tr><td style="color:#64748b;padding:5px 0;width:110px;vertical-align:top">Nom</td>' +
        '    <td style="font-weight:600;color:#0f172a">' + d.salarie + '</td></tr>' +
        '<tr><td style="color:#64748b;padding:5px 0">Matricule</td>' +
        '    <td style="font-family:monospace">' + d.mat + '</td></tr>' +
        '<tr><td style="color:#64748b;padding:5px 0">Fonction</td>' +
        '    <td>' + d.fonction + '</td></tr>' +
        '<tr><td style="color:#64748b;padding:5px 0">Service</td>' +
        '    <td>' + d.service + '</td></tr>' +
        '</table></div>' +

        /* ── Équipement ── */
        '<div>' +
        '<div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:10px">Équipement</div>' +
        '<table style="width:100%;border-collapse:collapse">' +
        '<tr><td style="color:#64748b;padding:5px 0;width:110px;vertical-align:top">Désignation</td>' +
        '    <td style="font-weight:600;color:#0f172a">' + d.materiel + '</td></tr>' +
        '<tr><td style="color:#64748b;padding:5px 0">Référence</td>' +
        '    <td style="font-family:monospace">' + d.ref + '</td></tr>' +
        '<tr><td style="color:#64748b;padding:5px 0">Catégorie</td>' +
        '    <td>' + d.cat + '</td></tr>' +
        '<tr><td style="color:#64748b;padding:5px 0">Valeur</td>' +
        '    <td style="font-weight:600">' + d.valeur + ' MAD</td></tr>' +
        '<tr><td style="color:#64748b;padding:5px 0">Date affec.</td>' +
        '    <td>' + d.date + '</td></tr>' +
        '<tr><td style="color:#64748b;padding:5px 0">Retour prévu</td>' +
        '    <td>' + d.date_retour + '</td></tr>' +
        '<tr><td style="color:#64748b;padding:5px 0">État remise</td>' +
        '    <td>' + d.etat + '</td></tr>' +
        '<tr><td style="color:#64748b;padding:5px 0">Décharge</td>' +
        '    <td>' + signedBadge + '</td></tr>' +
        (d.obs && d.obs !== '—'
            ? '<tr><td style="color:#64748b;padding:5px 0;vertical-align:top">Observations</td>' +
              '    <td style="color:#64748b">' + d.obs + '</td></tr>'
            : '') +
        '</table></div>' +
        '</div>';

    panel.style.display = 'block';
    setTimeout(function() {
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 50);
}

function closeDashDetail() {
    var panel = document.getElementById('dash-detail-panel');
    panel.style.display = 'none';
}

/* ── Init ── */
(function() {
    var sel = document.getElementById('sel-salarie');
    if (sel && sel.value) updateSalarieInfo(sel);
})();
</script>

@endsection
