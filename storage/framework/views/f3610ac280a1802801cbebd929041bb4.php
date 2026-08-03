<?php $__env->startSection('title', 'Gestion de la Paie'); ?>
<?php $__env->startSection('page-title', 'Paie'); ?>

<?php $__env->startSection('content'); ?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Gestion de la Paie</h1>
        <p>
            Période :
            <?php if($dateDebut && $dateFin): ?>
                <?php echo e(\Carbon\Carbon::parse($dateDebut)->locale('fr')->isoFormat('D MMM YYYY')); ?>

                → <?php echo e(\Carbon\Carbon::parse($dateFin)->locale('fr')->isoFormat('D MMM YYYY')); ?>

            <?php else: ?>
                <?php echo e(\Carbon\Carbon::create($year, $month)->locale('fr')->isoFormat('MMMM YYYY')); ?>

            <?php endif; ?>
        </p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">

        
        <div class="currency-switcher" id="currencySwitcher">
            <button id="btnMAD" class="active" onclick="setCurrency('MAD')">
                <span style="font-size:10px;opacity:.7;margin-right:2px;">MA</span>MAD
            </button>
            <button id="btnMRU" onclick="setCurrency('MRU')">
                <span style="font-size:10px;opacity:.7;margin-right:2px;">MR</span>MRU
            </button>
        </div>

        <form method="GET" action="<?php echo e(route('salary.index')); ?>" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">

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

            <select name="department" style="padding:10px 12px;border:1px solid var(--border);border-radius:8px;min-width:160px">
                <option value="">Départements</option>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($dept); ?>" <?php echo e($department == $dept ? 'selected' : ''); ?>><?php echo e($dept); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <div style="height:22px;width:1px;background:var(--border);margin:0 2px;flex-shrink:0;"></div>

            <div class="period-filter-bar">
                <span style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;">Du</span>
                <input type="date" name="date_debut" value="<?php echo e($dateDebut ?? ''); ?>" placeholder="jj/mm/aaaa" title="Date de début">
                <span style="font-size:13px;color:var(--text-muted);font-weight:600;">au</span>
                <input type="date" name="date_fin" value="<?php echo e($dateFin ?? ''); ?>" placeholder="jj/mm/aaaa" title="Date de fin">
            </div>

            <?php if($search || $department || $dateDebut): ?>
                <a href="<?php echo e(route('salary.index', ['year' => $year, 'month' => $month])); ?>" class="btn btn-ghost">✕ Réinitialiser</a>
            <?php endif; ?>
            <button type="submit" class="btn btn-ghost">Filtrer</button>
        </form>


        <a href="<?php echo e(route('variables.index', ['month'=>$month,'year'=>$year])); ?>" class="btn btn-ghost">
            Éléments variables
        </a>
        <a href="<?php echo e(route('salary.export-pdf', request()->query())); ?>" class="btn btn-ghost" target="_blank">
            <svg width="14" height="14" ...>...</svg>
            Export PDF
        </a>
    </div>
</div>


<?php if($dateDebut && $dateFin): ?>
<div style="margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
    <span class="period-active-tag">
        <?php echo e(\Carbon\Carbon::parse($dateDebut)->format('d/m/Y')); ?> → <?php echo e(\Carbon\Carbon::parse($dateFin)->format('d/m/Y')); ?>

        <a href="<?php echo e(route('salary.index', array_filter(['month' => $month, 'year' => $year, 'department' => $department, 'search' => $search, 'status' => request('status')]))); ?>"
           title="Réinitialiser la période">✕</a>
    </span>
    <?php $moisConcernes = $periodesMois ?? []; ?>
    <?php if(count($moisConcernes) > 0): ?>
        <span style="font-size:12px;color:var(--text-muted);">
            Bulletins couverts :
            <?php $__currentLoopData = $moisConcernes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <strong><?php echo e(\Carbon\Carbon::create($mc['year'], $mc['month'])->locale('fr')->isoFormat('MMMM YYYY')); ?></strong><?php echo e(!$loop->last ? ', ' : ''); ?>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </span>
    <?php endif; ?>
</div>
<?php endif; ?>


<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    <div class="salary-card">
        <div class="salary-label">Masse salariale brute</div>
        <div class="salary-net">
            <?php echo e(number_format($summary['total_gross'],0,',',' ')); ?>

            <span class="cur-label">MAD</span>
        </div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">
            Coût employeur : <?php echo e(number_format($summary['total_employer_cost'] ?? 0,0,',',' ')); ?>

            <span class="cur-label">MAD</span>
        </div>
    </div>
    <div class="salary-card">
        <div class="salary-label">Charges salariales</div>
        <div class="salary-net" style="font-size:1.4rem">
            <?php echo e(number_format($summary['total_cnss_sal']+$summary['total_amo_sal'],0,',',' ')); ?>

            <span class="cur-label">MAD</span>
        </div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">
            CNSS : <?php echo e(number_format($summary['total_cnss_sal'],0,',',' ')); ?> |
            AMO  : <?php echo e(number_format($summary['total_amo_sal'],0,',',' ')); ?>

        </div>
    </div>
    <div class="salary-card">
        <div class="salary-label">IR retenu à la source</div>
        <div class="salary-net" style="font-size:1.4rem">
            <?php echo e(number_format($summary['total_ir'],0,',',' ')); ?>

            <span class="cur-label">MAD</span>
        </div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">DGI — déclaration mensuelle</div>
    </div>
    <div class="salary-card">
        <div class="salary-label">Net à payer total</div>
        <div class="salary-net">
            <?php echo e(number_format($summary['total_net'],0,',',' ')); ?>

            <span class="cur-label">MAD</span>
        </div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">
            <span style="color:var(--success)"><?php echo e($summary['count_validated']); ?> validés</span> /
            <?php echo e($summary['count']); ?> bulletins
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <div class="card-title">
            Employés — <?php echo e($employees->total()); ?>

            <?php echo e($status ? ucfirst($status) : 'au total'); ?>

            <?php if($dateDebut && $dateFin): ?>
                <span style="font-size:12px;font-weight:normal;color:var(--text-muted);margin-left:6px;">
                    (période : <?php echo e(\Carbon\Carbon::parse($dateDebut)->format('d/m')); ?> → <?php echo e(\Carbon\Carbon::parse($dateFin)->format('d/m/Y')); ?>)
                </span>
            <?php endif; ?>
        </div>
        <div style="display:flex;gap:8px">
            <a href="<?php echo e(route('salary.index', array_merge(request()->only(['month','year','date_debut','date_fin','department']), ['status'=>null]))); ?>"
               class="badge badge-neutral <?php echo e(($status??null)===null?'active':''); ?>"
               style="<?php echo e(($status??null)===null?'font-weight:bold;box-shadow:0 2px 4px rgba(0,0,0,0.1)':''); ?>">
               Tous (<?php echo e($summary['count']); ?>)
            </a>
            <a href="<?php echo e(route('salary.index', array_merge(request()->only(['month','year','date_debut','date_fin','department']), ['status'=>'draft']))); ?>"
               class="badge badge-warning <?php echo e($status=='draft'?'active':''); ?>"
               style="<?php echo e($status=='draft'?'font-weight:bold;box-shadow:0 2px 4px rgba(0,0,0,0.1)':''); ?>">
               <?php echo e($summary['count_draft']); ?> brouillons
            </a>
            <a href="<?php echo e(route('salary.index', array_merge(request()->only(['month','year','date_debut','date_fin','department']), ['status'=>'validated']))); ?>"
               class="badge badge-success <?php echo e($status=='validated'?'active':''); ?>"
               style="<?php echo e($status=='validated'?'font-weight:bold;box-shadow:0 2px 4px rgba(0,0,0,0.1)':''); ?>">
               <?php echo e($summary['count_validated']); ?> validés
            </a>
            <a href="<?php echo e(route('salary.index', array_merge(request()->only(['month','year','date_debut','date_fin','department']), ['status'=>'paid']))); ?>"
               class="badge badge-info <?php echo e($status=='paid'?'active':''); ?>"
               style="<?php echo e($status=='paid'?'font-weight:bold;box-shadow:0 2px 4px rgba(0,0,0,0.1)':''); ?>">
               <?php echo e($summary['count_paid']); ?> rémunérés
            </a>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Employé</th>
                        <th>Département</th>
                        <?php if($dateDebut && $dateFin): ?>
                        <th>Période</th>
                        <?php endif; ?>
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
                        <?php $salList = $emp->salaries; ?>
                        <?php if($salList->isEmpty()): ?>
                            <tr>
                                <td>
                                    <div class="font-semibold"><?php echo e($emp->full_name); ?></div>
                                    <div style="font-size:0.78rem;color:var(--text-muted)"><?php echo e($emp->position); ?></div>
                                </td>
                                <td><?php echo e($emp->department); ?></td>
                                <?php if($dateDebut && $dateFin): ?><td>—</td><?php endif; ?>
                                <td style="font-size:0.82rem"><?php echo e(ucfirst($emp->payment_method ?? '—')); ?></td>
                                <td><?php echo e(number_format($emp->base_salary,0,',',' ')); ?></td>
                                <td>—</td><td>—</td><td>—</td><td>—</td>
                                <td><span class="badge badge-secondary">Non généré</span></td>
                                <td>
                                    <?php if (! (auth()->user()->isEmployee())): ?>
                                    <a href="<?php echo e(route('salary.create', [$emp,'month'=>$month,'year'=>$year])); ?>" class="btn btn-sm btn-primary">Saisir</a>
                                    <?php endif; ?>
                                    <a href="<?php echo e(route('salary.show', $emp)); ?>" class="btn btn-sm btn-ghost">Historique</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $__currentLoopData = $salList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $cur = $sal->currency ?? 'MAD'; ?>
                            <tr>
                                <td>
                                    <div class="font-semibold"><?php echo e($emp->full_name); ?></div>
                                    <div style="font-size:0.78rem;color:var(--text-muted)"><?php echo e($emp->position); ?></div>
                                </td>
                                <td><?php echo e($emp->department); ?></td>

                                <?php if($dateDebut && $dateFin): ?>
                                <td style="font-size:0.82rem;color:var(--text-muted);white-space:nowrap;">
                                    <?php echo e(\Carbon\Carbon::create($sal->year, $sal->month)->locale('fr')->isoFormat('MMMM YYYY')); ?>

                                </td>
                                <?php endif; ?>

                                <td style="font-size:0.82rem">
                                    <?php if($emp->payment_method == 'virement'): ?>
                                        Virement <?php echo e($emp->bank ?? '—'); ?>

                                    <?php else: ?>
                                        <?php echo e(ucfirst($emp->payment_method ?? '—')); ?>

                                    <?php endif; ?>
                                </td>
                                <td><?php echo e(number_format($emp->base_salary,0,',',' ')); ?></td>
                                <td class="font-semibold">
                                    <?php echo e(number_format($sal->gross_salary,0,',',' ')); ?>

                                    <span class="cur-label">MAD</span>
                                </td>
                                <td class="deduction" style="font-size:0.85rem">
                                    <?php echo e(number_format($sal->cnss_deduction + $sal->amo_deduction,0,',',' ')); ?>

                                    <span class="cur-label">MAD</span>
                                </td>
                                <td class="deduction" style="font-size:0.85rem">
                                    <?php echo e(number_format($sal->ir_deduction,0,',',' ')); ?>

                                    <span class="cur-label">MAD</span>
                                </td>
                                <td class="font-semibold" style="color:var(--success)">
                                    <?php echo e(number_format($sal->net_salary,0,',',' ')); ?>

                                    <span class="cur-label">MAD</span>
                                </td>

                                <td>
                                    <div style="display:flex;flex-direction:column;gap:4px;">
                                        <span class="badge badge-<?php echo e($sal->status_color); ?>">
                                            <?php echo e($sal->status_label); ?>

                                        </span>
                                        <?php if($sal->created_by): ?>
                                        <div style="font-size:0.7rem;color:#64748b;display:flex;align-items:center;gap:3px;white-space:nowrap;"
                                             title="Saisi le <?php echo e(\Carbon\Carbon::parse($sal->created_at)->format('d/m/Y à H:i')); ?>">
                                            <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            <span>Saisi : <strong><?php echo e($sal->createdBy?->name ?? '—'); ?></strong></span>
                                            <?php if($sal->created_at): ?>
                                            <span style="color:#94a3b8;">· <?php echo e(\Carbon\Carbon::parse($sal->created_at)->format('d/m H\hi')); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if(in_array($sal->status, ['validated', 'paid']) && $sal->validated_by): ?>
                                        <div style="font-size:0.7rem;color:#0d9488;display:flex;align-items:center;gap:3px;white-space:nowrap;"
                                             title="Validé le <?php echo e(\Carbon\Carbon::parse($sal->validated_at)->format('d/m/Y à H:i')); ?>">
                                            <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Validé : <strong><?php echo e($sal->validatedBy?->name ?? '—'); ?></strong></span>
                                            <?php if($sal->validated_at): ?>
                                            <span style="color:#94a3b8;">· <?php echo e(\Carbon\Carbon::parse($sal->validated_at)->format('d/m H\hi')); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if($sal->status === 'paid' && $sal->paid_by): ?>
                                        <div style="font-size:0.7rem;color:#1d4ed8;display:flex;align-items:center;gap:3px;white-space:nowrap;"
                                             title="Payé le <?php echo e(\Carbon\Carbon::parse($sal->paid_at)->format('d/m/Y à H:i')); ?>">
                                            <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                            </svg>
                                            <span>Payé : <strong><?php echo e($sal->paidBy?->name ?? '—'); ?></strong></span>
                                            <?php if($sal->paid_at): ?>
                                            <span style="color:#94a3b8;">· <?php echo e(\Carbon\Carbon::parse($sal->paid_at)->format('d/m H\hi')); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div style="display:flex;gap:4px">
                                        <?php if (! (auth()->user()->isEmployee())): ?>
                                        <a href="<?php echo e(route('salary.create', [$emp,'month'=>$sal->month,'year'=>$sal->year])); ?>"
                                           class="btn btn-sm btn-primary">Saisir</a>
                                        <?php endif; ?>
                                        <a href="<?php echo e(route('salary.show', $emp)); ?>" class="btn btn-sm btn-ghost">Historique</a>
                                        <a href="<?php echo e(route('salary.pdf', $sal)); ?>" class="btn btn-sm btn-ghost">PDF</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="11" style="text-align:center;padding:48px;color:var(--text-muted)">
                                Aucun bulletin trouvé pour cette période.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php if($employees->hasPages()): ?>
        <?php
            $currentPage = $employees->currentPage();
            $lastPage    = $employees->lastPage();
            $window      = 2;
            $rangeStart  = max(1, $currentPage - $window);
            $rangeEnd    = min($lastPage, $currentPage + $window);
        ?>
        <div class="pagination-summary">
            Affichage de <?php echo e($employees->firstItem()); ?> à <?php echo e($employees->lastItem()); ?> sur <?php echo e($employees->total()); ?> employés
        </div>
        <div class="custom-pagination">
            
            <?php if($employees->onFirstPage()): ?>
                <span class="page-btn disabled">‹ Précédent</span>
            <?php else: ?>
                <a href="<?php echo e($employees->appends(request()->query())->previousPageUrl()); ?>" class="page-btn">‹ Précédent</a>
            <?php endif; ?>

            
            <?php if($rangeStart > 1): ?>
                <a href="<?php echo e($employees->appends(request()->query())->url(1)); ?>" class="page-btn">1</a>
                <?php if($rangeStart > 2): ?>
                    <span class="page-dots">…</span>
                <?php endif; ?>
            <?php endif; ?>

            
            <?php for($p = $rangeStart; $p <= $rangeEnd; $p++): ?>
                <?php if($p == $currentPage): ?>
                    <span class="page-btn active"><?php echo e($p); ?></span>
                <?php else: ?>
                    <a href="<?php echo e($employees->appends(request()->query())->url($p)); ?>" class="page-btn"><?php echo e($p); ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            
            <?php if($rangeEnd < $lastPage): ?>
                <?php if($rangeEnd < $lastPage - 1): ?>
                    <span class="page-dots">…</span>
                <?php endif; ?>
                <a href="<?php echo e($employees->appends(request()->query())->url($lastPage)); ?>" class="page-btn"><?php echo e($lastPage); ?></a>
            <?php endif; ?>

            
            <?php if($employees->hasMorePages()): ?>
                <a href="<?php echo e($employees->appends(request()->query())->nextPageUrl()); ?>" class="page-btn">Suivant ›</a>
            <?php else: ?>
                <span class="page-btn disabled">Suivant ›</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
var currentCurrency = localStorage.getItem('paie_currency') || 'MAD';

function setCurrency(cur) {
    currentCurrency = cur;
    localStorage.setItem('paie_currency', cur);
    applyLabels();
}

function applyLabels() {
    var labels = document.querySelectorAll('.cur-label');
    for (var i = 0; i < labels.length; i++) {
        labels[i].textContent = currentCurrency;
    }
    document.getElementById('btnMAD').classList.toggle('active', currentCurrency === 'MAD');
    document.getElementById('btnMRU').classList.toggle('active', currentCurrency === 'MRU');
}

// Applique au chargement
applyLabels();
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/salary/index.blade.php ENDPATH**/ ?>