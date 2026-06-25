<?php $__env->startSection('title', 'Fiche équipements — ' . $employee->first_name . ' ' . $employee->last_name); ?>
<?php $__env->startSection('page-title', 'Fiche patrimoine salarié'); ?>

<?php $__env->startSection('content'); ?>

<style>
.eq-card {
 background: #fff;
 border-radius: 12px;
 padding: 20px;
 margin-bottom: 16px;
 box-shadow: 0 1px 3px rgba(0,0,0,.06);
 border: 1px solid #f1f5f9;
}
.eq-card-title {
 font-size: 13px;
 font-weight: 600;
 color: #374151;
 margin-bottom: 16px;
 display: flex;
 align-items: center;
 gap: 7px;
}
.eq-card-title i { color: #14b8a6; font-size: 15px; }
.eq-stat-card {
 background: #fff;
 border-radius: 12px;
 padding: 16px 18px;
 box-shadow: 0 1px 3px rgba(0,0,0,.06);
 border: 1px solid #f1f5f9;
 position: relative;
 overflow: hidden;
}
.eq-stat-card::before {
 content: '';
 position: absolute;
 top: 0; left: 0; right: 0;
 height: 3px;
 border-radius: 12px 12px 0 0;
}
.eq-stat-card.teal::before { background: #14b8a6; }
.eq-stat-card.blue::before { background: #3b82f6; }
.eq-stat-card.green::before { background: #22c55e; }
.eq-stat-card.amber::before { background: #f59e0b; }
.eq-stat-card .stat-label { font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 6px; }
.eq-stat-card .stat-val { font-size: 24px; font-weight: 700; color: #0f172a; line-height: 1; }
.eq-grid4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 20px; }
.eq-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.eq-table th {
 font-size: 11px; font-weight: 600; color: #6b7280;
 text-transform: uppercase; letter-spacing: .04em;
 padding: 10px 12px; border-bottom: 1px solid #f1f5f9;
 text-align: left; white-space: nowrap; background: #fafafa;
}
.eq-table td {
 padding: 10px 12px;
 border-bottom: 1px solid #f8fafc;
 color: #1e293b; vertical-align: middle;
}
.eq-table tbody tr:last-child td { border-bottom: none; }
.eq-table tbody tr:hover td { background: #f8fafc; }
/* Badges */
.t-blue { background:#eff6ff; color:#1d4ed8; border-radius:99px; padding:3px 9px; font-size:11px; font-weight:600; display:inline-flex; align-items:center; gap:3px; }
.t-green { background:#f0fdf4; color:#166534; border-radius:99px; padding:3px 9px; font-size:11px; font-weight:600; display:inline-flex; align-items:center; gap:3px; }
.t-amber { background:#fffbeb; color:#92400e; border-radius:99px; padding:3px 9px; font-size:11px; font-weight:600; display:inline-flex; align-items:center; gap:3px; }
.t-red { background:#fef2f2; color:#991b1b; border-radius:99px; padding:3px 9px; font-size:11px; font-weight:600; display:inline-flex; align-items:center; gap:3px; }
.t-gray { background:#f8fafc; color:#475569; border-radius:99px; padding:3px 9px; font-size:11px; font-weight:600; display:inline-flex; align-items:center; gap:3px; }
.t-teal { background:#f0fdfa; color:#0f766e; border-radius:99px; padding:3px 9px; font-size:11px; font-weight:600; display:inline-flex; align-items:center; gap:3px; }
.mono { font-family: 'Courier New', monospace; font-size: 11px; }
.eq-avatar {
 border-radius: 50%;
 background: linear-gradient(135deg, #14b8a6, #0ea5e9);
 display: flex; align-items: center; justify-content: center;
 font-weight: 700; color: #fff; flex-shrink: 0;
}
@media (max-width: 768px) { .eq-grid4 { grid-template-columns: 1fr 1fr; } }
</style>


<div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;font-size:13px">
 <a href="<?php echo e(route('equipements.index')); ?>"
 style="color:#14b8a6;text-decoration:none;font-weight:500;display:flex;align-items:center;gap:4px">
 Équipements
 </a>

 <a href="<?php echo e(route('equipements.index', ['tab' => 'salarie'])); ?>"
 style="color:#64748b;text-decoration:none">Fiche salarié</a>

 <span style="color:#0f172a;font-weight:500"><?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?></span>
</div>


<div class="eq-card" style="margin-bottom:20px">
 <div style="display:flex;align-items:center;gap:16px;margin-bottom:18px">
 <?php
 $initiales = mb_strtoupper(
 mb_substr($employee->first_name ?? 'X', 0, 1) .
 mb_substr($employee->last_name ?? 'X', 0, 1)
 );
 $isActive = in_array($employee->status ?? '', ['active', 'Actif', '1']);
 ?>
 <div class="eq-avatar" style="width:54px;height:54px;font-size:18px"><?php echo e($initiales); ?></div>
 <div style="flex:1">
 <div style="font-size:18px;font-weight:700;color:#0f172a;margin-bottom:4px">
 <?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?>

 </div>
 <div style="font-size:13px;color:#64748b;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
 <span><?php echo e($employee->position ?? $employee->poste ?? '—'); ?></span>
 <?php if($employee->department ?? $employee->departement ?? ''): ?>
 <span style="color:#cbd5e1">·</span>
 <span><?php echo e($employee->department ?? $employee->departement); ?></span>
 <?php endif; ?>
 <?php if($employee->employee_number ?? $employee->matricule ?? ''): ?>
 <span style="color:#cbd5e1">·</span>
 <span class="mono" style="font-size:12px"><?php echo e($employee->employee_number ?? $employee->matricule); ?></span>
 <?php endif; ?>
 </div>
 </div>
 <div style="display:flex;gap:8px;align-items:center">
 <?php if($isActive): ?>
 <span style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;border-radius:99px;padding:4px 12px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:5px">
 Actif
 </span>
 <?php else: ?>
 <span style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:99px;padding:4px 12px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:5px">
 Inactif
 </span>
 <?php endif; ?>
 <a href="<?php echo e(route('equipements.index', ['tab' => 'affecter', 'employee_id' => $employee->id])); ?>"
 style="height:36px;padding:0 16px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;text-decoration:none">
 Affecter matériel
 </a>
 </div>
 </div>

 
 <div class="eq-grid4">
 <div class="eq-stat-card blue">
 <div class="stat-label">Équipements actuels</div>
 <div class="stat-val"><?php echo e($metrics_salarie['equipements_actuels']); ?></div>
 </div>
 <div class="eq-stat-card teal">
 <div class="stat-label">Valeur confiée</div>
 <div class="stat-val" style="font-size:18px"><?php echo e(number_format($metrics_salarie['valeur_confiee'], 0, ',', ' ')); ?> <span style="font-size:12px;font-weight:500;color:#64748b">MAD</span></div>
 </div>
 <div class="eq-stat-card amber">
 <div class="stat-label">Dernière affectation</div>
 <div class="stat-val" style="font-size:16px"><?php echo e(optional($metrics_salarie['derniere_affectation'])->format('d/m/Y') ?? '—'); ?></div>
 </div>
 <div class="eq-stat-card green">
 <div class="stat-label">Décharges signées</div>
 <div class="stat-val"><?php echo e($metrics_salarie['decharges_signees']); ?></div>
 </div>
 </div>
</div>


<div class="eq-card">
 <div class="eq-card-title"> Patrimoine confié — équipements actuels</div>
 <div style="overflow-x:auto">
 <table class="eq-table" style="min-width:700px">
 <thead>
 <tr>
 <th>Référence</th>
 <th>Équipement</th>
 <th>Catégorie</th>
 <th>Affecté le</th>
 <th>État remise</th>
 <th style="text-align:right">Valeur (MAD)</th>
 <th>Décharge</th>
 <th style="text-align:center">Actions</th>
 </tr>
 </thead>
 <tbody>
 <?php $__empty_1 = true; $__currentLoopData = $affectations_actives; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
 <tr>
 <td class="mono" style="font-weight:600;color:#0f172a"><?php echo e($aff->equipement->reference ?? '—'); ?></td>
 <td style="font-weight:500"><?php echo e($aff->equipement->designation ?? '—'); ?></td>
 <td>
 <?php if($aff->equipement): ?>
 <span class="<?php echo e($aff->equipement->categorie_color ?? 't-gray'); ?>"><?php echo e($aff->equipement->categorie); ?></span>
 <?php else: ?> —
 <?php endif; ?>
 </td>
 <td style="color:#64748b;font-size:12px"><?php echo e(optional($aff->date_affectation)->format('d/m/Y')); ?></td>
 <td>
 <?php if($aff->equipement): ?>
 <span class="<?php echo e($aff->equipement->etat_color ?? 't-gray'); ?>"><?php echo e($aff->etat_remise); ?></span>
 <?php else: ?> —
 <?php endif; ?>
 </td>
 <td style="text-align:right;font-weight:600"><?php echo e(number_format($aff->equipement->valeur_acquisition ?? 0, 0, ',', ' ')); ?></td>
 <td>
 <?php if($aff->decharge_signee): ?>
 <span class="t-green"> Signée</span>
 <?php else: ?>
 <span class="t-amber">En attente</span>
 <?php endif; ?>
 </td>
 <td style="text-align:center">
 <div style="display:flex;gap:5px;justify-content:center">
 <a href="<?php echo e(route('equipements.index', ['tab' => 'decharge'])); ?>"
 title="Voir décharge"
 style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:7px;background:#f8fafc;color:#64748b;text-decoration:none;font-size:12px;border:1px solid #e2e8f0">
 <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>

 </a>
 <a href="<?php echo e(route('equipements.index', ['tab' => 'retour'])); ?>"
 title="Retour"
 style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:7px;background:#fef2f2;color:#991b1b;text-decoration:none;font-size:12px;border:1px solid #fecaca">
<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
</svg>
 </a>
 </div>
 </td>
 </tr>
 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
 <tr>
 <td colspan="8" style="text-align:center;color:#64748b;padding:28px;font-size:13px">

 Aucun équipement affecté actuellement
 </td>
 </tr>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
</div>


<div class="eq-card">
 <div class="eq-card-title"> Historique complet des affectations</div>
 <div style="overflow-x:auto">
 <table class="eq-table" style="min-width:580px">
 <thead>
 <tr>
 <th>Date</th>
 <th>Action</th>
 <th>Matériel</th>
 <th>État</th>
 <th>Note</th>
 </tr>
 </thead>
 <tbody>
 <?php $__empty_1 = true; $__currentLoopData = $historique; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
 <tr>
 <td style="color:#64748b;font-size:12px"><?php echo e(optional($h->date_affectation)->format('d/m/Y')); ?></td>
 <td>
 <?php if($h->statut === 'Restitué'): ?>
 <span class="t-amber">Restitution</span>
 <?php elseif($h->statut === 'Perdu'): ?>
 <span class="t-red">Perte déclarée</span>
 <?php else: ?>
 <span class="t-teal">Attribution</span>
 <?php endif; ?>
 </td>
 <td>
 <span style="font-weight:500"><?php echo e($h->equipement->designation ?? '—'); ?></span>
 <span class="mono" style="color:#94a3b8;margin-left:4px"><?php echo e($h->equipement->reference ?? ''); ?></span>
 </td>
 <td>
 <?php if($h->equipement): ?>
 <span class="<?php echo e($h->equipement->etat_color ?? 't-gray'); ?>"><?php echo e($h->etat_remise); ?></span>
 <?php else: ?> —
 <?php endif; ?>
 </td>
 <td style="color:#64748b;font-size:12px"><?php echo e($h->observations ?? '—'); ?></td>
 </tr>
 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
 <tr>
 <td colspan="5" style="text-align:center;color:#64748b;padding:24px">Aucun historique disponible</td>
 </tr>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
</div>

<div style="margin-top:4px">
 <a href="<?php echo e(route('equipements.index', ['tab' => 'salarie'])); ?>"
 style="font-size:13px;color:#14b8a6;text-decoration:none;font-weight:500;display:inline-flex;align-items:center;gap:5px">
 Retour à la liste
 </a>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/equipements/fiche_salarie.blade.php ENDPATH**/ ?>