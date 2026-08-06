<?php $__env->startSection('title', "Suivi d'activité"); ?>
<?php $__env->startSection('page-title', "Suivi d'activité — Tâches"); ?>

<?php $__env->startSection('content'); ?>
<div id="sa-app">

  <div class="sa-page-head">
    <div>
      <h1>Suivi d'activité — Équipe</h1>
      <p class="sa-sub">Gestion complète des tâches de l'entreprise.</p>
    </div>
    <div class="sa-head-actions">
      <a href="<?php echo e(route('activites.admin.tasks.export-pdf', request()->query())); ?>" class="sa-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export PDF
      </a>
      <a href="<?php echo e(route('activites.admin.tasks.export-excel', request()->query())); ?>" class="sa-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export Excel
      </a>
      <button type="button" class="sa-btn sa-btn-primary" onclick="document.getElementById('sa-new-task').open = true; document.getElementById('sa-new-task').scrollIntoView({behavior:'smooth'});">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        Nouvelle tâche
      </button>
    </div>
  </div>

  <div class="sa-tabs">
    <a href="<?php echo e(route('activites.admin.dashboard')); ?>" class="sa-tab <?php echo e(request()->routeIs('activites.admin.dashboard') ? 'active' : ''); ?>">État d'avancement</a>
    <a href="<?php echo e(route('activites.admin.projects.index')); ?>" class="sa-tab <?php echo e(request()->routeIs('activites.admin.projects.*') ? 'active' : ''); ?>">Projets</a>
    <a href="<?php echo e(route('activites.admin.tasks.index')); ?>" class="sa-tab <?php echo e(request()->routeIs('activites.admin.tasks.*') ? 'active' : ''); ?>">Tâches</a>
  </div>

  <details id="sa-new-task" class="sa-disclosure">
    <summary>
      <span class="sa-plus-icon">+</span>
      Créer une tâche
      <svg class="sa-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
    </summary>
    <div class="sa-disclosure-body">
      <form method="POST" action="<?php echo e(route('activites.admin.tasks.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Nom de la tâche</label>
            <input type="text" name="title" value="<?php echo e(old('title')); ?>"  required>
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
              <option value="" disabled selected>Choisir un projet</option>
              <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($proj->id); ?>" <?php if((int) old('project_id') === $proj->id): echo 'selected'; endif; ?>><?php echo e($proj->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['project_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="sa-field-error"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div class="sa-field">
            <label>Assigner à</label>
            <select name="assigned_to" required>
              <option value="" disabled selected>Choisir un employé</option>
              <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($employee->id); ?>" <?php if((int) old('assigned_to') === $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['assigned_to'];
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
            <label>Description</label>
            <textarea name="description" rows="2" ><?php echo e(old('description')); ?></textarea>
          </div>
        </div>
        <div class="sa-form-grid">
          <div class="sa-field sa-w-sm">
            <label>Priorité</label>
            <select name="priority">
              <?php $__currentLoopData = \App\Models\Task::PRIORITIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($p); ?>" <?php if(old('priority') === $p): echo 'selected'; endif; ?>><?php echo e(\App\Models\Task::PRIORITY_LABELS[$p]); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="sa-field sa-w-sm">
            <label>Date de début</label>
            <input type="date" name="start_date" value="<?php echo e(old('start_date')); ?>">
          </div>
          <div class="sa-field sa-w-sm">
            <label>Échéance</label>
            <input type="date" name="due_date" value="<?php echo e(old('due_date')); ?>">
          </div>
          <div class="sa-field sa-w-sm">
            <label>Estimation</label>
            <input type="text" name="estimated_duration" placeholder="ex: 4h" value="<?php echo e(old('estimated_duration')); ?>">
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
          <button type="submit" class="sa-btn sa-btn-primary">Créer &amp; assigner</button>
        </div>
      </form>
    </div>
  </details>

  <form method="GET" class="sa-filters">
    <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Rechercher une tâche...">
    <select name="user_id" onchange="this.form.submit()">
      <option value="">Tous les employés</option>
      <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($employee->id); ?>" <?php if(request('user_id') == $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->name); ?></option>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <select name="project_id" onchange="this.form.submit()">
      <option value="">Tous les projets</option>
      <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($proj->id); ?>" <?php if(request('project_id') == $proj->id): echo 'selected'; endif; ?>><?php echo e($proj->name); ?></option>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <select name="status" onchange="this.form.submit()">
      <option value="">Tous les statuts</option>
      <?php $__currentLoopData = \App\Models\Task::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($status); ?>" <?php if(request('status') === $status): echo 'selected'; endif; ?>><?php echo e(\App\Models\Task::STATUS_LABELS[$status]); ?></option>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <select name="priority" onchange="this.form.submit()">
      <option value="">Toutes les priorités</option>
      <?php $__currentLoopData = \App\Models\Task::PRIORITIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($priority); ?>" <?php if(request('priority') === $priority): echo 'selected'; endif; ?>><?php echo e(\App\Models\Task::PRIORITY_LABELS[$priority]); ?></option>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <button type="submit" class="sa-btn sa-btn-sm">Filtrer</button>
    <?php if(request()->anyFilled(['q','user_id','project_id','status','priority'])): ?>
      <a href="<?php echo e(route('activites.admin.tasks.index')); ?>" class="sa-btn sa-btn-sm">Réinitialiser</a>
    <?php endif; ?>
  </form>

  <?php if($tasks->isEmpty()): ?>
    <div class="sa-table-wrap">
      <div class="sa-empty">
        <div class="sa-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div>
        <div class="sa-empty-title">Aucune tâche ne correspond à ces filtres.</div>
      </div>
    </div>
  <?php else: ?>
    <div class="sa-table-wrap">
      <table class="sa-table">
        <thead>
          <tr>
            <th>Tâche</th>
            <th>Projet</th>
            <th>Assignée à</th>
            <th>Priorité</th>
            <th>Échéance</th>
            <th>Temps</th>
            <th>Statut</th>
            <th style="width:110px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
              <td>
                <div class="sa-cell-title"><?php echo e($task->title); ?></div>
                <?php if($task->owner && $task->assignee && $task->owner->id !== $task->assignee->id): ?>
                  <div class="sa-cell-muted">créée par <?php echo e($task->owner->name); ?></div>
                <?php endif; ?>
              </td>
              <td class="sa-cell-muted"><?php echo e($task->project->name); ?></td>
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
              <td class="sa-cell-muted" style="font-variant-numeric:tabular-nums;">
                <?php echo e(\App\Support\Duration::toHuman($task->logged_minutes)); ?><?php echo e($task->estimated_minutes ? ' / '.\App\Support\Duration::toHuman($task->estimated_minutes) : ''); ?>

              </td>
              <td><span class="sa-badge <?php echo e($task->statusBadgeClass()); ?>"><?php echo e($task->statusLabel()); ?></span></td>
              <td>
                <div class="sa-row-actions">
                  <a href="<?php echo e(route('activites.admin.tasks.show', $task)); ?>" class="sa-icon-btn" title="Voir">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                  </a>
                  <a href="<?php echo e(route('activites.admin.tasks.show', $task)); ?>#sa-edit-task" class="sa-icon-btn" title="Modifier">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </a>
                  <form method="POST" action="<?php echo e(route('activites.admin.tasks.destroy', $task)); ?>" onsubmit="return confirm('Supprimer cette tâche ?');">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="sa-icon-btn sa-icon-danger" title="Supprimer">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
    <div style="margin-top:16px;"><?php echo e($tasks->links()); ?></div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Medstaff-second-main\resources\views/activites/admin/tasks/index.blade.php ENDPATH**/ ?>