<?php $__env->startSection('title', 'Appliquer une Semaine Type'); ?>
<?php $__env->startSection('page-title', 'Appliquer un Modèle de Semaine'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <h1> Appliquer une Semaine Type</h1>
        <p>Appliquez un modèle de semaine à un employé ou un département entier</p>
    </div>
</div>


<?php if($errors->any()): ?>
<div style="margin-bottom:20px;padding:14px 18px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:12px;color:#b91c1c;font-size:0.9rem">
    <strong>⚠ Veuillez corriger les erreurs suivantes :</strong>
    <ul style="margin:8px 0 0 18px;padding:0">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?>


<?php if(session('success')): ?>
<div style="margin-bottom:20px;padding:14px 18px;background:rgba(22,163,74,.08);border:1px solid rgba(22,163,74,.25);border-radius:12px;color:#15803d;font-size:0.9rem">
    <?php echo session('success'); ?>

</div>
<?php endif; ?>

<form method="POST" action="<?php echo e(route('planning.templates.apply')); ?>" id="applyForm">
    <?php echo csrf_field(); ?>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Sélection</div>
        </div>
        <div class="card-body">
            <div style="display:grid;gap:20px;max-width:600px">

                
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">
                        Modèle de semaine <span style="color:#ef4444">*</span>
                    </label>
                    <select name="template_id" required
                        style="width:100%;padding:10px 12px;border:1px solid <?php echo e($errors->has('template_id') ? '#ef4444' : 'var(--border)'); ?>;border-radius:8px;font-size:0.9rem;background:white">
                        <option value="">Sélectionner un modèle</option>
                        <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($template->id); ?>"
                                <?php echo e(old('template_id', request('template_id')) == $template->id || ($selectedTemplate && $selectedTemplate->id == $template->id) ? 'selected' : ''); ?>>
                                <?php echo e($template->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['template_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="color:#ef4444;font-size:0.8rem;margin-top:4px"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">
                        <?php echo e($selectedTemplate?->department ? 'Département ciblé' : 'Cible'); ?>

                        <span style="color:#ef4444">*</span>
                    </label>

                    <?php if($selectedTemplate?->department): ?>
                        
                        <div style="padding:12px;background:var(--surface-2);border-radius:8px;border:1px solid var(--border);font-weight:500">
                             <?php echo e($selectedTemplate->department); ?>

                            <span style="font-size:0.85rem;color:var(--text-muted);font-weight:400">
                                (<?php echo e(\App\Models\Employee::where('department', $selectedTemplate->department)->where('status', 'active')->count()); ?> employés actifs)
                            </span>
                            <input type="hidden" name="department_target" value="<?php echo e($selectedTemplate->department); ?>">
                        </div>
                    <?php else: ?>
                        
                        <div style="padding:14px;background:var(--surface-2);border-radius:10px;border:1px solid <?php echo e($errors->has('employee_id') ? '#ef4444' : 'var(--border)'); ?>;display:grid;gap:10px">
                            <div>
                                <label style="font-size:0.8rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;display:block">
                                    Option 1 — Département entier
                                </label>
                                <select name="department_target" id="deptSelect"
                                    style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white"
                                    onchange="onTargetChange()">
                                    <option value="">Sélectionner un département</option>
                                    <?php $__currentLoopData = \App\Models\Department::orderBy('name')->pluck('name'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($dept); ?>" <?php echo e(old('department_target') == $dept ? 'selected' : ''); ?>>
                                            <?php echo e($dept); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div style="text-align:center;font-size:0.8rem;color:var(--text-muted);font-weight:600">— OU —</div>

                            <div>
                                <label style="font-size:0.8rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;display:block">
                                    Option 2 — Employé spécifique
                                </label>
                                <select name="employee_id" id="empSelect"
                                    style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white"
                                    onchange="onTargetChange()">
                                    <option value="">Sélectionner un employé</option>
                                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($emp->id); ?>" <?php echo e(old('employee_id') == $emp->id ? 'selected' : ''); ?>>
                                            <?php echo e($emp->full_name); ?> — <?php echo e($emp->department); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>

                        <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p style="color:#ef4444;font-size:0.8rem;margin-top:6px"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                        
                        <p id="targetError" style="color:#ef4444;font-size:0.8rem;margin-top:6px;display:none">
                            ⚠ Veuillez sélectionner un département ou un employé.
                        </p>
                    <?php endif; ?>
                </div>

                
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">
                        Date de début de semaine <span style="color:#ef4444">*</span>
                    </label>
                    <input type="date" name="start_date" required
                        value="<?php echo e(old('start_date', date('Y-m-d'))); ?>"
                        style="width:100%;padding:10px 12px;border:1px solid <?php echo e($errors->has('start_date') ? '#ef4444' : 'var(--border)'); ?>;border-radius:8px;font-size:0.9rem">
                    <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="color:#ef4444;font-size:0.8rem;margin-top:4px"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;margin-top:20px">
        <a href="<?php echo e(route('planning.templates.index')); ?>" class="btn btn-outline">Annuler</a>
        <button type="submit" class="btn btn-primary" id="submitBtn">Appliquer le modèle</button>
    </div>
</form>

<script>
function onTargetChange() {
    var dept = document.getElementById('deptSelect');
    var emp  = document.getElementById('empSelect');
    if (!dept || !emp) return;

    /* Si département choisi → vider employé, et vice-versa */
    if (dept.value) {
        emp.value = '';
    } else if (emp.value) {
        dept.value = '';
    }

    document.getElementById('targetError').style.display = 'none';
}

document.getElementById('applyForm').addEventListener('submit', function(e) {
    var dept = document.getElementById('deptSelect');
    var emp  = document.getElementById('empSelect');

    /* Si les deux selects existent (mode manuel) et les deux sont vides → bloquer */
    if (dept && emp && !dept.value && !emp.value) {
        e.preventDefault();
        document.getElementById('targetError').style.display = 'block';
        dept.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/planning/templates/apply.blade.php ENDPATH**/ ?>