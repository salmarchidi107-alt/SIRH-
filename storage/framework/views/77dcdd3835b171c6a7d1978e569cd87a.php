<?php $__env->startSection('title', "Suivi d'activité"); ?>
<?php $__env->startSection('page-title', "Suivi d'activité — {$project->name}"); ?>

<?php $__env->startSection('content'); ?>
<div id="sa-app">

  <a href="<?php echo e(route('activites.admin.projects.index')); ?>" class="sa-back">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Retour aux projets
  </a>

  <div class="sa-page-head">
    <div>
      <h1><?php echo e($project->name); ?></h1>
      <p class="sa-sub">
        <span class="sa-badge <?php echo e($project->statusBadgeClass()); ?>"><?php echo e($project->statusLabel()); ?></span>
        · Créé le <?php echo e($project->created_at->format('d/m/Y')); ?>

        · <?php echo e($project->tasks->count()); ?> tâche(s)
      </p>
    </div>
  </div>

  <div class="sa-grid-2">
    <div class="sa-card" id="sa-edit-project">
      <h3>Modifier le projet</h3>
      <form method="POST" action="<?php echo e(route('activites.admin.projects.update', $project)); ?>">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Nom du projet</label>
            <input type="text" name="name" value="<?php echo e(old('name', $project->name)); ?>" required>
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="sa-field-error"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div class="sa-field sa-w-sm">
            <label>Statut</label>
            <select name="status">
              <?php $__currentLoopData = \App\Models\Project::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($status); ?>" <?php if(old('status', $project->status) === $status): echo 'selected'; endif; ?>><?php echo e(\App\Models\Project::STATUS_LABELS[$status]); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
        </div>
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Description</label>
            <textarea name="description" rows="3"><?php echo e(old('description', $project->description)); ?></textarea>
          </div>
        </div>
        <div class="sa-form-foot">
          <button type="submit" class="sa-btn sa-btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>

    <div class="sa-card">
      <h3>Supprimer ce projet</h3>
      <p class="sa-sub" style="margin-bottom:14px;">Cette action supprime définitivement le projet ainsi que toutes ses tâches et saisies de temps associées.</p>
      <form method="POST" action="<?php echo e(route('activites.admin.projects.destroy', $project)); ?>" onsubmit="return confirm('Supprimer définitivement ce projet et toutes ses tâches ?');">
        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
        <button type="submit" class="sa-btn sa-btn-danger">Supprimer le projet</button>
      </form>
    </div>
  </div>

  <div class="sa-card">
    <h3>Tâches du projet</h3>
    <?php if($project->tasks->isEmpty()): ?>
      <div class="sa-empty-hint">Aucune tâche pour l'instant. Ajoute-en une depuis l'onglet « Tâches ».</div>
    <?php else: ?>
      <div class="sa-table-wrap">
        <table class="sa-table">
          <thead>
            <tr>
              <th>Tâche</th>
              <th>Assignée à</th>
              <th>Priorité</th>
              <th>Échéance</th>
              <th>Temps</th>
              <th>Statut</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $project->tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td class="sa-cell-title"><?php echo e($task->title); ?></td>
                <td>
                  <span style="display:flex; align-items:center; gap:6px;">
                    <span class="sa-avatar"><?php echo e(strtoupper(substr($task->assignee->name ?? '?', 0, 1))); ?></span>
                    <?php echo e($task->assignee->name ?? '—'); ?>

                  </span>
                </td>
                <td><span class="sa-badge <?php echo e($task->priorityBadgeClass()); ?>"><?php echo e($task->priorityLabel()); ?></span></td>
                <td class="sa-cell-muted" style="<?php echo e($task->isLate() ? 'color:var(--sa-red); font-weight:700;' : ''); ?>">
                  <?php echo e($task->due_date ? $task->due_date->format('d/m/Y') : '—'); ?>

                </td>
                <td class="sa-cell-muted"><?php echo e(\App\Support\Duration::toHuman($task->logged_minutes)); ?></td>
                <td><span class="sa-badge <?php echo e($task->statusBadgeClass()); ?>"><?php echo e($task->statusLabel()); ?></span></td>
                <td><a href="<?php echo e(route('activites.admin.tasks.show', $task)); ?>" class="sa-btn" style="padding:5px 10px;">Voir</a></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/activites/admin/projects/show.blade.php ENDPATH**/ ?>