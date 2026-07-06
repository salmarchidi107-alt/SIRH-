<?php $__env->startSection('title', 'Notes de frais'); ?>
<?php $__env->startSection('page-title', 'Notes de frais'); ?>

<?php $__env->startSection('content'); ?>
<div class="nf-wrapper">
    <div class="nf-top-tabs">
        <a href="<?php echo e(route('expenses.index')); ?>" class="nf-top-tab active">Liste des notes</a>
        <a href="<?php echo e(route('expenses.create')); ?>" class="nf-top-tab">Nouvelle note (OCR)</a>
        <a href="<?php echo e(route('expenses.import')); ?>" class="nf-top-tab">Import groupé</a>
    </div>

    <div class="nf-view">
        <div class="page-header">
            <div class="page-header-left">
                <h1>Notes de frais</h1>
                <p><?php echo e(now()->translatedFormat('F Y')); ?></p>
            </div>
            <div style="display:flex;gap:10px">
                <a class="btn btn-ghost" href="<?php echo e(route('expenses.import')); ?>">Import OCR</a>
                <a class="btn btn-ghost" href="#" onclick="return false">Export Excel</a>
                <a class="btn btn-primary" href="<?php echo e(route('expenses.create')); ?>">+ Nouvelle note</a>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="card mb-4">
                <div class="card-body" style="color:#065f46;background:#f0fdf4">
                    <?php echo e(session('success')); ?>

                </div>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('expenses.index')); ?>" style="display:flex;gap:16px;align-items:center;flex-wrap:wrap">
                    <select name="month" class="form-control" style="width:auto" onchange="this.form.submit()">
                        <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m); ?>" <?php echo e((int) request('month', now()->month) === $m ? 'selected' : ''); ?>>
                                <?php echo e(\Illuminate\Support\Carbon::create()->month($m)->translatedFormat('F')); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="year" class="form-control" style="width:auto" onchange="this.form.submit()">
                        <?php $__currentLoopData = range(now()->year, now()->year - 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($y); ?>" <?php echo e((int) request('year', now()->year) === $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <?php if (! ($isEmployeeMode)): ?>
                        <select name="employee_id" class="form-control" style="width:auto" onchange="this.form.submit()">
                            <option value="">Tous les employés</option>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($emp->id); ?>" <?php echo e((int) request('employee_id') === $emp->id ? 'selected' : ''); ?>>
                                    <?php echo e($emp->full_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                        <select name="status" class="form-control" style="width:auto" onchange="this.form.submit()">
                            <option value="">Tous statuts</option>
                            <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php echo e(request('status') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php endif; ?>

                    <div class="nf-stats-row" style="margin-left:auto">
                        <div><span style="color:var(--text-muted)">Total</span><strong><?php echo e($stats['total']); ?></strong></div>
                        <div><span style="color:var(--text-muted)">Montant</span><strong><?php echo e($stats['montant']); ?></strong></div>
                        <div><span style="color:#d97706">Soumis</span><strong><?php echo e($stats['soumis']); ?></strong></div>
                        <div><span style="color:#059669">Validé</span><strong><?php echo e($stats['valide']); ?></strong></div>
                        <div><span style="color:#dc2626">Rejeté</span><strong><?php echo e($stats['rejete']); ?></strong></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding:0">
                <table>
                    <thead>
                        <tr>
                            <th>Employé</th><th>Titre</th><th>Catégorie</th><th>Date</th>
                            <th style="text-align:right">Montant</th><th style="text-align:center">Statut</th>
                            <th style="text-align:center">Reçu</th><th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($expense->employee->full_name ?? '—'); ?></td>
                                <td style="font-weight:600"><?php echo e($expense->title); ?></td>
                                <td><?php echo e($expense->category_label); ?></td>
                                <td><?php echo e($expense->expense_date->format('d/m/Y')); ?></td>
                                <td style="text-align:right;font-weight:600"><?php echo e(number_format($expense->amount, 2, ',', ' ')); ?> <?php echo e($expense->currency); ?></td>
                                <td style="text-align:center">
                                    <span class="nf-badge nf-badge-<?php echo e($expense->status); ?>"><?php echo e($expense->status_label); ?></span>
                                </td>
                                <td style="text-align:center"><?php echo e($expense->receipt_path ? '📎' : '—'); ?></td>
                                <td style="text-align:right">
                                    <a class="btn btn-ghost" style="padding:4px 10px;font-size:0.78rem" href="#" onclick="return false">Modifier</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:24px">Aucune note de frais pour cette période.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Notes de frais : onglets, badges, stats (spécifique à ce module) ── */
.nf-top-tabs {
    background:var(--surface); border-bottom:1px solid var(--border);
    padding:0 24px; display:flex; gap:4px; margin-bottom:24px;
}
.nf-top-tab {
    padding:14px 18px; font-size:0.88rem; font-weight:600; color:var(--text-muted);
    cursor:pointer; border-bottom:2px solid transparent; transition:all .15s;
    text-decoration:none; display:inline-block;
}
.nf-top-tab:hover { color:var(--primary); }
.nf-top-tab.active { color:var(--primary); border-bottom-color:var(--primary); }

.nf-view { padding:0; }

.nf-badge { padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:700; white-space:nowrap; }
.nf-badge-brouillon { background:#f1f5f9; color:#64748b; }
.nf-badge-soumis { background:#fef3c7; color:#92400e; }
.nf-badge-valide { background:#d1fae5; color:#065f46; }
.nf-badge-rejete { background:#fee2e2; color:#991b1b; }

.nf-stats-row { display:flex; gap:20px; font-size:0.85rem; flex-wrap:wrap; }
.nf-stats-row div strong { margin-left:4px; }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/expenses/index.blade.php ENDPATH**/ ?>