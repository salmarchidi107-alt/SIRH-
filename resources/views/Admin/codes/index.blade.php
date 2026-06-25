@extends('layouts.app')

@section('page-title', 'Codes de vérification 2FA')

@push('styles')
<style>
    :root {
        --primary:     #0f6b7c;
        --primary-l:   #1a8fa5;
        --primary-d:   #094f5c;
        --bg:          #f4f7fa;
        --surface:     #ffffff;
        --border:      #e2e8f0;
        --border-soft: #f1f5f9;
        --text:        #0f172a;
        --muted:       #64748b;
        --light:       #94a3b8;
        --green:       #10b981;
        --red:         #ef4444;
        --amber:       #f59e0b;
        --indigo:      #4338ca;
        --indigo-bg:   #eef2ff;
        --indigo-bd:   #c7d2fe;
    }

    .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:16px; }
    @media(max-width:768px) { .stats-row { grid-template-columns:repeat(2,1fr); } }
    .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:10px; padding:14px 16px; position:relative; overflow:hidden; }
    .stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:10px 10px 0 0; }
    .stat-card.c-blue::before  { background:var(--primary-l); }
    .stat-card.c-green::before { background:var(--green); }
    .stat-card.c-red::before   { background:var(--red); }
    .stat-card.c-amber::before { background:var(--amber); }
    .stat-label { font-size:11px; color:var(--muted); font-weight:500; margin-bottom:4px; }
    .stat-val   { font-size:26px; font-weight:800; line-height:1; }
    .stat-val.blue  { color:var(--primary-l); }
    .stat-val.green { color:var(--green); }
    .stat-val.red   { color:var(--red); }
    .stat-val.amber { color:var(--amber); }

    .trimestre-bar { background:var(--surface); border:1px solid var(--border); border-radius:10px; padding:13px 18px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:16px; }
    .trim-info { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
    .trim-label { font-size:11px; font-weight:600; letter-spacing:.05em; text-transform:uppercase; color:var(--light); }
    .trim-quarters { display:flex; gap:5px; }
    .trim-q { padding:5px 13px; border-radius:6px; border:1px solid var(--border); background:none; color:var(--muted); font-size:12px; font-weight:700; cursor:pointer; font-family:inherit; transition:all .15s; }
    .trim-q.active { background:#e0f2fe; border-color:var(--primary-l); color:var(--primary-d); }
    .trim-progress { display:flex; align-items:center; gap:8px; }
    .trim-bar-wrap { width:90px; height:4px; background:var(--border); border-radius:2px; overflow:hidden; }
    .trim-bar-fill { height:4px; border-radius:2px; transition:width .4s; }
    .trim-bar-label { font-size:10px; color:var(--light); white-space:nowrap; }
    .trim-next-badge { font-size:11px; color:var(--light); background:var(--bg); padding:4px 10px; border-radius:20px; border:1px solid var(--border); }

    .actions-bar { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; align-items:center; }
    .act-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; font-family:inherit; transition:all .15s; border:none; }
    .act-btn svg { width:13px; height:13px; flex-shrink:0; }
    .act-btn-primary { background:var(--primary); color:#fff; }
    .act-btn-primary:hover { background:var(--primary-d); }
    .act-btn-primary:disabled { opacity:.45; cursor:not-allowed; }
    .act-btn-outline { background:var(--surface); color:var(--muted); border:1px solid var(--border); }
    .act-btn-outline:hover { border-color:var(--amber); color:#92400e; background:#fffbeb; }
    .act-btn-outline:disabled { opacity:.45; cursor:not-allowed; }
    .act-btn-export { background:var(--surface); color:var(--muted); border:1px solid var(--border); }
    .act-btn-export:hover { border-color:var(--primary-l); color:var(--primary); background:#e0f2fe; }

    .missing-alert { display:flex; align-items:center; gap:10px; padding:10px 14px; border-radius:8px; background:#fef3c7; border:1px solid #fcd34d; font-size:12px; color:#92400e; font-weight:500; margin-left:auto; }
    .missing-alert svg { width:14px; height:14px; flex-shrink:0; }

    .filters-bar { background:var(--surface); border:1px solid var(--border); border-radius:10px; padding:10px 14px; display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
    .filters-caption { font-size:10px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:var(--light); white-space:nowrap; }
    .f-divider { width:1px; height:16px; background:var(--border); flex-shrink:0; }
    .f-search { flex:1; min-width:160px; padding:6px 11px; border:1px solid var(--border); border-radius:7px; font-size:12px; color:var(--text); background:var(--bg); outline:none; font-family:inherit; transition:border-color .15s; }
    .f-search:focus { border-color:var(--primary-l); }
    .f-select { padding:6px 9px; border:1px solid var(--border); border-radius:7px; font-size:12px; color:var(--text); background:var(--bg); outline:none; cursor:pointer; font-family:inherit; }
    .f-count { font-size:11px; color:var(--light); background:var(--bg); padding:4px 10px; border-radius:20px; border:1px solid var(--border); margin-left:auto; white-space:nowrap; }
    .f-reset { display:inline-flex; align-items:center; gap:4px; padding:5px 10px; border-radius:7px; border:1px solid var(--border); background:var(--bg); color:var(--muted); font-size:11px; font-weight:500; cursor:pointer; font-family:inherit; transition:all .15s; }
    .f-reset:hover { border-color:var(--red); color:var(--red); background:#fef2f2; }
    .f-reset svg { width:10px; height:10px; }

    .codes-card { background:var(--surface); border:1px solid var(--border); border-radius:10px; overflow:hidden; }
    .codes-table { width:100%; border-collapse:collapse; }
    .codes-table thead th { background:#fafafa; padding:9px 13px; text-align:left; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--light); border-bottom:1px solid var(--border); white-space:nowrap; }
    .codes-table tbody tr { transition:background .1s; }
    .codes-table tbody tr:hover td { background:#f0fdfa; }
    .codes-table tbody tr:last-child td { border-bottom:none; }
    .codes-table td { padding:9px 13px; border-bottom:1px solid var(--border-soft); vertical-align:middle; }

    .user-row  { display:flex; align-items:center; gap:8px; }
    .user-av   { width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0; background:#e0f2fe; color:#0f6b7c; }
    .user-name { font-size:12px; font-weight:600; color:var(--text); }
    .user-email{ font-size:10px; color:var(--light); }

    .role-badge { display:inline-flex; align-items:center; padding:2px 6px; border-radius:4px; font-size:9px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; margin-top:2px; }
    .role-admin    { background:#e0f2fe; color:#0f6b7c; }
    .role-rh       { background:#dbeafe; color:#1e40af; }
    .role-employee { background:#f0fdf4; color:#166534; }

    .code-digits { display:inline-flex; gap:3px; align-items:center; }
    .code-digit  { width:24px; height:28px; border:1.5px solid var(--indigo-bd); border-radius:5px; background:var(--indigo-bg); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800; color:var(--indigo); font-family:'Courier New',monospace; transition:all .3s; }
    .code-digit.updated    { border-color:#6ee7b7; background:#d1fae5; color:#065f46; animation:digitFlash .5s ease; }
    .code-digit.st-revoked { border-color:#fca5a5; background:#fee2e2; color:#991b1b; }
    .code-digit.st-expired { border-color:#fde047; background:#fefce8; color:#a16207; }
    .code-digit.st-used    { border-color:#6ee7b7; background:#d1fae5; color:#065f46; }
    @keyframes digitFlash { 0%{transform:scale(1.15)} 60%{transform:scale(1.05)} 100%{transform:scale(1)} }

    .badge        { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:20px; font-size:10px; font-weight:600; white-space:nowrap; }
    .badge-dot    { width:5px; height:5px; border-radius:50%; background:currentColor; }
    .badge-assigned  { background:#e0f2fe; color:var(--primary-d); }
    .badge-used-once { background:#d1fae5; color:#065f46; }
    .badge-revoked   { background:#fee2e2; color:#991b1b; }
    .badge-expired   { background:#fef3c7; color:#92400e; }

    .actions-cell { display:flex; align-items:center; justify-content:flex-end; gap:5px; }
    .btn-replace,
    .btn-revoke { display:inline-flex; align-items:center; gap:4px; padding:4px 9px; border-radius:6px; border:1px solid var(--border); background:transparent; color:var(--light); font-size:10px; font-weight:600; cursor:pointer; font-family:inherit; transition:all .15s; }
    .btn-replace:hover { border-color:var(--indigo); color:var(--indigo); background:var(--indigo-bg); }
    .btn-revoke:hover  { border-color:var(--red); color:var(--red); background:#fef2f2; }
    .btn-replace svg, .btn-revoke svg { width:10px; height:10px; }
    .btn-replace.loading { opacity:.5; pointer-events:none; }

    .flash-success { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; border-radius:8px; padding:10px 14px; font-size:12px; font-weight:500; display:flex; gap:8px; align-items:center; margin-bottom:14px; }
    .flash-success svg { width:13px; height:13px; flex-shrink:0; }

    .empty-state { text-align:center; padding:48px 20px; color:var(--light); }
    .empty-state svg { margin:0 auto 12px; display:block; opacity:.35; }
    .empty-title { font-size:14px; font-weight:600; color:var(--muted); }
    .empty-sub   { font-size:12px; margin-top:4px; }

    .overlay { position:fixed; inset:0; background:rgba(13,33,55,.5); display:flex; align-items:center; justify-content:center; z-index:9998; opacity:0; pointer-events:none; transition:opacity .2s; }
    .overlay.show { opacity:1; pointer-events:all; }
    .modal { background:var(--surface); border-radius:12px; padding:24px; width:420px; max-width:92vw; box-shadow:0 24px 64px rgba(0,0,0,.2); transform:scale(.96); transition:transform .2s cubic-bezier(.16,1,.3,1); }
    .overlay.show .modal { transform:scale(1); }
    .modal-title { font-size:15px; font-weight:700; color:var(--text); margin-bottom:8px; }
    .modal-body  { font-size:13px; color:var(--muted); line-height:1.6; margin-bottom:20px; }
    .modal-acts  { display:flex; gap:8px; justify-content:flex-end; }
    .modal-cancel { padding:8px 16px; border-radius:8px; border:1px solid var(--border); background:var(--bg); color:var(--muted); font-size:13px; font-weight:500; cursor:pointer; font-family:inherit; }
    .modal-cancel:hover { border-color:var(--red); color:var(--red); }
    .modal-ok { padding:8px 16px; border-radius:8px; border:none; background:var(--primary); color:#fff; font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; }
    .modal-ok:hover { background:var(--primary-d); }
    .modal-ok.danger { background:var(--red); }
    .modal-ok.danger:hover { background:#dc2626; }

    .toast { position:fixed; bottom:22px; right:22px; z-index:9999; display:flex; align-items:center; gap:8px; padding:10px 15px; border-radius:9px; font-size:12px; font-weight:500; color:#fff; background:var(--primary); box-shadow:0 8px 32px rgba(0,0,0,.18); transform:translateY(70px); opacity:0; transition:all .3s cubic-bezier(.16,1,.3,1); pointer-events:none; max-width:320px; }
    .toast.show  { transform:translateY(0); opacity:1; }
    .toast.error { background:var(--red); }
    .toast.warn  { background:var(--amber); color:#451a03; }
    .toast svg   { width:13px; height:13px; flex-shrink:0; }
    .v-spin { animation:vSpin .6s linear infinite; display:inline-block; }
    @keyframes vSpin { to { transform:rotate(360deg); } }
</style>
@endpush

@section('content')

@php
    use App\Models\VerificationCode as VC;

    $cqNum      = (int) ceil(now()->month / 3);
    $cqLabel    = 'T' . $cqNum . '-' . now()->year;
    $qStart     = ($cqNum - 1) * 3 + 1;
    $qProgress  = min(max((int) round(((now()->month - $qStart) * 30 + now()->day) / 90 * 100), 0), 100);
    $nextStarts = [1 => '01/04', 2 => '01/07', 3 => '01/10', 4 => '01/01'];
    $nextYear   = $cqNum === 4 ? now()->year + 1 : now()->year;
    $nextDate   = $nextStarts[$cqNum] . '/' . $nextYear;
@endphp

@if(session('success'))
<div class="flash-success">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
@endif

{{-- ── Stats ─────────────────────────────────────────────────────────────── --}}
<div class="stats-row">
    <div class="stat-card c-blue">
        <div class="stat-label">Attribués (jamais utilisés)</div>
        <div class="stat-val blue" id="statAssigned">{{ $stats['assigned_this_quarter'] - $stats['used_at_least_once'] }}</div>
    </div>
    <div class="stat-card c-green">
        <div class="stat-label">Utilisés (réutilisables)</div>
        <div class="stat-val green" id="statUsed">{{ $stats['used_at_least_once'] }}</div>
    </div>
    <div class="stat-card c-red">
        <div class="stat-label">Révoqués ce trimestre</div>
        {{-- FIX : valeur initialisée à 0, recalculée par JS depuis le tableau --}}
        <div class="stat-val red" id="statRevoked">0</div>
    </div>
    <div class="stat-card c-amber">
        <div class="stat-label">Expiré</div>
        <div class="stat-val amber" id="statMissing">{{ $stats['missing_count'] }}</div>
    </div>
</div>

{{-- ── Trimestre ────────────────────────────────────────────────────────── --}}
<div class="trimestre-bar">
    <div class="trim-info">
        <span class="trim-label">Trimestre</span>
        <div class="trim-quarters">
            @foreach([1,2,3,4] as $qn)
            @php
                $qP    = $qn < $cqNum ? 100 : ($qn === $cqNum ? $qProgress : 0);
                $qNx   = $nextStarts[$qn] . '/' . ($qn === 4 ? now()->year + 1 : now()->year);
                $qColor= $qP >= 100 ? 'var(--green)' : ($qP === 0 ? 'var(--light)' : 'var(--primary-l)');
            @endphp
            <button class="trim-q {{ $qn === $cqNum ? 'active' : '' }}"
                    data-q="{{ $qn }}" data-progress="{{ $qP }}"
                    data-next="{{ $qNx }}" data-color="{{ $qColor }}"
                    onclick="selectQ(this)">T{{ $qn }}</button>
            @endforeach
        </div>
        <div class="trim-progress">
            <div class="trim-bar-wrap">
                <div class="trim-bar-fill" id="trimBarFill"
                     style="width:{{ $qProgress }}%;background:{{ $qProgress >= 100 ? 'var(--green)' : 'var(--primary-l)' }};"></div>
            </div>
            <span class="trim-bar-label" id="trimBarLabel">{{ $qProgress }}% écoulé</span>
        </div>
        <span class="trim-next-badge" id="trimNextLabel">Renouvellement : {{ $nextDate }}</span>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <span style="font-size:11px;color:var(--muted);">Couverture :</span>
        <span style="font-size:14px;font-weight:800;color:{{ $stats['coverage_pct'] >= 100 ? 'var(--green)' : ($stats['coverage_pct'] >= 50 ? 'var(--amber)' : 'var(--red)') }};"
              id="statCoverage">{{ $stats['coverage_pct'] }}%</span>
        <span style="font-size:11px;color:var(--light);">({{ $stats['active_employees'] }} employé(s))</span>
    </div>
</div>

{{-- ── Actions rapides ──────────────────────────────────────────────────── --}}
<div class="actions-bar">
    <button class="act-btn act-btn-primary" id="btnGenerate" onclick="generateMissing()">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        <span id="btnGenerateLbl">Générer les codes manquants</span>
    </button>

    <button class="act-btn act-btn-outline" id="btnRenew" onclick="confirmRenewQuarter()">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        <span id="btnRenewLbl">Renouveler le trimestre</span>
    </button>

    <button class="act-btn act-btn-export" onclick="exportPDF()">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 13h4M10 17h4M13 3v5a1 1 0 001 1h4"/>
        </svg>
        Exporter PDF
    </button>

    @if($stats['missing_count'] > 0)
    <div class="missing-alert">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <strong>{{ $stats['missing_count'] }} employé(s)</strong>&nbsp;sans code ce trimestre
    </div>
    @endif
</div>

{{-- ── Filtres ──────────────────────────────────────────────────────────── --}}
<div class="filters-bar">
    <span class="filters-caption">Filtres</span>
    <div class="f-divider"></div>
    <input type="text" id="fSearch" class="f-search"
           placeholder="Nom, email ou code…" oninput="filterRows()">
    <select class="f-select" id="fStatus" onchange="filterRows()">
        <option value="">Tous les statuts</option>
        <option value="assigned">Attribué</option>
        <option value="used_once">Utilisé</option>
        <option value="revoked">Révoqué</option>
        <option value="expired">Expiré</option>
    </select>
    <button class="f-reset" onclick="resetFilters()">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Réinitialiser
    </button>
    <span class="f-count" id="fCount">{{ count($rows) }} utilisateur(s)</span>
</div>

{{-- ── Tableau ──────────────────────────────────────────────────────────── --}}
<div class="codes-card">
    @if(count($rows) > 0)
    <table class="codes-table" id="codesTable">
        <thead>
            <tr>
                <th style="width:32px">#</th>
                <th>Collaborateur</th>
                <th>Code 2FA</th>
                <th>Statut</th>
                <th>Trimestre</th>
                <th>Attribué le</th>
                <th>Utilisé le</th>
                <th style="text-align:right;width:160px">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $code)
            @php
                $isUsedOnce = $code->status === VC::STATUS_ASSIGNED && !is_null($code->used_at);
                $isAssigned = $code->status === VC::STATUS_ASSIGNED && is_null($code->used_at);
                $isRevoked  = $code->status === VC::STATUS_REVOKED;
                $isExpired  = $code->status === VC::STATUS_EXPIRED;

                if ($isUsedOnce) {
                    $badgeClass   = 'badge-used-once';
                    $badgeLabel   = 'Utilisé';
                    $digitClass   = 'st-used';
                    $visualStatus = 'used_once';
                } elseif ($isAssigned) {
                    $badgeClass   = 'badge-assigned';
                    $badgeLabel   = 'Attribué';
                    $digitClass   = '';
                    $visualStatus = 'assigned';
                } elseif ($isRevoked) {
                    $badgeClass   = 'badge-revoked';
                    $badgeLabel   = 'Révoqué';
                    $digitClass   = 'st-revoked';
                    $visualStatus = 'revoked';
                } else {
                    $badgeClass   = 'badge-expired';
                    $badgeLabel   = 'Expiré';
                    $digitClass   = 'st-expired';
                    $visualStatus = 'expired';
                }

                $digits    = str_split(str_pad($code->code, 6, '0', STR_PAD_LEFT));
                $roleClass = match($code->user?->role ?? '') {
                    'admin' => 'role-admin',
                    'rh'    => 'role-rh',
                    default => 'role-employee',
                };
                $roleLabel = match($code->user?->role ?? '') {
                    'admin' => 'Admin',
                    'rh'    => 'RH',
                    default => 'Employé',
                };
                $safeName = addslashes($code->user?->name ?? 'cet utilisateur');
            @endphp
            <tr class="code-row"
                data-status="{{ $visualStatus }}"
                data-search="{{ strtolower(($code->user?->name ?? '') . ' ' . ($code->user?->email ?? '') . ' ' . $code->code) }}">
                <td style="font-size:10px;color:var(--light);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td>
                    @if($code->user)
                    <div class="user-row">
                        <div class="user-av">{{ strtoupper(substr($code->user->name ?? $code->user->email, 0, 2)) }}</div>
                        <div>
                            <div class="user-name">{{ $code->user->name ?? '—' }}</div>
                            <div class="user-email">{{ $code->user->email }}</div>
                            <span class="role-badge {{ $roleClass }}">{{ $roleLabel }}</span>
                        </div>
                    </div>
                    @else
                    <div class="user-row">
                        <div class="user-av" style="background:#fee2e2;color:#991b1b;">!</div>
                        <div>
                            <div class="user-name" style="color:var(--red);font-size:11px;">Orphelin</div>
                            <div class="user-email">user introuvable</div>
                        </div>
                    </div>
                    @endif
                </td>
                <td>
                    <div class="code-digits" id="digits-{{ $code->id }}">
                        @foreach($digits as $d)
                        <div class="code-digit {{ $digitClass }}">{{ $d }}</div>
                        @endforeach
                    </div>
                </td>
                <td>
                    <span class="badge {{ $badgeClass }}" id="badge-{{ $code->id }}">
                        <span class="badge-dot"></span>{{ $badgeLabel }}
                    </span>
                </td>
                <td style="font-size:11px;color:var(--muted);font-family:monospace;">{{ $code->quarter ?? '—' }}</td>
                <td style="font-size:11px;color:var(--muted);">{{ $code->assigned_at?->format('d/m/Y') ?? '—' }}</td>
                <td style="font-size:11px;color:{{ $isUsedOnce ? 'var(--green)' : 'var(--muted)' }};font-weight:{{ $isUsedOnce ? '600' : '400' }};"
                    id="used-cell-{{ $code->id }}">
                    @if($isUsedOnce)
                        <span title="{{ $code->used_at?->format('d/m/Y à H:i') }}">
                            {{ $code->used_at?->format('d/m/Y') }}<br>
                            <span style="font-size:10px;opacity:.85;">{{ $code->used_at?->format('H:i') }}</span>
                        </span>
                    @else
                        —
                    @endif
                </td>
                <td>
                    <div class="actions-cell" id="actions-{{ $code->id }}">
                        @if($code->status === VC::STATUS_ASSIGNED && $code->user_id)
                        <button class="btn-replace" id="rbtn-{{ $code->id }}"
                                onclick="replaceForUser({{ $code->user_id }}, {{ $code->id }}, '{{ $safeName }}')">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Remplacer
                        </button>
                        <button class="btn-revoke" id="vbtn-{{ $code->id }}"
                                onclick="confirmRevoke({{ $code->id }}, '{{ $safeName }}')">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            Révoquer
                        </button>
                        @else
                        <span style="font-size:11px;color:var(--light);">—</span>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
        </svg>
        <div class="empty-title">Aucun code trouvé</div>
        <div class="empty-sub">Cliquez sur "Générer les codes manquants" pour initialiser les codes 2FA.</div>
    </div>
    @endif
</div>

{{-- ── Modal ────────────────────────────────────────────────────────────── --}}
<div class="overlay" id="vModal" style="display:none;">
    <div class="modal">
        <div class="modal-title" id="vModalTitle">Confirmer</div>
        <div class="modal-body"  id="vModalBody">Cette action est irréversible.</div>
        <div class="modal-acts">
            <button class="modal-cancel" onclick="closeModal()">Annuler</button>
            <button class="modal-ok" id="vModalOk">Confirmer</button>
        </div>
    </div>
</div>

{{-- ── Toast ────────────────────────────────────────────────────────────── --}}
<div class="toast" id="vToast">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
    </svg>
    <span id="vToastMsg"></span>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
const CSRF            = document.querySelector('meta[name="csrf-token"]').content;
const URL_GENERATE    = "{{ route('admin.codes.generate-missing') }}";
const URL_RENEW       = "{{ route('admin.codes.renew-quarter') }}";
const URL_REPLACE_TPL = "{{ route('admin.codes.replace-user', ['user' => '~~UID~~']) }}";
const URL_REVOKE_TPL  = "{{ route('admin.codes.revoke', ['verificationCode' => '~~VID~~']) }}";

function replaceUrl(tpl, id) {
    return tpl.replace('~~UID~~', id).replace('~~VID~~', id);
}

/* ── FIX PRINCIPAL : compteur révoqués calculé depuis le DOM ─────────────
   On compte uniquement les lignes visibles dans le tableau avec
   data-status="revoked". Ainsi :
   - Au chargement : reflète exactement ce qui est affiché
   - Après un remplacement : la ligne repasse en "assigned" → compteur baisse
   - Après une révocation manuelle : la ligne passe en "revoked" → compteur monte
   Aucune dépendance au serveur, aucun faux positif.
───────────────────────────────────────────────────────────────────────── */
function syncRevokedCount() {
    const count = document.querySelectorAll('.code-row[data-status="revoked"]').length;
    const el    = document.getElementById('statRevoked');
    if (el) el.textContent = count;
}

/* Lancer au chargement de la page */
document.addEventListener('DOMContentLoaded', syncRevokedCount);

/* ── Trimestre ───────────────────────────────────────────────────────────── */
function selectQ(btn) {
    document.querySelectorAll('.trim-q').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const pct  = parseInt(btn.dataset.progress);
    const fill = document.getElementById('trimBarFill');
    fill.style.width      = pct + '%';
    fill.style.background = btn.dataset.color;
    document.getElementById('trimBarLabel').textContent  = pct + '% écoulé';
    document.getElementById('trimNextLabel').textContent = 'Renouvellement : ' + btn.dataset.next;
}

/* ── Générer codes manquants ─────────────────────────────────────────────── */
async function generateMissing() {
    const btn = document.getElementById('btnGenerate');
    const lbl = document.getElementById('btnGenerateLbl');
    btn.disabled = true;
    lbl.textContent = 'En cours…';
    try {
        const res  = await fetch(URL_GENERATE, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message);
            setTimeout(() => window.location.reload(), 1600);
        } else {
            showToast(data.message ?? 'Erreur', 'error');
            btn.disabled = false;
        }
    } catch {
        showToast('Erreur réseau. Réessayez.', 'error');
        btn.disabled = false;
    } finally {
        lbl.textContent = 'Générer les codes manquants';
    }
}

/* ── Renouvellement trimestriel ──────────────────────────────────────────── */
function confirmRenewQuarter() {
    openModal(
        'Renouveler le trimestre',
        `Les codes actifs du <strong>trimestre précédent</strong> seront expirés.<br>
         Un nouveau code sera créé pour <strong>chaque employé actif</strong>.<br><br>
         <strong>Cette action est irréversible.</strong>`,
        doRenewQuarter,
        true
    );
}

async function doRenewQuarter() {
    const btn = document.getElementById('btnRenew');
    const lbl = document.getElementById('btnRenewLbl');
    btn.disabled = true;
    lbl.textContent = 'En cours…';
    try {
        const res  = await fetch(URL_RENEW, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message);
            setTimeout(() => window.location.reload(), 1600);
        } else {
            showToast(data.message ?? 'Erreur', 'error');
        }
    } catch {
        showToast('Erreur réseau. Réessayez.', 'error');
    } finally {
        btn.disabled = false;
        lbl.textContent = 'Renouveler le trimestre';
    }
}

/* ── Remplacement individuel ─────────────────────────────────────────────── */
async function replaceForUser(userId, codeId, userName) {
    const btn  = document.getElementById('rbtn-' + codeId);
    const wrap = document.getElementById('digits-' + codeId);
    if (!btn || !wrap) return;

    btn.classList.add('loading');
    btn.innerHTML = '<span class="v-spin">⟳</span> En cours…';

    const url = replaceUrl(URL_REPLACE_TPL, userId);

    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();

        if (data.success) {
            /* Mise à jour chiffres */
            const digs = String(data.new_code).padStart(6, '0').split('');
            wrap.querySelectorAll('.code-digit').forEach((cell, i) => {
                cell.textContent = digs[i];
                cell.className   = 'code-digit updated';
                setTimeout(() => cell.className = 'code-digit', 2200);
            });

            /* Mise à jour IDs */
            wrap.id = 'digits-' + data.new_id;
            btn.id  = 'rbtn-'   + data.new_id;
            btn.setAttribute('onclick', `replaceForUser(${userId}, ${data.new_id}, '${userName.replace(/'/g, "\\'")}')`);

            /* Réinitialiser cellule "Utilisé le" */
            const usedCell = document.getElementById('used-cell-' + codeId);
            if (usedCell) {
                usedCell.id               = 'used-cell-' + data.new_id;
                usedCell.textContent      = '—';
                usedCell.style.color      = 'var(--muted)';
                usedCell.style.fontWeight = '400';
            }

            /* Réinitialiser badge */
            const badge = document.getElementById('badge-' + codeId);
            if (badge) {
                badge.id        = 'badge-' + data.new_id;
                badge.className = 'badge badge-assigned';
                badge.innerHTML = '<span class="badge-dot"></span>Attribué';
            }

            /* Remettre la ligne en "assigned" dans le DOM */
            btn.closest('tr').dataset.status = 'assigned';

            /* Mettre à jour bouton Révoquer */
            const revokeBtn = document.getElementById('vbtn-' + codeId);
            if (revokeBtn) {
                revokeBtn.id = 'vbtn-' + data.new_id;
                revokeBtn.setAttribute('onclick', `confirmRevoke(${data.new_id}, '${userName.replace(/'/g, "\\'")}')`);
            }

            /* Mettre à jour cellule actions */
            const actionsCell = document.getElementById('actions-' + codeId);
            if (actionsCell) actionsCell.id = 'actions-' + data.new_id;

            /* FIX : resync compteur — la ligne est repassée en "assigned" */
            syncRevokedCount();

            showToast('Nouveau code attribué à ' + userName);
        } else {
            showToast(data.message ?? 'Erreur lors du remplacement.', 'error');
        }
    } catch {
        showToast('Erreur réseau. Réessayez.', 'error');
    } finally {
        btn.classList.remove('loading');
        btn.innerHTML = svgRotate(10) + ' Remplacer';
    }
}

/* ── Révocation individuelle ─────────────────────────────────────────────── */
function confirmRevoke(codeId, userName) {
    openModal(
        'Révoquer le code de ' + userName,
        `Le code sera <strong>définitivement révoqué</strong>.<br>
         L'utilisateur n'aura plus de code actif jusqu'à attribution d'un nouveau.<br><br>
         <strong>Cette action est irréversible.</strong>`,
        () => doRevoke(codeId, userName),
        true
    );
}

async function doRevoke(codeId, userName) {
    const url = replaceUrl(URL_REVOKE_TPL, codeId);

    try {
        const res  = await fetch(url, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ reason: '' })
        });
        const data = await res.json();

        if (data.success) {
            const row = document.getElementById('rbtn-'   + codeId)?.closest('tr')
                     ?? document.getElementById('digits-' + codeId)?.closest('tr');
            if (row) {
                row.querySelectorAll('.code-digit').forEach(d => d.className = 'code-digit st-revoked');
                const badge = row.querySelector('.badge');
                if (badge) {
                    badge.className = 'badge badge-revoked';
                    badge.innerHTML = '<span class="badge-dot"></span>Révoqué';
                }
                const actCell = row.querySelector('.actions-cell');
                if (actCell) actCell.innerHTML = '<span style="font-size:11px;color:var(--light);">—</span>';

                /* Passer la ligne en "revoked" AVANT de resync */
                row.dataset.status = 'revoked';
            }

            /* FIX : resync compteur depuis le DOM */
            syncRevokedCount();

            showToast('Code de ' + userName + ' révoqué avec succès.');
        } else {
            showToast(data.message ?? 'Erreur lors de la révocation.', 'error');
        }
    } catch {
        showToast('Erreur réseau. Réessayez.', 'error');
    }
}

/* ── Filtres ─────────────────────────────────────────────────────────────── */
function filterRows() {
    const q = document.getElementById('fSearch').value.toLowerCase().trim();
    const s = document.getElementById('fStatus').value;
    let visible = 0;
    document.querySelectorAll('.code-row').forEach(row => {
        const show = (!q || row.dataset.search.includes(q)) && (!s || row.dataset.status === s);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('fCount').textContent = visible + ' utilisateur(s)';
}

function resetFilters() {
    document.getElementById('fSearch').value = '';
    document.getElementById('fStatus').value = '';
    document.querySelectorAll('.code-row').forEach(r => r.style.display = '');
    document.getElementById('fCount').textContent =
        document.querySelectorAll('.code-row').length + ' utilisateur(s)';
}

/* ── Export PDF ──────────────────────────────────────────────────────────── */
function exportPDF() {
    if (!window.jspdf) { showToast('jsPDF non chargé, réessayez.', 'warn'); return; }
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

    const activeQ = document.querySelector('.trim-q.active');
    const quarter = activeQ ? 'T' + activeQ.dataset.q + '-' + new Date().getFullYear() : '';

    doc.setFontSize(14); doc.setFont('helvetica', 'bold');
    doc.text('Codes de vérification 2FA', 14, 16);
    doc.setFontSize(9); doc.setFont('helvetica', 'normal');
    doc.text('Trimestre : ' + quarter, 14, 23);
    doc.text('Généré le : ' + new Date().toLocaleDateString('fr-FR', {
        day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'
    }), 14, 28);

    const rows = [];
    document.querySelectorAll('#codesTable tbody tr.code-row').forEach(tr => {
        if (tr.style.display === 'none') return;
        const tds   = tr.querySelectorAll('td');
        const name  = tr.querySelector('.user-name')?.textContent?.trim() ?? '—';
        const email = tr.querySelector('.user-email')?.textContent?.trim() ?? '—';
        const code  = [...tr.querySelectorAll('.code-digit')].map(d => d.textContent.trim()).join('');
        const status= tr.querySelector('.badge')?.textContent?.trim().replace('•','').trim() ?? '—';
        rows.push([
            String(rows.length + 1).padStart(2, '0'),
            name, email, code, status,
            tds[4]?.textContent?.trim() ?? '—',
            tds[5]?.textContent?.trim() ?? '—',
            tds[6]?.textContent?.trim().replace(/\s+/g, ' ') ?? '—',
        ]);
    });

    if (!rows.length) { showToast('Aucune ligne à exporter.', 'warn'); return; }

    doc.autoTable({
        startY: 34,
        head: [['#', 'Employé', 'Email', 'Code 2FA', 'Statut', 'Trimestre', 'Attribué le', 'Utilisé le']],
        body: rows,
        theme: 'grid',
        styles: { font:'helvetica', fontSize:8, cellPadding:3, textColor:20, lineColor:180, lineWidth:.2 },
        headStyles: { fillColor:[240,240,240], textColor:20, fontStyle:'bold', halign:'center' },
        columnStyles: {
            0: { halign:'center', cellWidth:8 },
            3: { halign:'center', cellWidth:22, font:'courier', fontSize:10, fontStyle:'bold' },
            4: { halign:'center', cellWidth:20 },
            5: { halign:'center', cellWidth:20 },
            6: { halign:'center', cellWidth:24 },
            7: { halign:'center', cellWidth:28 },
        },
        alternateRowStyles: { fillColor:[250,250,250] },
        margin: { left:14, right:14 },
    });

    const pageCount = doc.internal.getNumberOfPages();
    for (let p = 1; p <= pageCount; p++) {
        doc.setPage(p); doc.setFontSize(8); doc.setTextColor(150);
        doc.text(`Page ${p} / ${pageCount}`,
            doc.internal.pageSize.getWidth() / 2,
            doc.internal.pageSize.getHeight() - 8,
            { align: 'center' });
    }
    doc.save('2FA_codes_' + quarter + '.pdf');
    showToast('PDF exporté avec succès.');
}

/* ── Modal ───────────────────────────────────────────────────────────────── */
let _modalCb = null;

function openModal(title, body, cb, danger = false) {
    document.getElementById('vModalTitle').textContent = title;
    document.getElementById('vModalBody').innerHTML    = body;
    const ok = document.getElementById('vModalOk');
    ok.className = 'modal-ok' + (danger ? ' danger' : '');
    _modalCb = cb;
    document.getElementById('vModal').classList.add('show');
}

function closeModal() {
    document.getElementById('vModal').classList.remove('show');
    _modalCb = null;
}

document.getElementById('vModalOk').addEventListener('click', () => {
    const cb = _modalCb;
    closeModal();
    if (cb) cb();
});

document.getElementById('vModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

/* ── Toast ───────────────────────────────────────────────────────────────── */
let _toastTimer = null;
function showToast(msg, type = 'success') {
    const t = document.getElementById('vToast');
    document.getElementById('vToastMsg').textContent = msg;
    t.className = 'toast ' + type + ' show';
    if (_toastTimer) clearTimeout(_toastTimer);
    _toastTimer = setTimeout(() => t.classList.remove('show'), 3400);
}

/* ── SVG helper ──────────────────────────────────────────────────────────── */
function svgRotate(s) {
    return `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="width:${s}px;height:${s}px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>`;
}
</script>
@endpush
