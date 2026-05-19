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
                    <option value="nuit">Nuit</option>
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
            <a href="<?php echo e(route('planning.monthly', ['month' => $month - 1, 'year' => $month == 1 ? $year - 1 : $year])); ?>" class="btn btn-sm btn-outline">← Mois précédent</a>
            <select name="month" onchange="this.form.submit()" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem;min-width:120px">
                <?php for($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo e($m); ?>" <?php echo e($month == $m ? 'selected' : ''); ?>><?php echo e(\Carbon\Carbon::create(null, $m)->locale('fr')->monthName); ?></option>
                <?php endfor; ?>
            </select>
            <select name="year" onchange="this.form.submit()" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem;min-width:90px">
                <?php for($y = now()->year - 1; $y <= now()->year + 1; $y++): ?>
                    <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                <?php endfor; ?>
            </select>
            <a href="<?php echo e(route('planning.monthly', ['month' => $month + 1, 'year' => $month == 12 ? $year + 1 : $year])); ?>" class="btn btn-sm btn-outline">Mois suivant →</a>
        </div>
        <div style="display:flex;gap:8px;margin-left:auto">
            <input type="text" name="search" value="<?php echo e($search ?? ''); ?>" placeholder="Rechercher..." style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem;min-width:150px">
            <select name="department" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:0.8rem;min-width:140px">
                <option value="">Tous les services</option>
                <?php $__currentLoopData = $departments ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($dept); ?>" <?php echo e(($department ?? '') == $dept ? 'selected' : ''); ?>><?php echo e($dept); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
            <?php if(($search ?? '') || ($department ?? '')): ?>
                <a href="<?php echo e(route('planning.monthly', ['month' => $month, 'year' => $year])); ?>" class="btn btn-outline btn-sm">Réinit.</a>
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
                            $dayDate  = \Carbon\Carbon::create($year, $month, $i);
                            $isWeekend = in_array($dayDate->dayOfWeek, [\Carbon\Carbon::SUNDAY, \Carbon\Carbon::SATURDAY]);
                            $isToday  = $dayDate->isToday();
                        ?>
                        <th style="padding:8px 2px;text-align:center;min-width:38px;width:38px;border-bottom:2px solid var(--border);
                            <?php echo e($isWeekend ? 'background:#f1f5f9;color:#94a3b8' : ''); ?>

                            <?php echo e($isToday   ? 'background:#eff6ff;border-bottom:2px solid #3b82f6' : ''); ?>">
                            <div style="font-weight:600;font-size:0.55rem;color:<?php echo e($isToday ? '#3b82f6' : 'var(--primary)'); ?>;text-transform:uppercase">
                                <?php echo e(substr($dayDate->locale('fr')->dayName, 0, 2)); ?>

                            </div>
                            <div style="font-size:0.7rem;font-weight:700;color:<?php echo e($isToday ? '#3b82f6' : 'inherit'); ?>"><?php echo e($i); ?></div>
                        </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $empPlannings = isset($plannings) ? ($plannings->get($emp->id, collect()) ?? collect()) : collect();

                        // Salle la plus fréquente du mois
                        $roomCounts    = $empPlannings->whereNotNull('room')->groupBy('room')->map->count();
                        $mostCommonRoom = $roomCounts->isNotEmpty() ? $roomCounts->sortDesc()->keys()->first() : null;
                    ?>
                    <tr style="border-bottom:1px solid var(--border)">

                        
                        <td style="padding:8px;position:sticky;left:0;background:white;z-index:5;box-shadow:2px 0 4px rgba(0,0,0,0.05);min-width:150px">
                            <div style="display:flex;align-items:center;gap:6px">
                                <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#1a8fa5);color:white;font-weight:600;font-size:0.55rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <?php echo e(strtoupper(substr($emp->first_name ?? '', 0, 1))); ?><?php echo e(strtoupper(substr($emp->last_name ?? '', 0, 1))); ?>

                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:0.75rem"><?php echo e($emp->full_name ?? 'N/A'); ?></div>
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

                            $dayPlanning = $empPlannings->filter(function($p) use ($dayDateStr) {
                                return $p->date && $p->date->format('Y-m-d') === $dayDateStr;
                            })->first();

                            // Vérifier absence approuvée
                            $isAbsent = $emp->hasApprovedAbsenceOn($dayDate);

                            // Récupérer le type d'absence pour le tooltip
                            $absenceType = '';
                            if ($isAbsent) {
                                $absence = $emp->absences
                                    ->where('status', 'approved')
                                    ->filter(fn($a) => $a->start_date <= $dayDate && $a->end_date >= $dayDate)
                                    ->first();
                                $absenceType = $absence?->type ?? 'Absence';
                            }
                        ?>
                        <td style="padding:2px;text-align:center;vertical-align:middle;min-width:38px;width:38px;
                            <?php echo e($isWeekend ? 'background:#f8fafc' : ''); ?>

                            <?php echo e($isToday   ? 'background:#eff6ff' : ''); ?>"
                            title="<?php echo e($dayDate->format('d/m/Y')); ?><?php echo e($isAbsent ? ' — Absent : '.$absenceType : ''); ?>">

                            <?php if($isAbsent): ?>
                                
                                <div style="background:linear-gradient(135deg,#ef4444,#f87171);color:white;border-radius:3px;font-size:0.5rem;font-weight:700;padding:2px 1px;min-height:22px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 3px rgba(239,68,68,0.4)"
                                     title="Absent : <?php echo e($absenceType); ?>">
                                    ABS
                                </div>
                            <?php elseif($dayPlanning): ?>
                                
                                <div style="display:flex;flex-direction:column;align-items:stretch;gap:1px">
                                    <?php if(in_array($dayPlanning->shift_type ?? '', ['matin', 'journee'])): ?>
                                    <div style="font-size:0.45rem;padding:2px 1px;border-radius:2px;background:#0ea5e9;color:white;font-weight:600;line-height:1.2"
                                         title="Matin <?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?><?php echo e($dayPlanning->room ? ' — '.$dayPlanning->room : ''); ?>">
                                        <?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?>

                                        <?php if($dayPlanning->room): ?>
                                        <div style="font-size:0.38rem;opacity:0.9;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?php echo e($dayPlanning->room); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if(in_array($dayPlanning->shift_type ?? '', ['apres_midi', 'journee'])): ?>
                                    <div style="font-size:0.45rem;padding:2px 1px;border-radius:2px;background:#f59e0b;color:white;font-weight:600;line-height:1.2"
                                         title="Après-midi <?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?><?php echo e($dayPlanning->room ? ' — '.$dayPlanning->room : ''); ?>">
                                        <?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?>

                                        <?php if($dayPlanning->room && $dayPlanning->shift_type === 'apres_midi'): ?>
                                        <div style="font-size:0.38rem;opacity:0.9;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?php echo e($dayPlanning->room); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if(($dayPlanning->shift_type ?? '') === 'nuit'): ?>
                                    <div style="font-size:0.4rem;padding:2px 1px;border-radius:2px;background:#6366f1;color:white;font-weight:600;line-height:1.2"
                                         title="Nuit <?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?>-<?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?><?php echo e($dayPlanning->room ? ' — '.$dayPlanning->room : ''); ?>">
                                        <?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?>-<?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?>

                                        <?php if($dayPlanning->room): ?>
                                        <div style="font-size:0.38rem;opacity:0.9;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?php echo e($dayPlanning->room); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if(($dayPlanning->shift_type ?? '') === 'garde'): ?>
                                    <div style="font-size:0.4rem;padding:2px 1px;border-radius:2px;background:#ef4444;color:white;font-weight:600;line-height:1.2"
                                         title="Garde <?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?>-<?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?><?php echo e($dayPlanning->room ? ' — '.$dayPlanning->room : ''); ?>">
                                        <?php echo e(substr($dayPlanning->shift_start ?? '', 0, 5)); ?>-<?php echo e(substr($dayPlanning->shift_end ?? '', 0, 5)); ?>

                                        <?php if($dayPlanning->room): ?>
                                        <div style="font-size:0.38rem;opacity:0.9;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?php echo e($dayPlanning->room); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
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


<div style="display:flex;gap:16px;margin-top:16px;flex-wrap:wrap;align-items:center">
    <div style="display:flex;align-items:center;gap:6px">
        <div style="width:14px;height:14px;background:#0ea5e9;border-radius:3px"></div>
        <span style="font-size:0.8rem">Matin</span>
    </div>
    <div style="display:flex;align-items:center;gap:6px">
        <div style="width:14px;height:14px;background:#f59e0b;border-radius:3px"></div>
        <span style="font-size:0.8rem">Après-midi</span>
    </div>
    <div style="display:flex;align-items:center;gap:6px">
        <div style="width:14px;height:14px;background:#6366f1;border-radius:3px"></div>
        <span style="font-size:0.8rem">Nuit</span>
    </div>
    <div style="display:flex;align-items:center;gap:6px">
        <div style="width:14px;height:14px;background:#ef4444;border-radius:3px"></div>
        <span style="font-size:0.8rem">Garde</span>
    </div>
    <div style="display:flex;align-items:center;gap:6px">
        <div style="width:14px;height:14px;background:linear-gradient(135deg,#ef4444,#f87171);border-radius:3px"></div>
        <span style="font-size:0.8rem">Absent</span>
    </div>
    <div style="margin-left:auto;font-size:0.75rem;color:var(--text-muted)">
        💡 Survolez une cellule pour voir les détails (heure + salle)
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
    const employeeId = select.dataset.employee;
    const roomId     = select.value;
    const start      = select.dataset.start;
    const end        = select.dataset.end;

    fetch('/planning/update-room', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ employee_id: employeeId, room_id: roomId, start, end })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour le badge texte sous le select
            const badge = select.parentElement.querySelector('div');
            const selectedText = select.options[select.selectedIndex]?.text ?? '';
            if (badge) {
                badge.textContent = roomId ? selectedText : '';
            }
        } else {
            console.warn('Erreur mise à jour salle', data);
        }
    })
    .catch(err => console.error('updateRoom error', err));
}
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/planning/monthly.blade.php ENDPATH**/ ?>