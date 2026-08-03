<?php $__env->startSection('title', 'Planning Mensuel'); ?>
<?php $__env->startSection('page-title', 'Planning Mensuel'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Planning Mensuel</h1>
        <p><?php echo e(\Carbon\Carbon::create($year, $month)->locale('fr')->monthName); ?> <?php echo e($year); ?></p>
    </div>
    <div class="page-header-right" style="display:flex;gap:8px">
        <a href="<?php echo e(route('planning.weekly')); ?>" class="btn btn-outline">Vue Hebdomadaire</a>
        <a href="<?php echo e(route('planning.monthly.pdf', request()->query())); ?>" class="btn btn-outline" target="_blank">Exporter PDF</a>
        <button type="button" class="btn btn-primary" onclick="openPlanningModal()">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Créer un planning
        </button>
    </div>
</div>


<div id="planningModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5)">
    <div style="background:white;margin:5% auto;padding:24px;border-radius:12px;width:90%;max-width:500px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2 style="margin:0;font-size:1.25rem">Créer un planning</h2>
            <button type="button" onclick="closePlanningModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted)">×</button>
        </div>
        <form method="POST" action="<?php echo e(route('planning.store')); ?>">
            <?php echo csrf_field(); ?>
            <div style="margin-bottom:16px">
                <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Employé</label>
                <select name="employee_id" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem;background:white">
                    <option value="">Sélectionner un employé</option>
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
                    <option value="apres_midi">Après-midi</option>
                    <option value="journee">Journée complète</option>
                    <option value="garde">Garde</option>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:0.875rem">Heure de début</label>
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


<div class="filters-bar" style="margin-bottom:20px">
    <form method="GET" action="<?php echo e(route('planning.monthly')); ?>" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">

        
        <div style="display:flex;align-items:center;gap:8px">
            <a href="<?php echo e(route('planning.monthly', ['month' => $month - 1, 'year' => $month == 1 ? $year - 1 : $year, 'search' => $search ?? '', 'department' => $department ?? '', 'shift_type' => $shift_type ?? ''])); ?>"
               class="btn btn-sm btn-outline">← Mois précédent</a>

            <select name="month" onchange="this.form.submit()" style="min-width:120px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                <?php for($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo e($m); ?>" <?php echo e($month == $m ? 'selected' : ''); ?>>
                        <?php echo e(\Carbon\Carbon::create(null, $m)->locale('fr')->monthName); ?>

                    </option>
                <?php endfor; ?>
            </select>

            <select name="year" onchange="this.form.submit()" style="min-width:90px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                <?php for($y = now()->year - 1; $y <= now()->year + 1; $y++): ?>
                    <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                <?php endfor; ?>
            </select>

            <a href="<?php echo e(route('planning.monthly', ['month' => $month + 1, 'year' => $month == 12 ? $year + 1 : $year, 'search' => $search ?? '', 'department' => $department ?? '', 'shift_type' => $shift_type ?? ''])); ?>"
               class="btn btn-sm btn-outline">Mois suivant →</a>
        </div>

        
        <div style="display:flex;gap:8px;margin-left:auto;flex-wrap:wrap;align-items:center">
            <input type="text" name="search" value="<?php echo e($search ?? ''); ?>"
                   placeholder="Rechercher par nom..."
                   style="min-width:180px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">

            <select name="department" style="min-width:150px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                <option value="">Départements</option>
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
            </select>

            <select name="room_id" style="min-width:120px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem">
                <option value="">Salles</option>
                <?php $__currentLoopData = $rooms ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($room->id); ?>" <?php echo e((request('room_id') ?? '') == $room->id ? 'selected' : ''); ?>>
                        <?php echo e($room->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <button type="submit" class="btn btn-primary">Rechercher</button>

            <?php if(($search ?? '') || ($department ?? '') || ($shift_type ?? '') || (request('room_id') ?? '')): ?>
                <a href="<?php echo e(route('planning.monthly', ['month' => $month, 'year' => $year])); ?>" class="btn btn-outline">Réinitialiser</a>
            <?php endif; ?>
        </div>
    </form>
</div>


<div class="card">
    <div class="card-body" style="padding:0">
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:0.75rem">
                <thead>
                    <tr style="background:var(--surface-2)">
                        <th style="padding:14px 10px;text-align:left;min-width:150px;position:sticky;left:0;background:var(--surface-2);z-index:10;border-bottom:2px solid var(--border)">
                            Collaborateur
                        </th>
                        <th style="padding:14px 8px;text-align:center;min-width:80px;position:sticky;left:150px;background:var(--surface-2);z-index:10;border-bottom:2px solid var(--border);border-left:1px solid var(--border)">
                            Salle
                        </th>
                        <?php for($i = 1; $i <= $endOfMonth->day; $i++): ?>
                        <?php
                            $dayDate   = \Carbon\Carbon::create($year, $month, $i);
                            $isWeekend = in_array($dayDate->dayOfWeek, [\Carbon\Carbon::SUNDAY, \Carbon\Carbon::SATURDAY]);
                            $isToday   = $dayDate->isToday();
                        ?>
                        <th style="padding:8px 2px;text-align:center;min-width:42px;width:42px;border-bottom:2px solid var(--border);
                            <?php echo e($isWeekend ? 'background:#f1f5f9;color:#94a3b8' : ''); ?>

                            <?php echo e($isToday   ? 'background:#eff6ff;border-bottom:2px solid #3b82f6' : ''); ?>">
                            <div style="font-weight:600;font-size:0.58rem;color:<?php echo e($isToday ? '#3b82f6' : 'var(--primary)'); ?>;text-transform:uppercase">
                                <?php echo e(substr($dayDate->locale('fr')->dayName, 0, 2)); ?>

                            </div>
                            <div style="font-size:0.72rem;font-weight:700;color:<?php echo e($isToday ? '#3b82f6' : 'inherit'); ?>"><?php echo e($i); ?></div>
                        </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $empPlannings = isset($plannings) ? ($plannings->get($emp->id, collect()) ?? collect()) : collect();

                        // Filtre shift_type côté affichage (comme dans weekly)
                        $shiftFilter = $shift_type ?? '';
                        if ($shiftFilter) {
                            $empPlannings = $empPlannings->filter(fn($p) => $p->shift_type === $shiftFilter);
                        }

                        $roomCounts     = $empPlannings->whereNotNull('room')->groupBy('room')->map->count();
                        $mostCommonRoom = $roomCounts->isNotEmpty() ? $roomCounts->sortDesc()->keys()->first() : null;
                    ?>
                    <tr style="border-bottom:1px solid var(--border)">

                        
                        <td style="padding:8px;position:sticky;left:0;background:white;z-index:5;box-shadow:2px 0 4px rgba(0,0,0,0.05);min-width:150px">
                            <div style="display:flex;align-items:center;gap:6px">
                                <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#1a8fa5);color:white;font-weight:600;font-size:0.55rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <?php echo e(strtoupper(substr($emp->first_name ?? '', 0, 1))); ?><?php echo e(strtoupper(substr($emp->last_name ?? '', 0, 1))); ?>

                                </div>
                                <div>
                                    <span style="font-weight:600;font-size:0.75rem;color:var(--text-primary)">
                                        <?php echo e($emp->full_name ?? 'N/A'); ?>

                                    </span>
                                    <div style="font-size:0.6rem;color:var(--text-muted)"><?php echo e($emp->department ?? ''); ?></div>
                                </div>
                            </div>
                        </td>

                        
                        <td style="padding:6px 4px;text-align:center;position:sticky;left:150px;background:white;z-index:4;border-left:1px solid var(--border);min-width:80px">
                            <select
                                onchange="updateRoom(this)"
                                data-employee="<?php echo e($emp->id); ?>"
                                data-start="<?php echo e(\Carbon\Carbon::create($year, $month, 1)->format('Y-m-d')); ?>"
                                data-end="<?php echo e($endOfMonth->format('Y-m-d')); ?>"
                                style="padding:4px 6px;border-radius:6px;border:1px solid var(--border);font-size:0.7rem;width:100%;background:white;cursor:pointer">
                                <option value="">— Salle —</option>
                                <?php $__currentLoopData = $rooms ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($room->id); ?>" <?php echo e($room->name == $mostCommonRoom ? 'selected' : ''); ?>>
                                        <?php echo e($room->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php if($mostCommonRoom): ?>
                            <div style="font-size:0.6rem;color:var(--primary);margin-top:2px;font-weight:600"><?php echo e($mostCommonRoom); ?></div>
                            <?php endif; ?>
                        </td>

                        
                        <?php for($i = 1; $i <= $endOfMonth->day; $i++): ?>
                        <?php
                            $dayDate    = \Carbon\Carbon::create($year, $month, $i);
                            $dayDateStr = $dayDate->format('Y-m-d');
                            $isWeekend  = in_array($dayDate->dayOfWeek, [\Carbon\Carbon::SUNDAY, \Carbon\Carbon::SATURDAY]);
                            $isToday    = $dayDate->isToday();
                            $isAbsent   = $emp->hasApprovedAbsenceOn($dayDate);

                            $absenceType = '';
                            if ($isAbsent) {
                                $absence = $emp->absences
                                    ->where('status', 'approved')
                                    ->filter(fn($a) => $a->start_date <= $dayDate && $a->end_date >= $dayDate)
                                    ->first();
                                $absenceType = $absence?->type ?? 'Absence';
                            }

                            $dayPlannings = $empPlannings->filter(function($p) use ($dayDateStr) {
                                return $p->date && \Carbon\Carbon::parse($p->date)->format('Y-m-d') === $dayDateStr;
                            })->values();
                        ?>
                        <td style="padding:2px;text-align:center;vertical-align:middle;min-width:42px;width:42px;
                            <?php echo e($isWeekend ? 'background:#f8fafc' : ''); ?>

                            <?php echo e($isToday   ? 'background:#eff6ff' : ''); ?>"
                            title="<?php echo e($dayDate->format('d/m/Y')); ?><?php echo e($isAbsent ? ' — Absent : '.$absenceType : ''); ?>">

                            <?php if($isAbsent): ?>
                                
                                <div style="background:linear-gradient(135deg,#ef4444,#f87171);color:white;border-radius:4px;font-size:0.52rem;font-weight:700;padding:3px 2px;min-height:24px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 3px rgba(239,68,68,0.35)"
                                     title="Absent : <?php echo e($absenceType); ?>">
                                    ABS
                                </div>

                            <?php elseif($dayPlannings->isNotEmpty()): ?>
                                <div style="display:flex;flex-direction:column;align-items:stretch;gap:2px">
                                    <?php $__currentLoopData = $dayPlannings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayPlanning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $stype = $dayPlanning->shift_type ?? ''; ?>

                                    <?php if($stype === 'matin'): ?>
                                    
                                    <div style="background:linear-gradient(135deg,#0ea5e9,#38bdf8);color:white;padding:3px 2px;border-radius:4px;font-size:0.48rem;font-weight:700;line-height:1.3;box-shadow:0 1px 3px rgba(14,165,233,0.3)"
                                         title="Matin <?php echo e(substr($dayPlanning->shift_start??'',0,5)); ?>–<?php echo e(substr($dayPlanning->shift_end??'',0,5)); ?><?php echo e($dayPlanning->room ? ' — '.$dayPlanning->room : ''); ?>">
                                        <div>Matin</div>
                                        <div style="font-weight:400;font-size:0.42rem;opacity:0.9"><?php echo e(substr($dayPlanning->shift_start??'',0,5)); ?>–<?php echo e(substr($dayPlanning->shift_end??'',0,5)); ?></div>
                                        <?php if($dayPlanning->room): ?>
                                        <div style="font-size:0.38rem;opacity:0.85;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?php echo e($dayPlanning->room); ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <?php elseif($stype === 'apres_midi'): ?>
                                    
                                    <div style="background:linear-gradient(135deg,#f59e0b,#fbbf24);color:white;padding:3px 2px;border-radius:4px;font-size:0.48rem;font-weight:700;line-height:1.3;box-shadow:0 1px 3px rgba(245,158,11,0.3)"
                                         title="Après-midi <?php echo e(substr($dayPlanning->shift_start??'',0,5)); ?>–<?php echo e(substr($dayPlanning->shift_end??'',0,5)); ?><?php echo e($dayPlanning->room ? ' — '.$dayPlanning->room : ''); ?>">
                                        <div>Après-midi</div>
                                        <div style="font-weight:400;font-size:0.42rem;opacity:0.9"><?php echo e(substr($dayPlanning->shift_start??'',0,5)); ?>–<?php echo e(substr($dayPlanning->shift_end??'',0,5)); ?></div>
                                        <?php if($dayPlanning->room): ?>
                                        <div style="font-size:0.38rem;opacity:0.85;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?php echo e($dayPlanning->room); ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <?php elseif($stype === 'journee'): ?>
                                    
                                    <div style="background:linear-gradient(135deg,#10b981,#34d399);color:white;padding:3px 2px;border-radius:4px;font-size:0.48rem;font-weight:700;line-height:1.3;box-shadow:0 1px 3px rgba(16,185,129,0.3)"
                                         title="Journée <?php echo e(substr($dayPlanning->shift_start??'',0,5)); ?>–<?php echo e(substr($dayPlanning->shift_end??'',0,5)); ?><?php echo e($dayPlanning->room ? ' — '.$dayPlanning->room : ''); ?>">
                                        <div>Journée</div>
                                        <div style="font-weight:400;font-size:0.42rem;opacity:0.9"><?php echo e(substr($dayPlanning->shift_start??'',0,5)); ?>–<?php echo e(substr($dayPlanning->shift_end??'',0,5)); ?></div>
                                        <?php if($dayPlanning->room): ?>
                                        <div style="font-size:0.38rem;opacity:0.85;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?php echo e($dayPlanning->room); ?></div>
                                        <?php endif; ?>
                                    </div>


                                    <?php elseif($stype === 'garde'): ?>
                                    
                                    <div style="background:linear-gradient(135deg,#d766cd,#ef9be8);color:white;padding:3px 2px;border-radius:4px;font-size:0.48rem;font-weight:700;line-height:1.3;box-shadow:0 1px 3px rgba(215,102,205,0.3)"
                                         title="Garde <?php echo e(substr($dayPlanning->shift_start??'',0,5)); ?>–<?php echo e(substr($dayPlanning->shift_end??'',0,5)); ?><?php echo e($dayPlanning->room ? ' — '.$dayPlanning->room : ''); ?>">
                                        <div>Garde</div>
                                        <div style="font-weight:400;font-size:0.42rem;opacity:0.9"><?php echo e(substr($dayPlanning->shift_start??'',0,5)); ?>–<?php echo e(substr($dayPlanning->shift_end??'',0,5)); ?></div>
                                        <?php if($dayPlanning->room): ?>
                                        <div style="font-size:0.38rem;opacity:0.85;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?php echo e($dayPlanning->room); ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>

                            <?php else: ?>
                                <div style="width:4px;height:4px;border-radius:50%;background:transparent;margin:auto"></div>
                            <?php endif; ?>
                        </td>
                        <?php endfor; ?>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="<?php echo e($endOfMonth->day + 2); ?>" style="padding:40px;text-align:center;color:var(--text-muted)">
                            <div style="font-size:2rem;margin-bottom:8px">—</div>
                            <div>Aucun collaborateur trouvé</div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
window.onclick = function(e) {
    const m = document.getElementById('planningModal');
    if (e.target === m) { closePlanningModal(); }
};

function updateRoom(select) {
    fetch('/planning/update-room', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            employee_id: select.dataset.employee,
            room_id:     select.value,
            start:       select.dataset.start,
            end:         select.dataset.end
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const badge = select.parentElement.querySelector('div');
            const selectedText = select.options[select.selectedIndex]?.text ?? '';
            if (badge) badge.textContent = select.value ? selectedText : '';
        }
    })
    .catch(err => console.error('updateRoom error', err));
}
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/planning/monthly.blade.php ENDPATH**/ ?>