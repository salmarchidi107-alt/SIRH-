<?php $__env->startSection('title', 'Gestion de la Paie'); ?>
<?php $__env->startSection('page-title', 'Paie'); ?>

<?php $__env->startSection('content'); ?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Gestion de la Paie</h1>
        <p>Période : <?php echo e(\Carbon\Carbon::create($year, $month)->locale('fr')->isoFormat('MMMM YYYY')); ?></p>
    </div>
    
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <form method="GET" action="<?php echo e(route('salary.index')); ?>" style="display:flex;gap:8px">
            
            <select name="month" class="form-control" style="width:130px">
                <?php for($m=1; $m<=12; $m++): ?>
                    <option value="<?php echo e($m); ?>" <?php echo e($m==$month?'selected':''); ?>>
                        <?php echo e(\Carbon\Carbon::create(null,$m)->locale('fr')->monthName); ?>

                    </option>
                <?php endfor; ?>
            </select>
            <select name="year" class="form-control" style="width:90px">
                <?php for($y=now()->year; $y>=now()->year-2; $y--): ?>
                    <option value="<?php echo e($y); ?>" <?php echo e($y==$year?'selected':''); ?>><?php echo e($y); ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-ghost">Filtrer</button>
        </form>
        <form method="POST" action="<?php echo e(route('salary.generate-all')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="month" value="<?php echo e($month); ?>">
            <input type="hidden" name="year" value="<?php echo e($year); ?>">
            <button type="submit" class="btn btn-primary"
                    onclick="return confirm('Générer la paie pour tous les employés ?')">
                Générer tout le mois
            </button>
        </form>
        <a href="<?php echo e(route('variables.index', ['month'=>$month,'year'=>$year])); ?>" class="btn btn-ghost">
            Éléments variables
        </a>
        
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>


<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    <div class="salary-card">
        <div class="salary-label">Masse salariale brute</div>
        <div class="salary-net"><?php echo e(number_format($summary['total_gross'],0,',',' ')); ?> MAD</div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">
        Coût total employeur : 0 MAD
        </div>
    </div>
    <div class="salary-card">
        <div class="salary-label">Charges salariales</div>
        <div class="salary-net" style="font-size:1.4rem">
            <?php echo e(number_format($summary['total_cnss_sal']+$summary['total_amo_sal'],0,',',' ')); ?> MAD
        </div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">
            CNSS : <?php echo e(number_format($summary['total_cnss_sal'],0,',',' ')); ?> |
            AMO : <?php echo e(number_format($summary['total_amo_sal'],0,',',' ')); ?>

        </div>
    </div>
    <div class="salary-card">
        <div class="salary-label">IR retenu à la source</div>
        <div class="salary-net" style="font-size:1.4rem">
            <?php echo e(number_format($summary['total_ir'],0,',',' ')); ?> MAD
        </div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">DGI — déclaration mensuelle</div>
    </div>
    <div class="salary-card">
        <div class="salary-label">Net à payer total</div>
        <div class="salary-net"><?php echo e(number_format($summary['total_net'],0,',',' ')); ?> MAD</div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">
            <span style="color:var(--success)"><?php echo e($summary['count_validated']); ?> validés</span> /
            <?php echo e($summary['count']); ?> bulletins
        </div>
    </div>
</div>




<div class="card">
    <div class="card-header">
<div class="card-title">Employés — <?php echo e($employees->count()); ?> <?php echo e($status ? ucfirst($status) : 'au total'); ?></div>
        <div style="display:flex;gap:8px">
            <a href="<?php echo e(route('salary.index', array_merge(request()->only(['month', 'year']), ['status' => null]))); ?>" class="badge badge-neutral <?php echo e(($status ?? null) === null ? 'active' : ''); ?>" style="<?php echo e(($status ?? null) === null ? 'font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);' : ''); ?>">Tous (<?php echo e($summary['count']); ?>)</a>
            <a href="<?php echo e(route('salary.index', array_merge(request()->only(['month', 'year']), ['status' => 'draft']))); ?>" class="badge badge-warning <?php echo e($status == 'draft' ? 'active' : ''); ?>" style="<?php echo e($status == 'draft' ? 'font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);' : ''); ?>"><?php echo e($summary['count_draft']); ?> brouillons</a>
            <a href="<?php echo e(route('salary.index', array_merge(request()->only(['month', 'year']), ['status' => 'validated']))); ?>" class="badge badge-success <?php echo e($status == 'validated' ? 'active' : ''); ?>" style="<?php echo e($status == 'validated' ? 'font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);' : ''); ?>"><?php echo e($summary['count_validated']); ?> validés</a>
            <a href="<?php echo e(route('salary.index', array_merge(request()->only(['month', 'year']), ['status' => 'paid']))); ?>" class="badge badge-info <?php echo e($status == 'paid' ? 'active' : ''); ?>" style="<?php echo e($status == 'paid' ? 'font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);' : ''); ?>"><?php echo e($summary['count_paid']); ?> rémunérer</a>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Employé</th>
                        <th>Département</th>
                        <th>Mode paiement</th>
                        <th>Base</th>

                        <th>Brut</th>
                        <th>CNSS+AMO</th>
                        <th>IR</th>
                        <th style="color:var(--success)">Net à payer</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $sal = $emp->salaries->first(); ?>
                        <tr>
                            <td>
                                <div class="font-semibold"><?php echo e($emp->full_name); ?></div>
                                <div style="font-size:0.78rem;color:var(--text-muted)"><?php echo e($emp->position); ?></div>
                            </td>
                            <td><?php echo e($emp->department); ?></td>
                            <td style="font-size:0.82rem">
<?php if($emp->payment_method == 'virement'): ?>
    Virement <?php echo e($emp->bank ?? '—'); ?>

<?php else: ?>
    <?php echo e(ucfirst($emp->payment_method ?? '—')); ?>

<?php endif; ?>
</td>
                            <td><?php echo e(number_format($emp->base_salary,0,',',' ')); ?></td>

                            <td class="font-semibold">
                                <?php echo e($sal ? number_format($sal->gross_salary,0,',',' ') : '—'); ?>

                            </td>
                            <td class="deduction" style="font-size:0.85rem">
                                <?php if($sal): ?>
                                    <?php echo e(number_format($sal->cnss_deduction + $sal->amo_deduction,0,',',' ')); ?>

                                <?php else: ?> —
                                <?php endif; ?>
                            </td>
                            <td class="deduction" style="font-size:0.85rem">
                                <?php echo e($sal ? number_format($sal->ir_deduction,0,',',' ') : '—'); ?>

                            </td>
                            <td class="font-semibold" style="color:var(--success)">
                                <?php echo e($sal ? number_format($sal->net_salary,0,',',' ').' MAD' : '—'); ?>

                            </td>
                            <td>
                                <?php if($sal): ?>
                                    <span class="badge badge-<?php echo e($sal->status_color); ?>"><?php echo e($sal->status_label); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Non généré</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <?php if (! (auth()->user()->isEmployee())): ?>
                                        <a href="<?php echo e(route('salary.create', [$emp,'month'=>$month,'year'=>$year])); ?>"
                                           class="btn btn-sm btn-primary">Saisir</a>
                                    <?php endif; ?>
                                    <a href="<?php echo e(route('salary.show', $emp)); ?>"
                                       class="btn btn-sm btn-ghost">Historique</a>
                                    <?php if($sal): ?>
                                        <a href="<?php echo e(route('salary.pdf', $sal)); ?>"
                                           class="btn btn-sm btn-ghost">PDF</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" style="text-align:center;padding:48px;color:var(--text-muted)">
                                Aucun employé trouvé.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/salary/index.blade.php ENDPATH**/ ?>