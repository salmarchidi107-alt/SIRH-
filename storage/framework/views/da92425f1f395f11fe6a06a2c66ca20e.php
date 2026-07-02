<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 22px 18px; }
    * { box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; color:#000; font-size:10px; }

    .header { border-bottom: 2px solid #000; padding-bottom:8px; margin-bottom:10px;
               display:flex; justify-content:space-between; align-items:flex-end; }
    .header h1 { font-size:16px; margin:0; }
    .header .sub { font-size:10px; color:#333; margin-top:2px; }
    .header .meta { text-align:right; font-size:9px; color:#333; }

    .filters { font-size:9px; color:#333; margin-bottom:10px; }

    table { width:100%; border-collapse: collapse; margin-bottom:14px; }
    th, td { border:1px solid #000; padding:4px 6px; text-align:left; }
    th { background:#e8e8e8; font-size:9px; text-transform:uppercase; letter-spacing:.03em; }
    td { font-size:9.5px; }
    tr.absent td { color:#555; font-style:italic; }

    .badge { display:inline-block; padding:1px 5px; border:1px solid #000; border-radius:3px; font-size:8.5px; }
    .badge-garde { font-weight:bold; }

    .stats { display:flex; gap:24px; margin-bottom:12px; font-size:10px; }
    .stats strong { font-size:12px; }

    .summary-title { font-size:12px; font-weight:bold; margin:16px 0 6px; border-bottom:1px solid #000; padding-bottom:3px; }

    .footer { position: fixed; bottom: -10px; left:0; right:0; text-align:center; font-size:8px; color:#666; border-top:1px solid #ccc; padding-top:4px; }
</style>
</head>
<body>
    <div class="header">
    <div>
        <h1><?php echo e($tenant?->name ?? config('app.name')); ?></h1>
        <div class="sub">Rapport de pointage</div>
        <div class="sub"><?php echo e($periodeLabel); ?></div>
    </div>

    <div class="meta">
        Généré le <?php echo e($generatedAt); ?><br>
        <?php echo e($filterInfo); ?>

    </div>
</div>

    <?php if($summary): ?>
        <div class="summary-title">Recapitulatif par employe</div>
        <table>
            <thead>
                <tr>
                    <th>Employe</th>
                    <th>Departement</th>
                    <th style="width:70px;">Jours travailles</th>
                    <th style="width:70px;">Absences</th>
                    <th style="width:80px;">Total heures</th>
                </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $summary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($s['nom']); ?></td>
                    <td><?php echo e($s['department']); ?></td>
                    <td style="text-align:center;"><?php echo e($s['jours']); ?></td>
                    <td style="text-align:center;"><?php echo e($s['absences']); ?></td>
                    <td style="text-align:center;"><?php echo e(number_format($s['total_heures'], 1)); ?> h</td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <?php if($periode !== 'jour'): ?><th style="width:70px;">Date</th><?php endif; ?>
                <th>Employe</th>
                <th>Departement</th>
                <th style="width:55px;">Shift</th>
                <th style="width:45px;">Entree</th>
                <th style="width:45px;">Sortie</th>
                <th style="width:55px;">Total</th>
                <th style="width:55px;">Statut</th>
                <th style="width:35px;">Valide</th>
            </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="<?php echo e(in_array($r['statut'], ['absent','absence_injustifiee']) ? 'absent' : ''); ?>">
                <?php if($periode !== 'jour'): ?><td><?php echo e($r['date_label']); ?></td><?php endif; ?>
                <td><?php echo e($r['nom']); ?></td>
                <td><?php echo e($r['department']); ?></td>
                <td>
                    <span class="badge <?php echo e($r['shift_type'] === 'garde' ? 'badge-garde' : ''); ?>">
                        <?php echo e($r['shift_type'] === 'garde' ? 'Garde' : 'Normal'); ?>

                    </span>
                </td>
                <td><?php echo e($r['heure_entree'] ? \Carbon\Carbon::parse($r['heure_entree'])->format('H:i') : '-'); ?></td>
                <td><?php echo e($r['heure_sortie'] ? \Carbon\Carbon::parse($r['heure_sortie'])->format('H:i') : '-'); ?></td>
                <td><?php echo e($r['total_heures']); ?></td>
                <td>
                    <?php switch($r['statut']):
                        case ('absent'): ?> <?php case ('absence_injustifiee'): ?> Absent <?php break; ?>
                        <?php case ('pas_de_badge'): ?> Pas de badge <?php break; ?>
                        <?php default: ?> Present
                    <?php endswitch; ?>
                </td>
                <td style="text-align:center;"><?php echo e($r['valide'] ? 'Oui' : '-'); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>


    <div class="footer">Document genere automatiquement - systeme de pointage</div>

</body>
</html>
<?php /**PATH D:\Projects\SIRH-\resources\views/pointage/pdf.blade.php ENDPATH**/ ?>