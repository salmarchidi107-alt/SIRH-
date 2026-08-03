<?php $__env->startSection('title', 'État Visuel des Absences'); ?>
<?php $__env->startSection('page-title', 'État Visuel des Absences'); ?>

<?php $__env->startSection('content'); ?>


<div class="modal-overlay" id="conflictsModal" onclick="closeConflictsModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header" style="background:linear-gradient(135deg,#ef4444,#dc2626);color:white">
            <h3>⚠️ Conflits détectés</h3>
            <button class="modal-close" onclick="closeConflictsModal()">×</button>
        </div>
        <div class="modal-body">
            <ul style="list-style:none;padding:0;margin:0">
                <li style="text-align:center;color:var(--text-muted);padding:32px">Chargement...</li>
            </ul>
        </div>
    </div>
</div>

<div class="page-header">
    <div class="page-header-left">
        <h1>État Visuel des Absences</h1>
<p>Vue mensuelle — <?php echo e($firstDay->locale('fr')->translatedFormat('F Y')); ?></p>    </div>
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <div class="view-toggle">
            <button class="<?php echo e((!isset($viewMode) || $viewMode == 'calendar') ? 'active' : ''); ?>" onclick="switchView('calendar')">Calendrier</button>
            <button class="<?php echo e($viewMode == 'list' ? 'active' : ''); ?>" onclick="switchView('list')">Liste</button>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <a href="<?php echo e($prevMonthUrl); ?>" class="btn btn-ghost btn-sm">←</a>
            <a href="<?php echo e($todayUrl); ?>" class="btn btn-secondary btn-sm">Aujourd'hui</a>
            <a href="<?php echo e($nextMonthUrl); ?>" class="btn btn-ghost btn-sm">→</a>
        </div>
    </div>
</div>


<?php
    $conflictEmpIds = collect($conflicts)->pluck('employee_id')->unique()->toArray();
    $replacementEmpIds = $absences->whereNotNull('replacement_id')->pluck('employee_id')->unique()->toArray();
?>

<div class="quick-stats" id="quickStats">

    <button type="button"
        class="quick-stat quick-stat--approved"
        data-filter="approved"
        onclick="filterCalendar('approved')"
        title="Filtrer les absences approuvées">
        <div class="quick-stat-dot"></div>
        <span><strong><?php echo e($stats['approved_count']); ?></strong> Approuvée<?php echo e($stats['approved_count'] > 1 ? 's' : ''); ?></span>
    </button>

    <button type="button"
        class="quick-stat quick-stat--pending"
        data-filter="pending"
        onclick="filterCalendar('pending')"
        title="Filtrer les absences en attente">
        <div class="quick-stat-dot"></div>
        <span><strong><?php echo e($stats['pending_count']); ?></strong> En attente</span>
    </button>

    <button type="button"
        class="quick-stat quick-stat--conflict"
        data-filter="conflict"
        onclick="filterCalendar('conflict')"
        title="Filtrer les conflits">
        <div class="quick-stat-dot"></div>
        <span><strong><?php echo e($stats['conflicts_count']); ?></strong> Conflit<?php echo e($stats['conflicts_count'] > 1 ? 's' : ''); ?></span>
    </button>

    <button type="button"
        class="quick-stat quick-stat--replacement"
        data-filter="replacement"
        onclick="filterCalendar('replacement')"
        title="Filtrer les remplacements">
        <div class="quick-stat-dot"></div>
        <span><strong><?php echo e($stats['replacements_count']); ?></strong> Remplacement<?php echo e($stats['replacements_count'] > 1 ? 's' : ''); ?></span>
    </button>

    <div class="quick-stat quick-stat--days quick-stat--info">
        <div class="quick-stat-dot"></div>
        <span><strong><?php echo e($stats['total_days']); ?></strong> Jours total</span>
    </div>

    <button type="button"
        class="quick-stat quick-stat--reset"
        id="badgeResetBtn"
        onclick="filterCalendar(null)"
        style="display:none"
        title="Tout afficher">
        <span>✕ Tout afficher</span>
    </button>
</div>


<div id="filterNotice" style="display:none;margin-bottom:12px;padding:8px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:13px;color:#166534;align-items:center;gap:8px;">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
    <span id="filterNoticeText">Filtre actif</span>
    <button onclick="filterCalendar(null)" style="margin-left:auto;background:none;border:none;cursor:pointer;font-size:12px;color:#166534;font-weight:bold;text-decoration:underline;">Réinitialiser</button>
</div>


<div class="card" style="margin-bottom:24px">
    <div style="padding:16px">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
            <div style="font-weight:600;font-size:0.875rem">Filtrer par:</div>

            <select class="filter-select" style="padding:8px 12px" onchange="applyFilter('department', this.value)">
                <option value="">Tous les services</option>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($dept); ?>" <?php echo e(request('department') == $dept ? 'selected' : ''); ?>><?php echo e($dept); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <div class="search-bar" style="position:relative">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:var(--text-muted)">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" placeholder="Rechercher employé..." value="<?php echo e($search ?? ''); ?>"
                    style="padding:10px 12px 10px 40px;border:1px solid var(--border);border-radius:8px;min-width:220px">
            </div>



            <?php if(request()->anyFilled(['department', 'employee_id', 'status'])): ?>
                <a href="<?php echo e($resetUrl); ?>" class="btn btn-ghost btn-sm">✕ Réinitialiser</a>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php if(!isset($viewMode) || $viewMode == 'calendar'): ?>


<script>
    var CONFLICT_EMP_IDS    = <?php echo json_encode($conflictEmpIds, 15, 512) ?>;
    var REPLACEMENT_EMP_IDS = <?php echo json_encode($replacementEmpIds, 15, 512) ?>;
</script>

<div class="card">
    <div class="monthly-calendar-container">
        <table class="monthly-calendar">
            <thead>
                <tr>
                    <th class="employee-col">Collaborateur</th>
                    <?php for($day = 1; $day <= $daysInMonth; $day++): ?>
                        <?php
                            $dateH = \Carbon\Carbon::createFromDate($year, $month, $day);
                            $isTodayH   = $dateH->isSameDay($today);
                            $isWeekendH = $dateH->dayOfWeek == 0 || $dateH->dayOfWeek == 6;
                        ?>
                        <th class="day-header <?php echo e($isTodayH ? 'today-header' : ''); ?>">
                            <?php echo e($day); ?>

                            <?php if($isTodayH): ?>
                                <div style="font-size:0.5rem;color:var(--primary)">AUJ</div>
                            <?php endif; ?>
                        </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $filteredEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $empAbsences = $absenceMap[$emp->id] ?? []; ?>
                <tr class="employee-row" data-employee-id="<?php echo e($emp->id); ?>">
                    <td class="employee-cell">
                        <div class="employee-mini">
                            <div class="employee-mini-avatar">
                                <?php echo e(strtoupper(substr($emp->first_name,0,1))); ?><?php echo e(strtoupper(substr($emp->last_name,0,1))); ?>

                            </div>
                            <div class="employee-mini-info">
                                <div class="employee-mini-name"><?php echo e($emp->full_name); ?></div>
                                <div class="employee-mini-dept"><?php echo e($emp->department ?? 'N/A'); ?></div>
                            </div>
                        </div>
                    </td>

                    <?php for($day = 1; $day <= $daysInMonth; $day++): ?>
                        <?php
                            $date      = \Carbon\Carbon::createFromDate($year, $month, $day);
                            $isToday   = $date->isSameDay($today);
                            $isWeekend = $date->dayOfWeek == 0 || $date->dayOfWeek == 6;
                            $absence   = $empAbsences[$day] ?? null;

                            $tdData = '';
                            if ($absence) {
                                $hasReplacement = $absence->replacement_id ? '1' : '0';
                                $isConflict     = in_array($emp->id, $conflictEmpIds) ? '1' : '0';
                                $tdData = 'data-status="'.$absence->status.'" data-has-replacement="'.$hasReplacement.'" data-is-conflict="'.$isConflict.'" data-absence-id="'.$absence->id.'"';
                            }
                        ?>

                        <td
                            class="<?php echo e($isToday ? 'today-cell' : ''); ?> <?php echo e($isWeekend ? 'weekend-cell' : ''); ?> <?php echo e($absence ? 'absence-td' : ''); ?>"
                            <?php echo $absence ? $tdData : ''; ?>

                        >
                            <?php if($absence): ?>
                                <div class="absence-dot <?php echo e($absence->status); ?>"
                                     onclick="showAbsenceModal(
                                         <?php echo e($absence->id); ?>,
                                         '<?php echo e(addslashes($emp->full_name)); ?>',
                                         '<?php echo e(addslashes($emp->department ?? '')); ?>',
                                         '<?php echo e(addslashes($emp->position ?? '')); ?>',
                                         '<?php echo e($absence->start_date->format('d/m/Y')); ?>',
                                         '<?php echo e($absence->end_date->format('d/m/Y')); ?>',
                                         '<?php echo e(addslashes(\App\Models\Absence::TYPES[$absence->type] ?? $absence->type)); ?>',
                                         '<?php echo e($absence->status); ?>',
                                         <?php echo e($absence->days); ?>,
                                         '<?php echo e(addslashes($absence->reason ?? '')); ?>'
                                     )"
                                     title="<?php echo e(\App\Models\Absence::TYPES[$absence->type] ?? $absence->type); ?>">
                                    <span style="font-size:0.7rem">●</span>
                                </div>
                            <?php else: ?>
                                <div class="absence-dot empty"></div>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e($daysInMonth + 1); ?>" style="text-align:center;padding:48px;color:var(--text-muted)">
                        <div style="font-size:2.5rem;margin-bottom:12px">👥</div>
                        <div>Aucun employé trouvé</div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>


<?php if($viewMode == 'list'): ?>
<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Type</th>
                    <th>Période</th>
                    <th>Jours</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $absences->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $absence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="absence-card <?php echo e($absence->status); ?>"
                    onclick="showAbsenceModal(<?php echo e($absence->id); ?>, '<?php echo e(addslashes($absence->employee->full_name)); ?>', '<?php echo e(addslashes($absence->employee->department ?? '')); ?>', '<?php echo e(addslashes($absence->employee->position ?? '')); ?>', '<?php echo e($absence->start_date->format('d/m/Y')); ?>', '<?php echo e($absence->end_date->format('d/m/Y')); ?>', '<?php echo e(addslashes(\App\Models\Absence::TYPES[$absence->type] ?? $absence->type)); ?>', '<?php echo e($absence->status); ?>', <?php echo e($absence->days); ?>, '<?php echo e(addslashes($absence->reason ?? '')); ?>')"
                    style="cursor:pointer">
                    <td>
                        <div class="employee-mini">
                            <div class="employee-mini-avatar" style="width:36px;height:36px;font-size:0.75rem">
                                <?php echo e(strtoupper(substr($absence->employee->first_name,0,1))); ?><?php echo e(strtoupper(substr($absence->employee->last_name,0,1))); ?>

                            </div>
                            <div>
                                <div style="font-weight:600"><?php echo e($absence->employee->full_name); ?></div>
                                <div style="font-size:0.75rem;color:var(--text-muted)"><?php echo e($absence->employee->department); ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span style="font-weight:500"><?php echo e(\App\Models\Absence::TYPES[$absence->type] ?? $absence->type); ?></span></td>
                    <td>
                        <div style="font-size:0.875rem"><?php echo e($absence->start_date->format('d/m/Y')); ?> → <?php echo e($absence->end_date->format('d/m/Y')); ?></div>
                        <div style="font-size:0.75rem;color:var(--text-muted)"><?php echo e($absence->start_date->diffInDays($absence->end_date) + 1); ?> jour(s)</div>
                    </td>
                    <td><span class="badge badge-primary"><?php echo e($absence->days); ?> j</span></td>
                    <td>
                        <?php if($absence->status == 'pending'): ?>
                            <span class="badge badge-warning">En attente</span>
                        <?php elseif($absence->status == 'approved'): ?>
                            <span class="badge badge-success">Approuvé</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Rejeté</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="<?php echo e(route('absences.show', $absence)); ?>" class="btn btn-ghost btn-sm btn-icon" title="Voir" onclick="event.stopPropagation()">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            <?php if($absence->status == 'pending'): ?>
                                <form action="<?php echo e(route('absences.approve', $absence)); ?>" method="POST" onclick="event.stopPropagation()">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-success btn-sm" title="Approuver">✓</button>
                                </form>
                                <form action="<?php echo e(route('absences.reject', $absence)); ?>" method="POST" onclick="event.stopPropagation()">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-danger btn-sm" title="Rejeter">✗</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:48px;color:var(--text-muted)">
                        <div style="font-size:2.5rem;margin-bottom:12px">📅</div>
                        <div>Aucune absence trouvée</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>





<div class="modal-overlay" id="absenceModal" onclick="closeModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Détails de l'absence</h3>
            <button class="modal-close" onclick="closeAbsenceModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="modal-employee-header">
                <div class="modal-employee-avatar" id="modalAvatar">JD</div>
                <div class="modal-employee-info">
                    <h4 id="modalEmployeeName">John Doe</h4>
                    <p id="modalEmployeeDept">Département</p>
                </div>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Début</span>
                <span class="modal-detail-value" id="modalStartDate">—</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Fin</span>
                <span class="modal-detail-value" id="modalEndDate">—</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Motif</span>
                <span class="modal-detail-value"><span class="modal-type-badge" id="modalType">—</span></span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Jours</span>
                <span class="modal-detail-value" id="modalDays">—</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Statut</span>
                <span class="modal-detail-value"><span class="modal-status-badge" id="modalStatus">—</span></span>
            </div>
            <div class="modal-detail-row" id="modalReasonRow" style="display:none">
                <span class="modal-detail-label">Détails</span>
                <span class="modal-detail-value" id="modalReason" style="max-width:180px;text-align:right">—</span>
            </div>
            <div style="margin-top:16px;display:flex;gap:10px;justify-content:flex-end">
                <button class="btn btn-ghost btn-sm" onclick="closeAbsenceModal()">Fermer</button>
                <a href="#" id="modalDetailLink" class="btn btn-primary btn-sm">Voir plus</a>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
// =========================================================================
// VUE / FILTRES URL
// =========================================================================
function switchView(view) {
    var url = new URL(window.location.href);
    url.searchParams.set('view', view);
    window.location.href = url.toString();
}

function applyFilter(key, value) {
    var url = new URL(window.location.href);
    if (value) { url.searchParams.set(key, value); }
    else        { url.searchParams.delete(key); }
    window.location.href = url.toString();
}

// =========================================================================
// FILTRAGE DES BADGES — tout en JS, sans rechargement
// =========================================================================
var currentFilter = null;

var FILTER_LABELS = {
    approved:    'Affichage : absences approuvées uniquement',
    pending:     'Affichage : absences en attente uniquement',
    conflict:    'Affichage : employés avec conflits uniquement',
    replacement: 'Affichage : absences avec remplacement uniquement',
};

window.filterCalendar = function(filter) {
    // Toggle : recliquer désactive
    if (currentFilter === filter) { filter = null; }
    currentFilter = filter;

    // Mise à jour visuels badges
    document.querySelectorAll('.quick-stat[data-filter]').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.filter === filter);
    });

    var resetBtn = document.getElementById('badgeResetBtn');
    if (resetBtn) { resetBtn.style.display = filter ? '' : 'none'; }

    // Bandeau notification
    var notice     = document.getElementById('filterNotice');
    var noticeText = document.getElementById('filterNoticeText');
    if (notice) {
        if (filter) {
            notice.style.display = 'flex';
            if (noticeText) { noticeText.textContent = FILTER_LABELS[filter] || 'Filtre actif'; }
        } else {
            notice.style.display = 'none';
        }
    }

    // Application du filtre sur la grille
    if (!filter)                  { showAll(); }
    else if (filter === 'conflict')     { filterByData('conflict'); }
    else if (filter === 'replacement')  { filterByData('replacement'); }
    else                                { filterByStatus(filter); }
};

// Remet tout à l'état initial
function showAll() {
    document.querySelectorAll('.employee-row').forEach(function(row) {
        row.classList.remove('row-hidden');
    });
    document.querySelectorAll('.absence-td').forEach(function(td) {
        td.classList.remove('cell-dimmed', 'cell-highlight');
    });
}

// Filtre par statut : approved | pending
function filterByStatus(status) {
    document.querySelectorAll('.employee-row').forEach(function(row) {
        var allTds    = row.querySelectorAll('.absence-td');
        var matchTds  = row.querySelectorAll('.absence-td[data-status="' + status + '"]');

        if (allTds.length === 0) {
            // Ligne sans aucune absence → masquer
            row.classList.add('row-hidden');
            return;
        }

        if (matchTds.length === 0) {
            row.classList.add('row-hidden');
            allTds.forEach(function(td) { td.classList.remove('cell-dimmed', 'cell-highlight'); });
            return;
        }

        row.classList.remove('row-hidden');
        allTds.forEach(function(td) {
            if (td.dataset.status === status) {
                td.classList.remove('cell-dimmed');
                td.classList.add('cell-highlight');
            } else {
                td.classList.add('cell-dimmed');
                td.classList.remove('cell-highlight');
            }
        });
    });
}

// Filtre générique par data-attribute (conflict | replacement)
function filterByData(type) {
    var attr = type === 'conflict' ? 'data-is-conflict' : 'data-has-replacement';

    document.querySelectorAll('.employee-row').forEach(function(row) {
        var allTds   = row.querySelectorAll('.absence-td');
        var matchTds = row.querySelectorAll('.absence-td[' + attr + '="1"]');

        if (matchTds.length === 0) {
            row.classList.add('row-hidden');
            allTds.forEach(function(td) { td.classList.remove('cell-dimmed', 'cell-highlight'); });
            return;
        }

        row.classList.remove('row-hidden');
        allTds.forEach(function(td) {
            if (td.getAttribute(attr) === '1') {
                td.classList.remove('cell-dimmed');
                td.classList.add('cell-highlight');
            } else {
                td.classList.add('cell-dimmed');
                td.classList.remove('cell-highlight');
            }
        });
    });
}

// =========================================================================
// MODAL ABSENCE DETAIL
// =========================================================================
function showAbsenceModal(id, name, dept, position, startDate, endDate, type, status, days, reason) {
    var initials = name.split(' ').map(function(n){ return n[0] || ''; }).join('').toUpperCase().substring(0, 2);
    document.getElementById('modalAvatar').textContent = initials;
    document.getElementById('modalEmployeeName').textContent = name;
    document.getElementById('modalEmployeeDept').textContent = (position ? position + ' — ' : '') + dept;
    document.getElementById('modalStartDate').textContent = startDate;
    document.getElementById('modalEndDate').textContent   = endDate;
    document.getElementById('modalType').textContent      = type;
    document.getElementById('modalDays').textContent      = days + ' jour' + (days > 1 ? 's' : '');

    var statusBadge = document.getElementById('modalStatus');
    var statusLabels = { approved: 'Approuvé', pending: 'En attente', rejected: 'Rejeté' };
    statusBadge.textContent  = statusLabels[status] || status;
    statusBadge.className    = 'modal-status-badge ' + status;

    var reasonRow = document.getElementById('modalReasonRow');
    if (reason && reason.trim()) {
        document.getElementById('modalReason').textContent = reason;
        reasonRow.style.display = 'flex';
    } else {
        reasonRow.style.display = 'none';
    }

    document.getElementById('modalDetailLink').href = '/absences/' + id;
    document.getElementById('absenceModal').classList.add('active');
}

function closeAbsenceModal() {
    document.getElementById('absenceModal').classList.remove('active');
}

function closeModal(e) {
    if (e.target === document.getElementById('absenceModal')) {
        closeAbsenceModal();
    }
}

// =========================================================================
// MODAL CONFLITS
// =========================================================================
function loadConflictsModal() {
    var baseUrl = '<?php echo e(route("absences.conflicts.json")); ?>';
    var params  = new URLSearchParams(window.location.search);
    fetch(baseUrl + '?' + params.toString())
        .then(function(r){ return r.json(); })
        .then(function(conflicts) {
            var modal = document.getElementById('conflictsModal');
            var body  = modal.querySelector('.modal-body ul');
            body.innerHTML = '';
            if (conflicts.length === 0) {
                body.innerHTML = '<li style="text-align:center;color:var(--text-muted);padding:32px">Aucun conflit détecté</li>';
            } else {
                conflicts.forEach(function(conflict) {
                    var li = document.createElement('li');
                    li.style.cssText = 'padding:12px 0;border-bottom:1px solid #fee2e2;';
                    li.innerHTML =
                        '<div style="font-weight:600;margin-bottom:4px">' + conflict.employee + '</div>' +
                        '<div style="display:flex;gap:8px;font-size:0.85rem">' +
                            '<span style="background:#fef2f2;padding:4px 8px;border-radius:4px;color:#dc2626">' + conflict.absence1 + '</span>' +
                            '<span>vs</span>' +
                            '<span style="background:#fef2f2;padding:4px 8px;border-radius:4px;color:#dc2626">' + conflict.absence2 + '</span>' +
                        '</div>' +
                        '<div style="font-size:0.8rem;color:#991b1b;margin-top:4px">' + conflict.start + ' → ' + conflict.end + '</div>';
                    body.appendChild(li);
                });
            }
        })
        .catch(function(err){ console.error('Conflicts load error', err); });
}

function closeConflictsModal() {
    document.getElementById('conflictsModal').classList.remove('active');
}

// =========================================================================
// INIT
// =========================================================================
document.addEventListener('DOMContentLoaded', function() {
    // Echap ferme les modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAbsenceModal();
            closeConflictsModal();
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/absences/calendar.blade.php ENDPATH**/ ?>