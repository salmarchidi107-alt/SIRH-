
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Formations — Export PDF</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #111827; background: #fff; }

    .header {
        padding: 18px 24px 14px;
        border-bottom: 2px solid #1D9E75;
        display: flex; justify-content: space-between; align-items: flex-start;
    }
    .header-logo { font-size: 17px; font-weight: 700; color: #1D9E75; }
    .header-sub  { font-size: 10px; color: #6b7280; margin-top: 3px; }
    .header-right { text-align: right; font-size: 10px; color: #6b7280; }

    .content { padding: 14px 24px; }
    h2 { font-size: 13px; font-weight: 600; margin-bottom: 10px; color: #111827; }



    /* Main table */
    table.main { width: 100%; border-collapse: collapse; }
    table.main thead th {
        padding: 7px 8px;
        font-size: 9px; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.05em;
        color: #6b7280; background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
    }
    table.main tbody td {
        padding: 8px 8px;
        font-size: 10px; color: #374151;
        border-bottom: 0.5px solid #f3f4f6;
        vertical-align: middle;
    }
    table.main tbody tr:last-child td { border-bottom: none; }
    table.main tbody tr:nth-child(even) td { background: #fafafa; }

    .badge {
        display: inline-block; padding: 2px 7px;
        border-radius: 10px; font-size: 9px; white-space: nowrap;
    }
    .badge-planifiee { background: #E1F5EE; color: #085041; }
    .badge-encours   { background: #FAEEDA; color: #BA7517; }
    .badge-terminee  { background: #EAF3DE; color: #3B6D11; }
    .badge-annulee   { background: #FCEBEB; color: #A32D2D; }

    .footer {
        margin-top: 18px; padding-top: 8px;
        border-top: 0.5px solid #e5e7eb;
        font-size: 9px; color: #9ca3af; text-align: center;
    }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="header-logo">LMS — Gestion des Formations</div>
        <div class="header-sub">Rapport exporté le <?php echo e(now()->locale('fr')->translatedFormat('d F Y \à H:i')); ?></div>
    </div>
    <div class="header-right">
        Total : <?php echo e($formations->count()); ?> formation(s)
    </div>
</div>

<div class="content">
    <h2>Liste des formations</h2>


    <table class="main">
        <thead>
            <tr>
                <th style="width:16%">Employé</th>
                <th style="width:11%">Département</th>
                <th style="width:17%">Formation</th>
                <th style="width:14%">Formateur</th>
                <th style="width:14%">Organisme</th>
                <th style="width:10%">Date</th>
                <th style="width:10%">Horaire</th>
                <th style="width:8%">Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $formations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $emp = $f->employee;

                // Nom complet — adapté à toutes structures de colonnes
                $prenom   = $emp->prenom      ?? $emp->first_name ?? '';
                $nom      = $emp->nom          ?? $emp->last_name  ?? $emp->name ?? '';
                $nomComplet = trim("$prenom $nom") ?: '—';

                // Département — injecté par le controller via setAttribute
                $deptNom  = $emp?->getAttribute('dept_name') ?? $emp?->dept_name ?? '—';

                // Badge statut
                $badgeClass = match($f->statut) {
                    'Planifiée' => 'badge-planifiee',
                    'En cours'  => 'badge-encours',
                    'Terminée'  => 'badge-terminee',
                    'Annulée'   => 'badge-annulee',
                    default     => 'badge-planifiee',
                };
            ?>
            <tr>
                <td><?php echo e($nomComplet); ?></td>
                <td><?php echo e($deptNom); ?></td>
                <td><?php echo e($f->titre); ?></td>
                <td><?php echo e($f->formateur); ?></td>
                <td><?php echo e($f->organisme); ?></td>
                <td style="white-space:nowrap;"><?php echo e($f->date->format('d/m/Y')); ?></td>
                <td style="white-space:nowrap;font-variant-numeric:tabular-nums;"><?php echo e($f->horaire); ?></td>
                <td><span class="badge <?php echo e($badgeClass); ?>"><?php echo e($f->statut); ?></span></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">
                    Aucune formation enregistrée
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="footer">
    Document généré par le module LMS — Masaha Gestion RH
</div>

</body>
</html>
<?php /**PATH D:\Projects\SIRH-\resources\views/lms/pdf.blade.php ENDPATH**/ ?>