<?php $__env->startSection('title', "Suivi d'activité"); ?>
<?php $__env->startSection('page-title', "Suivi d'activité — {$task->title}"); ?>

<?php $__env->startSection('content'); ?>
<div id="sa-app">

  <a href="<?php echo e(route('activites.admin.tasks.index')); ?>" class="sa-back">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Retour aux tâches
  </a>

  <div class="sa-page-head">
    <div>
      <h1><?php echo e($task->title); ?></h1>
      <p class="sa-sub">
        Projet <a href="<?php echo e(route('activites.admin.projects.show', $task->project)); ?>"><?php echo e($task->project->name); ?></a>
        · <span class="sa-badge <?php echo e($task->statusBadgeClass()); ?>"><?php echo e($task->statusLabel()); ?></span>
        · <span class="sa-badge <?php echo e($task->priorityBadgeClass()); ?>"><?php echo e($task->priorityLabel()); ?></span>
      </p>
    </div>
  </div>

  <div class="sa-grid-2 sa-grid-2-wide">
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