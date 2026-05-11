<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<style>
  * { margin: 10px;; padding:0;4; box-sizing:border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size:9px; color:#1e293b; }

  /* ── En-tête ── */
  .header {
    border-bottom: 2px solid #0f2132;
    padding-bottom: 12px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
  }
  .header-left h1 { font-size:15px; font-weight:700; color:#0f2132; margin-bottom:3px; }
  .header-left p  { font-size:8px; color:#64748b; }
  .header-right   { text-align:right; font-size:8px; color:#64748b; line-height:1.6; }
  .header-right strong { color:#0f2132; font-size:10px; }

  /* ── Tableau ── */
  table { width:100%; border-collapse:collapse; margin-top:4px; }

  thead tr th {
    background:#0f2132;
    color:white;
    padding:6px 8px;
    text-align:left;
    font-size:8px;
    font-weight:700;
    letter-spacing:.04em;
    text-transform:uppercase;
  }

  tbody td {
    padding:6px 8px;
    border-bottom:1px solid #e2e8f0;
    font-size:9px;
    vertical-align:middle;
  }

  tbody tr:nth-child(even) td { background:#f8fafc; }

  /* ── Séparateur département ── */
  .dept-row td {
    background:#f1f5f9;
    color:#0f2132;
    font-weight:700;
    font-size:9px;
    padding:5px 8px;
    border-top: 1px solid #cbd5e1;
    border-bottom: 1px solid #cbd5e1;
    letter-spacing:.03em;
    text-transform:uppercase;
  }

  /* ── Badges statut ── */
  .pill {
    display:inline-block;
    padding:2px 7px;
    border-radius:20px;
    font-size:8px;
    font-weight:700;
  }
  .pill-active   { background:#dcfce7; color:#15803d; }
  .pill-inactive { background:#fee2e2; color:#b91c1c; }
  .pill-leave    { background:#fef9c3; color:#a16207; }

  /* ── Pied de page ── */
  .footer {
    margin-top:20px;
    padding-top:8px;
    border-top:1px solid #e2e8f0;
    display:flex;
    justify-content:space-between;
    font-size:8px;
    color:#94a3b8;
  }
</style>
</head>
<body>


<div class="header">
  <div class="header-left">
    <h1>Liste des Employés</h1>
    <p>HospitalRH — Système de gestion des ressources humaines</p>
  </div>
  <div class="header-right">
    <strong><?php echo e($total); ?> employé<?php echo e($total > 1 ? 's' : ''); ?></strong><br>
    Généré le <?php echo e($generatedAt); ?>

  </div>
</div>


<?php $currentDept = null; ?>
<table>
  <thead>
    <tr>
      <th>Matricule</th>
      <th>Nom complet</th>
      <th>Poste</th>
      <th>Département</th>
      <th>Email</th>
      <th>Téléphone</th>
      <th>Statut</th>
    </tr>
  </thead>
  <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

      
      <?php if($emp->department !== $currentDept): ?>
        <?php $currentDept = $emp->department; ?>
        <tr class="dept-row">
          <td colspan="7"><?php echo e($emp->department ?? 'Sans département'); ?></td>
        </tr>
      <?php endif; ?>

      <tr>
        <td><strong><?php echo e($emp->matricule ?? '—'); ?></strong></td>
        <td><?php echo e($emp->first_name); ?> <?php echo e($emp->last_name); ?></td>
        <td><?php echo e($emp->position ?? '—'); ?></td>
        <td><?php echo e($emp->department ?? '—'); ?></td>
        <td><?php echo e($emp->email ?? '—'); ?></td>
        <td><?php echo e($emp->phone ?? '—'); ?></td>
        <td>
          <?php $s = $emp->status ?? 'active'; ?>
          <span class="pill <?php echo e($s === 'active' ? 'pill-active' : ($s === 'leave' ? 'pill-leave' : 'pill-inactive')); ?>">
            <?php echo e($s === 'active' ? 'Actif' : ($s === 'leave' ? 'Congé' : 'Inactif')); ?>

          </span>
        </td>
      </tr>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr>
        <td colspan="7" style="text-align:center;padding:20px;color:#94a3b8;">
          Aucun employé trouvé.
        </td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>


<div class="footer">
  <span>HospitalRH — Document confidentiel</span>
  <span>Total : <?php echo e($total); ?> employé<?php echo e($total > 1 ? 's' : ''); ?></span>
</div>

</body>
</html><?php /**PATH C:\Users\HP\SIRH-\resources\views/pdf/employees.blade.php ENDPATH**/ ?>