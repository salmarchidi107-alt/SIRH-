<?php $__env->startSection('title', 'Absences & Congés'); ?>
<?php $__env->startSection('page-title', 'Absences & Congés'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Absences & Congés</h1>
        <p><?php echo e($absences->total()); ?> demandes d'absence</p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo e(route('absences.create')); ?>" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nouvelle demande
        </a>
    </div>
</div>

<div class="filters-bar">
    <form method="GET" action="<?php echo e(route('absences.index')); ?>" class="filters-bar flex-wrap gap-3">
        <div class="search-bar">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" placeholder="Rechercher..." value="<?php echo e(request('search')); ?>">
        </div>
        <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>En attente</option>
            <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approuvées</option>
            <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejetées</option>
        </select>
        <select name="employee_id" class="filter-select" onchange="this.form.submit()">
            <option value="">Tous les employés</option>
            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($emp->id); ?>" <?php echo e(request('employee_id') == $emp->id ? 'selected' : ''); ?>><?php echo e($emp->full_name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </form>
</div>

<?php if($pending_count > 0): ?>
<div class="alert alert-warning mb-5">
    ⚠️ <strong><?php echo e($pending_count); ?></strong> demande(s) en attente d'approbation
</div>
<?php endif; ?>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Type</th>
                    <th>Période</th>
                    <th>Jours</th>
                    <th>Créé le</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $absences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $absence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div class="table-employee">
                            <div class="table-avatar"><?php echo e(strtoupper(substr($absence->employee->first_name,0,1))); ?></div>
                            <div>
                                <div class="table-name"><?php echo e($absence->employee->full_name); ?></div>
                                <div class="table-sub"><?php echo e($absence->employee->department); ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?php echo e(\App\Models\Absence::TYPES[$absence->type] ?? $absence->type); ?></td>
                    <td class="text-sm">
                        <?php echo e($absence->start_date->format('d/m')); ?> → <?php echo e($absence->end_date->format('d/m/Y')); ?>

                    </td>
                    <td><span class="font-semibold"><?php echo e($absence->days); ?></span></td>
                    <td>
                        <span class="text-xs text-muted block">
                            <?php echo e($absence->created_at->format('d/m/Y')); ?>

                            <time class="text-[0.6875rem]"><?php echo e($absence->created_at->format('H:i')); ?></time>
                        </span>
                    </td>
                    <td>
                        <?php if($absence->status == 'pending'): ?>
                            <span class="badge badge-warning">En attente</span>
                        <?php elseif($absence->status == 'approved'): ?>
                            <span class="badge badge-success">Approuvé</span>
                        <?php elseif($absence->status == 'rejected'): ?>
                            <span class="badge badge-danger">Rejeté</span>
                        <?php else: ?>
                            <span class="badge badge-neutral">Annulé</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex gap-1.5">
                            <a href="<?php echo e(route('absences.show', $absence)); ?>" class="btn btn-ghost btn-sm btn-icon" title="Voir">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            <?php if($absence->status == 'pending' && in_array(auth()->user()->role, ['admin', 'rh'])): ?>
                                <form action="<?php echo e(route('absences.approve', $absence)); ?>" method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-success btn-sm" title="Approuver">✓</button>
                                </form>
                                <form action="<?php echo e(route('absences.reject', $absence)); ?>" method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-danger btn-sm" title="Rejeter">✗</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center p-12 text-muted-foreground">
                        <div class="text-6xl mb-3">📅</div>
                        <div>Aucune absence trouvée</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="px-4"><?php echo e($absences->withQueryString()->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/absences/index.blade.php ENDPATH**/ ?>