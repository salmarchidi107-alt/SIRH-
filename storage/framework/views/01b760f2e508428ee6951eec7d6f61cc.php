<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Tableau de bord'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="sa-page-title">Tableau de bord</div>
    <div class="sa-page-sub">Vue globale de la plateforme multi-tenant</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:22px;">
<?php
    $cards = [
        ['label'=>'Tenants total',  'value'=>$stats['total_tenants'],  'color'=>'#4f46e5', 'bg'=>'#ede9fe',
         'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5'],
        ['label'=>'Tenants actifs', 'value'=>$stats['active_tenants'], 'color'=>'#10b981', 'bg'=>'#ecfdf5',
         'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label'=>'En essai',       'value'=>$stats['trial_tenants'],  'color'=>'#3b82f6', 'bg'=>'#eff6ff',
         'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label'=>'Inactifs',       'value'=>$stats['inactive_tenants'], 'color'=>'#ef4444', 'bg'=>'#fef2f2',
         'icon'=>'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M9 7a4 4 0 1 0 8 0'],
    ];
?>
<?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="sa-stat">
        <div class="sa-stat-icon" style="background:<?php echo e($c['bg']); ?>;">
            <svg viewBox="0 0 24 24" fill="none" stroke="<?php echo e($c['color']); ?>" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($c['icon']); ?>"/>
            </svg>
        </div>
        <div class="sa-stat-val" data-count="<?php echo e($c['value']); ?>"><?php echo e($c['value']); ?></div>
        <div class="sa-stat-lbl"><?php echo e($c['label']); ?></div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="sa-card">
    <div class="sa-card-header">
        <div>
            <div class="sa-card-title">Tenants récents</div>
            <div class="sa-card-sub">Derniers tenants créés sur la plateforme</div>
        </div>
        <a href="<?php echo e(route('superadmin.tenants.index')); ?>" class="sa-btn sa-btn-ghost sa-btn-sm">
            Voir tout
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
    <table class="sa-table">
        <thead>
            <tr>
                <th>Société</th>
                <th>Plan</th>
                <th>Statut</th>
                <th>Admin</th>
                <th>Créé</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
$sc = match(($t->status?->value ?? 'inactive')) {
                    'active'    => 'sa-badge-active',
                    'suspended' => 'sa-badge-suspended',
                    'trial'     => 'sa-badge-trial',
                    default     => 'sa-badge-inactive',
                };
$pc = match(($t->plan?->value ?? 'starter')) {
                    'enterprise' => 'sa-badge-ent',
                    'pro'        => 'sa-badge-pro',
                    default      => 'sa-badge-starter',
                };
            ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:11px;">
                        <div style="width:36px;height:36px;border-radius:10px;background:<?php echo e($t->brand_color); ?>;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0;box-shadow:0 3px 8px <?php echo e($t->brand_color); ?>44;">
                            <?php echo e($t->initials); ?>

                        </div>
                        <div>
                            <div style="font-weight:700;font-size:13px;color:var(--text);"><?php echo e($t->name); ?></div>
                            <div style="font-size:11px;color:var(--text-muted);font-family:monospace;"><?php echo e($t->domain); ?></div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="sa-badge <?php echo e($sc); ?>">
                        <span style="width:5px;height:5px;border-radius:50%;background:currentColor;display:inline-block;"></span>
<?php echo e($t->status?->label() ?? 'Inconnu'); ?>

                    </span>
                </td>
                <td style="color:var(--text-muted);"><?php echo e($t->admin?->name ?? '—'); ?></td>
                <td style="color:var(--text-light);font-size:12px;"><?php echo e($t->created_at->format('d/m/Y')); ?></td>
                <td style="text-align:right;">
                    <a href="<?php echo e(route('superadmin.tenants.edit', $t)); ?>" class="sa-btn sa-btn-ghost sa-btn-sm">Modifier</a>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted);">
                    Aucun tenant.
                    <a href="<?php echo e(route('superadmin.tenants.create')); ?>" style="color:var(--primary);margin-left:6px;">Créer →</a>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.getAttribute('data-count'));
    let current = 0;
    const step = Math.ceil(target / 40);
    const timer = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = current.toLocaleString('fr-FR');
        if (current >= target) clearInterval(timer);
    }, 30);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/superadmin/dashboard.blade.php ENDPATH**/ ?>