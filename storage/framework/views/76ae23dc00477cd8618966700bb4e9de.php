<?php $__env->startSection('title', 'Nouvelle Absence'); ?>
<?php $__env->startSection('page-title', 'Nouvelle Demande d\'Absence'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Nouvelle Absence</h1>
        <p>Soumettre une demande d'absence ou de congé</p>
    </div>
    <a href="<?php echo e(route('absences.index')); ?>" class="btn btn-ghost">← Retour</a>
</div>


<?php if(session('conflict_warning')): ?>
<div id="conflict-banner" style="
    background: #fffbeb;
    border: 2px solid #f59e0b;
    border-radius: 12px;
    padding: 18px 24px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    box-shadow: 0 4px 16px rgba(245,158,11,0.13);
    animation: slideDown 0.35s ease;
">
    <div style="display:flex;align-items:flex-start;gap:14px">
        <span style="font-size:2rem;line-height:1">⚠️</span>
        <div>
            <div style="font-weight:700;color:#92400e;font-size:1rem;margin-bottom:4px">
                Conflit de dates détecté
            </div>
            <div style="color:#78350f;font-size:0.92rem;line-height:1.5">
                <?php echo session('conflict_warning'); ?>

            </div>
            <div style="margin-top:10px;font-size:0.85rem;color:#b45309">
                Vous pouvez annuler et choisir d'autres dates, ou soumettre quand même.
            </div>
        </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:8px;flex-shrink:0;min-width:180px">
        <button type="button"
            onclick="submitWithConflict()"
            style="
                background:#f59e0b;border:none;border-radius:8px;
                padding:10px 18px;color:#fff;font-weight:600;
                cursor:pointer;font-size:0.92rem;
                box-shadow:0 2px 6px rgba(245,158,11,0.25);
                transition:background 0.2s;
            "
            onmouseover="this.style.background='#d97706'"
            onmouseout="this.style.background='#f59e0b'">
            ✓ Soumettre quand même
        </button>
        <a href="<?php echo e(route('absences.create')); ?>"
            style="
                background:#fff;border:1.5px solid #f59e0b;border-radius:8px;
                padding:9px 18px;color:#92400e;font-weight:600;
                cursor:pointer;font-size:0.92rem;text-align:center;
                text-decoration:none;display:block;
                transition:background 0.2s;
            "
            onmouseover="this.style.background='#fef3c7'"
            onmouseout="this.style.background='#fff'">
            ✕ Annuler
        </a>
    </div>
</div>
<?php endif; ?>

<form action="<?php echo e(route('absences.store')); ?>" method="POST" id="absence-form">
    <?php echo csrf_field(); ?>
    
    <input type="hidden" name="conflict_confirmed" id="conflict_confirmed" value="0">

    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Informations de la Demande</div>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Département</label>
                    <select id="department_filter" name="department_filter" class="form-control">
                        <option value="">Tous les départements</option>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($department); ?>" <?php echo e(old('department_filter') == $department ? 'selected' : ''); ?>>
                                <?php echo e($department); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Employé *</label>
                    <?php if(isset($employee)): ?>
                        <input type="hidden" name="employee_id" value="<?php echo e($employee->id); ?>">
                        <div style="padding:16px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:24px">
                            <h3 style="margin:0 0 8px 0;color:var(--primary);font-size:1.1rem"><?php echo e($employee->full_name); ?></h3>
                            <div style="color:var(--text-muted);font-size:0.875rem"><?php echo e($employee->department); ?> — <?php echo e($employee->position); ?></div>
                        </div>
                    <?php else: ?>
                        <select id="employee_select" name="employee_id" class="form-control" required>
                            <option value="">Sélectionner un employé</option>
                            <?php if($employees ?? []): ?>
                                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($emp->id); ?>"
                                        <?php echo e(old('employee_id', request('employee_id')) == $emp->id ? 'selected' : ''); ?>>
                                        <?php echo e($emp->full_name); ?> — <?php echo e($emp->department); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Type d'absence *</label>
                    <select name="type" class="form-control" required>
                        <option value="">Sélectionner le type</option>
                        <?php $__currentLoopData = array_keys(\App\Models\Absence::TYPES); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type); ?>" <?php echo e(old('type') == $type ? 'selected' : ''); ?>>
                                <?php echo e(\App\Models\Absence::TYPES[$type]); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date de début *</label>
                    <input type="date" name="start_date" class="form-control"
                           value="<?php echo e(old('start_date')); ?>" required>
                </div>

                <div class="form-group">
                    <label>Date de fin *</label>
                    <input type="date" name="end_date" class="form-control"
                           value="<?php echo e(old('end_date')); ?>" required>
                </div>

                <div class="form-group full">
                    <label>Motif</label>
                    <textarea name="reason" class="form-control" rows="2"
                              placeholder="Raison de l'absence..."><?php echo e(old('reason')); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Employé de remplacement</label>
                    <select id="replacement_select" name="replacement_id" class="form-control">
                        <option value="">Aucun</option>
                        <?php if($employees ?? []): ?>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($emp->id); ?>"
                                    <?php echo e(old('replacement_id') == $emp->id ? 'selected' : ''); ?>>
                                    <?php echo e($emp->full_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group full">
                    <label>Notes supplémentaires</label>
                    <textarea name="notes" class="form-control" rows="2"><?php echo e(old('notes')); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
        <a href="<?php echo e(route('absences.index')); ?>" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary">Soumettre la demande</button>
    </div>
</form>

<?php $__env->startPush('styles'); ?>
<style>
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-16px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    /* ── Confirmer malgré le conflit ── */
    function submitWithConflict() {
        document.getElementById('conflict_confirmed').value = '1';
        document.getElementById('absence-form').submit();
    }

    /* ── Filtre département → employés ── */
    document.addEventListener('DOMContentLoaded', function () {
        const departmentSelect  = document.getElementById('department_filter');
        const employeeSelect    = document.getElementById('employee_select');
        const replacementSelect = document.getElementById('replacement_select');
        const employees         = <?php echo json_encode($employeeOptions, 15, 512) ?>;

        function renderOptions(select, options, blankLabel) {
            if (!select) return;
            const currentValue = select.value;
            select.innerHTML = '';

            if (blankLabel !== null) {
                const blankOption = document.createElement('option');
                blankOption.value = '';
                blankOption.textContent = blankLabel;
                select.appendChild(blankOption);
            }

            options.forEach(function (employee) {
                const option = document.createElement('option');
                option.value = employee.id;
                option.textContent = employee.label;
                if (String(employee.id) === String(currentValue)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        function filterEmployees() {
            if (!departmentSelect) return;
            const selectedDepartment = departmentSelect.value;
            const filtered = selectedDepartment
                ? employees.filter(e => e.department === selectedDepartment)
                : employees;
            renderOptions(employeeSelect,    filtered, 'Sélectionner un employé');
            renderOptions(replacementSelect, filtered, 'Aucun');
        }

        if (departmentSelect) {
            departmentSelect.addEventListener('change', filterEmployees);
            filterEmployees();
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/absences/create.blade.php ENDPATH**/ ?>