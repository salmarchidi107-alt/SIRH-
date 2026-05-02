<?php $__env->startSection('title', 'Détails Absence'); ?>
<?php $__env->startSection('page-title', 'Détails de l\'Absence'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Demande d'Absence</h1>
    </div>
    <a href="<?php echo e(route('absences.index')); ?>" class="btn btn-ghost">← Retour</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
    <div class="card">
        <div class="card-header">
            <div class="card-title"> Informations</div>
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Employé</div>
                    <div class="detail-value"><?php echo e($absence->employee->full_name); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Service</div>
                    <div class="detail-value"><?php echo e($absence->employee->department); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Type</div>
                    <div class="detail-value"><?php echo e(\App\Models\Absence::TYPES[$absence->type] ?? $absence->type); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Nombre de jours</div>
                    <div class="detail-value"><?php echo e($absence->days); ?> jour(s)</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date de début</div>
                    <div class="detail-value"><?php echo e($absence->start_date->format('d/m/Y')); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date de fin</div>
                    <div class="detail-value"><?php echo e($absence->end_date->format('d/m/Y')); ?></div>
                </div>
                <div class="detail-item" style="grid-column:1/-1">
                    <div class="detail-label">Motif</div>
                    <div class="detail-value"><?php echo e($absence->reason ?: 'Non spécifié'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title">Statut</div>
            </div>
          
            <div class="card-body">
                <?php if($absence->status == 'pending'): ?>
                    <span class="badge badge-warning" style="font-size:1rem;padding:8px 16px">En attente</span>
                    <div style="margin-top:16px;display:flex;flex-direction:column;gap:8px">
                        <form action="<?php echo e(route('absences.approve', $absence)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-success w-full">✓ Approuver</button>
                        </form>
                        <form action="<?php echo e(route('absences.reject', $absence)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-danger w-full">✗ Rejeter</button>
                        </form>
                    </div>
                <?php elseif($absence->status == 'approved'): ?>
                    <span class="badge badge-success" style="font-size:1rem;padding:8px 16px">Approuvé</span>
                    <?php if($absence->approved_at): ?>
                        <p style="margin-top:12px;font-size:0.875rem;color:var(--text-muted)">
                            Le <?php echo e($absence->approved_at->format('d/m/Y à H:i')); ?>

                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="badge badge-danger" style="font-size:1rem;padding:8px 16px">Rejeté</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if($absence->replacement): ?>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Remplacement</div>
            </div>
            <div class="card-body">
                <div style="display:flex;align-items:center;gap:12px">
                    <div class="table-avatar"><?php echo e(strtoupper(substr($absence->replacement->first_name,0,1))); ?></div>
                    <div>
                        <div class="table-name"><?php echo e($absence->replacement->full_name); ?></div>
                        <div class="table-sub"><?php echo e($absence->replacement->position); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/absences/show.blade.php ENDPATH**/ ?>