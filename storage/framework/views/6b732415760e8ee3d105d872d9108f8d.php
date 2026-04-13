<?php $__env->startSection('title', 'Tenants'); ?>
<?php $__env->startSection('page-title', 'Gestion des tenants'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="sa-page-title">Gestion des tenants</div>
    <div class="sa-page-sub"><?php echo e($counts['all']); ?> tenant(s) sur la plateforme</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-actions'); ?>
    <a href="<?php echo e(route('superadmin.tenants.create')); ?>" class="sa-btn sa-btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouveau tenant
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:20px;">
<?php $__currentLoopData = [
    ['Total',     $counts['all'],       'var(--text)'],
    ['Actifs',    $counts['active'],    'var(--success)'],
    ['Essai',     $counts['trial'],     'var(--info)'],
    ['Suspendus', $counts['suspended'], 'var(--warning)'],
    ['Inactifs',  $counts['inactive'],  'var(--text-muted)'],
]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$lbl, $val, $col]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="sa-stat" style="padding:13px 15px;">
        <div class="sa-stat-val" style="font-size:22px;color:<?php echo e($col); ?>;"><?php echo e($val); ?></div>
        <div class="sa-stat-lbl"><?php echo e($lbl); ?></div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<form method="GET" action="<?php echo e(route('superadmin.tenants.index')); ?>"
      style="display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap;">

    <div class="sa-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input type="text" name="search" placeholder="Rechercher…"
               value="<?php echo e(request('search')); ?>" oninput="this.form.submit()">
    </div>

    <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <?php $__currentLoopData = [
            '' => 'Tous ('.$counts['all'].')',
            'active' => 'Actifs', 'trial' => 'Essai',
            'suspended' => 'Suspendus', 'inactive' => 'Inactifs',
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('superadmin.tenants.index', array_merge(request()->except('status','page'), $val ? ['status'=>$val] : []))); ?>"
           class="sa-pill <?php echo e(request('status','') === $val ? 'on' : ''); ?>"><?php echo e($lbl); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <select name="sort" onchange="this.form.submit()"
            style="margin-left:auto;font-size:12px;background:var(--surface);border:1.5px solid var(--border);border-radius:8px;padding:6px 12px;color:var(--text);cursor:pointer;outline:none;font-family:inherit;">
        <option value="created_at" <?php echo e(request('sort','created_at')==='created_at'?'selected':''); ?>>Trier : Date</option>
        <option value="name"       <?php echo e(request('sort')==='name'?'selected':''); ?>>Trier : Nom</option>
        <option value="users"      <?php echo e(request('sort')==='users'?'selected':''); ?>>Trier : Utilisateurs</option>
    </select>
</form>


<?php if($tenants->isEmpty()): ?>
    <div class="sa-card" style="padding:60px;text-align:center;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-light)" stroke-width="1.5"
             style="margin:0 auto 14px;display:block;">
            <rect x="2" y="7" width="20" height="14" rx="2"/>
            <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
        </svg>
        <div style="font-size:14px;font-weight:600;color:var(--text-muted);">Aucun tenant trouvé</div>
        <a href="<?php echo e(route('superadmin.tenants.create')); ?>" class="sa-btn sa-btn-primary" style="margin-top:14px;display:inline-flex;">
            Créer le premier tenant
        </a>
    </div>
<?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
    <?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $sto = $t->storage_usage;
        $api = $t->api_usage;
        $stoC = $sto > 80 ? 'var(--danger)' : ($sto > 60 ? 'var(--warning)' : 'var(--success)');
        $apiC = $api > 80 ? 'var(--danger)' : ($api > 60 ? 'var(--warning)' : 'var(--success)');
$sc = match(($t->status?->value ?? 'inactive')) {
            'active'    => 'sa-badge-active',
            'suspended' => 'sa-badge-suspended',
            'trial'     => 'sa-badge-trial',
            default     => 'sa-badge-inactive',
        };
$pc = match(($t->plan?->value ?? 'starter')) {
            'enterprise' => 'sa-badge-ent',
            'pro'        => 'sa-badge-pro',
            default      => 'sa-badge-starter',
        };
    ?>
    <div class="sa-tenant-card">
        
        <div style="padding:14px 16px 11px;display:flex;align-items:flex-start;gap:12px;border-bottom:1px solid var(--border-light);">
            <div style="width:42px;height:42px;border-radius:12px;background:<?php echo e($t->brand_color); ?>;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:#fff;flex-shrink:0;box-shadow:0 4px 12px <?php echo e($t->brand_color); ?>44;">
                <?php echo e($t->initials); ?>

            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700;font-size:14px;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo e($t->name); ?></div>
                <div style="font-size:11px;color:var(--text-muted);font-family:monospace;margin-top:1px;"><?php echo e($t->domain); ?></div>
            </div>
            <div style="display:flex;gap:4px;flex-shrink:0;">
                <a href="<?php echo e(route('superadmin.tenants.edit', $t)); ?>"
                   class="sa-btn sa-btn-ghost sa-btn-sm" style="padding:4px 8px;" title="Modifier">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </a>
                <form method="POST" action="<?php echo e(route('superadmin.tenants.destroy', $t)); ?>"
                      onsubmit="return confirm('Supprimer <?php echo e(addslashes($t->name)); ?> ?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm" style="padding:4px 8px;" title="Supprimer">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        
        <div style="padding:12px 16px;">
            
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:12px;">
                <div style="background:var(--surface-2);border-radius:8px;padding:8px 10px;">
                    <div style="font-size:16px;font-weight:800;color:var(--text);"><?php echo e($t->users_count); ?></div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:1px;font-weight:600;">Utilisateurs</div>
                </div>
                <div style="background:var(--surface-2);border-radius:8px;padding:8px 10px;">
                    <div style="font-size:16px;font-weight:800;color:<?php echo e($stoC); ?>;"><?php echo e($sto); ?>%</div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:1px;font-weight:600;">Stockage</div>
                </div>
                <div style="background:var(--surface-2);border-radius:8px;padding:8px 10px;">
                    <div style="font-size:16px;font-weight:800;color:<?php echo e($apiC); ?>;"><?php echo e($api); ?>%</div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:1px;font-weight:600;">API</div>
                </div>
            </div>

            
            <div style="margin-bottom:12px;">
                <?php $__currentLoopData = [['Stockage', $sto, $stoC], ['API', $api, $apiC]]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$lbl2, $pct, $col2]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
                    <span style="font-size:10px;color:var(--text-muted);width:52px;font-weight:600;"><?php echo e($lbl2); ?></span>
                    <div class="sa-usage-bar"><div class="sa-usage-fill" style="width:<?php echo e($pct); ?>%;background:<?php echo e($col2); ?>;"></div></div>
                    <span style="font-size:10px;color:var(--text-muted);width:28px;text-align:right;"><?php echo e($pct); ?>%</span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                <span class="sa-badge <?php echo e($sc); ?>">
                    <span style="width:5px;height:5px;border-radius:50%;background:currentColor;"></span>
<?php echo e($t->status?->label() ?? 'Inconnu'); ?>

                </span>
<?php echo e($t->plan?->label() ?? 'Starter'); ?>


<?php if(($t->status?->value ?? '') === 'active'): ?>
                    <form method="POST" action="<?php echo e(route('superadmin.tenants.suspend', $t)); ?>" style="margin-left:auto;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" style="font-size:11px;font-weight:600;color:var(--warning);background:none;border:none;cursor:pointer;font-family:inherit;">Suspendre</button>
                    </form>
<?php elseif(($t->status?->value ?? '') === 'suspended'): ?>
                    <form method="POST" action="<?php echo e(route('superadmin.tenants.reactivate', $t)); ?>" style="margin-left:auto;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" style="font-size:11px;font-weight:600;color:var(--success);background:none;border:none;cursor:pointer;font-family:inherit;">Réactiver</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div style="margin-top:20px;"><?php echo e($tenants->links()); ?></div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/superadmin/tenants/stats.blade.php ENDPATH**/ ?>