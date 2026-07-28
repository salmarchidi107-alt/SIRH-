<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 90px 32px 60px 32px; }

    body{font-family: Helvetica, Arial, sans-serif; font-size:11px; color:#0f1720;}

    header{position: fixed; top:-70px; left:0; right:0; height:70px;}
    .header-row{display:table; width:100%; border-bottom:2px solid #14b8a6; padding-bottom:10px;}
    .header-left{display:table-cell; vertical-align:bottom;}
    .header-right{display:table-cell; vertical-align:bottom; text-align:right;}
    .tenant-name{font-size:13px; font-weight:bold; color:#0f1720;}
    .doc-title{font-size:16px; font-weight:bold; margin:2px 0 0; color:#0f1720;}
    .doc-sub{font-size:9.5px; color:#6b7684; margin-top:2px;}

    footer{position: fixed; bottom:-45px; left:0; right:0; height:40px; border-top:1px solid #e8eaee; padding-top:8px; font-size:8.5px; color:#9aa1ab;}
    footer .footer-row{display:table; width:100%;}
    footer .footer-left{display:table-cell;}
    footer .footer-right{display:table-cell; text-align:right;}
    .pagenum:before { content: counter(page); }
    .pagecount:before { content: counter(pages); }

    table{width:100%; border-collapse:collapse; margin-top:10px;}
    thead{display: table-header-group;}
    tr{page-break-inside: avoid;}
    th{text-align:left; font-size:9px; text-transform:uppercase; letter-spacing:.03em; color:#6b7684; border-bottom:1.5px solid #0f1720; padding:7px 8px; background:#fafbfc;}
    td{padding:8px; border-bottom:1px solid #e8eaee; vertical-align:top;}
    tbody tr:nth-child(even){background:#fbfcfd;}

    .badge{padding:2px 8px; border-radius:10px; font-size:9px; font-weight:bold; white-space:nowrap;}
    .badge-green{background:#e7f9ee; color:#22c55e;}
    .badge-gray{background:#eef0f3; color:#51586b;}

    .project-desc{color:#6b7684; font-size:9.5px; margin-top:2px; display:block;}

    .summary-bar{display:table; width:100%; margin-top:14px; margin-bottom:4px; background:#fafbfc; border:1px solid #e8eaee; border-radius:6px;}
    .summary-item{display:table-cell; padding:8px 14px; text-align:center; border-right:1px solid #e8eaee;}
    .summary-item:last-child{border-right:none;}
    .summary-value{font-size:13px; font-weight:bold; color:#0f1720;}
    .summary-label{font-size:8px; color:#6b7684; text-transform:uppercase; letter-spacing:.02em; margin-top:2px;}

    .empty-note{text-align:center; color:#9aa1ab; padding:30px 0; font-size:11px;}
  </style>
</head>
<body>

  <header>
    <div class="header-row">
      <div class="header-left">
        <div class="tenant-name"><?php echo e($tenantName); ?></div>
        <div class="doc-title">Liste des projets</div>
        <div class="doc-sub">Suivi d'activité — <?php echo e($projects->count()); ?> projet(s)</div>
      </div>
      <div class="header-right">
        <div class="doc-sub">Généré le <?php echo e(now()->format('d/m/Y à H:i')); ?></div>
        <div class="doc-sub">par <?php echo e(auth()->user()->name ?? '—'); ?></div>
      </div>
    </div>
  </header>

  <footer>
    <div class="footer-row">
      <div class="footer-left"><?php echo e($tenantName); ?> — Suivi d'activité</div>
      <div class="footer-right">Page <span class="pagenum"></span> / <span class="pagecount"></span></div>
    </div>
  </footer>

  <?php
    $activeCount = $projects->where('status', 'actif')->count();
    $totalTasks = $projects->sum('tasks_count');
    $totalDone = $projects->sum('done_tasks_count');
  ?>

  <div class="summary-bar">
    <div class="summary-item">
      <div class="summary-value"><?php echo e($projects->count()); ?></div>
      <div class="summary-label">Projets</div>
    </div>
    <div class="summary-item">
      <div class="summary-value"><?php echo e($activeCount); ?></div>
      <div class="summary-label">Actifs</div>
    </div>
    <div class="summary-item">
      <div class="summary-value"><?php echo e($totalTasks); ?></div>
      <div class="summary-label">Tâches (total)</div>
    </div>
    <div class="summary-item">
      <div class="summary-value"><?php echo e($totalDone); ?></div>
      <div class="summary-label">Tâches terminées</div>
    </div>
  </div>

  <?php if($projects->isEmpty()): ?>
    <div class="empty-note">Aucun projet ne correspond aux filtres sélectionnés.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Projet</th>
          <th>Statut</th>
          <th>Tâches</th>
          <th>Terminées</th>
          <th>Créé le</th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td>
              <strong><?php echo e($project->name); ?></strong>
              <?php if($project->description): ?>
                <span class="project-desc"><?php echo e(\Illuminate\Support\Str::limit($project->description, 90)); ?></span>
              <?php endif; ?>
            </td>
            <td><span class="badge <?php echo e($project->status === 'actif' ? 'badge-green' : 'badge-gray'); ?>"><?php echo e($project->statusLabel()); ?></span></td>
            <td><?php echo e($project->tasks_count); ?></td>
            <td><?php echo e($project->done_tasks_count); ?></td>
            <td><?php echo e($project->created_at->format('d/m/Y')); ?></td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  <?php endif; ?>

</body>
</html>
<?php /**PATH D:\Projects\SIRH-\resources\views/activites/admin/projects/export-pdf.blade.php ENDPATH**/ ?>