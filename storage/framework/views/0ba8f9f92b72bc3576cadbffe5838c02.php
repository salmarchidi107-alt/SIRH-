<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Tableau de bord'); ?>

<?php $__env->startSection('content'); ?>
<!-- Notifications Section -->
<?php if($recentNews->isNotEmpty() || $birthdays->isNotEmpty() || isset($conflicts) && count($conflicts) > 0): ?>
<div class="notifications-container" style="margin-bottom: 24px;">
    <?php if(isset($conflicts) && count($conflicts) > 0): ?>
    <div class="notification-banner" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 12px; padding: 16px 20px; color: white; margin-bottom: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div style="flex: 1;">
                <div style="font-weight: 600; font-size: 0.9rem;">⚠️ Conflits d'absences détectés ce mois</div>
                <div style="font-size: 0.8rem; opacity: 0.9;">
                    <?php $__currentLoopData = $conflicts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conflict): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span><?php echo e($conflict['employee']); ?> (<?php echo e($conflict['start']); ?> - <?php echo e($conflict['end']); ?>)</span><?php if(!$loop->last): ?>, <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <a href="<?php echo e(route('absences.calendar')); ?>" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem;">Voir calendrier</a>
        </div>
    </div>
    <?php endif; ?>
<div class="notifications-container" style="margin-bottom: 24px;">
    <?php if($recentNews->isNotEmpty()): ?>
    <div class="notification-banner" style="background: linear-gradient(135deg, #0f6b7c 0%, #1a8fa5 100%); border-radius: 12px; padding: 16px 20px; color: white; margin-bottom: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </div>
            <div style="flex: 1;">
                <div style="font-weight: 600; font-size: 0.9rem;">📢 Actualités récentes</div>
                <div style="font-size: 0.8rem; opacity: 0.9;">
                    <?php $__currentLoopData = $recentNews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $news): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span><?php echo e($news->title); ?></span><?php if(!$loop->last): ?>, <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($birthdays->isNotEmpty()): ?>
    <div class="notification-banner" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); border-radius: 12px; padding: 16px 20px; color: white;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"/>
                    <path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2-1 2-1"/>
                    <path d="M2 21h20"/>
                    <path d="M7 8v2"/>
                    <path d="M12 8v2"/>
                    <path d="M17 8v2"/>
                    <path d="M7 4h.01"/>
                    <path d="M12 4h.01"/>
                    <path d="M17 4h.01"/>
                </svg>
            </div>
            <div style="flex: 1;">
                <div style="font-weight: 600; font-size: 0.9rem;">🎂 Anniversaires ce mois-ci</div>
                <div style="font-size: 0.8rem; opacity: 0.9;">
                    <?php $__currentLoopData = $birthdays->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span><?php echo e($employee->full_name); ?> (<?php echo e($employee->birth_date->format('d')); ?>)</span><?php if(!$loop->last): ?>, <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-header">
            <div class="stat-icon primary">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <span class="stat-badge up">↑ +2 ce mois</span>
        </div>
        <div class="stat-value" data-count="<?php echo e($stats['total_employees']); ?>">0</div>
        <div class="stat-label">Total Employés</div>
    </div>

    <div class="stat-card success">
        <div class="stat-header">
            <div class="stat-icon success">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <span class="stat-badge up">Actifs</span>
        </div>
        <div class="stat-value" data-count="<?php echo e($stats['active_employees']); ?>">0</div>
        <div class="stat-label">Employés Actifs</div>
    </div>

    <?php if(isset($stats['pending_absences'])): ?>
    <div class="stat-card warning">
        <div class="stat-header">
            <div class="stat-icon warning">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <?php if($stats['pending_absences'] > 0): ?>
                <span class="stat-badge down">En attente</span>
            <?php endif; ?>
        </div>
        <div class="stat-value" data-count="<?php echo e($stats['pending_absences']); ?>">0</div>
        <div class="stat-label">Absences en attente</div>
    </div>
    <?php endif; ?>

    <div class="stat-card info">
        <div class="stat-header">
            <div class="stat-icon info">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <span class="stat-badge up">Aujourd'hui</span>
        </div>
        <div class="stat-value" data-count="<?php echo e($stats['today_present']); ?>">0</div>
        <div class="stat-label">Planning Aujourd'hui</div>
    </div>
</div>

<!-- Charts Grid -->
<div class="charts-grid">
    <!-- Absences Chart -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📊 Absences — 6 derniers mois</div>
        </div>
        <div class="card-body">
            <div class="chart-wrapper">
                <canvas id="absencesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Departments Chart -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🏥 Personnel/Service</div>
        </div>
        <div class="card-body">
            <div class="chart-wrapper">
                <canvas id="deptChart"></canvas>
            </div>
        </div>
    </div>
</div>

<?php if(isset($isAdminOrRH) && $isAdminOrRH): ?>
    <!-- Pending Absences (Admin/RH only) -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">⏳ Demandes en attente</div>
            <a href="<?php echo e(route('absences.index', ['status' => 'pending'])); ?>" class="btn btn-ghost btn-sm">Voir tout</a>
        </div>
        <div class="card-body" style="padding:0">
            <?php if($recent_absences->isEmpty()): ?>
                <div style="padding:32px;text-align:center;color:var(--text-muted)">
                    <div style="font-size:2rem;margin-bottom:8px">✅</div>
                    <div>Aucune demande en attente</div>
                </div>
            <?php else: ?>
                <ul class="activity-list" style="padding:0 24px">
                    <?php $__currentLoopData = $recent_absences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $absence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="activity-item">
                        <div class="activity-dot" style="background:var(--warning)"></div>
                        <div>
                            <div class="activity-text">
                                <strong><?php echo e($absence->employee->full_name); ?></strong> —
                                <?php echo e(\App\Models\Absence::TYPES[$absence->type] ?? $absence->type); ?>

                                (<?php echo e($absence->days); ?> j)
                            </div>
                            <div class="activity-time">
                                <?php echo e($absence->start_date->format('d/m/Y')); ?> → <?php echo e($absence->end_date->format('d/m/Y')); ?>

                            </div>
                            <div style="margin-top:6px;display:flex;gap:6px">
                                <form action="<?php echo e(route('absences.approve', $absence)); ?>" method="POST" style="display:inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-success btn-sm" style="padding:3px 10px;font-size:0.72rem">✓ Approuver</button>
                                </form>
                                <form action="<?php echo e(route('absences.reject', $absence)); ?>" method="POST" style="display:inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding:3px 10px;font-size:0.72rem">✗ Rejeter</button>
                                </form>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr;gap:20px;" class="dashboard-bottom">
    <!-- Today's Planning -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📅 Planning du jour — <?php echo e(now()->format('d F Y')); ?></div>
            <a href="<?php echo e(route('planning.weekly')); ?>" class="btn btn-ghost btn-sm">Planning complet</a>
        </div>
        <div class="card-body" style="padding:0">
            <?php if($today_planning->isEmpty()): ?>
                <div style="padding:32px;text-align:center;color:var(--text-muted)">
                    <div style="font-size:2rem;margin-bottom:8px">📅</div>
                    <div>Aucun planning aujourd'hui</div>
                </div>
            <?php else: ?>
                <ul class="activity-list" style="padding:0 24px">
                    <?php $__currentLoopData = $today_planning->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="activity-item">
                        <div class="activity-dot" style="background:#3b82f6"></div>
                        <div>
                            <div class="activity-text">
                                <strong><?php echo e($plan->employee->full_name); ?></strong> 
                                <span class="shift-pill shift-<?php echo e($plan->shift_type); ?>" style="margin-left:8px;font-size:0.8rem">
                                    <?php echo e(\App\Models\Planning::SHIFT_TYPES[$plan->shift_type]); ?>

                                </span>
                            </div>
                            <div class="activity-time">
                                <?php echo e($plan->shift_start); ?> – <?php echo e($plan->shift_end); ?>

                                <?php if($plan->employee->department): ?>
                                <span style="opacity:0.7;margin-left:12px">• <?php echo e($plan->employee->department); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
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
        <a href="<?php echo e(route('news.show', $news)); ?>" class="news-flyer-card" style="text-decoration: none;">
            <?php if($news->image): ?>
            <div class="news-flyer-image">
                <img src="<?php echo e(asset($news->image)); ?>" alt="<?php echo e($news->title); ?>">
            </div>
            <?php else: ?>
            <div class="news-flyer-image news-flyer-placeholder">
                <?php
                    $typeIcons = [
                        'annual_event' => '',
                        'meeting' => '',
                        'holiday' => '',
                        'new_recruit' => '',
                        'promotion' => '⬆'
                    ];
                ?>
                <div style="font-size: 3rem;"><?php echo e($typeIcons[$news->type] ?? '📰'); ?></div>
            </div>
            <?php endif; ?>
            <div class="news-flyer-content">
                <div class="news-flyer-badges">
                    <span class="badge bg-<?php echo e($news->type === 'holiday' ? 'success' : ($news->type === 'promotion' ? 'warning' : 'primary')); ?>">
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

<!-- Holidays Section - Same as News Flyer Style -->


<style>
@keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
.news-flyer-card {
    display: block;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}

.news-flyer-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.news-flyer-image {
    width: 100%;
    height: 180px;
    overflow: hidden;
    position: relative;
}

.news-flyer-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.news-flyer-card:hover .news-flyer-image img {
    transform: scale(1.05);
}

.news-flyer-placeholder {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.news-flyer-content {
    padding: 20px;
}

.news-flyer-badges {
    margin-bottom: 12px;
}

.news-flyer-title {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0 0 12px 0;
    color: #1e293b;
    line-height: 1.4;
}

.news-flyer-date {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #64748b;
    font-size: 0.85rem;
}
</style>
<?php endif; ?>

<!-- Birthday Section -->
<?php if($birthdays->isNotEmpty()): ?>
<div class="card mt-6">
    <div class="card-header">
        <div class="card-title">🎂 Anniversaires du mois</div>
    </div>
    <div class="card-body" style="padding:0">
        <div style="padding:16px 24px">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;">
                <?php $__currentLoopData = $birthdays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="background:var(--surface-2);padding:12px;border-radius:12px;text-align:center;border:1px solid var(--border)">
                    <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg, #f59e0b, #fbbf24);color:white;font-weight:700;font-size:1.1rem;display:flex;align-items:center;justify-content:center;margin:0 auto 8px">
                        <?php echo e(strtoupper(substr($employee->first_name, 0, 1))); ?><?php echo e(strtoupper(substr($employee->last_name, 0, 1))); ?>

                    </div>
                    <div style="font-weight:600;font-size:0.85rem"><?php echo e($employee->full_name); ?></div>
                    <div style="font-size:0.75rem;color:var(--text-muted)"><?php echo e($employee->department); ?></div>
                    <div style="font-size:0.8rem;font-weight:600;color:#f59e0b;margin-top:4px">🎂 <?php echo e($employee->birth_date->format('d F')); ?></div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Absences bar chart
const absCtx = document.getElementById('absencesChart').getContext('2d');
new Chart(absCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($monthly_absences, 'month')); ?>,
        datasets: [{
            label: 'Absences',
            data: <?php echo json_encode(array_column($monthly_absences, 'count')); ?>,
            backgroundColor: 'rgba(15, 107, 124, 0.15)',
            borderColor: '#0f6b7c',
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Plus Jakarta Sans' } } },
            x: { grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans' } } }
        }
    }
});

// Departments doughnut
const deptCtx = document.getElementById('deptChart').getContext('2d');
const deptColors = ['#0f6b7c','#1a8fa5','#00c9a7','#3b82f6','#f59e0b','#ef4444','#6366f1','#10b981','#ec4899'];
new Chart(deptCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($departments->keys()->toArray()); ?>,
        datasets: [{
            data: <?php echo json_encode($departments->values()->toArray()); ?>,
            backgroundColor: deptColors,
            borderWidth: 0,
            hoverOffset: 8,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: { font: { family: 'Plus Jakarta Sans', size: 12 }, padding: 16, usePointStyle: true }
            }
        },
        cutout: '68%',
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SI_RH\resources\views/dashboard/index.blade.php ENDPATH**/ ?>