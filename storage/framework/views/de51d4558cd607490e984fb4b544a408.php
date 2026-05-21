<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planning Mensuel</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page { size: A3 landscape; margin: 20px 24px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #222;
            margin: 20px 24px;
            font-size: 9px;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 4px;
            color: #1a2e44;
        }

        .meta {
            margin-bottom: 12px;
            font-size: 10px;
            color: #555;
        }
        .meta p { margin: 2px 0; }
        .meta strong { color: #1a2e44; }

        .filters {
            margin-top: 6px;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .filter-badge {
            background: #eaf4f4;
            color: #1a2e44;
            border: 1px solid #cdd8e0;
            border-radius: 4px;
            padding: 2px 7px;
            font-size: 8px;
            font-weight: bold;
        }

        /* ── TABLE ── */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 4px 3px;
            font-size: 8px;
        }

        /* Header */
        thead tr th {
            background: #f0f0f0;
            text-align: center;
            font-weight: bold;
            color: #1a2e44;
            text-transform: uppercase;
        }
        thead tr th.col-emp  { text-align: left; padding-left: 6px; width: 85px; }
        thead tr th.col-dept { text-align: left; padding-left: 4px; width: 60px; }

        .day-num  { font-size: 8px; font-weight: bold; }
        .day-name { font-size: 6.5px; font-weight: normal; color: #666; }

        /* Weekend */
        thead tr th.weekend { background: #e4eef2; color: #5a7a8a; }
        td.weekend-cell     { background: #f5f9fb !important; }

        /* Body rows */
        tbody tr:nth-child(odd)  td { background: #fafafa; }
        tbody tr:nth-child(even) td { background: #ffffff; }

        /* Employee cells */
        td.emp-cell {
            text-align: left;
            padding: 5px 6px;
            vertical-align: middle;
        }
        .emp-name {
            font-weight: bold;
            font-size: 8px;
            color: #1a2e44;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .emp-pos {
            font-size: 6.5px;
            color: #888;
            margin-top: 1px;
        }

        td.dept-cell {
            font-size: 7.5px;
            color: #555;
            vertical-align: middle;
            padding-left: 5px;
        }

        /* Shift cells */
        td.shift-cell {
            text-align: center;
            vertical-align: top;
            padding: 2px 2px;
        }

        .shift-block {
            border-radius: 3px;
            padding: 2px 2px;
            margin-bottom: 2px;
            font-size: 6.5px;
            font-weight: bold;
            text-align: center;
        }

        .shift-matin      { background: #cce8fb; color: #1565c0; }
        .shift-apres_midi { background: #ffe0a0; color: #8a5000; }
        .shift-nuit       { background: #d6d8f7; color: #3a3a8a; }
        .shift-garde      { background: #f8c8f0; color: #8a1a80; }
        .shift-journee    { background: #c8f0e0; color: #1a6040; }
        .shift-default    { background: #e8eef2; color: #3a5068; }

        .shift-time { font-size: 5.5px; font-weight: normal; color: #666; margin-top: 1px; }

        .no-shift { color: #ddd; font-size: 10px; }

        /* Note */
        .note {
            margin-top: 10px;
            font-size: 8px;
            color: #888;
            text-align: right;
        }
    </style>
</head>
<body>

    <?php
        $moisLongFr = [
            1=>'Janvier',  2=>'Février',   3=>'Mars',      4=>'Avril',
            5=>'Mai',      6=>'Juin',       7=>'Juillet',   8=>'Août',
            9=>'Septembre',10=>'Octobre',  11=>'Novembre', 12=>'Décembre'
        ];
        $joursCourts = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];

        $daysInMonth = [];
        $cursor = $startOfMonth->copy();
        while ($cursor->lte($endOfMonth)) {
            $daysInMonth[] = $cursor->copy();
            $cursor->addDay();
        }

        $shiftClasses = [
            'matin'      => 'shift-matin',
            'apres_midi' => 'shift-apres_midi',
            'nuit'       => 'shift-nuit',
            'garde'      => 'shift-garde',
            'journee'    => 'shift-journee',
        ];

        $shiftLabels = [
            'matin'      => 'Mat',
            'apres_midi' => 'A-M',
            'nuit'       => 'Nuit',
            'garde'      => 'Grd',
            'journee'    => 'Jour',
        ];

        $shiftLabelsDisplay = [
            'matin'      => 'Matin',
            'apres_midi' => 'Après-midi',
            'nuit'       => 'Nuit',
            'garde'      => 'Garde',
            'journee'    => 'Journée',
        ];

        $hasFilter = !empty($department) || !empty($search) || !empty($shift_type);
    ?>

    <h1>Planning Mensuel</h1>

    <div class="meta">
        <p>Mois : <strong><?php echo e($moisLongFr[$month]); ?> <?php echo e($year); ?></strong></p>
        <p>Période : <strong><?php echo e($startOfMonth->day); ?> <?php echo e($moisLongFr[$month]); ?> <?php echo e($year); ?> — <?php echo e($endOfMonth->day); ?> <?php echo e($moisLongFr[$month]); ?> <?php echo e($year); ?></strong></p>
        <p>Nombre d'employés : <strong><?php echo e(count($employees)); ?></strong></p>

        
        <?php if($hasFilter): ?>
            <div class="filters">
                <?php if(!empty($department)): ?>
                    <span class="filter-badge">Service : <?php echo e($department); ?></span>
                <?php endif; ?>
                <?php if(!empty($search)): ?>
                    <span class="filter-badge">Recherche : <?php echo e($search); ?></span>
                <?php endif; ?>
                <?php if(!empty($shift_type)): ?>
                    <span class="filter-badge">Shift : <?php echo e($shiftLabelsDisplay[$shift_type] ?? $shift_type); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-emp">Employé</th>
                <th class="col-dept">Département</th>
                <?php $__currentLoopData = $daysInMonth; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $dow = (int)$day->format('N');
                        $isWeekend = $dow >= 6;
                    ?>
                    <th class="<?php echo e($isWeekend ? 'weekend' : ''); ?>">
                        <div class="day-num"><?php echo e($day->day); ?></div>
                        <div class="day-name"><?php echo e($joursCourts[$dow - 1]); ?></div>
                    </th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $employeePlannings = $plannings->get($employee->id, collect());
                    $byDate = $employeePlannings->groupBy(fn($p) => $p->date?->format('Y-m-d'));
                ?>
                <tr>
                    <td class="emp-cell">
                        <div class="emp-name"><?php echo e($employee->full_name); ?></div>
                        <div class="emp-pos"><?php echo e($employee->position ?? ''); ?></div>
                    </td>
                    <td class="dept-cell"><?php echo e($employee->department); ?></td>

                    <?php $__currentLoopData = $daysInMonth; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $dow = (int)$day->format('N');
                            $isWeekend = $dow >= 6;
                            $dayShifts = $byDate->get($day->format('Y-m-d'), collect());
                        ?>
                        <td class="shift-cell <?php echo e($isWeekend ? 'weekend-cell' : ''); ?>">
                            <?php $__empty_2 = true; $__currentLoopData = $dayShifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <?php
                                    $badgeClass = $shiftClasses[$planning->shift_type] ?? 'shift-default';
                                    $label = $shiftLabels[$planning->shift_type]
                                             ?? mb_substr(ucfirst(str_replace('_', ' ', $planning->shift_type)), 0, 4);
                                ?>
                                <div class="shift-block <?php echo e($badgeClass); ?>">
                                    <div><?php echo e($label); ?></div>
                                    <div class="shift-time"><?php echo e(substr($planning->shift_start, 0, 5)); ?></div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                <span class="no-shift">·</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e(count($daysInMonth) + 2); ?>" style="text-align:center; padding:14px; color:#aaa;">
                        Aucun planning disponible pour cette sélection.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="note">
        Généré le <?php echo e(now()->day); ?> <?php echo e(strtolower($moisLongFr[(int)now()->format('n')])); ?> <?php echo e(now()->year); ?> à <?php echo e(now()->format('H:i')); ?>

    </div>

</body>
</html>
<?php /**PATH D:\Projects\SIRH-\resources\views/planning/monthly_pdf.blade.php ENDPATH**/ ?>