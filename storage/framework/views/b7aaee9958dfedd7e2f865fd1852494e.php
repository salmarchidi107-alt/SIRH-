<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notes de frais</title>
    <style>
        /* DomPDF ne supporte qu'un sous-ensemble de CSS : on reste volontairement
           simple (pas de flexbox/grid, pas de variables CSS). */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1f2937;
            margin: 0;
        }
        .header {
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0 0 4px 0;
        }
        .header .sub {
            margin: 0 0 2px 0;
            color: #6b7280;
            font-size: 10px;
        }
        .header p {
            margin: 4px 0 0 0;
            color: #9ca3af;
            font-size: 9px;
        }
        .stats {
            margin-bottom: 12px;
            font-size: 10px;
        }
        .stats span {
            margin-right: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            color: #374151;
        }
        td.num {
            text-align: right;
        }
        td.center {
            text-align: center;
        }
        .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 8px;
            font-weight: bold;
        }
        .status-valide { background: #d1fae5; color: #065f46; }
        .status-rejete { background: #fee2e2; color: #991b1b; }
        .status-en_attente { background: #fef9c3; color: #854d0e; }
        .footer {
            margin-top: 16px;
            font-size: 8px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1><?php echo e($tenant?->name ?? config('app.name')); ?></h1>
            <div class="sub">Notes de frais</div>
            <div class="sub"><?php echo e($periodLabel); ?></div>
        </div>
        <p>Généré le <?php echo e($generatedAt->locale('fr')->translatedFormat('d/m/Y à H:i')); ?></p>
    </div>

    <div class="stats">
        <span><strong>Total :</strong> <?php echo e($stats['total']); ?> note(s)</span>
        <span><strong>Montant cumulé :</strong> <?php echo e($stats['montant']); ?></span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employé</th>
                <th>Titre</th>
                <th>Catégorie</th>
                <th>Description</th>
                <th>Date</th>
                <th>HT</th>
                <th>TVA</th>
                <th>TTC</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($expense->employee->full_name ?? '—'); ?></td>
                    <td><?php echo e($expense->title); ?></td>
                    <td><?php echo e($expense->category_label); ?></td>
                    <td><?php echo e($expense->description ?: '—'); ?></td>
                    <td><?php echo e(optional($expense->expense_date)->format('d/m/Y')); ?></td>
                    <td class="num"><?php echo e($expense->amount_excluding_tax !== null ? number_format((float) $expense->amount_excluding_tax, 2, ',', ' ') : '—'); ?></td>
                    <td class="num"><?php echo e($expense->vat_amount !== null ? number_format((float) $expense->vat_amount, 2, ',', ' ') : '—'); ?></td>
                    <td class="num"><?php echo e(number_format((float) $expense->amount, 2, ',', ' ')); ?> <?php echo e($expense->currency); ?></td>
                    <td class="center">
                        <span class="status status-<?php echo e($expense->status); ?>"><?php echo e($expense->status_label); ?></span>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9" class="center">Aucune note de frais pour cette période.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Document généré automatiquement — SIRH.
    </div>
</body>
</html>
<?php /**PATH D:\Projects\SIRH-\resources\views/expenses/pdf.blade.php ENDPATH**/ ?>