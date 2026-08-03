<?php $__env->startSection('title', 'Notes de frais'); ?>
<?php $__env->startSection('page-title', 'Notes de frais'); ?>

<?php $__env->startSection('content'); ?>
<div class="nf-wrapper">
    <div class="nf-top-tabs">
        <a href="<?php echo e(route('expenses.index')); ?>" class="nf-top-tab active">Liste des notes</a>
        <a href="<?php echo e(route('expenses.create')); ?>" class="nf-top-tab">Nouvelle note (OCR)</a>
    </div>

    <div class="nf-view">
        <div class="page-header">
            <div class="page-header-left">
                <h1>Notes de frais</h1>
                <p><?php echo e(now()->locale('fr')->translatedFormat('F Y')); ?></p>
            </div>
            <div style="display:flex;gap:10px">
    <a class="btn btn-ghost" href="<?php echo e(route('expenses.export', request()->only(['month','year','employee_id','status','category','description']))); ?>">
         Export Excel
    </a>
    <a class="btn btn-ghost" href="<?php echo e(route('expenses.export.pdf', request()->only(['month','year','employee_id','status','category','description']))); ?>">
         Export PDF
    </a>
    <a class="btn btn-primary" href="<?php echo e(route('expenses.create')); ?>">+ Nouvelle note</a>
</div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('expenses.index')); ?>" style="display:flex;gap:10px;align-items:center;flex-wrap:nowrap;overflow-x:auto;padding-bottom:4px">
                    <select name="month" class="form-control" style="width:auto;flex-shrink:0" onchange="this.form.submit()">
                        <option value="">Tous les mois</option>
                        <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m); ?>" <?php echo e((int) request('month') === $m ? 'selected' : ''); ?>>
                                <?php echo e(\Illuminate\Support\Carbon::create()->month($m)->locale('fr')->translatedFormat('F')); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="year" class="form-control" style="width:auto;flex-shrink:0" onchange="this.form.submit()">
                        <option value="">Toutes les années</option>
                        <?php $__currentLoopData = range(now()->year, now()->year - 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($y); ?>" <?php echo e((int) request('year') === $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <?php if (! ($isEmployeeMode)): ?>
                        <select name="employee_id" class="form-control" style="width:auto;flex-shrink:0" onchange="this.form.submit()">
                            <option value="">Tous les employés</option>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($emp->id); ?>" <?php echo e((int) request('employee_id') === $emp->id ? 'selected' : ''); ?>>
                                    <?php echo e($emp->full_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                        <select name="status" class="form-control" style="width:auto;flex-shrink:0" onchange="this.form.submit()">
                            <option value="">Tous statuts</option>
                            <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php echo e(request('status') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php endif; ?>

                    
                    <select name="category" class="form-control" style="width:auto;flex-shrink:0" onchange="this.form.submit()">
                        <option value="">Toutes catégories</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php echo e(request('category') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    
                    <input type="text" name="description" class="form-control" style="width:170px;flex-shrink:0"
                           placeholder="Rechercher dans la description…" value="<?php echo e(request('description')); ?>">

                    <?php
                        $hasActiveFilters = collect(request()->query())->filter()->isNotEmpty();
                    ?>
                    <button type="submit" class="btn btn-primary" style="padding:8px 14px;flex-shrink:0;white-space:nowrap">Filtrer</button>
                    <?php if($hasActiveFilters): ?>
                        <a href="<?php echo e(route('expenses.index')); ?>" class="btn btn-ghost" style="padding:8px 14px;flex-shrink:0;white-space:nowrap">✕ Réinitialiser</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        
        <div class="nf-stats-panel">
            <div class="nf-stat-seg">
                <div class="nf-stat-icon nf-stat-icon-teal">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="nf-stat-val"><?php echo e($stats['total']); ?></div>
                    <div class="nf-stat-label">Notes au total</div>
                </div>
            </div>

            <div class="nf-stat-div"></div>

            <div class="nf-stat-seg nf-stat-seg-lead">
                <div class="nf-stat-icon nf-stat-icon-blue">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2"/>
                        <circle cx="12" cy="12" r="9"/>
                    </svg>
                </div>
                <div>
                    <div class="nf-stat-val nf-stat-val-lg"><?php echo e($stats['montant']); ?></div>
                    <div class="nf-stat-label">Montant cumulé</div>
                </div>
            </div>

            <div class="nf-stat-div"></div>

            <div class="nf-stat-seg">
                <div class="nf-stat-icon nf-stat-icon-green">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <div class="nf-stat-val"><?php echo e($stats['valide']); ?></div>
                    <div class="nf-stat-label">Validées</div>
                </div>
            </div>

            <div class="nf-stat-div"></div>

            <div class="nf-stat-seg">
                <div class="nf-stat-icon nf-stat-icon-red">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <div class="nf-stat-val"><?php echo e($stats['rejete']); ?></div>
                    <div class="nf-stat-label">Rejetées</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding:0">
                <table>
                    <thead>
                        <tr>
                            <th>Employé</th><th>Titre</th><th>Catégorie</th><th>Description</th><th>Date</th>
                            <th style="text-align:right">HT</th>
                            <th style="text-align:right">TVA</th>
                            <th style="text-align:right">TTC</th>
                            <th style="text-align:center">Statut</th>
                            <th style="text-align:center">Reçu</th><th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($expense->employee->full_name ?? '—'); ?></td>
                                <td style="font-weight:600"><?php echo e($expense->title); ?></td>
                                <td><?php echo e($expense->category_label); ?></td>
                                <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="<?php echo e($expense->description); ?>">
                                    <?php echo e($expense->description ?: '—'); ?>

                                </td>
                                <td><?php echo e($expense->expense_date->format('d/m/Y')); ?></td>
                                <td style="text-align:right">
                                    <?php echo e($expense->amount_excluding_tax !== null ? number_format($expense->amount_excluding_tax, 2, ',', ' ') : '—'); ?>

                                </td>
                                <td style="text-align:right">
                                    <?php echo e($expense->vat_amount !== null ? number_format($expense->vat_amount, 2, ',', ' ') : '—'); ?>

                                </td>
                                <td style="text-align:right;font-weight:600">
                                    <?php echo e(number_format($expense->amount, 2, ',', ' ')); ?> <?php echo e($expense->currency); ?>

                                </td>
                                <td style="text-align:center">
                                    <div class="nf-status-card nf-status-card-<?php echo e($expense->status); ?>">
                                        <?php echo e($expense->status_label); ?>

                                    </div>
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
                                    <div class="nf-actions">
                                        <a class="nf-icon-btn nf-icon-btn-neutral" href="<?php echo e(route('expenses.edit', $expense)); ?>" title="Modifier">
                                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        <?php if (! ($isEmployeeMode)): ?>
                                            <?php if($expense->status !== \App\Models\Expense::STATUS_VALIDE): ?>
                                                <form action="<?php echo e(route('expenses.approve', $expense)); ?>" method="POST" style="display:inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="nf-icon-btn nf-icon-btn-green" title="Valider">
                                                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if($expense->status !== \App\Models\Expense::STATUS_REJETE): ?>
                                                <form action="<?php echo e(route('expenses.reject', $expense)); ?>" method="POST" style="display:inline"
                                                      onsubmit="return confirm('Rejeter cette note de frais ?')">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="nf-icon-btn nf-icon-btn-red" title="Rejeter">
                                                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        
                                        <form action="<?php echo e(route('expenses.destroy', $expense)); ?>" method="POST" style="display:inline"
                                              onsubmit="return confirm('Supprimer définitivement cette note de frais ? Cette action est irréversible.')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="nf-icon-btn nf-icon-btn-red" title="Supprimer">
                                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9.5 4h5a1 1 0 011 1v2H8.5V5a1 1 0 011-1z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="11" style="text-align:center;color:var(--text-muted);padding:24px">Aucune note de frais pour cette période.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/expenses/index.blade.php ENDPATH**/ ?>