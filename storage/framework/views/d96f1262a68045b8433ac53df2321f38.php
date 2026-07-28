<?php $__env->startSection('title', "Suivi d'activité"); ?>
<?php $__env->startSection('page-title', "Suivi d'activité — Mes tâches"); ?>

<?php $__env->startSection('content'); ?>
<style>
  #sa-app{
    --sa-teal:#14b8a6; --sa-teal-dark:#0d9488; --sa-teal-light:#e6f7f5;
    --sa-ink:#0f1720; --sa-ink-2:#6b7684; --sa-line:#e8eaee; --sa-card:#ffffff;
    --sa-amber:#f59e0b; --sa-red:#ef4444; --sa-blue:#3b82f6; --sa-green:#22c55e;
    font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;
  }
  #sa-app .sa-page-head{margin-bottom:16px;}
  #sa-app .sa-page-head h1{font-size:19px; font-weight:800; margin:0 0 4px; color:var(--sa-ink);}
  #sa-app .sa-sub{font-size:12.5px; color:var(--sa-ink-2); margin:0;}

  #sa-app .sa-tabs{display:flex; gap:4px; border-bottom:1px solid var(--sa-line); margin-bottom:22px;}
  #sa-app .sa-tab{padding:10px 16px; font-size:13px; font-weight:600; color:var(--sa-ink-2); text-decoration:none; border-bottom:2px solid transparent; margin-bottom:-1px;}
  #sa-app .sa-tab.active{color:var(--sa-teal-dark); border-bottom-color:var(--sa-teal);}
  #sa-app .sa-tab:hover:not(.active){color:var(--sa-ink);}

  #sa-app .sa-stats{display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;}
  #sa-app .sa-stat{flex:1; min-width:150px; background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; padding:14px 16px;}
  #sa-app .sa-stat-label{font-size:11.5px; color:var(--sa-ink-2); margin-bottom:6px;}
  #sa-app .sa-stat-value{font-size:20px; font-weight:800; color:var(--sa-ink);}
  #sa-app .sa-stat.sa-warn .sa-stat-value{color:var(--sa-red);}

  #sa-app .sa-filters{display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap;}
  #sa-app .sa-filters select{border:1px solid var(--sa-line); border-radius:8px; padding:7px 10px; font-size:12.5px; background:#fff; font-family:inherit; color:var(--sa-ink);}

  #sa-app .sa-task-card{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; padding:16px 18px; margin-bottom:12px;}
  #sa-app .sa-task-head{display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:10px;}
  #sa-app .sa-task-title{font-weight:700; font-size:14px; color:var(--sa-ink); margin-bottom:4px;}
  #sa-app .sa-task-meta{font-size:12px; color:var(--sa-ink-2);}
  #sa-app .sa-task-meta b{color:var(--sa-ink);}

  #sa-app .sa-badge{padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap; display:inline-block;}
  #sa-app .sa-badge-gray{background:#eef0f3; color:#51586b;}
  #sa-app .sa-badge-blue{background:#e6f0ff; color:var(--sa-blue);}
  #sa-app .sa-badge-amber{background:#fff3e0; color:var(--sa-amber);}
  #sa-app .sa-badge-green{background:#e7f9ee; color:var(--sa-green);}
  #sa-app .sa-badge-red{background:#fdeaea; color:var(--sa-red);}

  #sa-app .sa-disclosure{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:14px; margin-bottom:20px; overflow:hidden;}
  #sa-app .sa-disclosure summary{list-style:none; cursor:pointer; padding:14px 20px; display:flex; align-items:center; gap:10px; font-weight:700; font-size:13.5px; color:var(--sa-ink);}
  #sa-app .sa-disclosure summary::-webkit-details-marker{display:none;}
  #sa-app .sa-plus-icon{width:20px; height:20px; border-radius:50%; background:var(--sa-teal); color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; flex-shrink:0;}
  #sa-app .sa-chevron{margin-left:auto; color:var(--sa-ink-2); transition:transform .15s ease;}
  #sa-app .sa-disclosure[open] .sa-chevron{transform:rotate(180deg);}
  #sa-app .sa-disclosure-body{padding:16px 20px 20px; border-top:1px solid var(--sa-line);}
  #sa-app .sa-form-grid{display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap;}
  #sa-app .sa-field.sa-w-sm{flex:0 0 150px;}
  #sa-app .sa-field-error{color:var(--sa-red); font-size:11px; margin-top:2px;}
  #sa-app .sa-form-foot{display:flex; justify-content:flex-end; margin-top:4px;}
  #sa-app textarea{border:1px solid var(--sa-line); border-radius:8px; padding:9px 10px; font-size:13px; font-family:inherit; background:#fff; color:var(--sa-ink); width:100%;}

  #sa-app .sa-update-form{display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; padding-top:12px; border-top:1px solid var(--sa-line);}
  #sa-app .sa-field{display:flex; flex-direction:column; gap:5px;}
  #sa-app .sa-field label{font-size:11px; color:var(--sa-ink-2); font-weight:600;}
  #sa-app .sa-field select, #sa-app .sa-field input{border:1px solid var(--sa-line); border-radius:8px; padding:8px 10px; font-size:12.5px; font-family:inherit; background:#fff; color:var(--sa-ink);}
  #sa-app .sa-field.sa-grow{flex:1; min-width:180px;}
  #sa-app .sa-field.sa-percent{width:100px;}
  #sa-app .sa-btn{display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; font-family:inherit; color:#fff; background:var(--sa-teal); border:1px solid var(--sa-teal); border-radius:8px; padding:9px 14px; cursor:pointer;}

  #sa-app .sa-empty-hint{font-size:12.5px; color:var(--sa-ink-2); text-align:center; padding:30px 0; background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px;}
</style>

<div id="sa-app">

  <div class="sa-page-head">
    <h1>Suivi d'activité</h1>
    <p class="sa-sub">Les tâches qui te sont assignées. Tu peux mettre à jour le statut, l'avancement et ajouter un commentaire.</p>
  </div>

  <div class="sa-tabs">
    <a href="<?php echo e(route('activites.my-tasks.index')); ?>" class="sa-tab <?php echo e(request()->routeIs('activites.my-tasks.*') ? 'active' : ''); ?>">Mes tâches</a>
    <a href="<?php echo e(route('activites.time-entries.index')); ?>" class="sa-tab <?php echo e(request()->routeIs('activites.time-entries.*') ? 'active' : ''); ?>">Saisie de temps</a>
  </div>

  <div class="sa-stats">
    <div class="sa-stat"><div class="sa-stat-label">Tâches en cours</div><div class="sa-stat-value"><?php echo e($stats['remaining']); ?></div></div>
    <div class="sa-stat sa-warn"><div class="sa-stat-label">En retard</div><div class="sa-stat-value"><?php echo e($stats['late']); ?></div></div>
  </div>

  <details class="sa-disclosure">
    <summary>
      <span class="sa-plus-icon">+</span>
      Nouvelle tâche
      <svg class="sa-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
    </summary>
    <div class="sa-disclosure-body">
      <?php if($projects->isEmpty()): ?>
        <p class="sa-sub">Aucun projet actif pour l'instant : demande à un admin d'en créer un.</p>
      <?php else: ?>
        <form method="POST" action="<?php echo e(route('activites.my-tasks.store')); ?>">
          <?php echo csrf_field(); ?>
          <div class="sa-form-grid">
            <div class="sa-field sa-grow">
              <label>Titre de la tâche</label>
              <input type="text" name="title" value="<?php echo e(old('title')); ?>" placeholder="Ex : Préparer le compte-rendu" required>
              <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="sa-field-error"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="sa-field sa-grow">
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
              <label>Échéance</label>
              <input type="date" name="due_date" value="<?php echo e(old('due_date')); ?>">
            </div>
            <div class="sa-field sa-w-sm">
              <label>Durée estimée *</label>
              <input type="text" name="estimated_duration" placeholder="ex: 4h" value="<?php echo e(old('estimated_duration')); ?>" required>
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
          <div class="sa-form-grid">
            <div class="sa-field sa-grow">
              <label>Description</label>
              <textarea name="description" rows="2" placeholder="Optionnel"><?php echo e(old('description')); ?></textarea>
            </div>
          </div>
          <div class="sa-form-foot">
            <button type="submit" class="sa-btn">Créer la tâche</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </details>

  <form method="GET" class="sa-filters">
    <select name="status" onchange="this.form.submit()">
      <option value="">Tous les statuts</option>
      <?php $__currentLoopData = \App\Models\Task::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($status); ?>" <?php if(request('status') === $status): echo 'selected'; endif; ?>><?php echo e(\App\Models\Task::STATUS_LABELS[$status]); ?></option>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
  </form>

  <?php $__empty_1 = true; $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="sa-task-card">
      <div class="sa-task-head">
        <div>
          <div class="sa-task-title"><?php echo e($task->title); ?></div>
          <div class="sa-task-meta">
            Projet <b><?php echo e($task->project->name); ?></b>
            · Priorité <b><?php echo e($task->priorityLabel()); ?></b>
            <?php if($task->due_date): ?>
              · Échéance <b><?php echo e($task->due_date->format('d/m/Y')); ?></b>
              <?php if($task->isLate()): ?>
                · <span style="color:var(--sa-red); font-weight:700;">En retard</span>
              <?php endif; ?>
            <?php endif; ?>
            <?php if($task->estimated_minutes): ?>
              · Temps <b><?php echo e(\App\Support\Duration::toHuman($task->logged_minutes)); ?> / <?php echo e(\App\Support\Duration::toHuman($task->estimated_minutes)); ?></b> estimé
            <?php endif; ?>
          </div>
          <?php if($task->description): ?>
            <div class="sa-task-meta" style="margin-top:6px;"><?php echo e($task->description); ?></div>
          <?php endif; ?>
        </div>
        <span class="sa-badge <?php echo e($task->statusBadgeClass()); ?>"><?php echo e($task->statusLabel()); ?></span>
      </div>

      <form method="POST" action="<?php echo e(route('activites.my-tasks.update', $task)); ?>" class="sa-update-form">
        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
        <div class="sa-field">
          <label>Statut</label>
          <select name="status">
            <?php $__currentLoopData = \App\Models\Task::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($status); ?>" <?php if($task->status === $status): echo 'selected'; endif; ?>><?php echo e(\App\Models\Task::STATUS_LABELS[$status]); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="sa-field sa-percent">
          <label>Avancement (%)</label>
          <input type="number" name="percent_complete" min="0" max="100" value="<?php echo e($task->percent_complete); ?>">
        </div>
        <div class="sa-field sa-grow">
          <label>Commentaire</label>
          <input type="text" name="employee_comment" value="<?php echo e($task->employee_comment); ?>" placeholder="Optionnel">
        </div>
        <button type="submit" class="sa-btn">Mettre à jour</button>
      </form>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="sa-empty-hint">Aucune tâche ne t'est assignée pour l'instant.</div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/activites/employee/my-tasks.blade.php ENDPATH**/ ?>