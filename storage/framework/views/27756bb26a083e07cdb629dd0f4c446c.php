<?php $__env->startSection('title', 'Éléments Variables'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(url('/')); ?>">Accueil</a></li>
                        <li class="breadcrumb-item active">Éléments Variables</li>
                    </ol>
                </div>
                <h4 class="page-title">Éléments Variables - <?php echo e($month); ?>/<?php echo e($year); ?></h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row mb-4">
                        <div class="col-md-3">
                            <label>Mois:</label>
                            <select name="month" class="form-control">
                                <?php for($m=1; $m<=12; $m++): ?>
                                    <option value="<?php echo e($m); ?>" <?php echo e($month == $m ? 'selected' : ''); ?>><?php echo e($m); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Année:</label>
                            <input type="number" name="year" value="<?php echo e($year); ?>" class="form-control" min="2020" max="2030">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Filtrer</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Employé</th>
                                    <th>Rubrique</th>
                                    <th>Label</th>
                                    <th>Montant</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $variableElements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($element->employee->first_name . ' ' . $element->employee->last_name ?? 'N/A'); ?></td>
                                        <td><?php echo e($element->rubrique ?? $element->category); ?></td>
                                        <td><?php echo e($element->label); ?></td>
                                        <td><?php echo e(number_format($element->amount, 2)); ?> <?php echo e($element->unit); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($element->type_color); ?>"><?php echo e($element->type_label); ?></span>
                                        </td>
                                        <td><?php echo e($element->created_at->format('d/m/Y')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Aucun élément variable pour <?php echo e($month); ?>/<?php echo e($year); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        <?php echo e($variableElements->appends(request()->query())->links()); ?>

                    </div>

                    <h5 class="mt-4">Employés Actifs (<?php echo e($employees->count()); ?>)</h5>
                    <p>Utilisez cette liste pour ajouter de nouveaux éléments variables.</p>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/variable-elements/index.blade.php ENDPATH**/ ?>