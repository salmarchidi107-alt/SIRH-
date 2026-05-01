<?php $__env->startSection('title', 'Historique — '.$employee->full_name); ?>
<?php $__env->startSection('page-title', 'Historique de Paie'); ?>

<?php $__env->startSection('content'); ?>

<div class="page-header">
    <div class="page-header-left">
        <h1><?php echo e($employee->full_name); ?></h1>
        <p><?php echo e($employee->department); ?> — <?php echo e($employee->position); ?></p>
    </div>
    <?php if (! (auth()->user()->isEmployee())): ?>
        <div style="display:flex;gap:8px">
            <a href="<?php echo e(route('salary.create', [$employee,'month'=>now()->month,'year'=>now()->year])); ?>"
                class="btn btn-primary">Saisir la paie du mois</a>
            <a href="<?php echo e(route('salary.index')); ?>" class="btn btn-ghost">← Retour</a>
        </div>
    <?php endif; ?>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>


<div class="card mb-4">
    <div class="card-body" style="padding:14px 20px">
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;font-size:0.85rem">
            <div><div style="color:var(--text-muted);font-size:0.75rem">CIN</div><strong><?php echo e($employee->cin ?? '—'); ?></strong></div>
            <div><div style="color:var(--text-muted);font-size:0.75rem">N° CNSS</div><strong><?php echo e($employee->cnss_number ?? '—'); ?></strong></div>
            <div><div style="color:var(--text-muted);font-size:0.75rem">Date d'embauche</div><strong><?php echo e($employee->hire_date?->format('d/m/Y') ?? '—'); ?></strong></div>
            <div><div style="color:var(--text-muted);font-size:0.75rem">Mode paiement</div><strong>
<?php if($employee->payment_method == 'virement'): ?>
    Virement <?php echo e($employee->bank ?? '—'); ?>

<?php else: ?>
    <?php echo e(ucfirst($employee->payment_method ?? '—')); ?>

<?php endif; ?>
</strong></div>
            <div><div style="color:var(--text-muted);font-size:0.75rem">Salaire base</div><strong><?php echo e(number_format($employee->base_salary,0,',',' ')); ?> MAD</strong></div>

            <div><div style="color:var(--text-muted);font-size:0.75rem">Situation familiale</div><strong><?php echo e(ucfirst($employee->family_status ?? 'Célibataire')); ?></strong></div>
            <div><div style="color:var(--text-muted);font-size:0.75rem">Enfants</div><strong><?php echo e($employee->children_count ?? 0); ?></strong></div>
            <div><div style="color:var(--text-muted);font-size:0.75rem">Banque</div><strong><?php echo e($employee->bank_name ?? '—'); ?></strong></div>
            <div><div style="color:var(--text-muted);font-size:0.75rem">RIB</div><strong style="font-size:0.75rem"><?php echo e($employee->rib ?? '—'); ?></strong></div>
            <div><div style="color:var(--text-muted);font-size:0.75rem">Paiement</div><strong><?php echo e(ucfirst($employee->payment_mode ?? 'Virement')); ?></strong></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3>Historique des paiements (12 derniers mois)</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Période</th>
                        <th>Net à payer</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $salaries->take(12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $salary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($salary->month_name); ?> <?php echo e($salary->year); ?></td>
                            <td style="font-weight:600;color:var(--success)"><?php echo e(number_format($salary->net_salary,0,',',' ')); ?> MAD</td>
                            <td><span class="badge badge-<?php echo e($salary->status_color); ?>"><?php echo e($salary->status_label); ?></span></td>
                            <td>
<a href="<?php echo e(route('salary.pdf', $salary)); ?>" class="btn btn-sm btn-ghost" download="bulletin.pdf">PDF</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;padding:20px;color:var(--text-muted)">Aucun historique</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if($salaries->isEmpty()): ?>
    <div class="card">
        <div class="card-body" style="padding:60px;text-align:center;color:var(--text-muted)">
            <div style="font-size:3rem;margin-bottom:12px">💰</div>
            <div style="font-size:1rem;margin-bottom:8px">Aucun bulletin de paie</div>
<?php if (! (auth()->user()->isEmployee())): ?>
                    <a href="<?php echo e(route('salary.create',[$employee,'month'=>now()->month,'year'=>now()->year])); ?>"
                       class="btn btn-primary" style="margin-top:12px">Générer le premier bulletin</a>
                <?php endif; ?>
        </div>
    </div>
<?php else: ?>


<?php $__currentLoopData = $salaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $salary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="card mb-3">

    
    <div class="card-header" style="cursor:pointer;user-select:none"
         onclick="toggleBulletin('b<?php echo e($salary->id); ?>')">
        <div style="display:flex;align-items:center;gap:16px;width:100%">
            <div style="flex:1">
                <span class="font-semibold" style="font-size:1rem">
                    <?php echo e($salary->month_name); ?> <?php echo e($salary->year); ?>

                </span>
                <span class="badge badge-<?php echo e($salary->status_color); ?>" style="margin-left:8px">
                    <?php echo e($salary->status_label); ?>

                </span>
            </div>
            <div style="display:flex;gap:24px;font-size:0.85rem;align-items:center">
                <span>Brut : <strong><?php echo e(number_format($salary->gross_salary,0,',',' ')); ?> MAD</strong></span>
                <span class="deduction">CNSS+AMO : -<?php echo e(number_format($salary->cnss_deduction+$salary->amo_deduction,0,',',' ')); ?></span>
                <span class="deduction">IR : -<?php echo e(number_format($salary->ir_deduction,0,',',' ')); ?></span>
                <span style="color:var(--success);font-weight:700;font-size:1rem">
                    Net : <?php echo e(number_format($salary->net_salary,0,',',' ')); ?> MAD
                </span>
            </div>
            <div style="display:flex;gap:6px">
<a href="<?php echo e(route('salary.pdf',$salary)); ?>"
                   class="btn btn-sm btn-ghost" download="bulletin.pdf" onclick="event.stopPropagation()">PDF</a>
                <?php if (! (auth()->user()->isEmployee())): ?>
                    <?php if($salary->status==='draft'): ?>
                        <form method="POST" action="<?php echo e(route('salary.validate',$salary)); ?>" onclick="event.stopPropagation()">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button class="btn btn-sm btn-success">Valider</button>
                        </form>
                    <?php endif; ?>
                    <?php if($salary->status==='validated'): ?>
                        <form method="POST" action="<?php echo e(route('salary.paid',$salary)); ?>" onclick="event.stopPropagation()">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button class="btn btn-sm btn-primary">Marquer rémunérer</button>
                        </form>
                    <?php endif; ?>
                    <?php if($salary->status==='draft'): ?>
                        <form method="POST" action="<?php echo e(route('salary.destroy',$salary)); ?>"
                              onsubmit="return confirm('Supprimer ce bulletin ?')" onclick="event.stopPropagation()">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div id="b<?php echo e($salary->id); ?>" style="display:<?php echo e($i===0?'block':'none'); ?>;border-top:1px solid var(--border-color)">

        
        <div>
            <div style="padding:10px 16px;background:#f0fff4;font-weight:700;font-size:0.82rem;color:#065f46;border-bottom:1px solid #d1fae5;letter-spacing:0.04em">
                GAINS
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:0.83rem">
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Salaire de base</td>
                    <td class="bonus" style="padding:7px 16px;text-align:right;font-weight:600"><?php echo e(number_format($salary->base_salary,2,',',' ')); ?></td>
                </tr>
                <?php if($salary->seniority_bonus > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Prime ancienneté (<?php echo e($salary->employee->seniority_rate*100); ?>%)</td>
                    <td class="bonus" style="padding:7px 16px;text-align:right;font-weight:600">+<?php echo e(number_format($salary->seniority_bonus,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($salary->overtime_day_amount > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">HS jour (25%)</td>
                    <td class="bonus" style="padding:7px 16px;text-align:right">+<?php echo e(number_format($salary->overtime_day_amount,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($salary->overtime_night_amount > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">HS nuit (50%)</td>
                    <td class="bonus" style="padding:7px 16px;text-align:right">+<?php echo e(number_format($salary->overtime_night_amount,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($salary->overtime_weekend_amount > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">HS weekend/férié (100%)</td>
                    <td class="bonus" style="padding:7px 16px;text-align:right">+<?php echo e(number_format($salary->overtime_weekend_amount,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($salary->performance_bonus > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Prime de rendement</td>
                    <td class="bonus" style="padding:7px 16px;text-align:right">+<?php echo e(number_format($salary->performance_bonus,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($salary->transport_allowance > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Indemnité transport</td>
                    <td class="bonus" style="padding:7px 16px;text-align:right">+<?php echo e(number_format($salary->transport_allowance,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($salary->meal_allowance > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Prime de panier</td>
                    <td class="bonus" style="padding:7px 16px;text-align:right">+<?php echo e(number_format($salary->meal_allowance,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($salary->housing_allowance > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Indemnité logement</td>
                    <td class="bonus" style="padding:7px 16px;text-align:right">+<?php echo e(number_format($salary->housing_allowance,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($salary->responsibility_allowance > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Indemnité responsabilité</td>
                    <td class="bonus" style="padding:7px 16px;text-align:right">+<?php echo e(number_format($salary->responsibility_allowance,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($salary->other_gains > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Autres gains</td>
                    <td class="bonus" style="padding:7px 16px;text-align:right">+<?php echo e(number_format($salary->other_gains,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <tr style="background:#d1fae5">
                    <td style="padding:9px 16px;font-weight:700;color:#065f46">Salaire Brut</td>
                    <td style="padding:9px 16px;text-align:right;font-weight:700;color:#065f46"><?php echo e(number_format($salary->gross_salary,2,',',' ')); ?></td>
                </tr>
            </table>
        </div>

        
        <div style="border-top:2px solid var(--border-color)">
            <div style="padding:10px 16px;background:#fff0f0;font-weight:700;font-size:0.82rem;color:#991b1b;border-bottom:1px solid #fecaca;letter-spacing:0.04em">
                RETENUES
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:0.83rem">
                <?php if($salary->absence_deduction > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Absences (<?php echo e($salary->absence_days); ?>j)</td>
                    <td class="deduction" style="padding:7px 16px;text-align:right">-<?php echo e(number_format($salary->absence_deduction,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($salary->advance_deduction > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Avance sur salaire</td>
                    <td class="deduction" style="padding:7px 16px;text-align:right">-<?php echo e(number_format($salary->advance_deduction,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($salary->loan_deduction > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Remboursement prêt</td>
                    <td class="deduction" style="padding:7px 16px;text-align:right">-<?php echo e(number_format($salary->loan_deduction,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($salary->garnishment_deduction > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Saisie sur salaire</td>
                    <td class="deduction" style="padding:7px 16px;text-align:right">-<?php echo e(number_format($salary->garnishment_deduction,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($salary->other_deductions > 0): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Autres retenues</td>
                    <td class="deduction" style="padding:7px 16px;text-align:right">-<?php echo e(number_format($salary->other_deductions,2,',',' ')); ?></td>
                </tr>
                <?php endif; ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">CNSS (4,48% × <?php echo e(number_format($salary->cnss_base,0,',',' ')); ?>)</td>
                    <td class="deduction" style="padding:7px 16px;text-align:right">-<?php echo e(number_format($salary->cnss_deduction,2,',',' ')); ?></td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">AMO salariale (2,26%)</td>
                    <td class="deduction" style="padding:7px 16px;text-align:right">-<?php echo e(number_format($salary->amo_deduction,2,',',' ')); ?></td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color);background:#f0f9ff">
                    <td style="padding:7px 16px;color:#1e3a5f">Frais pro déduits (20%)</td>
                    <td style="padding:7px 16px;text-align:right;color:#059669">-<?php echo e(number_format($salary->fp_deduction,2,',',' ')); ?></td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color);background:#f0f9ff">
                    <td style="padding:7px 16px;font-weight:600;color:#1e3a5f">Net imposable</td>
                    <td style="padding:7px 16px;text-align:right;font-weight:600;color:#1e3a5f"><?php echo e(number_format($salary->taxable_income,2,',',' ')); ?></td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">IR mensuel</td>
                    <td class="deduction" style="padding:7px 16px;text-align:right">-<?php echo e(number_format($salary->ir_deduction,2,',',' ')); ?></td>
                </tr>
                <tr style="background:#fecaca">
                    <td style="padding:9px 16px;font-weight:700;color:#991b1b">Total retenues</td>
                    <td style="padding:9px 16px;text-align:right;font-weight:700;color:#991b1b">-<?php echo e(number_format($salary->total_retentions,2,',',' ')); ?></td>
                </tr>
            </table>
        </div>

        
        <div style="border-top:2px solid var(--border-color)">
            <div style="padding:10px 16px;background:#eff6ff;font-weight:700;font-size:0.82rem;color:#1e3a5f;border-bottom:1px solid #bfdbfe;letter-spacing:0.04em">
                RÉSUMÉ & CHARGES PATRONALES
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:0.83rem">
                <tr style="border-bottom:2px solid var(--border-color);background:#d1fae5">
                    <td style="padding:12px 16px;font-weight:700;color:#065f46;font-size:1rem">NET À PAYER</td>
                    <td style="padding:12px 16px;text-align:right;font-weight:700;color:#065f46;font-size:1rem"><?php echo e(number_format($salary->net_salary,2,',',' ')); ?> MAD</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:6px 16px;font-size:0.75rem;font-weight:600;color:var(--text-muted);background:#f9fafb">
                        Charges patronales (info)
                    </td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">CNSS patronale (10,29%)</td>
                    <td style="padding:7px 16px;text-align:right;color:#BA7517"><?php echo e(number_format($salary->employer_cnss,2,',',' ')); ?></td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">AMO patronale (2,26%)</td>
                    <td style="padding:7px 16px;text-align:right;color:#BA7517"><?php echo e(number_format($salary->employer_amo,2,',',' ')); ?></td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">TFP (1,60%)</td>
                    <td style="padding:7px 16px;text-align:right;color:#BA7517"><?php echo e(number_format($salary->employer_tfp,2,',',' ')); ?></td>
                </tr>
                <tr style="background:#fef3c7;border-bottom:2px solid var(--border-color)">
                    <td style="padding:9px 16px;font-weight:700;color:#78350f">Coût total employeur</td>
                    <td style="padding:9px 16px;text-align:right;font-weight:700;color:#78350f"><?php echo e(number_format($salary->employer_total_cost,2,',',' ')); ?> MAD</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:6px 16px;font-size:0.75rem;font-weight:600;color:var(--text-muted);background:#f9fafb">
                        Détail IR
                    </td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">IR annuel calculé</td>
                    <td style="padding:7px 16px;text-align:right"><?php echo e(number_format($salary->ir_annual,2,',',' ')); ?> MAD</td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 16px;color:var(--text-muted)">Déductions familiales</td>
                    <td style="padding:7px 16px;text-align:right;color:#059669">-<?php echo e(number_format($salary->ir_family_deduction,2,',',' ')); ?> MAD</td>
                </tr>
                <tr>
                    <td style="padding:7px 16px;color:var(--text-muted)">IR mensuel final</td>
                    <td class="deduction" style="padding:7px 16px;text-align:right;font-weight:600"><?php echo e(number_format($salary->ir_deduction,2,',',' ')); ?> MAD</td>
                </tr>
            </table>
        </div>

    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php endif; ?>

<script>
function toggleBulletin(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/salary/show.blade.php ENDPATH**/ ?>