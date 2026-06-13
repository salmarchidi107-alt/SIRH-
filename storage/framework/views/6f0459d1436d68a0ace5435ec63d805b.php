<?php $__env->startSection('title', 'Planning'); ?>
<?php $__env->startSection('page-title', 'Planning'); ?>

<?php
    $isEmployee   = isset($isEmployee) && $isEmployee;
    $filterAbsence  = ($shift_type ?? '') === 'absence';
    $filterSansShift = ($shift_type ?? '') === 'sans_shift';
?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <?php if($isEmployee): ?>
            <h1>Votre Planning Personnel</h1>
            <?php if(isset($startOfWeek, $endOfWeek)): ?>
                <p>Semaine du <?php echo e($startOfWeek->format('d')); ?> au <?php echo e($endOfWeek->format('d M Y')); ?></p>
            <?php else: ?>
                <p>Semaine du ? au ?</p>
            <?php endif; ?>
        <?php else: ?>
            <h1>Planning</h1>
            <?php if(isset($startOfWeek, $endOfWeek)): ?>
                <p>Semaine du <?php echo e($startOfWeek->format('d')); ?> au <?php echo e($endOfWeek->format('d M Y')); ?></p>
            <?php else: ?>
                <p>Semaine du ? au ?</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if(!$isEmployee): ?>
    <div class="page-header-right" style="display:flex;gap:8px">
        <a href="<?php echo e(route('planning.monthly')); ?>" class="btn btn-outline">Vue Mensuelle</a>
        <a href="<?php echo e(route('planning.weekly.pdf', request()->query())); ?>" class="btn btn-outline" target="_blank">Exporter PDF</a>
        <a href="<?php echo e(route('planning.templates.index')); ?>" class="btn btn-outline">Semaines Types</a>
        <button type="button" class="btn btn-primary" onclick="openPlanningModal()">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Creer un planning
        </button>
    </div>
    <?php endif; ?>
</div>


<?php if(!$isEmployee): ?>
<div id="planningModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5)">
    <div style="background:white;margin:5% auto;padding:24px;border-radius:12px;width:90%;max-width:500px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2 style="margin:0;font-size:1.25rem">Creer un planning</h2>
            <button type="button" onclick="closePlanningModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted)">×</button>
        </div>
        <form method="POST" action="<?php echo e(route('planning.store')); ?>">
            <?php echo csrf_field(); ?>
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Employe</label>
                <select name="employee_id" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                    <option value="">Selectionner un employe</option>
                    <?php if(isset($employees)): ?>
                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($emp->id); ?>"><?php echo e($emp->full_name); ?> - <?php echo e($emp->department); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </select>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Date</label>
                <input type="date" name="date" id="createDate" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Type de shift</label>
                <select name="shift_type" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                    <option value="matin">Matin</option>
                    <option value="apres_midi">Apres-midi</option>
                    <option value="journee">Journee complete</option>
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
            <div style="margin-bottom:20px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Salle (optionnel)</label>
                <input type="text" name="room" placeholder="Salle" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end">
                <button type="button" onclick="closePlanningModal()" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>


<div id="editShiftModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5)">
    <div style="background:white;margin:5% auto;padding:24px;border-radius:12px;width:90%;max-width:480px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2 style="margin:0;font-size:1.25rem" id="editShiftTitle"><?php echo e($isEmployee ? 'Detail du shift' : 'Modifier le shift'); ?></h2>
            <button type="button" onclick="closeEditShiftModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted)">×</button>
        </div>
        <form id="editShiftForm" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Type de shift</label>
                <select name="shift_type" id="editShiftType" required <?php echo e($isEmployee ? 'disabled' : ''); ?>

                    style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:<?php echo e($isEmployee ? '#f9fafb' : 'white'); ?>">
                    <option value="matin">Matin</option>
                    <option value="apres_midi">Apres-midi</option>
                    <option value="journee">Journee complete</option>
                    <option value="garde">Garde</option>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de debut</label>
                    <input type="time" name="shift_start" id="editShiftStart" required <?php echo e($isEmployee ? 'readonly' : ''); ?>

                        style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:<?php echo e($isEmployee ? '#f9fafb' : 'white'); ?>">
                </div>
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de fin</label>
                    <input type="time" name="shift_end" id="editShiftEnd" required <?php echo e($isEmployee ? 'readonly' : ''); ?>

                        style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:<?php echo e($isEmployee ? '#f9fafb' : 'white'); ?>">
                </div>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Salle</label>
                <input type="text" name="room" id="editShiftRoom" placeholder="Salle (optionnel)" <?php echo e($isEmployee ? 'readonly' : ''); ?>

                    style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:<?php echo e($isEmployee ? '#f9fafb' : 'white'); ?>">
            </div>
            <div style="margin-bottom:20px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Notes</label>
                <textarea name="notes" id="editShiftNotes" rows="3" placeholder="Ajouter une note..." <?php echo e($isEmployee ? 'readonly' : ''); ?>

                    style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;resize:vertical;background:<?php echo e($isEmployee ? '#f9fafb' : 'white'); ?>"></textarea>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center">
                <?php if(!$isEmployee): ?>
                <button type="button" id="deleteShiftBtn" onclick="deleteShift()"
                    style="padding:8px 16px;border:1px solid #ef4444;border-radius:8px;background:white;color:#ef4444;font-size:0.875rem;cursor:pointer;font-weight:600">
                    Supprimer
                </button>
                <?php else: ?>
                <div></div>
                <?php endif; ?>
                <div style="display:flex;gap:10px">
                    <button type="button" onclick="closeEditShiftModal()" class="btn btn-outline"><?php echo e($isEmployee ? 'Fermer' : 'Annuler'); ?></button>
                    <?php if(!$isEmployee): ?>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>


<?php if(!$isEmployee): ?>
<div id="quickAddModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5)">
    <div style="background:white;margin:5% auto;padding:24px;border-radius:12px;width:90%;max-width:460px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2 style="margin:0;font-size:1.25rem">Ajouter un shift</h2>
            <button type="button" onclick="closeQuickAddModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted)">×</button>
        </div>
        <form method="POST" action="<?php echo e(route('planning.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="employee_id" id="qaEmployeeId">
            <input type="hidden" name="date" id="qaDate">
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Type de shift</label>
                <select name="shift_type" id="qaShiftType" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                    <option value="matin">Matin</option>
                    <option value="apres_midi">Apres-midi</option>
                    <option value="journee">Journee complete</option>
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
            <div style="margin-bottom:20px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Salle (optionnel)</label>
                <input type="text" name="room" placeholder="Salle" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end">
                <button type="button" onclick="closeQuickAddModal()" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>


<?php if(!$isEmployee): ?>
<?php if(isset($week, $year)): ?>
<div class="filters-bar" style="margin-bottom:20px">
    <form method="GET" action="<?php echo e(route('planning.weekly')); ?>" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
        <div style="display:flex;align-items:center;gap:8px">
            <a href="<?php echo e(route('planning.weekly', ['week' => $week - 1, 'year' => $year, 'search' => $search ?? '', 'department' => $department ?? '', 'shift_type' => $shift_type ?? ''])); ?>"
               class="btn btn-sm btn-outline">← Semaine precedente</a>
            <select name="week" onchange="this.form.submit()" style="min-width:120px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                <?php for($w = 1; $w <= 52; $w++): ?>
                    <option value="<?php echo e($w); ?>" <?php echo e(($week ?? 0) == $w ? 'selected' : ''); ?>>Semaine <?php echo e($w); ?></option>
                <?php endfor; ?>
            </select>
            <select name="year" onchange="this.form.submit()" style="min-width:100px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                <?php for($y = now()->year - 1; $y <= now()->year + 1; $y++): ?>
                    <option value="<?php echo e($y); ?>" <?php echo e(($year ?? 0) == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                <?php endfor; ?>
            </select>
            <a href="<?php echo e(route('planning.weekly', ['week' => $week + 1, 'year' => $year, 'search' => $search ?? '', 'department' => $department ?? '', 'shift_type' => $shift_type ?? ''])); ?>"
               class="btn btn-sm btn-outline">Semaine suivante →</a>
        </div>
        <div style="display:flex;gap:8px;margin-left:auto;flex-wrap:wrap;align-items:center">
            <input type="text" name="search" value="<?php echo e($search ?? ''); ?>"
                   placeholder="Rechercher par nom..."
                   style="min-width:180px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
            <select name="department" style="min-width:150px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                <option value="">Departements</option>
                <?php $__currentLoopData = $departments ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($dept); ?>" <?php echo e(($department ?? '') == $dept ? 'selected' : ''); ?>><?php echo e($dept); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="shift_type" style="min-width:160px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                <option value="">Tous les shifts</option>
                <option value="matin"      <?php echo e(($shift_type ?? '') === 'matin'      ? 'selected' : ''); ?>>Matin</option>
                <option value="apres_midi" <?php echo e(($shift_type ?? '') === 'apres_midi' ? 'selected' : ''); ?>>Après-midi</option>
                <option value="journee"    <?php echo e(($shift_type ?? '') === 'journee'    ? 'selected' : ''); ?>>Journée complète</option>
                <option value="garde"      <?php echo e(($shift_type ?? '') === 'garde'      ? 'selected' : ''); ?>>Garde</option>
                <option value="absence"    <?php echo e(($shift_type ?? '') === 'absence'    ? 'selected' : ''); ?>

                    style="color:#ef4444;font-weight:700">
                    Absences uniquement
                </option>
                <option value="sans_shift" <?php echo e(($shift_type ?? '') === 'sans_shift' ? 'selected' : ''); ?>

                    style="color:#6b7280;font-weight:700">
                    Sans shift
                </option>
            </select>
            <select name="room_id" style="min-width:120px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                <option value="">Salles</option>
                <?php $__currentLoopData = $rooms ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($room->id); ?>" <?php echo e(request('room_id') == $room->id ? 'selected' : ''); ?>>
                        <?php echo e($room->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="btn btn-primary">Rechercher</button>
            <?php if(($search ?? '') || ($department ?? '') || ($shift_type ?? '')): ?>
                <a href="<?php echo e(route('planning.weekly', ['week' => $week, 'year' => $year])); ?>" class="btn btn-outline">Reinitialiser</a>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php endif; ?>

<?php else: ?>
<?php if(isset($week, $year)): ?>
<div style="margin-bottom:20px;display:flex;align-items:center;gap:8px">
    <a href="<?php echo e(route('planning.weekly', ['week' => $week - 1, 'year' => $year])); ?>" class="btn btn-sm btn-outline">← Semaine precedente</a>
    <span style="padding:8px 16px;background:var(--surface-2);border-radius:8px;font-size:0.85rem;font-weight:600">Semaine <?php echo e($week); ?> — <?php echo e($year); ?></span>
    <a href="<?php echo e(route('planning.weekly', ['week' => $week + 1, 'year' => $year])); ?>" class="btn btn-sm btn-outline">Semaine suivante →</a>
</div>
<?php endif; ?>
<?php endif; ?>


<div class="card" style="overflow-x:auto">
    <div class="card-body" style="padding:0">
        <table style="width:100%;border-collapse:collapse;font-size:0.85rem">
            <thead>
                <tr style="background:var(--surface-2)">
                    <th style="padding:16px 12px;text-align:left;min-width:200px;position:sticky;left:0;background:var(--surface-2);z-index:10">Employe</th>
                    <th style="padding:16px 12px;text-align:left;min-width:120px;">Salle</th>
                    <?php $__currentLoopData = $weekDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th style="padding:12px 8px;text-align:center;min-width:140px;white-space:nowrap">
                        <div style="font-weight:600;color:var(--primary)"><?php echo e(ucfirst($day['day_name'])); ?></div>
                        <div style="font-size:0.75rem;color:var(--text-muted)"><?php echo e($day['day_number']); ?> <?php echo e($day['date']->locale('fr')->monthName); ?></div>
                    </th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    $shiftFilter = ($shift_type ?? '');

                    // Filtrer les employés selon le filtre actif
                    $displayEmployees = $employees;

                    if ($filterAbsence) {
                        // Absences uniquement : garder seulement ceux avec au moins 1 absence cette semaine
                        $displayEmployees = $employees->filter(function($emp) use ($weekDays) {
                            foreach ($weekDays as $day) {
                                if ($emp->hasApprovedAbsenceOn($day['date'])) return true;
                            }
                            return false;
                        });
                    } elseif ($filterSansShift) {
                        // Sans shift : garder seulement ceux sans aucun shift toute la semaine
                        $displayEmployees = $employees->filter(function($emp) use ($weekDays, $plannings) {
                            $empPlannings = $plannings->get($emp->id, collect());
                            foreach ($weekDays as $day) {
                                $hasShift = $empPlannings->filter(fn($p) =>
                                    \Carbon\Carbon::parse($p->date)->format('Y-m-d') === $day['date']->format('Y-m-d')
                                )->isNotEmpty();
                                if ($hasShift) return false;
                            }
                            return true;
                        });
                    } elseif ($shiftFilter) {
                        // Filtre shift normal : garder les employés qui ont au moins 1 shift de ce type cette semaine
                        $displayEmployees = $employees->filter(function($emp) use ($weekDays, $plannings, $shiftFilter) {
                            $empPlannings = $plannings->get($emp->id, collect());
                            foreach ($weekDays as $day) {
                                $hasMatchingShift = $empPlannings->filter(fn($p) =>
                                    \Carbon\Carbon::parse($p->date)->format('Y-m-d') === $day['date']->format('Y-m-d')
                                    && $p->shift_type === $shiftFilter
                                )->isNotEmpty();
                                if ($hasMatchingShift) return true;
                            }
                            return false;
                        });
                    }
                ?>

                <?php $__empty_1 = true; $__currentLoopData = $displayEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $empPlannings = $plannings->get($emp->id, collect());
                    $roomCounts     = $empPlannings->whereNotNull('room')->groupBy('room')->map->count();
                    $mostCommonRoom = $roomCounts->isNotEmpty() ? $roomCounts->sortDesc()->keys()->first() : null;
                ?>
                <tr style="border-bottom:1px solid var(--border)" data-employee-id="<?php echo e($emp->id); ?>">

                    <td style="padding:12px;position:sticky;left:0;background:white;z-index:5;box-shadow:2px 0 4px rgba(0,0,0,0.05)">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg, var(--primary), #1a8fa5);color:white;font-weight:600;font-size:0.75rem;display:flex;align-items:center;justify-content:center">
                                <?php echo e(strtoupper(substr($emp->first_name, 0, 1))); ?><?php echo e(strtoupper(substr($emp->last_name, 0, 1))); ?>

                            </div>
                            <div>
                                <span style="font-weight:600;color:var(--text-primary)"><?php echo e($emp->full_name); ?></span>
                                <div style="font-size:0.7rem;color:var(--text-muted)"><?php echo e($emp->department); ?></div>
                            </div>
                        </div>
                    </td>

                    <td style="padding:12px;">
                        <?php if(!$isEmployee): ?>
                            <select onchange="updateRoom(this)" data-employee="<?php echo e($emp->id); ?>"
                                data-start="<?php echo e($startOfWeek->format('Y-m-d')); ?>"
                                data-end="<?php echo e($endOfWeek->format('Y-m-d')); ?>"
                                style="padding:6px;border-radius:8px;border:1px solid var(--border);font-size:0.85rem;">
                                <option value="">Choisir salle</option>
                                <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($room->id); ?>" <?php echo e($room->name == $mostCommonRoom ? 'selected' : ''); ?>><?php echo e($room->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        <?php else: ?>
                            <span style="font-size:0.85rem;color:var(--text-muted)"><?php echo e($mostCommonRoom ?? '—'); ?></span>
                        <?php endif; ?>
                    </td>

                    <?php $__currentLoopData = $weekDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $dayPlannings = $empPlannings->filter(function($p) use ($day) {
                            return \Carbon\Carbon::parse($p->date)->format('Y-m-d') === $day['date']->format('Y-m-d');
                        })->values();

                        $isAbsent = $emp->hasApprovedAbsenceOn($day['date']);

                        // Plannings filtrés par type de shift (pour l'affichage dans la cellule)
                        $filteredDayPlannings = (!$isEmployee && $shiftFilter && !in_array($shiftFilter, ['absence', 'sans_shift']))
                            ? $dayPlannings->filter(fn($p) => $p->shift_type === $shiftFilter)->values()
                            : $dayPlannings;
                    ?>
                    <td style="padding:6px 8px;text-align:center;vertical-align:top;min-height:60px"
                        data-date="<?php echo e($day['date']->format('Y-m-d')); ?>"
                        data-employee="<?php echo e($emp->id); ?>"
                        <?php if(!$isAbsent && !$isEmployee): ?>
                            ondragover="allowDrop(event)"
                            ondrop="drop(event, '<?php echo e($day['date']->format('Y-m-d')); ?>', <?php echo e($emp->id); ?>)"
                        <?php endif; ?>>

                        <?php if($isAbsent && ($filterAbsence || !$shiftFilter)): ?>
                            
                            <div style="background:linear-gradient(135deg,#ef4444,#f87171);color:white;padding:8px 12px;border-radius:8px;font-size:0.75rem;font-weight:700;min-height:48px;display:flex;align-items:center;justify-content:center;">
                                ABS
                            </div>

                        <?php elseif($isAbsent || $filterAbsence || $filterSansShift): ?>
                            
                            <div style="min-height:48px"></div>

                        <?php elseif($filteredDayPlannings->isNotEmpty()): ?>
                            
                            <div style="display:flex;flex-direction:column;gap:4px">
                                <?php $__currentLoopData = $filteredDayPlannings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayPlanning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div
                                    <?php if(!$isEmployee): ?>
                                        draggable="true"
                                        ondragstart="drag(event, <?php echo e($dayPlanning->id); ?>)"
                                    <?php endif; ?>
                                    data-planning-id="<?php echo e($dayPlanning->id); ?>"
                                    onclick="openEditShiftModal(<?php echo e($dayPlanning->id); ?>,'<?php echo e($dayPlanning->shift_type); ?>','<?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?>','<?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?>',<?php echo \Illuminate\Support\Js::from($dayPlanning->notes ?? '')->toHtml() ?>,<?php echo \Illuminate\Support\Js::from($dayPlanning->room ?? '')->toHtml() ?>)"
                                    style="cursor:pointer;transition:transform 0.15s,opacity 0.15s"
                                    onmouseover="this.style.transform='scale(1.03)'"
                                    onmouseout="this.style.transform='scale(1)'">

                                    <?php if($dayPlanning->shift_type === 'journee'): ?>
                                    <div style="background:linear-gradient(135deg,#10b981,#34d399);color:white;padding:6px 8px;border-radius:6px;font-size:0.72rem;position:relative">
                                        <div style="font-weight:700">Journée</div>
                                        <div><?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?> – <?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?></div>
                                        <?php if($dayPlanning->notes): ?><div style="position:absolute;top:4px;right:5px;font-size:0.6rem" title="<?php echo e($dayPlanning->notes); ?>"></div><?php endif; ?>
                                    </div>
                                    <?php elseif($dayPlanning->shift_type === 'matin'): ?>
                                    <div style="background:linear-gradient(135deg,#0ea5e9,#38bdf8);color:white;padding:6px 8px;border-radius:6px;font-size:0.72rem;position:relative">
                                        <div style="font-weight:700">Matin</div>
                                        <div><?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?> – <?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?></div>
                                        <?php if($dayPlanning->notes): ?><div style="position:absolute;top:4px;right:5px;font-size:0.6rem" title="<?php echo e($dayPlanning->notes); ?>"></div><?php endif; ?>
                                    </div>
                                    <?php elseif($dayPlanning->shift_type === 'apres_midi'): ?>
                                    <div style="background:linear-gradient(135deg,#f59e0b,#fbbf24);color:white;padding:6px 8px;border-radius:6px;font-size:0.72rem;position:relative">
                                        <div style="font-weight:700">Apres-midi</div>
                                        <div><?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?> – <?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?></div>
                                        <?php if($dayPlanning->notes): ?><div style="position:absolute;top:4px;right:5px;font-size:0.6rem" title="<?php echo e($dayPlanning->notes); ?>">📝</div><?php endif; ?>
                                    </div>
                                    <?php elseif($dayPlanning->shift_type === 'garde'): ?>
                                    <div style="background:linear-gradient(135deg,#d766cd,#ef9be8);color:white;padding:6px 8px;border-radius:6px;font-size:0.72rem;position:relative">
                                        <div style="font-weight:700">Garde</div>
                                        <div><?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?> - <?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?></div>
                                        <?php if($dayPlanning->notes): ?><div style="position:absolute;top:4px;right:5px;font-size:0.6rem" title="<?php echo e($dayPlanning->notes); ?>">📝</div><?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                        <?php elseif(!$shiftFilter): ?>
                            
                            <?php if(!$isEmployee): ?>
                            <div onclick="openQuickAddModal('<?php echo e($day['date']->format('Y-m-d')); ?>', <?php echo e($emp->id); ?>)"
                                 style="color:var(--text-muted);font-size:0.75rem;min-height:48px;display:flex;align-items:center;justify-content:center;border:2px dashed var(--border);border-radius:6px;cursor:pointer;transition:all 0.2s"
                                 onmouseover="this.style.background='rgba(14,165,233,0.07)';this.style.borderColor='var(--primary)';this.style.color='var(--primary)'"
                                 onmouseout="this.style.background='';this.style.borderColor='var(--border)';this.style.color='var(--text-muted)'">
                                + Creer shift
                            </div>
                            <?php endif; ?>

                        <?php else: ?>
                            
                            <div style="min-height:48px"></div>
                        <?php endif; ?>

                    </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9" style="padding:40px;text-align:center;color:var(--text-muted)">
                        <?php if($filterAbsence): ?>
                        <div style="font-size:2rem;margin-bottom:8px"></div>
                        <div>Aucune absence cette semaine</div>
                        <?php elseif($filterSansShift): ?>
                        <div style="font-size:2rem;margin-bottom:8px"></div>
                        <div>Tous les employes ont au moins un shift cette semaine</div>
                        <?php else: ?>
                        <div style="font-size:2rem;margin-bottom:8px"></div>
                        <div>Aucun employe trouve</div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const IS_EMPLOYEE = <?php echo e($isEmployee ? 'true' : 'false'); ?>;
let draggedPlanningId = null;

function drag(event, planningId) {
    if (IS_EMPLOYEE) return;
    draggedPlanningId = planningId;
    event.dataTransfer.setData("text/plain", planningId);
    event.target.closest('[data-planning-id]').style.opacity = '0.5';
}
function allowDrop(event) { if (IS_EMPLOYEE) return; event.preventDefault(); event.target.closest('td').style.background = 'rgba(14,165,233,0.08)'; }
function drop(event, newDate, newEmployeeId) {
    if (IS_EMPLOYEE) return;
    event.preventDefault();
    event.target.closest('td').style.background = '';
    if (!draggedPlanningId) return;
    fetch('<?php echo e(route("planning.dragDrop")); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        body: JSON.stringify({ planning_id: draggedPlanningId, new_date: newDate, new_employee_id: newEmployeeId })
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); else alert('Erreur: ' + (data.error || 'Inconnue')); }).catch(() => alert('Erreur réseau'));
    draggedPlanningId = null;
}
document.addEventListener('dragend', e => { const el = e.target.closest('[data-planning-id]'); if (el) el.style.opacity = '1'; });

function openPlanningModal() { if (IS_EMPLOYEE) return; document.getElementById('planningModal').style.display = 'block'; document.body.style.overflow = 'hidden'; }
function closePlanningModal() { const m = document.getElementById('planningModal'); if (m) { m.style.display = 'none'; document.body.style.overflow = 'auto'; } }

let currentEditPlanningId = null;
function openEditShiftModal(id, shiftType, shiftStart, shiftEnd, notes, room) {
    currentEditPlanningId = id;
    document.getElementById('editShiftForm').action = '/planning/' + id;
    document.getElementById('editShiftType').value  = shiftType;
    document.getElementById('editShiftStart').value = shiftStart;
    document.getElementById('editShiftEnd').value   = shiftEnd;
    document.getElementById('editShiftNotes').value = notes || '';
    document.getElementById('editShiftRoom').value  = room || '';
    document.getElementById('editShiftModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function closeEditShiftModal() { document.getElementById('editShiftModal').style.display = 'none'; document.body.style.overflow = 'auto'; }
function deleteShift() {
    if (IS_EMPLOYEE) return;
    if (!confirm('Supprimer ce shift ?')) return;
    fetch('/planning/' + currentEditPlanningId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' }
    }).then(r => { if (r.ok) location.reload(); else r.json().then(d => alert('Erreur: ' + (d.message || d.error || 'Inconnue'))).catch(() => alert('Erreur suppression')); }).catch(e => alert('Erreur réseau: ' + e.message));
}

function openQuickAddModal(date, employeeId) {
    if (IS_EMPLOYEE) return;
    document.getElementById('qaDate').value = date;
    document.getElementById('qaEmployeeId').value = employeeId;
    document.getElementById('quickAddModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function closeQuickAddModal() { const m = document.getElementById('quickAddModal'); if (m) { m.style.display = 'none'; document.body.style.overflow = 'auto'; } }

function updateRoom(select) {
    if (IS_EMPLOYEE) return;
    fetch('/planning/update-room', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ employee_id: select.dataset.employee, room_id: select.value, start: select.dataset.start, end: select.dataset.end })
    }).then(res => res.json()).then(() => console.log('Salle mise a jour')).catch(err => console.error(err));
}

window.onclick = function(e) {
    ['planningModal','editShiftModal','quickAddModal'].forEach(id => {
        const m = document.getElementById(id);
        if (m && e.target === m) { m.style.display = 'none'; document.body.style.overflow = 'auto'; }
    });
};
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-v2\resources\views/planning/weekly.blade.php ENDPATH**/ ?>