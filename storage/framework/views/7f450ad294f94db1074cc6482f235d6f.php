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


<?php if($errors->any()): ?>
<div style="
    background: #fef2f2;
    border: 2px solid #ef4444;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    box-shadow: 0 4px 16px rgba(239,68,68,0.10);
">
    <div style="font-weight:700;color:#991b1b;margin-bottom:8px;font-size:0.95rem;">
        ⚠️ Veuillez corriger les erreurs suivantes :
    </div>
    <ul style="margin:0;padding-left:20px;color:#7f1d1d;font-size:0.9rem;line-height:1.7">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?>


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


<div id="self-conflict-banner" style="
    display:none;
    background:#fef2f2;
    border:2px solid #ef4444;
    border-radius:12px;
    padding:18px 24px;
    margin-bottom:24px;
    align-items:center;
    gap:16px;
    box-shadow:0 4px 16px rgba(239,68,68,0.13);
    animation: slideDown 0.35s ease;
">
    <span style="font-size:2rem;line-height:1">🚫</span>
    <div>
        <div style="font-weight:700;color:#991b1b;font-size:1rem;margin-bottom:4px">
            Cet employé a déjà une absence approuvée sur cette période
        </div>
        <div id="self-conflict-detail" style="color:#7f1d1d;font-size:0.92rem;line-height:1.5"></div>
    </div>
</div>

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
                        
                        <input type="hidden" name="employee_id" id="employee_id_hidden" value="<?php echo e($employee->id); ?>">
                        <div style="padding:16px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:24px">
                            <h3 style="margin:0 0 8px 0;color:var(--primary);font-size:1.1rem"><?php echo e($employee->full_name); ?></h3>
                            <div style="color:var(--text-muted);font-size:0.875rem"><?php echo e($employee->department); ?> — <?php echo e($employee->position); ?></div>
                        </div>
                    <?php else: ?>
                        
                        <select id="employee_select"
                                name="employee_id"
                                class="form-control <?php echo e($errors->has('employee_id') ? 'is-invalid' : ''); ?>"
                                required
                                data-old="<?php echo e(old('employee_id', request('employee_id'))); ?>">
                            <option value="">Sélectionner un employé</option>
                            
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($emp->id); ?>"
                                    <?php echo e(old('employee_id', request('employee_id')) == $emp->id ? 'selected' : ''); ?>>
                                    <?php echo e($emp->full_name); ?> — <?php echo e($emp->department); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php endif; ?>
                </div>

                
                <div class="form-group">
                    <label>Type d'absence *</label>
                    <select name="type" id="type_select"
                            class="form-control <?php echo e($errors->has('type') ? 'is-invalid' : ''); ?>"
                            required
                            onchange="toggleAutreType(this)">
                        <option value="">Sélectionner le type</option>
                        <?php $__currentLoopData = array_keys(\App\Models\Absence::TYPES); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type); ?>" <?php echo e(old('type') == $type ? 'selected' : ''); ?>>
                                <?php echo e(\App\Models\Absence::TYPES[$type]); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <option value="autre" <?php echo e(old('type') == 'autre' ? 'selected' : ''); ?>>Autre</option>
                    </select>
                    <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="form-group" id="autre_type_group"
                     style="display:<?php echo e(old('type') == 'autre' ? 'block' : 'none'); ?>">
                    <label>Préciser le type *</label>
                    <input type="text" name="type_autre" id="type_autre"
                           class="form-control <?php echo e($errors->has('type_autre') ? 'is-invalid' : ''); ?>"
                           value="<?php echo e(old('type_autre')); ?>"
                           placeholder="Ex : Formation, Mission externe…">
                    <?php $__errorArgs = ['type_autre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="form-group">
                    <label>Date de début *</label>
                    <input type="date" name="start_date" id="start_date"
                           class="form-control <?php echo e($errors->has('start_date') ? 'is-invalid' : ''); ?>"
                           value="<?php echo e(old('start_date')); ?>" required>
                    <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="form-group">
                    <label>Date de fin *</label>
                    <input type="date" name="end_date" id="end_date"
                           class="form-control <?php echo e($errors->has('end_date') ? 'is-invalid' : ''); ?>"
                           value="<?php echo e(old('end_date')); ?>" required>
                    <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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

            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
        <a href="<?php echo e(route('absences.index')); ?>" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary">Soumettre la demande</button>
    </div>
</form>

<script>
/* ─────────────────────────────────────────────────────────────
   Données passées depuis le contrôleur
   ───────────────────────────────────────────────────────────── */
var employees     = <?php echo json_encode($employeeOptions, 15, 512) ?>;
var selfConflicts = <?php echo json_encode($selfConflicts ?? [], 15, 512) ?>;

/* ─────────────────────────────────────────────────────────────
   Confirmer malgré le conflit (autre employé)
   ───────────────────────────────────────────────────────────── */
function submitWithConflict() {
    document.getElementById('conflict_confirmed').value = '1';
    document.getElementById('absence-form').submit();
}

/* ─────────────────────────────────────────────────────────────
   Afficher/masquer le champ "Préciser le type"
   ───────────────────────────────────────────────────────────── */
function toggleAutreType(select) {
    var group = document.getElementById('autre_type_group');
    var input = document.getElementById('type_autre');
    if (select.value === 'autre') {
        group.style.display = 'block';
        input.required = true;
    } else {
        group.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}

/* ─────────────────────────────────────────────────────────────
   Helpers : obtenir l'employee_id sélectionné
   ───────────────────────────────────────────────────────────── */
function getSelectedEmployeeId() {
    var sel = document.getElementById('employee_select');
    var hid = document.getElementById('employee_id_hidden');
    if (sel) return sel.value;
    if (hid) return hid.value;
    return '';
}

/* ─────────────────────────────────────────────────────────────
   Filtre remplacement : exclut l'employé sélectionné
   ───────────────────────────────────────────────────────────── */
function renderReplacementOptions(excludeId) {
    var select  = document.getElementById('replacement_select');
    if (!select) return;
    var current = select.value;
    select.innerHTML = '<option value="">Aucun</option>';
    employees.forEach(function(emp) {
        if (String(emp.id) === String(excludeId)) return;
        var opt = document.createElement('option');
        opt.value = emp.id;
        opt.textContent = emp.label;
        if (String(emp.id) === String(current)) opt.selected = true;
        select.appendChild(opt);
    });
}

/* ─────────────────────────────────────────────────────────────
   Filtre département → reconstruit la liste des employés
   FIX : restaure old('employee_id') via data-old après rebuild
   ───────────────────────────────────────────────────────────── */
function renderEmployeeOptions(filtered) {
    var select = document.getElementById('employee_select');
    if (!select) return;

    // ── FIX : priorité à data-old (valeur PHP old()), sinon valeur courante
    var oldId   = select.getAttribute('data-old') || '';
    var current = select.value || oldId;

    select.innerHTML = '<option value="">Sélectionner un employé</option>';
    filtered.forEach(function(emp) {
        var opt = document.createElement('option');
        opt.value = emp.id;
        opt.textContent = emp.label;
        if (String(emp.id) === String(current)) opt.selected = true;
        select.appendChild(opt);
    });

    // ── FIX : une fois restaurée, vider data-old pour ne pas bloquer
    //          les changements manuels ultérieurs
    select.removeAttribute('data-old');

    renderReplacementOptions(select.value);
}

/* ─────────────────────────────────────────────────────────────
   Détection conflit propre (même employé, dates communes)
   ───────────────────────────────────────────────────────────── */
function checkSelfConflict() {
    var empId    = getSelectedEmployeeId();
    var startVal = document.getElementById('start_date').value;
    var endVal   = document.getElementById('end_date').value;
    var banner   = document.getElementById('self-conflict-banner');
    var detail   = document.getElementById('self-conflict-detail');

    if (!empId || !startVal || !endVal) {
        banner.style.display = 'none';
        return;
    }

    var newStart = new Date(startVal);
    var newEnd   = new Date(endVal);

    var conflicts = selfConflicts.filter(function(c) {
        if (String(c.employee_id) !== String(empId)) return false;
        var cStart = new Date(c.start_date);
        var cEnd   = new Date(c.end_date);
        return newStart <= cEnd && newEnd >= cStart;
    });

    if (conflicts.length > 0) {
        var lines = conflicts.map(function(c) {
            return '• ' + c.type_label + ' : du ' + c.start_fmt + ' au ' + c.end_fmt;
        });
        detail.innerHTML = 'Absence(s) approuvée(s) déjà enregistrée(s) :<br>' + lines.join('<br>');
        banner.style.display = 'flex';
    } else {
        banner.style.display = 'none';
    }
}

/* ─────────────────────────────────────────────────────────────
   Initialisation au chargement
   ───────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function() {
    var departmentSelect = document.getElementById('department_filter');
    var employeeSelect   = document.getElementById('employee_select');

    /* Filtre département */
    function filterByDepartment() {
        if (!departmentSelect) return;
        var dep      = departmentSelect.value;
        var filtered = dep
            ? employees.filter(function(e) { return e.department === dep; })
            : employees;
        renderEmployeeOptions(filtered);
    }

    if (departmentSelect) {
        departmentSelect.addEventListener('change', filterByDepartment);
        filterByDepartment(); // état initial — restaure aussi old('employee_id')
    }

    /* Mise à jour du remplacement quand on change d'employé */
    if (employeeSelect) {
        employeeSelect.addEventListener('change', function() {
            renderReplacementOptions(this.value);
            checkSelfConflict();
        });
    }

    /* Détection conflit propre à la saisie des dates */
    var startInput = document.getElementById('start_date');
    var endInput   = document.getElementById('end_date');
    if (startInput) startInput.addEventListener('change', checkSelfConflict);
    if (endInput)   endInput.addEventListener('change',   checkSelfConflict);

    /* Initialiser l'affichage du champ "Autre" au cas où old() le restaure */
    var typeSelect = document.getElementById('type_select');
    if (typeSelect) toggleAutreType(typeSelect);

    /* Pour les cas où employee_id est fixe (mode employé connecté) */
    renderReplacementOptions(getSelectedEmployeeId());
    checkSelfConflict();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Medstaff-second-main\resources\views/absences/create.blade.php ENDPATH**/ ?>