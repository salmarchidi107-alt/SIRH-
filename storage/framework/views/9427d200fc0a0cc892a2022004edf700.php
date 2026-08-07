<?php $__env->startSection('title', 'Alertes de sécurité'); ?>
<?php $__env->startSection('page-title', 'Alertes de sécurité'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="sa-page-title">Alertes de sécurité</div>
    <div class="sa-page-sub">Fuites de données inter-tenants détectées côté frontend</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<form method="GET" class="sa-card" style="padding:14px 16px;margin-bottom:16px;">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;">
        <div style="min-width:180px;">
            <label style="font-size:11px;font-weight:600;color:var(--text-muted);">Tenant</label>
            <select name="tenant_id" class="sa-input" style="width:100%;">
                <option value="">Tous</option>
                <?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($t->id); ?>" <?php if(request('tenant_id') == $t->id): echo 'selected'; endif; ?>><?php echo e($t->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div style="min-width:160px;">
            <label style="font-size:11px;font-weight:600;color:var(--text-muted);">Module</label>
            <select name="module" class="sa-input" style="width:100%;">
                <option value="">Tous</option>
                <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($m); ?>" <?php if(request('module') === $m): echo 'selected'; endif; ?>><?php echo e($m); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div style="min-width:130px;">
            <label style="font-size:11px;font-weight:600;color:var(--text-muted);">Du</label>
            <input type="date" name="date_from" class="sa-input" style="width:100%;" value="<?php echo e(request('date_from')); ?>">
        </div>
        <div style="min-width:130px;">
            <label style="font-size:11px;font-weight:600;color:var(--text-muted);">Au</label>
            <input type="date" name="date_to" class="sa-input" style="width:100%;" value="<?php echo e(request('date_to')); ?>">
        </div>
        <div style="flex:1;min-width:200px;">
            <label style="font-size:11px;font-weight:600;color:var(--text-muted);">Recherche</label>
            <input type="text" name="q" class="sa-input" style="width:100%;"
                   placeholder="Utilisateur, route, module, ressource..." value="<?php echo e(request('q')); ?>">
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="sa-btn sa-btn-sm">Filtrer</button>
            <?php if(request()->anyFilled(['tenant_id', 'module', 'date_from', 'date_to', 'q', 'user_id'])): ?>
                <a href="<?php echo e(route('superadmin.security-alerts.index')); ?>" class="sa-btn sa-btn-ghost sa-btn-sm">Réinitialiser</a>
            <?php endif; ?>
        </div>
    </div>
</form>


<div class="sa-card" style="padding:0;">
    <table class="sa-table" style="font-size:12.5px;">
        <thead>
            <tr>
                <th>Date</th>
                <th>Utilisateur</th>
                <th>Tenant attendu → reçu</th>
                <th>Module / Ressource</th>
                <th>Enregistrements</th>
                <th>Route</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="white-space:nowrap;color:var(--text-light);">
                    <?php echo e($alert->created_at->format('d/m/Y H:i:s')); ?>

                </td>
                <td>
                    <div style="font-weight:600;color:var(--text);"><?php echo e($alert->user_name); ?></div>
                    <div style="font-size:11px;color:var(--text-muted);">#<?php echo e($alert->user_id ?? '—'); ?></div>
                </td>
                <td style="white-space:nowrap;font-family:ui-monospace,monospace;">
                    <span style="color:var(--text-muted);"><?php echo e($alert->expected_tenant_id ?? '—'); ?></span>
                    <span style="color:#ef4444;font-weight:700;"> → <?php echo e($alert->received_tenant_id ?? '—'); ?></span>
                </td>
                <td>
                    <div style="font-weight:600;color:var(--text);"><?php echo e($alert->module); ?></div>
                    <div style="font-size:11px;color:var(--text-muted);font-family:ui-monospace,monospace;"><?php echo e($alert->model_name); ?></div>
                </td>
                <td style="font-family:ui-monospace,monospace;font-size:11.5px;max-width:220px;">
                    <?php $ids = $alert->record_ids ?? []; ?>
                    <?php if(count($ids) <= 5): ?>
                        <?php echo e(implode(', ', array_map(fn ($id) => '#'.$id, $ids))); ?>

                    <?php else: ?>
                        <?php echo e(implode(', ', array_map(fn ($id) => '#'.$id, array_slice($ids, 0, 5)))); ?>

                        <span style="color:var(--text-muted);">+<?php echo e(count($ids) - 5); ?></span>
                    <?php endif; ?>
                </td>
                <td style="font-family:ui-monospace,monospace;font-size:11.5px;max-width:200px;
                           overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                    title="<?php echo e($alert->url); ?>">
                    <?php echo e($alert->route_name ?? $alert->url); ?>

                </td>
                <td style="font-family:ui-monospace,monospace;color:var(--text-muted);white-space:nowrap;">
                    <?php echo e($alert->ip_address ?? '—'); ?>

                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">
                    Aucune alerte enregistrée pour ces filtres.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div style="margin-top:14px;">
    <?php echo e($alerts->links()); ?>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/superadmin/security-alerts/index.blade.php ENDPATH**/ ?>