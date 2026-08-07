<?php $__env->startSection('title', "Suivi d'activité"); ?>
<?php $__env->startSection('page-title', "Suivi d'activité — État d'avancement"); ?>

<?php $__env->startSection('content'); ?>
<div id="sa-app">

  <div class="sa-page-head">
    <div>
      <h1>Suivi d'activité — Équipe</h1>
      <p class="sa-sub">Où en est l'équipe sur ses tâches et ses projets.</p>
    </div>
  </div>

  <div class="sa-tabs">
    <a href="<?php echo e(route('activites.admin.dashboard')); ?>" class="sa-tab <?php echo e(request()->routeIs('activites.admin.dashboard') ? 'active' : ''); ?>">État d'avancement</a>
    <a href="<?php echo e(route('activites.admin.projects.index')); ?>" class="sa-tab <?php echo e(request()->routeIs('activites.admin.projects.*') ? 'active' : ''); ?>">Projets</a>
    <a href="<?php echo e(route('activites.admin.tasks.index')); ?>" class="sa-tab <?php echo e(request()->routeIs('activites.admin.tasks.*') ? 'active' : ''); ?>">Tâches</a>
  </div>

  <div class="sa-kpis">
    <div class="sa-kpi">
      <div class="sa-kpi-value"><?php echo e($stats['total_projects']); ?></div>
      <div class="sa-kpi-label">Projets</div>
    </div>
    <div class="sa-kpi sa-good">
      <div class="sa-kpi-value"><?php echo e($stats['done_tasks']); ?></div>
      <div class="sa-kpi-label">Tâches terminées</div>
    </div>
    <div class="sa-kpi">
      <div class="sa-kpi-value"><?php echo e($stats['in_progress_tasks']); ?></div>
      <div class="sa-kpi-label">Tâches en cours</div>
    </div>
    <div class="sa-kpi sa-warn">
      <div class="sa-kpi-value"><?php echo e($stats['late_tasks']); ?></div>
      <div class="sa-kpi-label">Tâches en retard</div>
    </div>
  </div>

  <div class="sa-section">
    <div class="sa-section-title">Avancement global</div>
    <div class="sa-progress-hero">
      <div class="sa-progress-ring">
        <canvas id="saGlobalChart" width="160" height="160"></canvas>
        <div class="sa-progress-ring-center">
          <div class="sa-progress-pct"><?php echo e($stats['completion_rate']); ?><small>%</small></div>
          <div class="sa-progress-caption">Complété</div>
        </div>
      </div>

      <div class="sa-progress-divider"></div>

      <div class="sa-progress-details">
        <p class="sa-progress-lead">
          <strong><?php echo e($stats['done_tasks']); ?></strong> tâches terminées sur <strong><?php echo e($stats['total_tasks']); ?></strong> au total
        </p>
        <div class="sa-progress-chips">
          <div class="sa-chip">
            <span class="sa-chip-value"><?php echo e($globalStats['employees_fully_done']); ?>/<?php echo e($globalStats['employees_with_tasks']); ?></span>
            <span class="sa-chip-label">Employés à jour</span>
          </div>
          <div class="sa-chip">
            <span class="sa-chip-value"><?php echo e(100 - $stats['completion_rate']); ?>%</span>
            <span class="sa-chip-label">Restant</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="sa-section">
    <div class="sa-section-title">Avancement par employé</div>

    <?php if(empty($employeeProgress)): ?>
      <div class="sa-table-wrap">
        <div class="sa-empty-hint">Aucune tâche assignée pour le moment.</div>
      </div>
    <?php else: ?>
      <div class="sa-table-wrap">
        <table class="sa-table">
          <thead>
            <tr>
              <th>Employé</th>
              <th>Tâches assignées</th>
              <th>Terminées</th>
              <th>En retard</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $employeeProgress; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td class="sa-table-name"><?php echo e($row['name']); ?></td>
                <td>
                  <span class="sa-mini-track"><span class="sa-mini-fill" style="width:<?php echo e($row['percent']); ?>%;"></span></span>
                  <?php echo e($row['done']); ?> / <?php echo e($row['total']); ?>

                </td>
                <td class="sa-table-muted"><?php echo e($row['percent']); ?>%</td>
                <td class="<?php echo e($row['late'] > 0 ? 'sa-status-late' : 'sa-table-muted'); ?>"><?php echo e($row['late'] > 0 ? $row['late'] : '—'); ?></td>
                <td>
                  <?php if($row['complete']): ?>
                    <span class="sa-status-ok">Toutes terminées</span>
                  <?php elseif($row['late'] > 0): ?>
                    <span class="sa-status-late"><?php echo e($row['remaining']); ?> restante(s), dont en retard</span>
                  <?php else: ?>
                    <span class="sa-status-pending"><?php echo e($row['remaining']); ?> restante(s)</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="sa-section">
    <div class="sa-section-title">Avancement par projet</div>

    <?php if(empty($projectProgress)): ?>
      <div class="sa-table-wrap">
        <div class="sa-empty-hint">Aucun projet avec des tâches pour le moment.</div>
      </div>
    <?php else: ?>
      <div class="sa-table-wrap">
        <table class="sa-table">
          <thead>
            <tr>
              <th>Projet</th>
              <th>Tâches</th>
              <th>Terminées</th>
              <th>En retard</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $projectProgress; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td class="sa-table-name">
                  <?php echo e($row['name']); ?>

                  <?php if($row['status'] === 'archive'): ?>
                    <span class="sa-table-muted">(archivé)</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="sa-mini-track"><span class="sa-mini-fill" style="width:<?php echo e($row['percent']); ?>%;"></span></span>
                  <?php echo e($row['done']); ?> / <?php echo e($row['total']); ?>

                </td>
                <td class="sa-table-muted"><?php echo e($row['percent']); ?>%</td>
                <td class="<?php echo e($row['late'] > 0 ? 'sa-status-late' : 'sa-table-muted'); ?>"><?php echo e($row['late'] > 0 ? $row['late'] : '—'); ?></td>
                <td>
                  <?php if($row['complete']): ?>
                    <span class="sa-status-ok">Toutes terminées</span>
                  <?php elseif($row['late'] > 0): ?>
                    <span class="sa-status-late"><?php echo e($row['remaining']); ?> restante(s), dont en retard</span>
                  <?php else: ?>
                    <span class="sa-status-pending"><?php echo e($row['remaining']); ?> restante(s)</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const completion = <?php echo e($stats['completion_rate']); ?>;
    const canvas = document.getElementById('saGlobalChart');
    const ctx = canvas.getContext('2d');

    const gradient = ctx.createLinearGradient(0, 0, 160, 160);
    gradient.addColorStop(0, '#2dd4bf');
    gradient.addColorStop(1, '#0d9488');

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [completion, 100 - completion],
                backgroundColor: [gradient, '#eef1f3'],
                borderWidth: 0,
                borderRadius: 8,
                spacing: 3,
            }],
        },
        options: {
            cutout: '80%',
            responsive: false,
            animation: { animateRotate: true, duration: 800, easing: 'easeOutQuart' },
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
        },
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/activites/admin/dashboard.blade.php ENDPATH**/ ?>