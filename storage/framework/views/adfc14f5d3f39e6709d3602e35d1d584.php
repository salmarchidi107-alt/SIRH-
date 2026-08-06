<?php $__env->startSection('title', 'Mon Tableau de Bord'); ?>
<?php $__env->startSection('page-title', 'Mon espace — Employé'); ?>

<?php $__env->startSection('content'); ?>
<div class="emp-greeting">
    <strong>Bonjour <?php echo e($employee->first_name); ?> 👋</strong>
    <span><?php echo e(now()->isoFormat('dddd D MMMM YYYY')); ?> <?php if($employee->department): ?> — <?php echo e($employee->department); ?> <?php endif; ?> <?php if($employee->position): ?> · <?php echo e($employee->position); ?> <?php endif; ?></span>
</div>

<?php if($myTasks->isNotEmpty()): ?>
<div class="emp-alert-tasks <?php echo e($myTasksLate > 0 ? 'is-late' : 'is-info'); ?>">
    <div class="emp-alert-head">
        <span class="emp-alert-title">
            <?php if($myTasksLate > 0): ?>
                ⚠️ <?php echo e($myTasksLate); ?> tâche(s) en retard sur <?php echo e($myTasks->count()); ?> en attente
            <?php else: ?>
                📋 <?php echo e($myTasks->count()); ?> tâche(s) à réaliser
            <?php endif; ?>
        </span>
        <a href="<?php echo e(route('activites.my-tasks.index')); ?>" class="emp-alert-link">Voir mes tâches →</a>
    </div>
    <?php $__currentLoopData = $myTasks->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="emp-alert-row">
            <div>
                <div class="emp-alert-task-name"><?php echo e($task->title); ?></div>
                <div class="emp-alert-task-meta">
                    <?php echo e($task->project->name); ?>

                    <?php if($task->due_date): ?>
                        · Échéance <?php echo e($task->due_date->format('d/m/Y')); ?>

                    <?php endif; ?>
                </div>
            </div>
            <?php if($task->isLate()): ?>
                <span class="emp-alert-late-badge">En retard</span>
            <?php else: ?>
                <span class="emp-alert-task-meta"><?php echo e(\App\Models\Task::PRIORITY_LABELS[$task->priority]); ?></span>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<div class="emp-kpi-row">
    <div class="card">
        <div class="card-body">
            <div class="emp-kpi-label">Congés restants</div>
            <div class="emp-kpi-num"><?php echo e($congesRestants); ?></div>
            <div class="emp-kpi-sub" style="color: var(--emp-green);">jours</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="emp-kpi-label">Demandes en attente</div>
            <div class="emp-kpi-num"><?php echo e($demandesEnAttente); ?></div>
            <div class="emp-kpi-sub" style="color: var(--emp-amber);">En cours</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="emp-kpi-label">Heures ce mois</div>
            <div class="emp-kpi-num"><?php echo e($heuresMois); ?>h</div>
            <div class="emp-kpi-sub" style="color: var(--text-muted);">/ <?php echo e($heuresPrevues); ?>h</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <?php if($demandesEnAttente === 0): ?>
        <div class="emp-empty">
            <div class="emp-empty-icon">✓</div>
            <span class="emp-empty-txt">Aucune demande</span>
        </div>
    <?php else: ?>
        <div class="card-header">
            <div class="card-title">⏳ Demandes</div>
        </div>
        <div class="card-body">
            <?php $__currentLoopData = $absences->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $abs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="emp-req-row">
                <div>
                    <div class="emp-req-name"><?php echo e(\App\Models\Absence::TYPES[$abs->type] ?? $abs->type); ?></div>
                    <div class="emp-req-date"><?php echo e($abs->start_date->format('d M Y')); ?></div>
                </div>
                <?php if($abs->status === 'approved'): ?>
                    <span class="emp-chip emp-chip-ok">OK</span>
                <?php elseif($abs->status === 'rejected'): ?>
                    <span class="emp-chip emp-chip-refuse">KO</span>
                <?php else: ?>
                    <span class="emp-chip emp-chip-wait">Attente</span>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <div style="margin-top:12px">
                <a href="<?php echo e(route('absences.create')); ?>?employee_id=<?php echo e($employee->id); ?>" class="btn btn-outline w-full">+ Nouvelle</a>
            </div>
        </div>
    <?php endif; ?>
</div>

    <div class="card mb-4">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <div class="card-title">📅 Planning semaine</div>
            <a href="<?php echo e(route('planning.weekly')); ?>" class="btn btn-ghost btn-sm" style="font-size:0.8rem">Planning complet →</a>
        </div>
        <div class="card-body">
            <?php $__empty_1 = true; $__currentLoopData = $planningSemaine; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="emp-plan-row">
                <div class="emp-plan-left">
                    <div class="emp-dot <?php echo e($jour->absence ? 'emp-dot-red' : ($jour->planning ? 'emp-dot-blue' : 'emp-dot-amber')); ?>"></div>
                    <div>
                        <div class="emp-plan-name"><?php echo e($jour->date->isoFormat('dddd D')); ?></div>
                        <div class="emp-plan-sub"><?php echo e($jour->absence ? \App\Models\Absence::TYPES[$jour->absence->type] ?? 'Absence' : ($jour->planning ? $jour->heure_debut.'–'.$jour->heure_fin : 'Repos')); ?></div>
                    </div>
                </div>
                <span class="emp-chip <?php echo e($jour->absence ? 'emp-chip-refuse' : ($jour->planning ? 'emp-chip-matin' : 'emp-chip-repos')); ?>"><?php echo e($jour->absence ? 'Absence' : $jour->periode); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="emp-empty">
                <div class="emp-empty-icon">📅</div>
                <span class="emp-empty-txt">Aucun planning cette semaine</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

<div class="emp-two-col">
    <div class="card">
        <div class="card-header">
            <div class="card-title">📊 Absences</div>
        </div>
        <div class="card-body">
            <div class="emp-abs-row">
                <div class="emp-abs-top">
                    <span>Congés</span>
                    <span><?php echo e($absencesData['conges_utilises']); ?>/<?php echo e($absencesData['conges_total']); ?></span>
                </div>
                <div class="emp-abs-bar">
                    <div class="emp-abs-fill" style="width:<?php echo e($absencesData['conges_pct']); ?>%;background:var(--emp-green);"></div>
                </div>
            </div>
            <div class="emp-abs-row">
                <div class="emp-abs-top">
                    <span>Maladie</span>
                    <span><?php echo e($absencesData['maladie_utilises']); ?>/<?php echo e($absencesData['maladie_total']); ?></span>
                </div>
                <div class="emp-abs-bar">
                    <div class="emp-abs-fill" style="width:<?php echo e($absencesData['maladie_pct']); ?>%;background:var(--emp-amber);"></div>
                </div>
            </div>
            <div class="emp-abs-row">
                <div class="emp-abs-top">
                    <span>RTT</span>
                    <span><?php echo e($absencesData['rtt_utilises']); ?>/<?php echo e($absencesData['rtt_total']); ?></span>
                </div>
                <div class="emp-abs-bar">
                    <div class="emp-abs-fill" style="width:<?php echo e($absencesData['rtt_pct']); ?>%;background:var(--emp-blue);"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">🔔 Actualités</div>
        </div>
        <div class="card-body">
            <?php $__empty_1 = true; $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="emp-ev-row">
                <div class="emp-ev-tag ev-tag-event">Événement</div>
                <span class="emp-ev-name"><?php echo e($ev->title); ?></span>
                <span class="emp-ev-date"><?php echo e($ev->event_date->format('d M')); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="emp-empty">
                <span class="emp-empty-txt">Aucune actu</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- News Flyers Section -->
<?php if($upcomingNews->isNotEmpty()): ?>
<div class="card mt-6" style="border: none; box-shadow: none; background: transparent; padding: 0;">
    <div class="card-header" style="background: transparent; padding: 0 0 16px 0;">
        <div class="card-title" style="font-size: 1.25rem; font-weight: 700;">📰 Événements à venir</div>
        <a href="<?php echo e(route('news.index')); ?>" class="btn btn-ghost btn-sm">Voir tout →</a>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        <?php $__currentLoopData = $upcomingNews->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $news): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('news.show', $news)); ?>" class="news-flyer-card">
            <?php if($news->image): ?>
            <div class="news-flyer-image">
                <img src="<?php echo e(asset($news->image)); ?>" alt="<?php echo e($news->title); ?>">
            </div>
            <?php else: ?>
            <div class="news-flyer-image news-flyer-placeholder">
                <div style="font-size: 3rem;">📰</div>
            </div>
            <?php endif; ?>
            <div class="news-flyer-content">
                <div class="news-flyer-badges">
                    <span class="badge bg-primary">
                        <?php echo e(\App\Models\News::TYPES[$news->type] ?? $news->type); ?>

                    </span>
                </div>
                <h3 class="news-flyer-title"><?php echo e($news->title); ?></h3>
                <div class="news-flyer-date">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <?php echo e($news->event_date->format('d F Y')); ?>

                </div>
            </div>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Medstaff-second-main\resources\views/employe/dashboard.blade.php ENDPATH**/ ?>