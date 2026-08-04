<?php $__env->startSection('page-title', 'Codes de vérification 2FA'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="sa-breadcrumb">
        <a href="<?php echo e(route('superadmin.dashboard')); ?>">Dashboard</a>
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span>Codes 2FA</span>
    </div>
    <div class="sa-page-title">Codes de vérification 2FA</div>
    <div class="sa-page-sub">Attribution automatique — codes permanents jusqu'à révocation ou remplacement.</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
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

    /* ── Stats globales ─────────────────────────────── */
    .stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:14px; }
    .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:10px; padding:14px 16px; position:relative; overflow:hidden; }
    .stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:10px 10px 0 0; }
    .stat-card.c-blue::before  { background:var(--primary-l); }
    .stat-card.c-green::before { background:var(--green); }
    .stat-card.c-red::before   { background:var(--red); }
    .stat-label { font-size:11px; color:var(--muted); font-weight:500; margin-bottom:4px; }
    .stat-val   { font-size:26px; font-weight:800; line-height:1; }
    .stat-val.blue  { color:var(--primary-l); }
    .stat-val.green { color:var(--green); }
    .stat-val.red   { color:var(--red); }

    /* ── Panel Générer ──────────────────────── */
    .gen-panel { background:var(--surface); border:1px solid var(--border); border-radius:10px; margin-bottom:14px; overflow:hidden; }
    .gen-panel-header { padding:13px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .gen-panel-title { font-size:13px; font-weight:700; color:var(--text); }
    .gen-panel-sub   { font-size:11px; color:var(--light); margin-top:1px; }
    .gen-panel-toggle { padding:5px 12px; border-radius:7px; border:1px solid var(--border); background:var(--bg); color:var(--muted); font-size:11px; font-weight:600; cursor:pointer; font-family:inherit; transition:all .15s; }
    .gen-panel-toggle:hover { border-color:var(--primary-l); color:var(--primary); }
    .gen-panel-body { padding:18px; display:none; }
    .gen-panel-body.open { display:block; }
    .gen-inner { display:flex; gap:16px; flex-wrap:wrap; align-items:flex-start; }
    .gen-left { display:flex; flex-direction:column; gap:10px; min-width:200px; }
    .gen-form-label { font-size:11px; font-weight:600; color:var(--muted); }
    .gen-form-select { padding:7px 10px; border:1px solid var(--border); border-radius:7px; font-size:12px; color:var(--text); background:var(--bg); outline:none; font-family:inherit; cursor:pointer; transition:border-color .15s; min-width:200px; }
    .gen-form-select:focus { border-color:var(--primary-l); background:var(--surface); }
    .tenant-preview { flex:1; min-width:260px; background:var(--bg); border:1px solid var(--border); border-radius:9px; padding:14px 16px; display:none; }
    .tenant-preview.visible { display:block; }
    .tp-title { font-size:11px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px; }
    .tp-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:8px; }
    .tp-stat { background:var(--surface); border:1px solid var(--border); border-radius:7px; padding:10px 12px; }
    .tp-stat-label { font-size:10px; color:var(--light); font-weight:500; }
    .tp-stat-val   { font-size:18px; font-weight:800; line-height:1.1; }
    .tp-stat-val.ok   { color:var(--green); }
    .tp-stat-val.warn { color:var(--amber); }
    .tp-stat-val.info { color:var(--primary-l); }
    .tp-stat-val.muted{ color:var(--muted); }
    .tp-alert { margin-top:10px; padding:8px 12px; border-radius:7px; font-size:11px; font-weight:500; display:flex; align-items:center; gap:6px; }
    .tp-alert.ok   { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; }
    .tp-alert.warn { background:#fef3c7; color:#92400e; border:1px solid #fcd34d; }
    .tp-alert svg  { width:12px; height:12px; flex-shrink:0; }
    .tp-skeleton { animation:skPulse 1.2s ease infinite; }
    @keyframes skPulse { 0%,100%{opacity:.5} 50%{opacity:1} }
    .tp-skel-bar { height:16px; border-radius:4px; background:var(--border); margin-bottom:6px; }
    .gen-actions { display:flex; flex-direction:column; gap:8px; justify-content:flex-end; min-width:180px; }
    .gen-btn { display:flex; align-items:center; justify-content:center; gap:7px; padding:9px 18px; border-radius:8px; border:none; font-size:12px; font-weight:600; cursor:pointer; font-family:inherit; transition:all .15s; white-space:nowrap; }
    .gen-btn svg { width:13px; height:13px; flex-shrink:0; }
    .gen-btn-primary { background:var(--primary); color:#fff; }
    .gen-btn-primary:hover { background:var(--primary-d); }
    .gen-btn-primary:disabled { opacity:.45; cursor:not-allowed; }
    .gen-btn-outline { background:var(--surface); color:var(--muted); border:1px solid var(--border); }
    .gen-btn-outline:hover { border-color:var(--amber); color:var(--amber); background:#fffbeb; }
    .gen-btn-outline:disabled { opacity:.45; cursor:not-allowed; }
    .gen-note { margin-top:12px; padding:9px 12px; border-radius:7px; background:#d1fae5; border:1px solid #6ee7b7; font-size:11px; color:#065f46; display:none; }

    /* ── Flash ──────────────────────────────── */
    .flash-success { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; border-radius:8px; padding:10px 14px; font-size:12px; font-weight:500; display:flex; gap:8px; align-items:center; margin-bottom:14px; }
    .flash-success svg { width:13px; height:13px; flex-shrink:0; }

    /* ── Filtres ────────────────────────────── */
    .filters-bar { background:var(--surface); border:1px solid var(--border); border-radius:10px; padding:10px 14px; display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
    .filters-caption { font-size:10px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:var(--light); white-space:nowrap; }
    .f-divider { width:1px; height:16px; background:var(--border); flex-shrink:0; }
    .f-tenant-wrap { position:relative; }
    .f-tenant-wrap svg.arr { position:absolute; right:8px; top:50%; transform:translateY(-50%); pointer-events:none; color:var(--light); width:11px; height:11px; }
    .f-tenant-dd { appearance:none; -webkit-appearance:none; padding:6px 26px 6px 9px; border:1px solid var(--border); border-radius:7px; font-size:12px; color:var(--text); background:var(--bg); outline:none; cursor:pointer; font-family:inherit; min-width:150px; transition:border-color .15s; }
    .f-tenant-dd:focus { border-color:var(--primary-l); }
    .f-search { flex:1; min-width:140px; padding:6px 11px; border:1px solid var(--border); border-radius:7px; font-size:12px; color:var(--text); background:var(--bg); outline:none; font-family:inherit; transition:border-color .15s; }
    .f-search:focus { border-color:var(--primary-l); }
    .f-select { padding:6px 9px; border:1px solid var(--border); border-radius:7px; font-size:12px; color:var(--text); background:var(--bg); outline:none; cursor:pointer; font-family:inherit; }
    .f-count { font-size:11px; color:var(--light); background:var(--bg); padding:4px 10px; border-radius:20px; border:1px solid var(--border); margin-left:auto; white-space:nowrap; }
    .f-reset { display:inline-flex; align-items:center; gap:4px; padding:5px 10px; border-radius:7px; border:1px solid var(--border); background:var(--bg); color:var(--muted); font-size:11px; font-weight:500; cursor:pointer; font-family:inherit; transition:all .15s; }
    .f-reset:hover { border-color:var(--red); color:var(--red); background:#fef2f2; }
    .f-reset svg { width:10px; height:10px; }

    /* ── Tenant cards ───────────────────────── */
    .tenant-card { background:var(--surface); border:1px solid var(--border); border-radius:10px; overflow:hidden; margin-bottom:12px; }
    .tenant-header { display:flex; align-items:center; justify-content:space-between; padding:11px 16px; background:#f8fafc; border-bottom:1px solid var(--border); cursor:pointer; user-select:none; transition:background .12s; }
    .tenant-header:hover { background:#ecf4f6; }
    .tenant-header.collapsed .chevron { transform:rotate(-90deg); }
    .tenant-header-left { display:flex; align-items:center; gap:10px; }
    .tenant-avatar { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; flex-shrink:0; }
    .tenant-name { font-size:13px; font-weight:700; color:var(--text); }
    .tenant-meta { font-size:11px; color:var(--light); margin-top:1px; }
    .tenant-header-right { display:flex; align-items:center; gap:8px; }
    .coverage-badge { padding:3px 9px; border-radius:20px; font-size:10px; font-weight:700; }
    .coverage-badge.full { background:#d1fae5; color:#065f46; }
    .coverage-badge.part { background:#fef3c7; color:#92400e; }
    .coverage-badge.none { background:#fee2e2; color:#991b1b; }
    .chevron { font-size:10px; color:var(--light); transition:transform .2s; flex-shrink:0; }

    /* ── Table ──────────────────────────────── */
    .codes-table { width:100%; border-collapse:collapse; }
    .codes-table thead th { background:#fafafa; padding:8px 13px; text-align:left; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--light); border-bottom:1px solid var(--border); }
    .codes-table tbody tr { transition:background .1s; }
    .codes-table tbody tr:hover td { background:#f0fdfa; }
    .codes-table tbody tr:last-child td { border-bottom:none; }
    .codes-table td { padding:9px 13px; border-bottom:1px solid var(--border-soft); vertical-align:middle; }

    .user-row  { display:flex; align-items:center; gap:8px; }
    .user-av   { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; flex-shrink:0; }
    .user-name { font-size:12px; font-weight:600; color:var(--text); }
    .user-email{ font-size:10px; color:var(--light); }

    .code-digits { display:inline-flex; gap:3px; align-items:center; }
    .code-digit {
        width:24px; height:28px; border:1.5px solid var(--indigo-bd); border-radius:5px;
        background:var(--indigo-bg); display:flex; align-items:center; justify-content:center;
        font-size:13px; font-weight:800; color:var(--indigo); font-family:'Courier New',monospace; transition:all .3s;
    }
    .code-digit.updated { border-color:#6ee7b7; background:#d1fae5; color:#065f46; animation:digitFlash .5s ease; }
    @keyframes digitFlash { 0%{transform:scale(1.15)} 60%{transform:scale(1.05)} 100%{transform:scale(1)} }
    .code-digit.st-revoked { border-color:#fca5a5; background:#fee2e2; color:#991b1b; }
    .code-digit.st-used    { border-color:#6ee7b7; background:#d1fae5; color:#065f46; }

    /* Badge statut */
    .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:20px; font-size:10px; font-weight:600; white-space:nowrap; }
    .badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
    .badge-assigned  { background:#e0f2fe; color:var(--primary-d); }
    .badge-used-once { background:#d1fae5; color:#065f46; }
    .badge-revoked   { background:#fee2e2; color:#991b1b; }

    .actions-cell { display:flex; align-items:center; justify-content:flex-end; gap:5px; }
    .btn-replace { display:inline-flex; align-items:center; gap:4px; padding:4px 9px; border-radius:6px; border:1px solid var(--border); background:transparent; color:var(--light); font-size:10px; font-weight:600; cursor:pointer; font-family:inherit; transition:all .15s; }
    .btn-replace:hover { border-color:var(--indigo); color:var(--indigo); background:var(--indigo-bg); }
    .btn-replace svg { width:10px; height:10px; }
    .btn-replace.loading { opacity:.5; pointer-events:none; }
    .btn-revoke { display:inline-flex; align-items:center; gap:4px; padding:4px 9px; border-radius:6px; border:1px solid var(--border); background:transparent; color:var(--light); font-size:10px; font-weight:600; cursor:pointer; font-family:inherit; transition:all .15s; }
    .btn-revoke:hover { border-color:var(--red); color:var(--red); background:#fef2f2; }
    .btn-revoke svg { width:10px; height:10px; }

    /* ── Pagination ──────────────────────────── */
    .v-pagination { padding:10px 14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
    .v-pg-info { font-size:11px; color:var(--muted); }
    .v-pg-btns { display:flex; align-items:center; gap:4px; }
    .v-pg-btn { padding:4px 9px; border-radius:6px; border:1px solid var(--border); background:var(--bg); color:var(--muted); font-size:11px; font-weight:500; cursor:pointer; text-decoration:none; transition:all .15s; font-family:inherit; }
    .v-pg-btn:hover { border-color:var(--primary-l); color:var(--primary); }
    .v-pg-btn.cur { background:#0d2137; color:#fff; border-color:#0d2137; cursor:default; }
    .v-pg-btn.off { opacity:.4; cursor:default; pointer-events:none; }

    /* ── Empty ───────────────────────────────── */
    .empty-state { text-align:center; padding:48px 20px; color:var(--light); }
    .empty-state svg { margin:0 auto 12px; display:block; opacity:.35; }
    .empty-title { font-size:14px; font-weight:600; color:var(--muted); }
    .empty-sub   { font-size:12px; margin-top:4px; }

    /* ── Modal ───────────────────────────────── */
    .overlay { position:fixed; inset:0; background:rgba(13,33,55,.5); display:flex; align-items:center; justify-content:center; z-index:1000; opacity:0; pointer-events:none; transition:opacity .2s; }
    .overlay.show { opacity:1; pointer-events:all; }
    .modal { background:var(--surface); border-radius:12px; padding:24px; width:400px; max-width:92vw; box-shadow:0 24px 64px rgba(0,0,0,.2); transform:scale(.96); transition:transform .2s cubic-bezier(.16,1,.3,1); }
    .overlay.show .modal { transform:scale(1); }
    .modal-title { font-size:15px; font-weight:700; color:var(--text); margin-bottom:8px; }
    .modal-body  { font-size:13px; color:var(--muted); line-height:1.6; margin-bottom:20px; }
    .modal-acts  { display:flex; gap:8px; justify-content:flex-end; }
    .modal-cancel { padding:8px 16px; border-radius:8px; border:1px solid var(--border); background:var(--bg); color:var(--muted); font-size:13px; font-weight:500; cursor:pointer; font-family:inherit; transition:all .15s; }
    .modal-cancel:hover { border-color:var(--red); color:var(--red); }
    .modal-ok { padding:8px 16px; border-radius:8px; border:none; background:var(--primary); color:#fff; font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; transition:background .15s; }
    .modal-ok:hover { background:var(--primary-d); }
    .modal-ok.danger { background:var(--red); }
    .modal-ok.danger:hover { background:#dc2626; }

    /* ── Toast ───────────────────────────────── */
    .toast { position:fixed; bottom:22px; right:22px; z-index:9999; display:flex; align-items:center; gap:8px; padding:10px 15px; border-radius:9px; font-size:12px; font-weight:500; color:#fff; background:var(--primary); box-shadow:0 8px 32px rgba(0,0,0,.18); transform:translateY(70px); opacity:0; transition:all .3s cubic-bezier(.16,1,.3,1); pointer-events:none; }
    .toast.show  { transform:translateY(0); opacity:1; }
    .toast.error { background:var(--red); }
    .toast.warn  { background:var(--amber); }
    .toast svg   { width:13px; height:13px; flex-shrink:0; }
    .v-spin { animation:vSpin .6s linear infinite; display:inline-block; }
    @keyframes vSpin { to { transform:rotate(360deg); } }
    .t-export-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 11px; border-radius: 6px;
    border: 1px solid var(--border); background: var(--bg);
    color: var(--muted); font-size: 11px; font-weight: 600;
    cursor: pointer; font-family: inherit; transition: all .15s;
}
.t-export-btn:hover { border-color: var(--primary-l); color: var(--primary); background: #e0f2fe; }
.t-export-btn svg   { width: 11px; height: 11px; }
.role-badge {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 6px; border-radius: 4px;
    font-size: 9px; font-weight: 700; letter-spacing: .04em;
    text-transform: uppercase; margin-top: 3px;
}
.role-badge.role-admin    { background: #e0f2fe; color: #0f6b7c; }
.role-badge.role-rh       { background: #e0f2fe; color: #0f6b7c; }
.role-badge.role-employee { background: #e0f2fe; color: #0f6b7c; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php
    use App\Models\VerificationCode as VC;

    /*
     * Compteurs globaux :
     * - "assigned"  = ASSIGNED + used_at NULL  (jamais utilisé)
     * - "used_once" = ASSIGNED + used_at non NULL (utilisé au moins une fois, réutilisable)
     * - "revoked"   = STATUS_REVOKED
     */
    $statCounts = [
        'assigned'  => VC::where('status', VC::STATUS_ASSIGNED)->whereNull('used_at')->count(),
        'used_once' => VC::where('status', VC::STATUS_ASSIGNED)->whereNotNull('used_at')->count(),
        'revoked'   => VC::where('status', VC::STATUS_REVOKED)
            ->whereNotIn(
                'user_id',
                VC::where('status', VC::STATUS_ASSIGNED)->pluck('user_id')
            )
            ->distinct('user_id')
            ->count('user_id'),
    ];

    $tenantPalette = [
        ['bg' => '#e0f2fe', 'color' => '#0f6b7c'],
        ['bg' => '#fef3c7', 'color' => '#92400e'],
        ['bg' => '#d1fae5', 'color' => '#065f46'],
        ['bg' => '#ede9fe', 'color' => '#4c1d95'],
        ['bg' => '#fee2e2', 'color' => '#991b1b'],
        ['bg' => '#fdf4ff', 'color' => '#701a75'],
    ];

    $tenantMap = $tenants->keyBy('id');

    /*
     * ─── LOGIQUE CLÉ ────────────────────────────────────────────────────────
     * On ne veut afficher qu'UNE SEULE ligne par utilisateur, avec :
     *   - Son code ASSIGNED actif (le seul autorisé, permanent)
     *   - Si pas de code ASSIGNED → on prend son dernier code (révoqué)
     *     pour montrer l'historique
     *
     * On regroupe par tenant, puis par user_id, en gardant uniquement
     * le code le plus pertinent (ASSIGNED prioritaire, sinon le + récent).
     */
    $userRowsByTenant = [];

    foreach ($tenants as $tenant) {
        $tid = $tenant->id;

        // Tous les codes du tenant (ASSIGNED en premier via orderByRaw)
        $allCodes = \App\Models\VerificationCode::with(['user', 'assignedBy', 'revokedBy'])
            ->where('tenant_id', $tid)
            ->orderByRaw("FIELD(status, 'assigned', 'used', 'revoked')")
            ->orderByDesc('assigned_at')
            ->get();

        // 1 ligne par user_id : priorité au code ASSIGNED, sinon le premier (le + récent)
        $byUser = [];
        foreach ($allCodes as $code) {
            $uid = $code->user_id ?? ('orphan_' . $code->id);
            if (!isset($byUser[$uid])) {
                $byUser[$uid] = $code;
            } elseif ($code->status === VC::STATUS_ASSIGNED && $byUser[$uid]->status !== VC::STATUS_ASSIGNED) {
                // Remplace par le code actif si on en trouve un
                $byUser[$uid] = $code;
            }
        }

        $userRowsByTenant[$tid] = array_values($byUser);
    }
?>


<?php if(session('success')): ?>
<div class="flash-success">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
    </svg>
    <?php echo e(session('success')); ?>

</div>
<?php endif; ?>


<div class="stats-row">
    <div class="stat-card c-blue">
        <div class="stat-label">Attribués (jamais utilisés)</div>
        <div class="stat-val blue"><?php echo e(number_format($statCounts['assigned'])); ?></div>
    </div>
    <div class="stat-card c-green">
        <div class="stat-label">Utilisés (réutilisables)</div>
        <div class="stat-val green"><?php echo e(number_format($statCounts['used_once'])); ?></div>
    </div>
    <div class="stat-card c-red">
        <div class="stat-label">Révoqués</div>
        <div class="stat-val red"><?php echo e(number_format($statCounts['revoked'])); ?></div>
    </div>
</div>


<div class="gen-panel">
    <div class="gen-panel-header">
        <div>
            <div class="gen-panel-title">Générer les codes manquants</div>
            <div class="gen-panel-sub">Attribution automatique aux employés sans code — aucune quantité à saisir</div>
        </div>
        <button class="gen-panel-toggle" onclick="toggleGenPanel(this)">▾ Ouvrir</button>
    </div>
    <div class="gen-panel-body" id="genPanelBody">
        <div class="gen-inner">
            <div class="gen-left">
                <label class="gen-form-label">Tenant</label>
                <select class="gen-form-select" id="genTenant" onchange="loadTenantStats()">
                    <option value="">— Choisir un tenant —</option>
                    <?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($t->id); ?>"><?php echo e($t->name ?? $t->id); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="tenant-preview" id="tenantPreview">
                <div class="tp-title" id="tpTitle">Chargement…</div>
                <div id="tpContent">
                    <div class="tp-skeleton">
                        <div class="tp-skel-bar" style="width:80%"></div>
                        <div class="tp-skel-bar" style="width:60%"></div>
                    </div>
                </div>
            </div>
            <div class="gen-actions">
                <button class="gen-btn gen-btn-primary" id="btnGenerateMissing"
                        onclick="submitGenerateMissing()" disabled>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span id="btnGenLbl">Générer les codes manquants</span>
                </button>
            </div>
        </div>
        <div class="gen-note" id="genNote"></div>
    </div>
</div>


<div class="filters-bar">
    <span class="filters-caption">Filtres</span>
    <div class="f-divider"></div>
    <div class="f-tenant-wrap">
        <select class="f-tenant-dd" id="fTenant" onchange="filterByTenant()">
            <option value="">Tous les tenants</option>
            <?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($t->id); ?>"><?php echo e($t->name ?? $t->id); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <svg class="arr" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
    <input type="text" id="fSearch" class="f-search"
           placeholder="Nom, email ou code…" oninput="filterRows()">
    <select class="f-select" id="fStatus" onchange="filterRows()">
        <option value="">Tous les statuts</option>
        <option value="assigned">Attribué</option>
        <option value="used_once">Utilisé</option>
        <option value="revoked">Révoqué</option>
    </select>
    <button class="f-reset" onclick="resetFilters()">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Réinitialiser
    </button>
    <span class="f-count" id="fCount"><?php echo e(collect($userRowsByTenant)->flatten()->count()); ?> utilisateur(s)</span>
</div>


<div id="tenantCards">
<?php $__empty_1 = true; $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<?php
    $tenantId  = $tenant->id;
    $tName     = $tenant->name ?? $tenantId;
    $initials  = strtoupper(substr(str_replace('-', '', $tName), 0, 2));
    $tci       = abs(crc32((string)$tenantId)) % count($tenantPalette);
    $tc        = $tenantPalette[$tci];
    $tStats    = $dashboardStats[$tenantId] ?? null;
    $rows      = $userRowsByTenant[$tenantId] ?? [];
    $tAssigned = collect($rows)->where('status', VC::STATUS_ASSIGNED)->count();
    $tTotal    = count($rows);
    $coveragePct   = $tStats['coverage_pct'] ?? 0;
    $coverageClass = $coveragePct >= 100 ? 'full' : ($coveragePct >= 50 ? 'part' : 'none');
?>
<?php if($tTotal === 0): ?> <?php continue; ?> <?php endif; ?>
<div class="tenant-card" data-tenant-id="<?php echo e($tenantId); ?>">
    <div class="tenant-header" onclick="toggleTenant(this)">
        <div class="tenant-header-left">
            <div class="tenant-avatar" style="background:<?php echo e($tc['bg']); ?>;color:<?php echo e($tc['color']); ?>;"><?php echo e($initials); ?></div>
            <div>
                <div class="tenant-name"><?php echo e($tName); ?></div>
                <div class="tenant-meta">
                    <?php echo e($tTotal); ?> code(s) · <?php echo e($tAssigned); ?> attribué(s)
                    <?php if($tStats): ?>
                        · <?php echo e($tStats['active_employees']); ?> employé(s)
                        <?php if($tStats['missing_count'] > 0): ?>
                            · <strong style="color:var(--amber)"><?php echo e($tStats['missing_count']); ?> sans code</strong>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="tenant-header-right">
        <button class="t-export-btn"
            onclick="event.stopPropagation();exportTenantPDF('<?php echo e($tenantId); ?>','<?php echo e(addslashes($tName)); ?>')">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 13h4M10 17h4M13 3v5a1 1 0 001 1h4"/>
        </svg>
        PDF
    </button>
            <span class="coverage-badge <?php echo e($coverageClass); ?>"><?php echo e($coveragePct); ?>% couvert</span>
            <span class="chevron">▾</span>
        </div>
    </div>

    <div class="tenant-body">
        <table class="codes-table">
            <thead>
                <tr>
                    <th style="width:32px">#</th>
                    <th>Collaborateur</th>
                    <th>Code 2FA</th>
                    <th>Statut</th>
                    <th>Attribué le</th>
                    <th>Utilisé le</th>
                    <th style="text-align:right;width:160px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    /*
                     * Détermination du statut VISUEL :
                     * - ASSIGNED + used_at non null → "Utilisé" (badge vert, chiffres verts)
                     *   mais le code reste actif (Remplacer + Révoquer disponibles)
                     * - ASSIGNED + used_at null     → "Attribué" (badge bleu)
                     * - REVOKED                     → "Révoqué"  (badge rouge)
                     */
                    $isUsedOnce   = ($code->status === VC::STATUS_ASSIGNED && !is_null($code->used_at));
                    $isAssigned   = ($code->status === VC::STATUS_ASSIGNED && is_null($code->used_at));
                    $isRevoked    = ($code->status === VC::STATUS_REVOKED);

                    if ($isUsedOnce) {
                        $badgeClass  = 'badge-used-once';
                        $badgeLabel  = 'Utilisé';
                        $digitClass  = 'st-used';
                        $visualStatus = 'used_once';
                    } elseif ($isAssigned) {
                        $badgeClass  = 'badge-assigned';
                        $badgeLabel  = 'Attribué';
                        $digitClass  = '';
                        $visualStatus = 'assigned';
                    } else {
                        $badgeClass  = 'badge-revoked';
                        $badgeLabel  = 'Révoqué';
                        $digitClass  = 'st-revoked';
                        $visualStatus = 'revoked';
                    }

                    $digits = str_split(str_pad($code->code, 6, '0', STR_PAD_LEFT));
                ?>
                <tr class="code-row"
                    data-tenant="<?php echo e($tenantId); ?>"
                    data-status="<?php echo e($visualStatus); ?>"
                    data-search="<?php echo e(strtolower(($code->user?->name ?? '') . ' ' . ($code->user?->email ?? '') . ' ' . $code->code)); ?>">
                    <td style="font-size:10px;color:var(--light);"><?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?></td>
                    <td>
                        <?php if($code->user): ?>
<div class="user-row">
    <div class="user-av" style="background:<?php echo e($tc['bg']); ?>;color:<?php echo e($tc['color']); ?>;">
        <?php echo e(strtoupper(substr($code->user->name ?? $code->user->email, 0, 2))); ?>

    </div>
    <div>
        <div class="user-name"><?php echo e($code->user->name ?? '—'); ?></div>
        <div class="user-email"><?php echo e($code->user->email); ?></div>
        <?php
            $roleClass = match($code->user->role ?? '') {
                'admin'    => 'role-admin',
                'rh'       => 'role-rh',
                default    => 'role-employee',
            };
            $roleLabel = match($code->user->role ?? '') {
                'admin'    => 'Admin',
                'rh'       => 'RH',
                default    => 'Employé',
            };
        ?>
        <span class="role-badge <?php echo e($roleClass); ?>"><?php echo e($roleLabel); ?></span>
    </div>
</div>
                        <?php else: ?>
                        <div class="user-row">
                            <div class="user-av" style="background:#fee2e2;color:#991b1b;">!</div>
                            <div>
                                <div class="user-name" style="color:var(--red);font-size:11px;">Orphelin — à nettoyer</div>
                                <div class="user-email" style="color:var(--light);">user_id introuvable</div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="code-digits" id="digits-<?php echo e($code->id); ?>">
                            <?php $__currentLoopData = $digits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="code-digit <?php echo e($digitClass); ?>"><?php echo e($d); ?></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?php echo e($badgeClass); ?>">
                            <span class="badge-dot"></span><?php echo e($badgeLabel); ?>

                        </span>
                    </td>
                    <td style="font-size:11px;color:var(--muted);">
                        <?php echo e($code->assigned_at?->format('d/m/Y') ?? '—'); ?>

                    </td>
<td style="font-size:11px;color:<?php echo e($isUsedOnce ? 'var(--green)' : 'var(--muted)'); ?>;font-weight:<?php echo e($isUsedOnce ? '600' : '400'); ?>;">
    <?php if($isUsedOnce): ?>
        <span title="<?php echo e($code->used_at?->format('d/m/Y à H:i')); ?>">
            <?php echo e($code->used_at?->format('d/m/Y')); ?><br>
            <span style="font-size:10px;opacity:.85;"><?php echo e($code->used_at?->format('H:i')); ?></span>
        </span>
    <?php else: ?>
        —
    <?php endif; ?>
</td>
                    <td>
                        <div class="actions-cell">
                            
                            <?php if($code->status === VC::STATUS_ASSIGNED && $code->user_id): ?>
                            <button class="btn-replace" id="rbtn-<?php echo e($code->id); ?>"
                                    onclick="replaceForUser(<?php echo e($code->user_id); ?>, <?php echo e($code->id); ?>, '<?php echo e(addslashes($code->user?->name ?? '')); ?>')">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Remplacer
                            </button>
                            <?php endif; ?>
                            
                            <?php if($code->status === VC::STATUS_ASSIGNED && $code->user_id): ?>
                            <button class="btn-revoke"
                                    onclick="confirmRevoke(<?php echo e($code->id); ?>, '<?php echo e(addslashes($code->user?->name ?? 'cet utilisateur')); ?>')">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                Révoquer
                            </button>
                            <?php endif; ?>
                            
                            <?php if($isRevoked): ?>
                            <span style="font-size:11px;color:var(--light);">—</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;">
    <div class="empty-state">
        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
        </svg>
        <div class="empty-title">Aucun code trouvé</div>
        <div class="empty-sub">Ouvrez le panneau "Générer les codes manquants" pour commencer.</div>
    </div>
</div>
<?php endif; ?>
</div>

<script>
const REVOKE_BASE_URL = "/superadmin/codes/";
const REVOKE_BASE_SUFFIX = "/revoke";
</script>


<div class="overlay" id="vModal">
    <div class="modal">
        <div class="modal-title" id="vModalTitle">Confirmer</div>
        <div class="modal-body"  id="vModalBody">Cette action est irréversible.</div>
        <div class="modal-acts">
            <button class="modal-cancel" onclick="closeModal()">Annuler</button>
            <button class="modal-ok"     id="vModalOk">Confirmer</button>
        </div>
    </div>
</div>


<div class="toast" id="vToast">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
    </svg>
    <span id="vToastMsg"></span>
</div>


<script>
const CSRF                = document.querySelector('meta[name="csrf-token"]').content;
const TENANT_STATS_URL    = "<?php echo e(rtrim(route('superadmin.codes.tenant-stats', '__tid__'), '__tid__')); ?>";
const GENERATE_MISSING_URL= "<?php echo e(route('superadmin.codes.generate-missing')); ?>";
const REPLACE_USER_URL    = "<?php echo e(rtrim(route('superadmin.codes.replace-user', '__uid__'), '__uid__')); ?>";
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
/* ══════════════════════════════════════════════════════════════════
   PANEL GÉNÉRER
══════════════════════════════════════════════════════════════════ */
function toggleGenPanel(btn) {
    const body = document.getElementById('genPanelBody');
    const open = body.classList.toggle('open');
    btn.textContent = open ? '▴ Fermer' : '▾ Ouvrir';
}

let _statsTimeout = null;

async function loadTenantStats() {
    const tid      = document.getElementById('genTenant').value;
    const preview  = document.getElementById('tenantPreview');
    const btnGen   = document.getElementById('btnGenerateMissing');

    if (!tid) {
        preview.classList.remove('visible');
        btnGen.disabled = true;
        return;
    }

    preview.classList.add('visible');
    document.getElementById('tpTitle').textContent = 'Chargement…';
    document.getElementById('tpContent').innerHTML = `
        <div class="tp-skeleton">
            <div class="tp-skel-bar" style="width:80%"></div>
            <div class="tp-skel-bar" style="width:60%"></div>
        </div>`;
    btnGen.disabled = true;

    clearTimeout(_statsTimeout);
    _statsTimeout = setTimeout(async () => {
        try {
            const res  = await fetch(TENANT_STATS_URL + tid, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.message ?? 'Erreur');
            renderTenantPreview(data.tenant_name, data.stats);
            btnGen.disabled = data.stats.missing_count <= 0;
        } catch (e) {
            document.getElementById('tpTitle').textContent = 'Erreur de chargement';
            document.getElementById('tpContent').innerHTML =
                `<div style="color:var(--red);font-size:11px;">${e.message}</div>`;
        }
    }, 200);
}

function renderTenantPreview(name, stats) {
    document.getElementById('tpTitle').textContent = name;
    const coverageColor = stats.coverage_pct >= 100 ? 'ok' : (stats.coverage_pct >= 50 ? 'warn' : 'muted');
    const missingColor  = stats.missing_count  > 0  ? 'warn' : 'ok';
    const alertHtml = stats.missing_count > 0
        ? `<div class="tp-alert warn">
               <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                   <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
               </svg>
               <strong>${stats.missing_count} employé(s)</strong> sans code
           </div>`
        : `<div class="tp-alert ok">
               <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                   <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
               </svg>
               Couverture complète — tous les employés ont un code
           </div>`;

    document.getElementById('tpContent').innerHTML = `
        <div class="tp-grid">
            <div class="tp-stat"><div class="tp-stat-label">Employés actifs</div><div class="tp-stat-val info">${stats.active_employees}</div></div>
            <div class="tp-stat"><div class="tp-stat-label">Codes attribués</div><div class="tp-stat-val info">${stats.assigned_count}</div></div>
            <div class="tp-stat"><div class="tp-stat-label">Sans code</div><div class="tp-stat-val ${missingColor}">${stats.missing_count}</div></div>
            <div class="tp-stat"><div class="tp-stat-label">Couverture</div><div class="tp-stat-val ${coverageColor}">${stats.coverage_pct}%</div></div>
        </div>
        ${alertHtml}`;
}

/* ── Génération ──────────────────────────────────────────────────── */
async function submitGenerateMissing() {
    const tid = document.getElementById('genTenant').value;
    if (!tid) { showToast('Sélectionnez un tenant.', 'error'); return; }
    const btn  = document.getElementById('btnGenerateMissing');
    const note = document.getElementById('genNote');
    btn.disabled = true;
    document.getElementById('btnGenLbl').textContent = 'En cours…';
    note.style.display = 'none';
    try {
        const res  = await fetch(GENERATE_MISSING_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ tenant_id: tid })
        });
        const data = await res.json();
        if (data.success) {
            note.textContent   = data.message;
            note.style.display = 'block';
            showToast(data.message);
            if (data.stats) renderTenantPreview(document.getElementById('genTenant').selectedOptions[0].text, data.stats);
            setTimeout(() => window.location.reload(), 1600);
        } else {
            showToast(data.message ?? 'Erreur', 'error');
            btn.disabled = false;
        }
    } catch (e) {
        showToast('Erreur réseau. Réessayez.', 'error');
        btn.disabled = false;
    } finally {
        document.getElementById('btnGenLbl').textContent = 'Générer les codes manquants';
    }
}

/* ══════════════════════════════════════════════════════════════════
   REMPLACEMENT INDIVIDUEL — met à jour la ligne en place
══════════════════════════════════════════════════════════════════ */
async function replaceForUser(userId, codeId, userName) {
    const btn  = document.getElementById('rbtn-' + codeId);
    const wrap = document.getElementById('digits-' + codeId);
    if (!btn || !wrap) return;

    btn.classList.add('loading');
    btn.innerHTML = '<span class="v-spin">⟳</span>';

    try {
        const res  = await fetch(REPLACE_USER_URL + userId, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();

        if (data.success) {
            // Mettre à jour les chiffres avec animation
            const digs = String(data.new_code).padStart(6, '0').split('');
            wrap.querySelectorAll('.code-digit').forEach((cell, i) => {
                cell.textContent = digs[i];
                cell.className   = 'code-digit updated';
                setTimeout(() => cell.className = 'code-digit', 2200);
            });

            // Mettre à jour les IDs et le onclick du bouton Remplacer
            wrap.id = 'digits-' + data.new_id;
            btn.id  = 'rbtn-'   + data.new_id;
            btn.setAttribute('onclick', `replaceForUser(${userId}, ${data.new_id}, '${userName.replace(/'/g,"\\'")}' )`);

            // Mettre à jour le form de révocation caché
            const oldForm = document.getElementById('revokeForm-' + codeId);
            if (oldForm) {
                oldForm.id     = 'revokeForm-' + data.new_id;
                oldForm.action = oldForm.action.replace('/codes/' + codeId + '/', '/codes/' + data.new_id + '/');
            }

            // Réinitialiser la colonne "Utilisé le" à "—" (nouveau code jamais utilisé)
            const row   = wrap.closest('tr');
            const cells = row.querySelectorAll('td');
            // colonne 5 = "Utilisé le" (index 5 : #, Collaborateur, Code, Statut, Attribué le, Utilisé le)
            cells[5].textContent   = '—';
            cells[5].style.color   = 'var(--muted)';
            cells[5].style.fontWeight = '400';

            // Réinitialiser le badge à "Attribué"
            const badge = row.querySelector('.badge');
            if (badge) {
                badge.className   = 'badge badge-assigned';
                badge.innerHTML   = '<span class="badge-dot"></span>Attribué';
            }
            row.dataset.status = 'assigned';

// Mettre à jour le bouton Révoquer avec le nouveau codeId
const revokeBtn = row.querySelector('.btn-revoke');
if (revokeBtn) {
    revokeBtn.setAttribute('onclick',
        `confirmRevoke(${data.new_id}, '${userName.replace(/'/g, "\\'")}')` );
}
            showToast('Nouveau code attribué à ' + userName);
        } else {
            showToast(data.message ?? 'Erreur', 'error');
        }
    } catch { showToast('Erreur réseau', 'error'); }
    finally {
        btn.classList.remove('loading');
        btn.innerHTML = svgR(10) + ' Remplacer';
    }
}

/* ══════════════════════════════════════════════════════════════════
   RÉVOCATION — via fetch DELETE
══════════════════════════════════════════════════════════════════ */
function confirmRevoke(codeId, userName) {
    openModal(
        'Révoquer le code de ' + userName,
        `Le code sera <strong>définitivement révoqué</strong>.<br>
         L'utilisateur n'aura plus de code actif jusqu'à ce qu'un nouveau lui soit attribué.<br><br>
         <strong>Cette action est irréversible.</strong>`,
        () => doRevoke(codeId, userName),
        true
    );
}

async function doRevoke(codeId, userName) {
    const url = REVOKE_BASE_URL + codeId + REVOKE_BASE_SUFFIX;
    if (!url) { showToast('URL de révocation introuvable.', 'error'); return; }

    try {
        const res  = await fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ reason: '' }),
        });
        const data = await res.json();

        if (data.success) {
            // Mettre à jour la ligne visuellement sans rechargement
            const row = document.getElementById('rbtn-' + codeId)?.closest('tr')
                     ?? document.getElementById('digits-' + codeId)?.closest('tr');

            if (row) {
                // Chiffres en rouge
                row.querySelectorAll('.code-digit').forEach(d => {
                    d.className = 'code-digit st-revoked';
                });
                // Badge → Révoqué
                const badge = row.querySelector('.badge');
                if (badge) {
                    badge.className = 'badge badge-revoked';
                    badge.innerHTML = '<span class="badge-dot"></span>Révoqué';
                }
                // Supprimer les boutons d'action
                const actCell = row.querySelector('.actions-cell');
                if (actCell) actCell.innerHTML = '<span style="font-size:11px;color:var(--light);">—</span>';
                // Mettre à jour le data-status pour les filtres
                row.dataset.status = 'revoked';
            }

            showToast('Code de ' + userName + ' révoqué avec succès.');
        } else {
            showToast(data.message ?? 'Erreur lors de la révocation.', 'error');
        }
    } catch (e) {
        showToast('Erreur réseau. Réessayez.', 'error');
    }
}

/* ══════════════════════════════════════════════════════════════════
   FILTRES
══════════════════════════════════════════════════════════════════ */
function filterByTenant() {
    const tid = document.getElementById('fTenant').value;
    document.querySelectorAll('.tenant-card').forEach(card => {
        card.style.display = (!tid || card.dataset.tenantId === tid) ? '' : 'none';
    });
    updateCount();
}

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
    document.getElementById('fTenant').value = '';
    document.querySelectorAll('.tenant-card').forEach(c => c.style.display = '');
    document.querySelectorAll('.code-row').forEach(r => r.style.display = '');
    updateCount();
}

function updateCount() {
    const n = document.querySelectorAll('.code-row:not([style*="display: none"])').length;
    document.getElementById('fCount').textContent = n + ' utilisateur(s)';
}

/* ══════════════════════════════════════════════════════════════════
   TOGGLE TENANT CARD
══════════════════════════════════════════════════════════════════ */
function toggleTenant(header) {
    const body = header.nextElementSibling;
    header.classList.toggle('collapsed');
    body.style.display = header.classList.contains('collapsed') ? 'none' : '';
}

/* ══════════════════════════════════════════════════════════════════
   MODAL
══════════════════════════════════════════════════════════════════ */
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
document.getElementById('vModal').addEventListener('click', function (e) { if (e.target === this) closeModal(); });

/* ══════════════════════════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════════════════════════ */
function showToast(msg, type = 'success') {
    const t = document.getElementById('vToast');
    document.getElementById('vToastMsg').textContent = msg;
    t.className = 'toast ' + type;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3400);
}

/* ── Helper SVG ─────────────────────────────────────────────────── */
function svgR(s) {
    return `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="width:${s}px;height:${s}px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>`;
}

/* ══════════════════════════════════════════════════════════════════
   EXPORT PDF PAR TENANT
══════════════════════════════════════════════════════════════════ */
function exportTenantPDF(tenantId, tenantName) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

    // ── En-tête ──
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('Codes de vérification 2FA — ' + tenantName, 14, 16);

    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.text('Généré le : ' + new Date().toLocaleDateString('fr-FR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }), 14, 23);

    // ── Collecte des lignes depuis le DOM ──
    const card = document.querySelector(`.tenant-card[data-tenant-id="${tenantId}"]`);
    if (!card) { showToast('Tenant introuvable dans la page.', 'error'); return; }

    const rows = [];
    card.querySelectorAll('tbody tr.code-row').forEach((tr, i) => {
        const tds   = tr.querySelectorAll('td');
        const name  = tr.querySelector('.user-name')?.textContent?.trim() ?? '—';
        const email = tr.querySelector('.user-email')?.textContent?.trim() ?? '—';

        // Code : concaténer les chiffres des cases
        const code  = [...tr.querySelectorAll('.code-digit')]
            .map(d => d.textContent.trim()).join('');

        const status   = tr.querySelector('.badge')?.textContent?.trim() ?? '—';
        const assigned = tds[4]?.textContent?.trim() ?? '—';
        const used     = tds[5]?.textContent?.trim().replace(/\s+/g, ' ') ?? '—';

        rows.push([
            String(i + 1).padStart(2, '0'),
            name,
            email,
            code,
            status,
            assigned,
            used,
        ]);
    });

    if (rows.length === 0) {
        showToast('Aucune ligne à exporter pour ce tenant.', 'warn');
        return;
    }

    // ── Tableau ──
    doc.autoTable({
        startY: 29,
        head: [['#', 'Employé', 'Email', 'Code 2FA', 'Statut', 'Attribué le', 'Utilisé le']],
        body: rows,
        theme: 'grid',
        styles: {
            font: 'helvetica',
            fontSize: 8,
            cellPadding: 3,
            textColor: 20,
            lineColor: 180,
            lineWidth: 0.2,
        },
        headStyles: {
            fillColor: [240, 240, 240],
            textColor: 20,
            fontStyle: 'bold',
            halign: 'center',
        },
        columnStyles: {
            0: { halign: 'center', cellWidth: 8  },   // #
            3: { halign: 'center', cellWidth: 22, font: 'courier', fontSize: 10, fontStyle: 'bold' }, // Code
            4: { halign: 'center', cellWidth: 20 },   // Statut
            5: { halign: 'center', cellWidth: 24 },   // Attribué le
            6: { halign: 'center', cellWidth: 28 },   // Utilisé le
        },
        alternateRowStyles: { fillColor: [250, 250, 250] },
        margin: { left: 14, right: 14 },
    });

    // ── Numérotation des pages ──
    const pageCount = doc.internal.getNumberOfPages();
    for (let p = 1; p <= pageCount; p++) {
        doc.setPage(p);
        doc.setFontSize(8);
        doc.setTextColor(150);
        doc.text(
            `Page ${p} / ${pageCount}`,
            doc.internal.pageSize.getWidth() / 2,
            doc.internal.pageSize.getHeight() - 8,
            { align: 'center' }
        );
    }

    // ── Téléchargement ──
    const safeName = tenantName.replace(/[^a-zA-Z0-9]/g, '_');
    doc.save(`2FA_${safeName}.pdf`);

    showToast('PDF exporté : 2FA_' + safeName + '.pdf');
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Medstaff-second-main\resources\views/superadmin/codes/index.blade.php ENDPATH**/ ?>