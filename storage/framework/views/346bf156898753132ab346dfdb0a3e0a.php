<?php $__env->startSection('title', 'Planning Mensuel'); ?>
<?php $__env->startSection('page-title', 'Planning Mensuel'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Planning Mensuel</h1>
        <p><?php echo e(\Carbon\Carbon::create($year, $month)->locale('fr')->monthName); ?> <?php echo e($year); ?></p>
    </div>
    <div class="page-header-right" style="display:flex;gap:8px">
        <a href="<?php echo e(route('planning.weekly')); ?>" class="btn btn-outline">
            Vue Hebdomadaire
        </a>
        <a href="<?php echo e(route('planning.monthly.pdf', request()->query())); ?>" class="btn btn-outline" target="_blank">Exporter PDF</a>
        <button type="button" class="btn btn-primary" onclick="openPlanningModal()">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Creer un planning
        </button>
    </div>
</div>

<!-- Create Planning Modal -->
<div id="planningModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5)">
    <div class="modal-content" style="background-color:white;margin:5% auto;padding:24px;border-radius:12px;width:90%;max-width:500px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2 style="margin:0;font-size:1.25rem">Creer un planning</h2>
            <button type="button" onclick="closePlanningModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted)">x</button>
        </div>

        <form method="POST" action="<?php echo e(route('planning.store')); ?>">
            <?php echo csrf_field(); ?>

            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Employe</label>
                <select name="employee_id" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                    <option value="">Selectionner un employe</option>
                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($emp->id); ?>"><?php echo e($emp->full_name); ?> - <?php echo e($emp->department); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Date</label>
                <input type="date" name="date" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
            </div>

            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Type de shift</label>
                <select name="shift_type" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                    <option value="matin">Matin</option>
                    <option value="apres_midi">Apres-midi</option>
                    <option value="journee">Journee complete</option>
                    <option value="nuit">Nuit</option>
                    <option value="garde">Garde</option>
                </select>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de debut</label>
                    <input type="time" name="shift_start" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                </div>
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de fin</label>
                    <input type="time" name="shift_end" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
                </div>
            </div>

            <div style="margin-bottom:20px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Notes (optionnel)</label>
                <textarea name="notes" rows="2" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;resize:vertical"></textarea>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end">
                <button type="button" onclick="closePlanningModal()" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPlanningModal() {
    document.getElementById('planningModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closePlanningModal() {
    document.getElementById('planningModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

window.onclick = function(event) {
    var modal = document.getElementById('planningModal');
    if (event.target == modal) {
        closePlanningModal();
    }
}
</script>

<!-- Filters Bar -->
<div class="filters-bar" style="margin-bottom: 20px;">
    <form method="GET" action="<?php echo e(route('planning.monthly')); ?>" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
        <div style="display:flex;align-items:center;gap:8px">
            <a href="<?php echo e(route('planning.monthly', ['month' => $month - 1, 'year' => $month == 1 ? $year - 1 : $year])); ?>" class="btn btn-sm btn-outline">< Mois precedent</a>
            <select name="month" onchange="this.form.submit()" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem;min-width:120px;">
                <?php for($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo e($m); ?>" <?php echo e($month == $m ? 'selected' : ''); ?>><?php echo e(\Carbon\Carbon::create(null, $m)->locale('fr')->monthName); ?></option>
                <?php endfor; ?>
            </select>
            <select name="year" onchange="this.form.submit()" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem;min-width:90px;">
                <?php for($y = now()->year - 1; $y <= now()->year + 1; $y++): ?>
                    <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                <?php endfor; ?>
            </select>
            <a href="<?php echo e(route('planning.monthly', ['month' => $month + 1, 'year' => $month == 12 ? $year + 1 : $year])); ?>" class="btn btn-sm btn-outline">Mois suivant ></a>
        </div>

        <div style="display:flex;gap:8px;margin-left:auto">
            <input type="text" name="search" value="<?php echo e($search ?? ''); ?>" placeholder="Rechercher..." style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem;min-width:150px;">
            <select name="department" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem;min-width:140px;">
                <option value="">Tous les services</option>
                <?php if(isset($departments)): ?>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($dept); ?>" <?php echo e(($department ?? '') == $dept ? 'selected' : ''); ?>><?php echo e($dept); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
            <?php if(isset($search) && $search || isset($department) && $department): ?>
                <a href="<?php echo e(route('planning.monthly', ['month' => $month, 'year' => $year])); ?>" class="btn btn-outline btn-sm">Reinit.</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Monthly Planning Table -->
<div class="card">
    <div class="card-body" style="padding:0">
        <div style="overflow-x:auto; min-width: 100%;">
        <table style="width:100%;min-width:100%;border-collapse:collapse;font-size:0.75rem">
            <thead>
                <tr style="background:var(--surface-2)">
                    <th style="padding:14px 10px;text-align:left;min-width:150px;position:sticky;left:0;background:var(--surface-2);z-index:10;border-bottom:2px solid var(--border);">
                        Collaborateur
                    </th>
                    <?php for($i = 1; $i <= $endOfMonth->day; $i++): ?>
                    <?php
                        $dayDate = \Carbon\Carbon::create($year, $month, $i);
                        $isWeekend = in_array($dayDate->dayOfWeek, [\Carbon\Carbon::SUNDAY, \Carbon\Carbon::SATURDAY]);
                    ?>
                    <th style="padding:8px 2px;text-align:center;min-width:35px;width:35px;<?php echo e($isWeekend ? 'background:#f1f5f9;color:#94a3b8' : ''); ?>">
                        <div style="font-weight:600;font-size:0.5rem;color:var(--primary);text-transform:uppercase;"><?php echo e(substr($dayDate->locale('fr')->dayName, 0, 2)); ?></div>
                        <div style="font-size:0.7rem;font-weight:700"><?php echo e($i); ?></div>
                    </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $empPlannings = isset($plannings) ? ($plannings->get($emp->id, collect()) ?? collect()) : collect();
                ?>
                <tr style="border-bottom:1px solid var(--border)">
                    <!-- Employee Info -->
                    <td style="padding:8px;position:sticky;left:0;background:white;z-index:5;box-shadow:2px 0 4px rgba(0,0,0,0.05);min-width:150px">
                        <div style="display:flex;align-items:center;gap:6px">
                            <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg, var(--primary), #1a8fa5);color:white;font-weight:600;font-size:0.55rem;display:flex;align-items:center;justify-content:center">
                                <?php echo e(strtoupper(substr($emp->first_name ?? '', 0, 1))); ?><?php echo e(strtoupper(substr($emp->last_name ?? '', 0, 1))); ?>

                            </div>
                            <div>
                                <div style="font-weight:600;font-size:0.75rem"><?php echo e($emp->full_name ?? 'N/A'); ?></div>
                                <div style="font-size:0.6rem;color:var(--text-muted)"><?php echo e($emp->department ?? ''); ?></div>
                            </div>
                        </div>
                    </td>

                    <!-- Day Cells -->
                    <?php for($i = 1; $i <= $endOfMonth->day; $i++): ?>
                    <?php
                        $dayDate = \Carbon\Carbon::create($year, $month, $i);
                        $dayDateStr = $dayDate->format('Y-m-d');
                        $isWeekend = in_array($dayDate->dayOfWeek, [\Carbon\Carbon::SUNDAY, \Carbon\Carbon::SATURDAY]);

                        $dayPlanning = $empPlannings->filter(function($planning) use ($dayDateStr) {
                            return $planning->date && $planning->date->format('Y-m-d') === $dayDateStr;
                        })->first();
                    ?>
                    <td style="padding:2px;text-align:center;vertical-align:middle;min-width:35px;width:35px;<?php echo e($isWeekend ? 'background:#f8fafc' : ''); ?>"
                        title="<?php echo e($dayDate->format('d/m/Y')); ?>">
                        <?php if($dayPlanning): ?>
                        <div style="display:flex;flex-direction:column;align-items:center;gap:1px">
                            <?php if(in_array($dayPlanning->shift_type ?? '', ['matin', 'journee'])): ?>
                            <div style="font-size:0.45rem;padding:1px;border-radius:2px;background:#0ea5e9;color:white;font-weight:600;width:100%">
                                <?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?>

                            </div>
                            <?php endif; ?>
                            <?php if(in_array($dayPlanning->shift_type ?? '', ['apres_midi', 'journee'])): ?>
                            <div style="font-size:0.45rem;padding:1px;border-radius:2px;background:#f59e0b;color:white;font-weight:600;width:100%">
                                <?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?>

                            </div>
                            <?php endif; ?>
                            <?php if(($dayPlanning->shift_type ?? '') === 'nuit'): ?>
                            <div style="font-size:0.4rem;padding:1px;border-radius:2px;background:#6366f1;color:white;font-weight:600;width:100%">
                                <?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?>-<?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?>

                            </div>
                            <?php endif; ?>
                            <?php if(($dayPlanning->shift_type ?? '') === 'garde'): ?>
                            <div style="font-size:0.4rem;padding:1px;border-radius:2px;background:#ef4444;color:white;font-weight:600;width:100%">
                                <?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?>-<?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?>

                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div style="width:3px;height:3px;border-radius:50%;background:transparent"></div>
                        <?php endif; ?>
                    </td>
                    <?php endfor; ?>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e($endOfMonth->day + 1); ?>" style="padding:40px;text-align:center;color:var(--text-muted)">
                        <div style="font-size:2rem;margin-bottom:8px">-</div>
                        <div>Aucun collaborateur trouve</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Legend -->
<div style="display:flex;gap:20px;margin-top:16px;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:8px">
        <div style="width:16px;height:16px;background:#0ea5e9;border-radius:3px"></div>
        <span style="font-size:0.8rem">Matin</span>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
        <div style="width:16px;height:16px;background:#f59e0b;border-radius:3px"></div>
        <span style="font-size:0.8rem">Apres-midi</span>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
        <div style="width:16px;height:16px;background:#6366f1;border-radius:3px"></div>
        <span style="font-size:0.8rem">Nuit</span>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
        <div style="width:16px;height:16px;background:#ef4444;border-radius:3px"></div>
        <span style="font-size:0.8rem">Garde</span>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/planning/monthly.blade.php ENDPATH**/ ?>