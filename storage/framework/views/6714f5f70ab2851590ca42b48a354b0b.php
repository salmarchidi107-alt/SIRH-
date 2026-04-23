<?php $__env->startSection('title', 'Semaines Types'); ?>
<?php $__env->startSection('page-title', 'Modèles de Semaines'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <h1> Semaines Types</h1>
        <p>Créer et gérer des modèles de planification réutilisables</p>
    </div>
    <div class="page-header-right">
        <a href="<?php echo e(route('planning.templates.create')); ?>" class="btn btn-primary">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nouvelle semaine type
        </a>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nom du modèle</th>
                    <th>Description rapide</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div style="font-weight:600"><?php echo e($template->name); ?></div>
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap">
                            <?php if($template->monday_shift_type): ?>
                                <span class="shift-pill shift-<?php echo e($template->monday_shift_type); ?>">L</span>
                            <?php endif; ?>
                            <?php if($template->tuesday_shift_type): ?>
                                <span class="shift-pill shift-<?php echo e($template->tuesday_shift_type); ?>">Ma</span>
                            <?php endif; ?>
                            <?php if($template->wednesday_shift_type): ?>
                                <span class="shift-pill shift-<?php echo e($template->wednesday_shift_type); ?>">Me</span>
                            <?php endif; ?>
                            <?php if($template->thursday_shift_type): ?>
                                <span class="shift-pill shift-<?php echo e($template->thursday_shift_type); ?>">J</span>
                            <?php endif; ?>
                            <?php if($template->friday_shift_type): ?>
                                <span class="shift-pill shift-<?php echo e($template->friday_shift_type); ?>">V</span>
                            <?php endif; ?>
                            <?php if($template->saturday_shift_type): ?>
                                <span class="shift-pill shift-<?php echo e($template->saturday_shift_type); ?>">S</span>
                            <?php endif; ?>
                            <?php if($template->sunday_shift_type): ?>
                                <span class="shift-pill shift-<?php echo e($template->sunday_shift_type); ?>">D</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;gap:8px">
                            <a href="<?php echo e(route('planning.templates.apply')); ?>?template_id=<?php echo e($template->id); ?>" class="btn btn-sm btn-outline">
                                Appliquer
                            </a>
                            <form method="POST" action="<?php echo e(route('planning.templates.destroy', $template->id)); ?>" style="display:inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-outline" style="color:var(--danger);border-color:var(--danger)" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce modèle?')">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="3" style="text-align:center;padding:48px;color:var(--text-muted)">
                        <div style="font-size:2.5rem;margin-bottom:12px">  </div>
                        <div>Aucune semaine type créée</div>
                        <a href="<?php echo e(route('planning.templates.create')); ?>" class="btn btn-primary" style="margin-top:12px">Créer une semaine type</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:24px">
    <a href="<?php echo e(route('planning.weekly')); ?>" class="btn btn-outline">← Retour au planning</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/planning/templates/index.blade.php ENDPATH**/ ?>