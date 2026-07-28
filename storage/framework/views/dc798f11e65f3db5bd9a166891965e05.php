<?php $__env->startSection('title', "Suivi d'activité"); ?>
<?php $__env->startSection('page-title', "Suivi d'activité — Projets"); ?>

<?php $__env->startSection('content'); ?>
<style>
  #sa-app{
    --sa-teal:#14b8a6; --sa-teal-dark:#0d9488; --sa-teal-light:#e6f7f5;
    --sa-ink:#0f1720; --sa-ink-2:#6b7684; --sa-line:#e8eaee; --sa-card:#ffffff;
    --sa-amber:#f59e0b; --sa-red:#ef4444; --sa-blue:#3b82f6; --sa-green:#22c55e;
    font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;
  }
  #sa-app .sa-page-head{display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:16px; flex-wrap:wrap;}
  #sa-app .sa-page-head h1{font-size:19px; font-weight:800; margin:0 0 4px; color:var(--sa-ink);}
  #sa-app .sa-sub{font-size:12.5px; color:var(--sa-ink-2); margin:0;}
  #sa-app .sa-head-actions{display:flex; gap:8px; flex-wrap:wrap;}

  #sa-app .sa-tabs{display:flex; gap:4px; border-bottom:1px solid var(--sa-line); margin-bottom:22px;}
  #sa-app .sa-tab{padding:10px 16px; font-size:13px; font-weight:600; color:var(--sa-ink-2); text-decoration:none; border-bottom:2px solid transparent; margin-bottom:-1px;}
  #sa-app .sa-tab.active{color:var(--sa-teal-dark); border-bottom-color:var(--sa-teal);}
  #sa-app .sa-tab:hover:not(.active){color:var(--sa-ink);}

  #sa-app .sa-btn{display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; font-family:inherit; color:var(--sa-ink); background:#fff; border:1px solid var(--sa-line); border-radius:8px; padding:9px 14px; cursor:pointer; text-decoration:none; line-height:1;}
  #sa-app .sa-btn svg{width:15px; height:15px; flex-shrink:0;}
  #sa-app .sa-btn-primary{background:var(--sa-teal); border-color:var(--sa-teal); color:#fff;}
  #sa-app .sa-btn-primary svg{stroke:#fff;}
  #sa-app .sa-btn-sm{padding:6px 10px; font-size:11.5px;}
  #sa-app .sa-btn-danger{color:var(--sa-red); border-color:#f6c9c9;}

  #sa-app .sa-disclosure{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:14px; margin-bottom:22px; overflow:hidden;}
  #sa-app .sa-disclosure summary{list-style:none; cursor:pointer; padding:14px 20px; display:flex; align-items:center; gap:10px; font-weight:700; font-size:13.5px; color:var(--sa-ink);}
  #sa-app .sa-disclosure summary::-webkit-details-marker{display:none;}
  #sa-app .sa-plus-icon{width:20px; height:20px; border-radius:50%; background:var(--sa-teal); color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; flex-shrink:0;}
  #sa-app .sa-chevron{margin-left:auto; color:var(--sa-ink-2); transition:transform .15s ease;}
  #sa-app .sa-disclosure[open] .sa-chevron{transform:rotate(180deg);}
  #sa-app .sa-disclosure-body{padding:16px 20px 20px; border-top:1px solid var(--sa-line);}

  #sa-app .sa-form-grid{display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap;}
  #sa-app .sa-field{flex:1; min-width:160px; display:flex; flex-direction:column; gap:5px;}
  #sa-app .sa-field.sa-w-sm{flex:0 0 160px;}
  #sa-app .sa-field label{font-size:11.5px; color:var(--sa-ink-2); font-weight:600;}
  #sa-app .sa-field input, #sa-app .sa-field select, #sa-app .sa-field textarea{border:1px solid var(--sa-line); border-radius:8px; padding:9px 10px; font-size:13px; font-family:inherit; background:#fff; color:var(--sa-ink);}
  #sa-app .sa-field-error{color:var(--sa-red); font-size:11px; margin-top:2px;}
  #sa-app .sa-form-foot{display:flex; justify-content:flex-end; gap:8px; margin-top:4px;}

  #sa-app .sa-filters{display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap; align-items:center;}
  #sa-app .sa-filters input, #sa-app .sa-filters select{border:1px solid var(--sa-line); border-radius:8px; padding:7px 10px; font-size:12.5px; background:#fff; font-family:inherit; color:var(--sa-ink);}

  #sa-app .sa-table-wrap{background:var(--sa-card); border:1px solid var(--sa-line); border-radius:12px; overflow:hidden; overflow-x:auto;}
  #sa-app .sa-table{width:100%; border-collapse:collapse; font-size:12.5px;}
  #sa-app .sa-table th{text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:var(--sa-ink-2); padding:10px 16px; border-bottom:1px solid var(--sa-line); background:#fafbfc; white-space:nowrap;}
  #sa-app .sa-table td{padding:12px 16px; border-bottom:1px solid var(--sa-line); color:var(--sa-ink); vertical-align:middle;}
  #sa-app .sa-table tr:last-child td{border-bottom:none;}
  #sa-app .sa-cell-title{font-weight:600; color:var(--sa-ink);}
  #sa-app .sa-cell-muted{color:var(--sa-ink-2); font-size:12px;}

  #sa-app .sa-badge{padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap; display:inline-block;}
  #sa-app .sa-badge-gray{background:#eef0f3; color:#51586b;}
  #sa-app .sa-badge-green{background:#e7f9ee; color:var(--sa-green);}

  #sa-app .sa-row-actions{display:flex; gap:6px; justify-content:flex-end;}
  #sa-app .sa-icon-btn{width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--sa-line); border-radius:7px; background:#fff; color:var(--sa-ink-2); cursor:pointer; text-decoration:none;}
  #sa-app .sa-icon-btn:hover{color:var(--sa-ink); border-color:#d6dae0;}
  #sa-app .sa-icon-btn.sa-icon-danger:hover{color:var(--sa-red); border-color:#f6c9c9;}
  #sa-app .sa-icon-btn svg{width:14px; height:14px;}

  #sa-app .sa-empty{text-align:center; padding:34px 20px;}
  #sa-app .sa-empty-icon{width:44px; height:44px; margin:0 auto 10px; border-radius:12px; background:#eef0f3; color:var(--sa-ink-2); display:flex; align-items:center; justify-content:center;}
  #sa-app .sa-empty-icon svg{width:22px; height:22px;}
  #sa-app .sa-empty-title{font-size:12.5px; color:var(--sa-ink-2);}
</style>

<div id="sa-app">

  <div class="sa-page-head">
    <div>
      <h1>Suivi d'activité — Équipe</h1>
      <p class="sa-sub">Gestion complète des projets de l'entreprise.</p>
    </div>
    <div class="sa-head-actions">
      <a href="<?php echo e(route('activites.admin.projects.export-pdf', request()->query())); ?>" class="sa-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export PDF
      </a>
      <a href="<?php echo e(route('activites.admin.projects.export-excel', request()->query())); ?>" class="sa-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export Excel
      </a>
      <button type="button" class="sa-btn sa-btn-primary" onclick="document.getElementById('sa-new-project').open = true; document.getElementById('sa-new-project').scrollIntoView({behavior:'smooth'});">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        Nouveau projet
      </button>
    </div>
  </div>

  <div class="sa-tabs">
    <a href="<?php echo e(route('activites.admin.dashboard')); ?>" class="sa-tab <?php echo e(request()->routeIs('activites.admin.dashboard') ? 'active' : ''); ?>">État d'avancement</a>
    <a href="<?php echo e(route('activites.admin.projects.index')); ?>" class="sa-tab <?php echo e(request()->routeIs('activites.admin.projects.*') ? 'active' : ''); ?>">Projets</a>
    <a href="<?php echo e(route('activites.admin.tasks.index')); ?>" class="sa-tab <?php echo e(request()->routeIs('activites.admin.tasks.*') ? 'active' : ''); ?>">Tâches</a>
  </div>

  <details id="sa-new-project" class="sa-disclosure">
    <summary>
      <span class="sa-plus-icon">+</span>
      Créer un projet
      <svg class="sa-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
    </summary>
    <div class="sa-disclosure-body">
      <form method="POST" action="<?php echo e(route('activites.admin.projects.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Nom du projet</label>
            <input type="text" name="name" value="<?php echo e(old('name')); ?>"  required>
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
                <option value="<?php echo e($status); ?>" <?php if(old('status') === $status): echo 'selected'; endif; ?>><?php echo e(\App\Models\Project::STATUS_LABELS[$status]); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
        </div>
        <div class="sa-form-grid">
          <div class="sa-field">
            <label>Description</label>
            <textarea name="description" rows="2" ><?php echo e(old('description')); ?></textarea>
          </div>
        </div>
        <div class="sa-form-foot">
          <button type="submit" class="sa-btn sa-btn-primary">Créer le projet</button>
        </div>
      </form>
    </div>
  </details>

  <form method="GET" class="sa-filters">
    <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Rechercher un projet...">
    <select name="status" onchange="this.form.submit()">
      <option value="">Tous les statuts</option>
      <?php $__currentLoopData = \App\Models\Project::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($status); ?>" <?php if(request('status') === $status): echo 'selected'; endif; ?>><?php echo e(\App\Models\Project::STATUS_LABELS[$status]); ?></option>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" title="Créé depuis le">
    <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" title="Créé jusqu'au">
    <button type="submit" class="sa-btn sa-btn-sm">Filtrer</button>
    <?php if(request()->anyFilled(['q','status','date_from','date_to'])): ?>
      <a href="<?php echo e(route('activites.admin.projects.index')); ?>" class="sa-btn sa-btn-sm">Réinitialiser</a>
    <?php endif; ?>
  </form>

  <?php if($projects->isEmpty()): ?>
    <div class="sa-table-wrap">
      <div class="sa-empty">
        <div class="sa-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4zm0 0v10l9 4 9-4V7"/></svg></div>
        <div class="sa-empty-title">Aucun projet ne correspond à ces filtres.</div>
      </div>
    </div>
  <?php else: ?>
    <div class="sa-table-wrap">
      <table class="sa-table">
        <thead>
          <tr>
            <th>Projet</th>
            <th>Statut</th>
            <th>Description du projet</th>
            <th>Créé le</th>
            <th style="width:120px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
              <td class="sa-cell-title"><?php echo e($project->name); ?></td>
              <td><span class="sa-badge <?php echo e($project->statusBadgeClass()); ?>"><?php echo e($project->statusLabel()); ?></span></td>
              <td class="sa-cell-muted">
                <?php if($project->description): ?>
                  <?php echo e(\Illuminate\Support\Str::limit($project->description, 90)); ?>

                <?php else: ?>
                  <em>Aucune description</em>
                <?php endif; ?>
              </td>
              <td class="sa-cell-muted"><?php echo e($project->created_at->format('d/m/Y')); ?></td>
              <td>
                <div class="sa-row-actions">
                  <a href="<?php echo e(route('activites.admin.projects.show', $project)); ?>" class="sa-icon-btn" title="Voir">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                  </a>
                  <a href="<?php echo e(route('activites.admin.projects.show', $project)); ?>#sa-edit-project" class="sa-icon-btn" title="Modifier">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </a>
                  <form method="POST" action="<?php echo e(route('activites.admin.projects.destroy', $project)); ?>" onsubmit="return confirm('Supprimer ce projet et toutes ses tâches ?');">
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
    <div style="margin-top:16px;"><?php echo e($projects->links()); ?></div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/activites/admin/projects/index.blade.php ENDPATH**/ ?>