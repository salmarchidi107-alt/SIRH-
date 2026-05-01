


<?php $__env->startSection('title', 'Badges PIN — Employés'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    :root {
        --pin-bg:         #f8fafc;
        --pin-surface:    #ffffff;
        --pin-border:     #e2e8f0;
        --pin-border-soft:#f1f5f9;
        --pin-text:       #0f172a;
        --pin-muted:      #64748b;
        --pin-light:      #94a3b8;
        --pin-teal:       #0d9488;
        --pin-teal-bg:    #f0fdfa;
        --pin-teal-light: #ccfbf1;
        --pin-purple:     #0ea9a6;
        --pin-purple-bg:  #f5f3ff;
        --pin-purple-light:#ede9fe;
        --pin-red:        #dc2626;
        --pin-red-bg:     #fef2f2;
        --pin-amber:      #d97706;
        --pin-amber-bg:   #fffbeb;
        --pin-green:      #16a34a;
        --pin-green-bg:   #f0fdf4;
    }

    .pin-wrap {
        min-height: calc(100vh - 64px);
        background: var(--pin-bg);
        display: flex;
        flex-direction: column;
    }

    /* ── Topbar ── */
    .pin-topbar {
        background: var(--pin-surface);
        border-bottom: 1px solid var(--pin-border);
        padding: 0 1.5rem;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .pin-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--pin-text);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pin-title-icon {
        width: 30px; height: 30px; border-radius: 8px;
        background: var(--pin-purple-bg);
        color: var(--pin-purple);
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
    }
    .pin-back {
        display: flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 8px;
        background: var(--pin-bg); border: 1px solid var(--pin-border);
        color: var(--pin-muted); font-size: 13px; font-weight: 500;
        text-decoration: none; transition: all .15s;
    }
    .pin-back:hover { border-color: var(--pin-teal); color: var(--pin-teal); }

    /* ── Filtres ── */
    .pin-filters {
        background: var(--pin-surface);
        border-bottom: 1px solid var(--pin-border);
        padding: .75rem 1.5rem;
        display: flex; align-items: center; gap: .75rem;
    }
    .pin-search {
        flex: 1; padding: .45rem .75rem;
        border: 1px solid var(--pin-border); border-radius: 8px;
        font-size: 13px; color: var(--pin-text); outline: none;
        transition: border-color .15s;
    }
    .pin-search:focus { border-color: var(--pin-purple); }
    .pin-select {
        padding: .45rem .75rem; border: 1px solid var(--pin-border);
        border-radius: 8px; font-size: 13px; color: var(--pin-text);
        background: var(--pin-surface); outline: none; cursor: pointer;
        transition: border-color .15s;
    }
    .pin-select:focus { border-color: var(--pin-purple); }

    /* ── Boutons action globaux ── */
    .pin-action-bar {
        background: var(--pin-surface);
        border-bottom: 1px solid var(--pin-border);
        padding: .6rem 1.5rem;
        display: flex; align-items: center; gap: .75rem;
        flex-wrap: wrap;
    }
    .pin-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 16px; border-radius: 8px;
        font-size: 13px; font-weight: 600; cursor: pointer;
        border: none; transition: all .15s; text-decoration: none;
    }
    .pin-btn-regen-all {
        background: var(--pin-purple); color: #fff;
    }
    .pin-btn-regen-all:hover { background: #6d28d9; }
    .pin-btn-regen-all:disabled { opacity:.6; cursor:not-allowed; }

    .pin-btn-print {
        background: var(--pin-teal); color: #fff;
    }
    .pin-btn-print:hover { background: #0f766e; }

    .pin-btn-export {
        background: var(--pin-bg); border: 1px solid var(--pin-border);
        color: var(--pin-muted);
    }
    .pin-btn-export:hover { border-color: var(--pin-teal); color: var(--pin-teal); }

    .pin-count-badge {
        background: var(--pin-purple-light); color: var(--pin-purple);
        font-size: 11px; font-weight: 600; padding: 3px 10px;
        border-radius: 20px; margin-left: auto;
    }

    /* ── Contenu ── */
    .pin-content {
        flex: 1; padding: 1.5rem; overflow-y: auto;
        display: flex; flex-direction: column; gap: 1.5rem;
    }

    /* ── Département card ── */
    .dept-card {
        background: var(--pin-surface);
        border: 1px solid var(--pin-border);
        border-radius: 12px; overflow: hidden;
    }
    .dept-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: .85rem 1.25rem;
        background: linear-gradient(135deg, var(--pin-purple-bg) 0%, var(--pin-teal-bg) 100%);
        border-bottom: 1px solid var(--pin-border);
        cursor: pointer; user-select: none;
    }
    .dept-header-left {
        display: flex; align-items: center; gap: 10px;
    }
   
    .dept-name {
        font-size: 14px; font-weight: 700; color: var(--pin-text);
    }
    .dept-count {
        font-size: 11px; color: var(--pin-muted); margin-top: 1px;
    }
    .dept-chevron {
        font-size: 16px; color: var(--pin-muted);
        transition: transform .2s;
    }
    .dept-header.collapsed .dept-chevron { transform: rotate(-90deg); }

    .dept-regen-btn {
        display: flex; align-items: center; gap: 5px;
        padding: 5px 12px; border-radius: 7px;
        background: var(--pin-purple-bg); border: 1px solid var(--pin-purple-light);
        color: var(--pin-purple); font-size: 12px; font-weight: 600;
        cursor: pointer; transition: all .15s;
    }
    .dept-regen-btn:hover { background: var(--pin-purple); color: #fff; }

    /* ── Table ── */
    .pin-table {
        width: 100%; border-collapse: collapse;
    }
    .pin-table thead th {
        background: #fafafa;
        padding: 9px 14px;
        text-align: left;
        font-size: 11px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--pin-muted);
        border-bottom: 1px solid var(--pin-border);
    }
    .pin-table tbody tr {
        transition: background .12s;
    }
    .pin-table tbody tr:hover td { background: var(--pin-teal-bg); }
    .pin-table tbody tr:last-child td { border-bottom: none; }
    .pin-table td {
        padding: 10px 14px;
        border-bottom: 1px solid var(--pin-border-soft);
        vertical-align: middle;
    }

    /* Avatar */
    .emp-avatar {
        width: 32px; height: 32px; border-radius: 50%;
        background: var(--pin-teal-light); color: var(--pin-teal);
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; flex-shrink: 0;
    }
    .emp-name {
        font-size: 13px; font-weight: 500; color: var(--pin-text);
    }
    .emp-matricule {
        font-size: 11px; color: var(--pin-muted); font-family: 'SF Mono', 'Fira Code', monospace;
    }

    /* PIN display */
    .pin-display {
        display: inline-flex; align-items: center; gap: 6px;
    }
    .pin-code {
        font-family: 'SF Mono', 'Fira Code', 'Courier New', monospace;
        font-size: 18px; font-weight: 700; letter-spacing: 4px;
        color: var(--pin-purple);
        background: var(--pin-purple-bg);
        padding: 4px 12px; border-radius: 8px;
        border: 1px solid var(--pin-purple-light);
        min-width: 80px; text-align: center;
        transition: all .3s;
    }
    .pin-code.updated {
        background: var(--pin-green-bg);
        color: var(--pin-green);
        border-color: #bbf7d0;
        animation: pinFlash .6s ease;
    }
    @keyframes pinFlash {
        0%   { transform: scale(1.1); }
        50%  { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    /* Regen single button */
    .pin-regen-single {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; border-radius: 6px;
        background: transparent; border: 1px solid var(--pin-border);
        color: var(--pin-muted); font-size: 11px; font-weight: 500;
        cursor: pointer; transition: all .15s;
    }
    .pin-regen-single:hover {
        border-color: var(--pin-purple);
        color: var(--pin-purple);
        background: var(--pin-purple-bg);
    }
    .pin-regen-single.loading {
        opacity: .6; pointer-events: none;
    }

    /* Spinner */
    .spin {
        display: inline-block;
        animation: spin .6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Toast */
    .pin-toast {
        position: fixed; bottom: 24px; right: 24px; z-index: 9999;
        display: flex; align-items: center; gap: 10px;
        padding: 12px 18px; border-radius: 10px;
        font-size: 13px; font-weight: 500; color: #fff;
        background: var(--pin-teal);
        box-shadow: 0 8px 32px rgba(0,0,0,.18);
        transform: translateY(80px); opacity: 0;
        transition: all .3s cubic-bezier(.16,1,.3,1);
        pointer-events: none;
    }
    .pin-toast.show {
        transform: translateY(0); opacity: 1;
    }
    .pin-toast.error { background: var(--pin-red); }

    /* Empty state */
    .pin-empty {
        text-align: center; padding: 3rem;
        color: var(--pin-muted); font-size: 14px;
    }

    /* Confirm modal overlay */
    .pin-modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,.45);
        display: flex; align-items: center; justify-content: center;
        z-index: 1000; opacity: 0; pointer-events: none;
        transition: opacity .2s;
    }
    .pin-modal-overlay.show { opacity: 1; pointer-events: all; }
    .pin-modal {
        background: var(--pin-surface); border-radius: 14px;
        padding: 1.75rem; width: 400px; max-width: 90vw;
        box-shadow: 0 24px 64px rgba(0,0,0,.2);
        transform: scale(.95);
        transition: transform .2s cubic-bezier(.16,1,.3,1);
    }
    .pin-modal-overlay.show .pin-modal { transform: scale(1); }
    .pin-modal-title {
        font-size: 16px; font-weight: 700; color: var(--pin-text); margin-bottom: .5rem;
    }
    .pin-modal-body {
        font-size: 13px; color: var(--pin-muted); margin-bottom: 1.25rem; line-height: 1.6;
    }
    .pin-modal-actions { display: flex; gap: .75rem; justify-content: flex-end; }
    .pin-modal-cancel {
        padding: 8px 18px; border-radius: 8px;
        background: var(--pin-bg); border: 1px solid var(--pin-border);
        color: var(--pin-muted); font-size: 13px; font-weight: 500;
        cursor: pointer; transition: all .15s;
    }
    .pin-modal-cancel:hover { border-color: var(--pin-red); color: var(--pin-red); }
    .pin-modal-confirm {
        padding: 8px 18px; border-radius: 8px;
        background: var(--pin-purple); color: #fff;
        border: none; font-size: 13px; font-weight: 600;
        cursor: pointer; transition: background .15s;
    }
    .pin-modal-confirm:hover { background: #6d28d9; }

    /* Screen vs Print */
    .screen-only { display: block; }
    .print-only { display: none; }
    
    /* Print styles */
    @media print {
        .pin-topbar, .pin-filters, .pin-action-bar,
        .pin-regen-single, .dept-regen-btn,
        .pin-btn-regen-all, .pin-back, .no-print { display: none !important; }
        .pin-wrap { min-height: auto; }
        .dept-card { break-inside: avoid; margin-bottom: 1rem; }
        .dept-body { display: block !important; }
        
        /* Afficher la section d'impression complet à la place */
        .screen-only { display: none !important; }
        .print-only { display: block !important; }
        
        /* Style pour la section d'impression */
        .print-all-content { width: 100%; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="pin-wrap">

    
    <div class="pin-topbar">
        <div class="pin-title">
            <div class="pin-title-icon"></div>
            Badges PIN — Employés
        </div>
        <div style="display:flex;align-items:center;gap:.75rem;">
            <a href="<?php echo e(route('pointage.index')); ?>" class="pin-back">
                ← Retour Pointage
            </a>
        </div>
    </div>

    
    <div class="pin-filters">
        <strong style="font-size:13px;color:var(--pin-muted);">Filtrer :</strong>
        <input type="text" id="searchInput" class="pin-search"
               placeholder="Nom, prénom ou matricule…"
               value="<?php echo e(request('search')); ?>"
               oninput="filterTable()">
        <select id="deptFilter" class="pin-select" onchange="filterByDept()">
            <option value="">Tous les départements</option>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($dept); ?>" <?php echo e(request('department') == $dept ? 'selected' : ''); ?>>
                <?php echo e($dept); ?>

            </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    
    <div class="pin-action-bar">
        <button class="pin-btn pin-btn-regen-all no-print" id="btnRegenAll"
                onclick="confirmRegenAll()">
            🔄 Régénérer tous les PINs
        </button>
        <button class="pin-btn pin-btn-regen-all no-print" id="btnRegenDept"
                onclick="confirmRegenDept()" style="background:#6d28d9;display:none;">
            🔄 Régénérer ce département
        </button>
        <button class="pin-btn pin-btn-print no-print" onclick="window.print()">
            🖨 Imprimer
        </button>

        <span class="pin-count-badge" id="totalCount">
            <?php echo e($byDept->flatten()->count()); ?> employés
        </span>
    </div>

    
    <div class="pin-content screen-only" id="pinContent">
        <?php $__empty_1 = true; $__currentLoopData = $byDept; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept => $employees): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="dept-card" data-dept="<?php echo e($dept); ?>" id="dept-<?php echo e(Str::slug($dept)); ?>">

            
            <div class="dept-header" onclick="toggleDept(this)">
                <div class="dept-header-left">
                    <div>
                        <div class="dept-name"><?php echo e($dept ?: 'Sans département'); ?></div>
                        <div class="dept-count"><?php echo e($employees->count()); ?> employé<?php echo e($employees->count() > 1 ? 's' : ''); ?></div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <button class="dept-regen-btn no-print"
                            onclick="event.stopPropagation(); confirmRegenDept('<?php echo e($dept); ?>', '<?php echo e(Str::slug($dept)); ?>')"
                            title="Régénérer tous les PINs de ce département">
                        🔄 Régénérer dept.
                    </button>
                    <span class="dept-chevron">▾</span>
                </div>
            </div>

            
            <div class="dept-body">
                <table class="pin-table">
                    <thead>
                        <tr>
                            <th style="width:44px"></th>
                            <th>Employé</th>
                            <th>Matricule</th>
                            <th style="width:160px">PIN Badge</th>
                            <th class="no-print" style="width:130px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr id="emp-row-<?php echo e($emp->id); ?>" class="emp-row"
                            data-name="<?php echo e(strtolower($emp->first_name . ' ' . $emp->last_name)); ?>"
                            data-matricule="<?php echo e(strtolower($emp->matricule ?? '')); ?>"
                            data-dept="<?php echo e(strtolower($dept)); ?>">
                            
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="emp-avatar">
                                        <?php echo e(strtoupper(substr($emp->first_name,0,1).substr($emp->last_name,0,1))); ?>

                                    </div>
                                    <div>
                                        <div class="emp-name"><?php echo e($emp->first_name); ?> <?php echo e($emp->last_name); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="emp-matricule"><?php echo e($emp->matricule ?? '—'); ?></span>
                            </td>
                            <td>
                                <div class="pin-display">
                                    <span class="pin-code" id="pin-<?php echo e($emp->id); ?>" data-emp-id="<?php echo e($emp->id); ?>">
                                        <?php echo e($emp->plain_pin ?? '——'); ?>

                                    </span>
                                </div>
                            </td>
                            <td class="no-print">
                                <button class="pin-regen-single"
                                        id="regen-btn-<?php echo e($emp->id); ?>"
                                        onclick="regenSingle(<?php echo e($emp->id); ?>)">
                                    🔄 Nouveau PIN
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="pin-empty">
            <div style="font-size:48px;margin-bottom:1rem;"></div>
            <div>Aucun employé trouvé.</div>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="print-all-content print-only">
        <?php $__empty_1 = true; $__currentLoopData = $allByDept; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept => $employees): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="dept-card" style="break-inside: avoid; margin-bottom: 1rem;">

            
            <div class="dept-header" style="display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.25rem;background:linear-gradient(135deg, var(--pin-purple-bg) 0%, var(--pin-teal-bg) 100%);border-bottom:1px solid var(--pin-border);">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:34px;height:34px;border-radius:8px;background:var(--pin-purple);color:#fff;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;flex-shrink:0;">
                        <?php echo e(strtoupper(substr($dept ?: 'N', 0, 1))); ?>

                    </div>
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: var(--pin-text);"><?php echo e($dept ?: 'Sans département'); ?></div>
                        <div style="font-size: 11px; color: var(--pin-muted); margin-top: 1px;"><?php echo e($employees->count()); ?> employé<?php echo e($employees->count() > 1 ? 's' : ''); ?></div>
                    </div>
                </div>
            </div>

            
            <table class="pin-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width:44px;background:#fafafa;padding:9px 14px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--pin-muted);border-bottom:1px solid var(--pin-border);">#</th>
                        <th style="background:#fafafa;padding:9px 14px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--pin-muted);border-bottom:1px solid var(--pin-border);">Employé</th>
                        <th style="background:#fafafa;padding:9px 14px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--pin-muted);border-bottom:1px solid var(--pin-border);">Matricule</th>
                        <th style="width:160px;background:#fafafa;padding:9px 14px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--pin-muted);border-bottom:1px solid var(--pin-border);">PIN Badge</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr style="transition:background .12s;">
                        <td style="padding:10px 14px;border-bottom:1px solid var(--pin-border-soft);vertical-align:middle;color:var(--pin-muted);font-size:12px;"><?php echo e($i + 1); ?></td>
                        <td style="padding:10px 14px;border-bottom:1px solid var(--pin-border-soft);vertical-align:middle;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:32px;height:32px;border-radius:50%;background:var(--pin-teal-light);color:var(--pin-teal);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">
                                    <?php echo e(strtoupper(substr($emp->first_name,0,1).substr($emp->last_name,0,1))); ?>

                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:500;color:var(--pin-text);"><?php echo e($emp->first_name); ?> <?php echo e($emp->last_name); ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:10px 14px;border-bottom:1px solid var(--pin-border-soft);vertical-align:middle;">
                            <span style="font-size:11px;color:var(--pin-muted);font-family:'SF Mono','Fira Code',monospace;"><?php echo e($emp->matricule ?? '—'); ?></span>
                        </td>
                        <td style="padding:10px 14px;border-bottom:1px solid var(--pin-border-soft);vertical-align:middle;">
                            <div style="display:inline-flex;align-items:center;gap:6px;">
                                <span style="font-family:'SF Mono','Fira Code','Courier New',monospace;font-size:18px;font-weight:700;letter-spacing:4px;color:var(--pin-purple);background:var(--pin-purple-bg);padding:4px 12px;border-radius:8px;border:1px solid var(--pin-purple-light);min-width:80px;text-align:center;">
                                    <?php echo e($emp->plain_pin ?? '——'); ?>

                                </span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="text-align:center;padding:3rem;color:var(--pin-muted);font-size:14px;">
            <div style="font-size:48px;margin-bottom:1rem;"></div>
            <div>Aucun employé trouvé.</div>
        </div>
        <?php endif; ?>
    </div>

</div>


<div class="pin-toast" id="pinToast"></div>




<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;


/* ── Toggle département ── */
function toggleDept(header) {
    const body = header.nextElementSibling;
    const isCollapsed = header.classList.contains('collapsed');
    header.classList.toggle('collapsed', !isCollapsed);
    body.style.display = isCollapsed ? '' : 'none';
}

/* ── Filtre live par nom/matricule ── */
function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    document.querySelectorAll('.emp-row').forEach(row => {
        const match = row.dataset.name.includes(q) || row.dataset.matricule.includes(q);
        row.style.display = match ? '' : 'none';
    });
    updateCount();
}

/* ── Filtre par département ── */
function filterByDept() {
    const dept = document.getElementById('deptFilter').value.toLowerCase();
    const regenDeptBtn = document.getElementById('btnRegenDept');

    document.querySelectorAll('.dept-card').forEach(card => {
        if (!dept || card.dataset.dept.toLowerCase() === dept) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });

    if (dept) {
        regenDeptBtn.style.display = '';
        regenDeptBtn.dataset.dept = document.getElementById('deptFilter').value;
    } else {
        regenDeptBtn.style.display = 'none';
    }

    updateCount();
}

function updateCount() {
    const visible = document.querySelectorAll('.emp-row:not([style*="display: none"])').length;
    document.getElementById('totalCount').textContent = visible + ' employé' + (visible > 1 ? 's' : '');
}

/* ── Régénérer un seul PIN ── */
async function regenSingle(empId) {
    const btn = document.getElementById('regen-btn-' + empId);
    const pinEl = document.getElementById('pin-' + empId);

    btn.classList.add('loading');
    btn.innerHTML = '<span class="spin">⟳</span> Génération…';

    try {
        const res = await fetch('<?php echo e(route('pointage.regenerer-pin')); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ employee_id: empId })
        });
        const data = await res.json();

        if (data.success) {
            pinEl.textContent = data.new_pin;
            pinEl.classList.add('updated');
            setTimeout(() => pinEl.classList.remove('updated'), 2000);
            showToast('✓ PIN mis à jour : ' + data.new_pin);
        } else {
            showToast('Erreur lors de la régénération', true);
        }
    } catch (e) {
        showToast('Erreur réseau', true);
    } finally {
        btn.classList.remove('loading');
        btn.innerHTML = '🔄 Nouveau PIN';
    }
}

/* ── Modal confirmation ── */
let _pendingAction = null;

function confirmRegenAll() {
    document.getElementById('modalTitle').textContent = '🔄 Régénérer TOUS les PINs';
    document.getElementById('modalBody').innerHTML =
        '⚠️ Cette action va régénérer les PINs de <strong>tous les employés</strong>.<br>' +
        'Les anciens PINs seront définitivement remplacés en base de données.<br><br>' +
        'Cette action est <strong>irréversible</strong>. Confirmer ?';
    _pendingAction = () => doRegenAll(null);
    document.getElementById('confirmModal').classList.add('show');
}

function confirmRegenDept(dept, slug) {
    const deptName = dept || document.getElementById('btnRegenDept').dataset.dept;
    document.getElementById('modalTitle').textContent = '🔄 Régénérer PINs – ' + deptName;
    document.getElementById('modalBody').innerHTML =
        'Cette action va régénérer les PINs de tous les employés du département <strong>' + deptName + '</strong>.<br><br>' +
        'Les anciens PINs seront définitivement remplacés. Confirmer ?';
    _pendingAction = () => doRegenAll(deptName);
    document.getElementById('confirmModal').classList.add('show');
}

function closeModal() {
    document.getElementById('confirmModal').classList.remove('show');
    _pendingAction = null;
}

document.getElementById('modalConfirmBtn').addEventListener('click', () => {
    closeModal();
    if (_pendingAction) _pendingAction();
});

// Fermer en cliquant hors de la modal
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

/* ── Régénérer tous les PINs (optionnel : filtrer par dept) ── */
async function doRegenAll(department = null) {
    const btnAll  = document.getElementById('btnRegenAll');
    const btnDept = document.getElementById('btnRegenDept');

    [btnAll, btnDept].forEach(b => {
        b.disabled = true;
        b.innerHTML = '<span class="spin">⟳</span> En cours…';
    });

    try {
        const body = department ? { department } : {};
        const res = await fetch('<?php echo e(route('pointage.regenerer-tous-pins')); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        });
        const data = await res.json();

        if (data.success) {
            // Mettre à jour tous les affichages PIN
            data.pins.forEach(item => {
                const el = document.getElementById('pin-' + item.id);
                if (el) {
                    el.textContent = item.pin;
                    el.classList.add('updated');
                    setTimeout(() => el.classList.remove('updated'), 2500);
                }
            });
            showToast('✓ ' + data.count + ' PINs régénérés avec succès');
        } else {
            showToast('Erreur lors de la régénération', true);
        }
    } catch (e) {
        showToast('Erreur réseau', true);
    } finally {
        btnAll.disabled = false;
        btnAll.innerHTML = '🔄 Régénérer tous les PINs';
        btnDept.disabled = false;
        btnDept.innerHTML = '🔄 Régénérer ce département';
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/pointage/badges-pin.blade.php ENDPATH**/ ?>