<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planning Hebdomadaire</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1a2e44; margin: 20px; font-size: 10px; }

        h1 { font-size: 16px; font-weight: bold; margin-bottom: 2px; color: #1a2e44; }

        .meta { font-size: 10px; color: #555; margin-bottom: 14px; }
        .meta span { margin-right: 16px; }

        .filters {
            margin-top: 4px;
            font-size: 9px;
            color: #555;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .filter-badge {
            background: #eaf4f4;
            color: #1a2e44;
            border: 1px solid #cdd8e0;
            border-radius: 4px;
            padding: 2px 7px;
            font-size: 8.5px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead tr th {
            background-color: #eaf4f4;
            color: #1a2e44;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            padding: 6px 4px;
            border: 1px solid #cdd8e0;
            text-transform: uppercase;
        }

        thead tr th:first-child,
        thead tr th:nth-child(2) {
            text-align: left;
            padding-left: 8px;
        }

        tbody tr td {
            border: 1px solid #cdd8e0;
            padding: 5px 4px;
            vertical-align: top;
        }

        td.emp-name {
            font-weight: bold;
            font-size: 9px;
            color: #1a2e44;
            padding-left: 8px;
        }

        td.emp-dept {
            font-size: 8px;
            color: #666;
            padding-left: 8px;
        }

        .shift-cell {
            text-align: center;
        }

        .shift-block {
            border-radius: 5px;
            padding: 3px 4px;
            margin-bottom: 3px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
        }

        .shift-matin      { background: #cce8fb; color: #1565c0; }
        .shift-apres_midi { background: #ffe0a0; color: #8a5000; }
        .shift-garde      { background: #f8c8f0; color: #8a1a80; }
        .shift-journee    { background: #c8f0e0; color: #1a6040; }
        .shift-default    { background: #e8eef2; color: #3a5068; }

        .shift-time {
            font-size: 7px;
            font-weight: normal;
            margin-top: 1px;
        }

        .shift-room {
            font-size: 7px;
            font-weight: normal;
            font-style: italic;
            color: #888;
            margin-top: 1px;
        }

        .no-shift {
            color: #ccc;
            font-size: 9px;
        }

        tbody tr:nth-child(odd) td {
            background-color: #f9fbfc;
        }
        tbody tr:nth-child(even) td {
            background-color: #ffffff;
        }

        .footer {
            margin-top: 12px;
            font-size: 8px;
            color: #aaa;
            text-align: right;
        }
    </style>
</head>
<body>

    <h1>Planning Hebdomadaire</h1>

    <div class="meta">
        
        <span>Semaine <?php echo e($week); ?> / <?php echo e($year); ?></span>
        <span><?php echo e($startOfWeek->format('d/m/Y')); ?> — <?php echo e($endOfWeek->format('d/m/Y')); ?></span>

        
        <?php
            $shiftLabelsDisplay = [
                'matin'      => 'Matin',
                'apres_midi' => 'Après-midi',
                'garde'      => 'Garde',
                'journee'    => 'Journée',
            ];
            $hasFilter = !empty($department) || !empty($search) || !empty($shift_type) || !empty($roomName);
        ?>

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
                <?php if(!empty($roomName)): ?>
                    <span class="filter-badge">Salle : <?php echo e($roomName); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = $startOfWeek->copy()->addDays($i);
        }

        $dayLabels = ['LUN', 'MAR', 'MER', 'JEU', 'VEN', 'SAM', 'DIM'];

        $shiftClasses = [
            'matin'      => 'shift-matin',
            'apres_midi' => 'shift-apres_midi',
            'garde'      => 'shift-garde',
            'journee'    => 'shift-journee',
        ];

        $shiftLabels = [
            'matin'      => 'Matin',
            'apres_midi' => 'Après-midi',
            'garde'      => 'Garde',
            'journee'    => 'Journée',
        ];
    ?>

    <table>
        <thead>
            <tr>
                <th style="width:15%;">EMPLOYÉ</th>
                <th style="width:10%;">DÉPARTEMENT</th>
                <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th style="width:10.7%;">
                        <?php echo e($dayLabels[$i]); ?><br>
                        <span style="font-weight:normal; text-transform:none;"><?php echo e($day->format('d M')); ?></span>
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
                    <td class="emp-name"><?php echo e($employee->full_name); ?></td>
                    <td class="emp-dept"><?php echo e($employee->department); ?></td>

                    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $dayShifts = $byDate->get($day->format('Y-m-d'), collect()); ?>
                        <td class="shift-cell">
                            <?php $__empty_2 = true; $__currentLoopData = $dayShifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <?php
                                    $badgeClass = $shiftClasses[$planning->shift_type] ?? 'shift-default';
                                    $label = $shiftLabels[$planning->shift_type] ?? ucfirst(str_replace('_', ' ', $planning->shift_type));
                                ?>
                                <div class="shift-block <?php echo e($badgeClass); ?>">
                                    <?php echo e($label); ?>

                                    <div class="shift-time"><?php echo e($planning->shift_start); ?> – <?php echo e($planning->shift_end); ?></div>
                                    <?php if($planning->room): ?>
                                        <div class="shift-room"><?php echo e($planning->room); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                <span class="no-shift">—</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9" style="text-align:center; padding:16px; color:#888;">
                        Aucun employé trouvé pour cette sélection.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Généré le <?php echo e(now()->format('d/m/Y à H:i')); ?>

    </div>

</body>
</html>
<?php /**PATH D:\Projects\SIRH-\resources\views/planning/weekly_pdf.blade.php ENDPATH**/ ?>