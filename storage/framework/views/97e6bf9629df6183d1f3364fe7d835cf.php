<?php $__env->startSection('title', 'Appliquer une Semaine Type'); ?>
<?php $__env->startSection('page-title', 'Appliquer un Modèle de Semaine'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <h1> Appliquer une Semaine Type</h1>
        <p>Appliquez un modèle de semaine à un employé</p>
    </div>
</div>

<form method="POST" action="<?php echo e(route('planning.templates.apply')); ?>">
    <?php echo csrf_field(); ?>
    
    <div class="card">
        <div class="card-header">
            <div class="card-title">Sélection</div>
        </div>
        <div class="card-body">
            <div style="display:grid;gap:16px;max-width:600px">
                
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Modèle de semaine</label>
                    <select name="template_id" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                        <option value="">Sélectionner un modèle</option>
                        <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($template->id); ?>" <?php echo e(request('template_id') == $template->id || ($selectedTemplate && $selectedTemplate->id == $template->id) ? 'selected' : ''); ?>>
                                <?php echo e($template->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">
                        <?php echo e($selectedTemplate?->department ? 'Département ciblé' : 'Employé / Département'); ?>

                    </label>
                    <?php if($selectedTemplate?->department): ?>
                        
                        <div style="padding:12px;background:var(--surface-2);border-radius:8px;border:1px solid var(--border);font-weight:500">
                            📍 <?php echo e($selectedTemplate->department); ?> 
                            <span style="font-size:0.85rem;color:var(--text-muted);font-weight:400">
                                (<?php echo e(\App\Models\Employee::where('department', $selectedTemplate->department)->where('status', 'active')->count()); ?> employés)
                            </span>
                            <input type="hidden" name="department_target" value="<?php echo e($selectedTemplate->department); ?>">
                        </div>
                    <?php else: ?>
                        
                        <select name="department_target" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white;margin-bottom:12px">
                            <option value="">Sélectionner un département (mass apply)</option>
                            <?php $__currentLoopData = \App\Models\Department::orderBy('name')->pluck('name'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($dept); ?>"><?php echo e($dept); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <select name="employee_id" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                            <option value="">OU un employé spécifique</option>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($emp->id); ?>"><?php echo e($emp->full_name); ?> - <?php echo e($emp->department); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Date de début de semaine</label>
                    <input type="date" name="start_date" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                </div>

            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;margin-top:20px">
        <a href="<?php echo e(route('planning.templates.index')); ?>" class="btn btn-outline">Annuler</a>
        <button type="submit" class="btn btn-primary">Appliquer le modèle</button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/planning/templates/apply.blade.php ENDPATH**/ ?>