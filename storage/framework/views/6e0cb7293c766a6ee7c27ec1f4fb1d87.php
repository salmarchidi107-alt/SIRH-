<?php $__env->startSection('title', 'Tenants'); ?>
<?php $__env->startSection('page-title', 'Gestion des tenants'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="sa-page-title">Gestion des tenants</div>
    <div class="sa-page-sub"><?php echo e($counts['all']); ?> tenant(s) sur la plateforme</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-actions'); ?>
    <a href="<?php echo e(route('superadmin.tenants.create')); ?>" class="sa-btn sa-btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouveau tenant
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:20px;">
<?php $__currentLoopData = [
    ['Total tenants',    $counts['all'],            '#4f46e5'],
    ['Utilisateurs',     $counts['total_users'],    '#10b981'],
    ['Nouveaux ce mois', $counts['new_this_month'], '#3b82f6'],
]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$lbl, $val, $col]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="sa-stat" style="padding:13px 15px;">
        <div class="sa-stat-val" style="font-size:22px;color:<?php echo e($col); ?>;"><?php echo e($val); ?></div>
        <div class="sa-stat-lbl"><?php echo e($lbl); ?></div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<form method="GET" action="<?php echo e(route('superadmin.tenants.index')); ?>"
      style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
    <div class="sa-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input type="text" name="search" placeholder="Rechercher par nom, slug, secteur…"
               value="<?php echo e(request('search')); ?>" oninput="this.form.submit()">
    </div>
</form>

<?php if($tenants->isEmpty()): ?>
    <div class="sa-card" style="padding:60px;text-align:center;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-light)"
             stroke-width="1.5" style="margin:0 auto 14px;display:block;">
            <rect x="2" y="7" width="20" height="14" rx="2"/>
            <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
        </svg>
        <div style="font-size:14px;font-weight:600;color:var(--text-muted);">Aucun tenant trouvé</div>
        <a href="<?php echo e(route('superadmin.tenants.create')); ?>"
           class="sa-btn sa-btn-primary" style="margin-top:14px;display:inline-flex;">
            Créer le premier tenant
        </a>
    </div>
<?php else: ?>
    <div class="sa-card" style="overflow:hidden;">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Société</th>
                    <th>Secteur</th>
                    <th>Région</th>
                    <th>Contact</th>
                    <th>Utilisateurs</th>
                    <th>Admin</th>
                    <th>Créé</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:38px;height:38px;border-radius:10px;background:<?php echo e($t->brand_color); ?>;
                                        display:flex;align-items:center;justify-content:center;
                                        font-size:13px;font-weight:800;color:#fff;flex-shrink:0;
                                        box-shadow:0 3px 8px <?php echo e($t->brand_color); ?>44;">
                                <?php echo e($t->initials); ?>

                            </div>
                            <div>
                                <div style="font-weight:700;font-size:13px;color:var(--text);"><?php echo e($t->name); ?></div>
                                <div style="font-size:11px;color:var(--text-muted);font-family:monospace;"><?php echo e($t->domain); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if($t->sector): ?>
                            <span style="background:#f0f9ff;color:#0369a1;font-size:11px;
                                         font-weight:600;padding:3px 10px;border-radius:20px;">
                                <?php echo e($t->sector); ?>

                            </span>
                        <?php else: ?>
                            <span style="color:var(--text-muted);">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:var(--text-muted);"><?php echo e($t->region ?? '—'); ?></td>
                    <td>
                        <div style="font-size:12px;color:var(--text);"><?php echo e($t->phone ?? '—'); ?></div>
                        <div style="font-size:11px;color:var(--text-muted);"><?php echo e($t->email_societe ?? '—'); ?></div>
                    </td>
                    <td>
                        <span style="font-weight:700;font-size:14px;color:var(--text);"><?php echo e($t->users_count); ?></span>
                    </td>
                    <td style="font-size:12px;color:var(--text-muted);"><?php echo e($t->admin?->name ?? '—'); ?></td>
                    <td style="font-size:12px;color:var(--text-light);"><?php echo e($t->created_at->format('d/m/Y')); ?></td>
                    <td style="text-align:right;white-space:nowrap;">
                        <div style="display:inline-flex;gap:4px;">
                            <a href="<?php echo e(route('superadmin.tenants.edit', $t)); ?>"
                               class="sa-btn sa-btn-ghost sa-btn-sm" title="Modifier">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            <form method="POST" action="<?php echo e(route('superadmin.tenants.destroy', $t)); ?>"
                                  onsubmit="return confirm('Supprimer <?php echo e(addslashes($t->name)); ?> et tous ses utilisateurs ?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm" title="Supprimer">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php if($tenants->hasPages()): ?>
            <div style="padding:16px 20px;border-top:1px solid var(--border-light);">
                <?php echo e($tenants->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/superadmin/tenants/index.blade.php ENDPATH**/ ?>