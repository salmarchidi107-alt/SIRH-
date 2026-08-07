<?php $__env->startSection('title', 'Diagnostic Multi-Tenant'); ?>
<?php $__env->startSection('page-title', 'Diagnostic Multi-Tenant'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="sa-page-title">Diagnostic Multi-Tenant</div>
    <div class="sa-page-sub">Vérification de l'isolation des données entre les tenants</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<?php
    $statusTheme = [
        'ok'      => ['bg' => '#ecfdf5', 'border' => '#a7f3d0', 'text' => '#065f46', 'dot' => '#10b981', 'label' => 'OK'],
        'warning' => ['bg' => '#fffbeb', 'border' => '#fde68a', 'text' => '#92400e', 'dot' => '#f59e0b', 'label' => 'Avertissement'],
        'error'   => ['bg' => '#fef2f2', 'border' => '#fecaca', 'text' => '#991b1b', 'dot' => '#ef4444', 'label' => 'Erreur'],
        'skipped' => ['bg' => '#f3f4f6', 'border' => '#e5e7eb', 'text' => '#4b5563', 'dot' => '#9ca3af', 'label' => 'Ignoré'],
    ];
    $overall = $report['status'];
    $overallTheme = $statusTheme[$overall];
    $checks = $report['checks'];
?>


<div style="background:<?php echo e($overallTheme['bg']); ?>;border:1px solid <?php echo e($overallTheme['border']); ?>;
            border-radius:14px;padding:18px 20px;margin-bottom:22px;display:flex;
            align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:12px;">
        <span style="width:11px;height:11px;border-radius:50%;background:<?php echo e($overallTheme['dot']); ?>;
                     flex-shrink:0;box-shadow:0 0 0 4px <?php echo e($overallTheme['dot']); ?>22;"></span>
        <div>
            <div style="font-weight:700;font-size:14px;color:<?php echo e($overallTheme['text']); ?>;">
                Statut global : <?php echo e($overallTheme['label']); ?>

            </div>
            <div style="font-size:12.5px;color:<?php echo e($overallTheme['text']); ?>;margin-top:2px;">
                <?php echo e($report['total_anomalies']); ?> anomalie(s) détectée(s) sur <?php echo e(count($checks)); ?> contrôle(s) exécuté(s)
                — dernière analyse : <?php echo e($report['generated_at']->format('d/m/Y H:i')); ?>

            </div>
        </div>
    </div>
    <a href="<?php echo e(route('superadmin.tenant-diagnostic.index', ['refresh' => 1])); ?>" class="sa-btn sa-btn-ghost sa-btn-sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M4 9a9 9 0 0114.13-5.36M20 15a9 9 0 01-14.13 5.36"/>
        </svg>
        Relancer l'analyse
    </a>
</div>


<div style="display:flex;flex-direction:column;gap:12px;">
<?php $__currentLoopData = $checks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $theme = $statusTheme[$result['status']] ?? $statusTheme['skipped']; ?>
    <div class="sa-card" style="padding:0;">
        <div style="padding:16px 20px;display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="display:flex;align-items:flex-start;gap:12px;">
                <span style="margin-top:4px;flex-shrink:0;font-size:11px;font-weight:700;letter-spacing:.02em;
                             padding:3px 10px;border-radius:20px;background:<?php echo e($theme['bg']); ?>;
                             color:<?php echo e($theme['text']); ?>;border:1px solid <?php echo e($theme['border']); ?>;white-space:nowrap;">
                    <?php echo e($theme['label']); ?>

                </span>
                <div>
                    <div style="font-weight:700;font-size:13.5px;color:var(--text);">
                        <?php echo e($result['label']); ?>

                    </div>
                    <div style="font-size:12.5px;color:var(--text-muted);margin-top:3px;max-width:640px;">
                        <?php echo e($result['description']); ?>

                    </div>
                    <?php if($result['recommendation']): ?>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:6px;">
                            <strong style="color:var(--text);">Recommandation :</strong> <?php echo e($result['recommendation']); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div style="font-size:12px;color:var(--text-muted);white-space:nowrap;">
                <?php if($result['status'] === 'skipped'): ?>
                    Non applicable
                <?php else: ?>
                    <strong style="color:var(--text);font-size:15px;"><?php echo e($result['count']); ?></strong> enregistrement(s)
                <?php endif; ?>
            </div>
        </div>

        <?php if(!empty($result['records'])): ?>
            <details style="border-top:1px solid var(--border-light, #eef0f3);">
                <summary style="cursor:pointer;padding:10px 20px;font-size:12px;font-weight:600;color:var(--primary);list-style:none;">
                    Voir les enregistrements concernés (<?php echo e(count($result['records'])); ?><?php if($result['count'] > count($result['records'])): ?> sur <?php echo e($result['count']); ?> <?php endif; ?>)
                </summary>
                <div style="padding:0 20px 16px;">
                    <table class="sa-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Détail</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $result['records']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="font-weight:600;color:var(--text);">#<?php echo e($record['id']); ?></td>
                                <td style="color:var(--text-muted);font-size:12.5px;"><?php echo e($record['detail'] ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </details>
        <?php endif; ?>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/superadmin/tenant-diagnostic/index.blade.php ENDPATH**/ ?>