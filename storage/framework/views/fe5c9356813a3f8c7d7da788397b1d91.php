<?php $__env->startSection('title', "Suivi d'activité"); ?>
<?php $__env->startSection('page-title', "Suivi d'activité — {$task->title}"); ?>

<?php $__env->startSection('content'); ?>
<style>
  #sa-app{
    --sa-teal:#14b8a6; --sa-teal-dark:#0d9488; --sa-teal-light:#e6f7f5;
    --sa-ink:#0f1720; --sa-ink-2:#6b7684; --sa-line:#e8eaee; --sa-card:#ffffff;
    --sa-amber:#f59e0b; --sa-red:#ef4444; --sa-blue:#3b82f6; --sa-green:#22c55e;
    font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;
  }
  #sa-app .sa-back{display:inline-flex; align-items:center; gap:6px; font-size:12px; color:var(--sa-ink-2); text-decoration:none; margin-bottom:8px;}
  #sa-app .sa-back svg{width:14px; height:14px;}
  #sa-app .sa-page-head h1{font-size:19px; font-weight:800; margin:0 0 4px; color:var(--sa-ink);}
  #sa-app .sa-sub{font-size:12.5px; color:var(--sa-ink-2); margin:0 0 20px;}

  #sa-app .sa-btn{display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; font-family:inherit; color:var(--sa-ink); background:#fff; border:1px solid var(--sa-line); border-radius:8px; padding:9px 14px; cursor:pointer; text-decoration:none; line-height:1;}
  #sa-app .sa-btn-primary{background:var(--sa-teal); border-color:var(--sa-teal); color:#fff;}
  #sa-app .sa-btn-danger{color:var(--sa-red); border-color:#f6c9c9;}

  #sa-app .sa-grid-2{display:grid; grid-template-columns:1.3fr 1fr; gap:16px; margin-bottom:22px;}
  @media (max-width:900px){ #sa-app .sa-grid-2{grid-template-columns:1fr;} }

  #sa-app .sa-card{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; padding:18px 20px;}
  #sa-app .sa-card h3{font-size:13.5px; font-weight:700; margin:0 0 14px; color:var(--sa-ink);}

  #sa-app .sa-form-grid{display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap;}
  #sa-app .sa-field{flex:1; min-width:160px; display:flex; flex-direction:column; gap:5px;}
  #sa-app .sa-field.sa-w-sm{flex:0 0 150px;}
  #sa-app .sa-field label{font-size:11.5px; color:var(--sa-ink-2); font-weight:600;}
  #sa-app .sa-field input, #sa-app .sa-field select, #sa-app .sa-field textarea{border:1px solid var(--sa-line); border-radius:8px; padding:9px 10px; font-size:13px; font-family:inherit; background:#fff; color:var(--sa-ink);}
  #sa-app .sa-field-error{color:var(--sa-red); font-size:11px; margin-top:2px;}
  #sa-app .sa-form-foot{display:flex; justify-content:flex-end; gap:8px; margin-top:4px;}

  #sa-app .sa-badge{padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap; display:inline-block;}
  #sa-app .sa-badge-gray{background:#eef0f3; color:#51586b;}
  #sa-app .sa-badge-blue{background:#e6f0ff; color:var(--sa-blue);}
  #sa-app .sa-badge-amber{background:#fff3e0; color:var(--sa-amber);}
  #sa-app .sa-badge-green{background:#e7f9ee; color:var(--sa-green);}
  #sa-app .sa-badge-red{background:#fdeaea; color:var(--sa-red);}

  #sa-app .sa-progress-wrap{display:flex; align-items:center; gap:8px; margin-bottom:16px;}
  #sa-app .sa-progress-track{flex:1; height:8px; background:#f1f3f5; border-radius:4px; overflow:hidden;}
  #sa-app .sa-progress-fill{height:100%; border-radius:4px; background:var(--sa-teal-dark);}

  #sa-app .sa-meta-list{list-style:none; margin:0; padding:0; font-size:12.5px;}
  #sa-app .sa-meta-list li{display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--sa-line);}
  #sa-app .sa-meta-list li:last-child{border-bottom:none;}
  #sa-app .sa-meta-list span:first-child{color:var(--sa-ink-2);}
  #sa-app .sa-meta-list span:last-child{font-weight:600; color:var(--sa-ink);}

  #sa-app .sa-table-wrap{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; overflow:hidden; overflow-x:auto;}
  #sa-app .sa-table{width:100%; border-collapse:collapse; font-size:12.5px;}
  #sa-app .sa-table th{text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:var(--sa-ink-2); padding:10px 16px; border-bottom:1px solid var(--sa-line); background:#fafbfc; white-space:nowrap;}
  #sa-app .sa-table td{padding:12px 16px; border-bottom:1px solid var(--sa-line); color:var(--sa-ink); vertical-align:middle;}
  #sa-app .sa-table tr:last-child td{border-bottom:none;}
  #sa-app .sa-cell-muted{color:var(--sa-ink-2); font-size:12px;}
  #sa-app .sa-empty-hint{font-size:12.5px; color:var(--sa-ink-2); text-align:center; padding:20px 0;}
</style>

<div id="sa-app">

  <a href="<?php echo e(route('activites.admin.tasks.index')); ?>" class="sa-back">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Retour aux tâches
  </a>

  <div class="sa-page-head">
    <h1><?php echo e($task->title); ?></h1>
    <p class="sa-sub">
      Projet <a href="<?php echo e(route('activites.admin.projects.show', $task->project)); ?>"><?php echo e($task->project->name); ?></a>
      · <span class="sa-badge <?php echo e($task->statusBadgeClass()); ?>"><?php echo e($task->statusLabel()); ?></span>
      · <span class="sa-badge <?php echo e($task->priorityBadgeClass()); ?>"><?php echo e($task->priorityLabel()); ?></span>
    </p>
  </div>

  <div class="sa-grid-2">
    <div class="sa-card" id="sa-edit-task">
      <h3>Modifier la tâche</h3>
      <form method="POST" action="<?php echo e(route('activites.admin.tasks.update', $task)); ?>">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Nom de la tâche</label>
            <input type="text" name="title" value="<?php echo e(old('title', $task->title)); ?>" required>
            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="sa-field-error"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
        </div>
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Projet</label>
            <select name="project_id" required>
              <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($proj->id); ?>" <?php if(old('project_id', $task->project_id) == $proj->id): echo 'selected'; endif; ?>><?php echo e($proj->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="sa-field">
            <label>Assigner à</label>
            <select name="assigned_to" required>
              <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($employee->id); ?>" <?php if(old('assigned_to', $task->assigned_to) == $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
        </div>
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Description</label>
            <textarea name="description" rows="3"><?php echo e(old('description', $task->description)); ?></textarea>
          </div>
        </div>
        <div class="sa-form-grid">
          <div class="sa-field sa-w-sm">
            <label>Priorité</label>
            <select name="priority">
              <?php $__currentLoopData = \App\Models\Task::PRIORITIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($p); ?>" <?php if(old('priority', $task->priority) === $p): echo 'selected'; endif; ?>><?php echo e(\App\Models\Task::PRIORITY_LABELS[$p]); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="sa-field sa-w-sm">
            <label>Statut</label>
            <select name="status">
              <?php $__currentLoopData = \App\Models\Task::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s); ?>" <?php if(old('status', $task->status) === $s): echo 'selected'; endif; ?>><?php echo e(\App\Models\Task::STATUS_LABELS[$s]); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
        </div>
        <div class="sa-form-grid">
          <div class="sa-field sa-w-sm">
            <label>Date de début</label>
            <input type="date" name="start_date" value="<?php echo e(old('start_date', optional($task->start_date)->format('Y-m-d'))); ?>">
          </div>
          <div class="sa-field sa-w-sm">
            <label>Échéance</label>
            <input type="date" name="due_date" value="<?php echo e(old('due_date', optional($task->due_date)->format('Y-m-d'))); ?>">
          </div>
          <div class="sa-field sa-w-sm">
            <label>Estimation</label>
            <input type="text" name="estimated_duration" placeholder="ex: 4h" value="<?php echo e(old('estimated_duration', $task->estimated_minutes ? \App\Support\Duration::toHuman($task->estimated_minutes) : '')); ?>">
            <?php $__errorArgs = ['estimated_duration'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="sa-field-error"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
        </div>
        <div class="sa-form-foot">
          <button type="submit" class="sa-btn sa-btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>

    <div>
      <div class="sa-card" style="margin-bottom:16px;">
        <h3>Avancement</h3>
        <div class="sa-progress-wrap">
          <div class="sa-progress-track"><div class="sa-progress-fill" style="width:<?php echo e($task->percent_complete); ?>%;"></div></div>
          <strong><?php echo e($task->percent_complete); ?>%</strong>
        </div>
        <ul class="sa-meta-list">
          <li><span>Créée par</span><span><?php echo e($task->owner->name ?? '—'); ?></span></li>
          <li><span>Temps loggé</span><span><?php echo e(\App\Support\Duration::toHuman($task->logged_minutes)); ?></span></li>
          <li><span>Temps estimé</span><span><?php echo e($task->estimated_minutes ? \App\Support\Duration::toHuman($task->estimated_minutes) : '—'); ?></span></li>
          <li><span>Échéance</span><span style="<?php echo e($task->isLate() ? 'color:var(--sa-red);' : ''); ?>"><?php echo e($task->due_date ? $task->due_date->format('d/m/Y') : '—'); ?><?php echo e($task->isLate() ? ' (dépassée)' : ''); ?></span></li>
        </ul>
        <?php if($task->employee_comment): ?>
          <div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--sa-line); font-size:12.5px;">
            <div style="color:var(--sa-ink-2); margin-bottom:4px;">Commentaire de l'employé :</div>
            <?php echo e($task->employee_comment); ?>

          </div>
        <?php endif; ?>
      </div>

      <div class="sa-card">
        <h3>Supprimer cette tâche</h3>
        <form method="POST" action="<?php echo e(route('activites.admin.tasks.destroy', $task)); ?>" onsubmit="return confirm('Supprimer définitivement cette tâche ?');">
          <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
          <button type="submit" class="sa-btn sa-btn-danger">Supprimer la tâche</button>
        </form>
      </div>
    </div>
  </div>

  <div class="sa-card">
    <h3>Saisies de temps</h3>
    <?php if($task->activities->isEmpty()): ?>
      <div class="sa-empty-hint">Aucune saisie de temps sur cette tâche pour l'instant.</div>
    <?php else: ?>
      <div class="sa-table-wrap">
        <table class="sa-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Employé</th>
              <th>Durée</th>
              <th>Description</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $task->activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td class="sa-cell-muted"><?php echo e($activity->activity_date->format('d/m/Y')); ?></td>
                <td><?php echo e($activity->user->name ?? '—'); ?></td>
                <td class="sa-cell-muted"><?php echo e(\App\Support\Duration::toHuman($activity->duration_minutes)); ?></td>
                <td class="sa-cell-muted"><?php echo e(\Illuminate\Support\Str::limit($activity->comment, 60) ?: '—'); ?></td>
                <td><span class="sa-badge <?php echo e($activity->statusBadgeClass()); ?>"><?php echo e($activity->statusLabel()); ?></span></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/activites/admin/tasks/show.blade.php ENDPATH**/ ?>