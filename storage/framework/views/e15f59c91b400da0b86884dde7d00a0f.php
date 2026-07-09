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
                <a class="btn btn-ghost" href="<?php echo e(route('expenses.export', request()->only(['month','year','employee_id','status']))); ?>">📥 Export CSV</a>
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
                                <td><?php echo e($expense->expense_date->locale('fr')->translatedFormat('d F Y')); ?></td>                                <td style="text-align:right;font-weight:600"><?php echo e(number_format($expense->amount, 2, ',', ' ')); ?> <?php echo e($expense->currency); ?></td>
                                <td style="text-align:center">
                                    <span class="nf-badge nf-badge-<?php echo e($expense->status); ?>"><?php echo e($expense->status_label); ?></span>
                                </td>
                                <td style="text-align:center">
                                    <?php if($expense->receipt_path): ?>
                                        <a href="<?php echo e(Storage::url($expense->receipt_path)); ?>" target="_blank" rel="noopener" title="Voir le justificatif" style="display:inline-flex;color:var(--text-muted)">
                                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                            </svg>
                                        </a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right">
                                    <div style="display:flex;gap:6px;justify-content:flex-end">
                                        <a class="btn btn-ghost" style="padding:4px 10px;font-size:0.78rem" href="<?php echo e(route('expenses.edit', $expense)); ?>">Modifier</a>

                                        <?php if (! ($isEmployeeMode)): ?>
                                            <?php if($expense->status !== \App\Models\Expense::STATUS_VALIDE): ?>
                                                <form action="<?php echo e(route('expenses.approve', $expense)); ?>" method="POST" style="display:inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn btn-ghost" style="padding:4px 10px;font-size:0.78rem;color:#059669">✓ Valider</button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if($expense->status !== \App\Models\Expense::STATUS_REJETE): ?>
                                                <form action="<?php echo e(route('expenses.reject', $expense)); ?>" method="POST" style="display:inline"
                                                      onsubmit="return confirm('Rejeter cette note de frais ?')">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn btn-ghost" style="padding:4px 10px;font-size:0.78rem;color:#dc2626">✕ Rejeter</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
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
.nf-badge-valide { background:#d1fae5; color:#065f46; }
.nf-badge-rejete { background:#fee2e2; color:#991b1b; }

.nf-stats-row { display:flex; gap:20px; font-size:0.85rem; flex-wrap:wrap; }
.nf-stats-row div strong { margin-left:4px; }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/expenses/index.blade.php ENDPATH**/ ?>