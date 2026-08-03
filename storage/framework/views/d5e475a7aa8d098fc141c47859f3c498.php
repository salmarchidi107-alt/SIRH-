<?php $__env->startSection('title', 'Fiche patrimoine — ' . $employee->first_name . ' ' . $employee->last_name); ?>
<?php $__env->startSection('page-title', 'Fiche salarié'); ?>

<?php $__env->startSection('content'); ?>

<div class="fs-header">
    <div class="fs-identity">
        <?php $ini = mb_strtoupper(mb_substr($employee->first_name ?? 'X', 0, 1) . mb_substr($employee->last_name ?? 'X', 0, 1)); ?>
        <div class="fs-avatar"><?php echo e($ini); ?></div>
        <div>
            <div class="fs-name"><?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?></div>
            <div class="fs-sub">
                <?php echo e($employee->employee_number ?? $employee->matricule ?? '—'); ?>

                — <?php echo e($employee->position ?? $employee->poste ?? '—'); ?>

                — <?php echo e($employee->department ?? $employee->departement ?? '—'); ?>

            </div>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="<?php echo e(route('equipements.fiche_salarie.pdf', $employee->id)); ?>"
           style="height:38px;padding:0 16px;background:#0f172a;color:#fff;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;gap:7px">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
            Exporter en PDF
        </a>
        <a href="<?php echo e(route('equipements.index', ['tab' => 'salarie'])); ?>"
           style="height:38px;padding:0 16px;background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;text-decoration:none;display:inline-flex;align-items:center">
            ← Retour
        </a>
    </div>
</div>

<div class="fs-grid4">
    <div class="fs-stat">
        <div class="v"><?php echo e($metrics_salarie['equipements_actuels']); ?></div>
        <div class="l">Équipements actuels</div>
    </div>
    <div class="fs-stat">
        <div class="v"><?php echo e(number_format($metrics_salarie['valeur_confiee'], 0, ',', ' ')); ?> MAD</div>
        <div class="l">Valeur confiée</div>
    </div>
    <div class="fs-stat">
        <div class="v"><?php echo e($metrics_salarie['derniere_affectation'] ? \Carbon\Carbon::parse($metrics_salarie['derniere_affectation'])->format('d/m/Y') : '—'); ?></div>
        <div class="l">Dernière affectation</div>
    </div>
    <div class="fs-stat">
        <div class="v"><?php echo e($metrics_salarie['decharges_signees']); ?> / <?php echo e($metrics_salarie['equipements_actuels']); ?></div>
        <div class="l">Décharges signées</div>
    </div>
</div>

<div class="eq-card">
    <div class="eq-card-title">Équipements actuellement en sa possession</div>
    <table class="eq-table">
        <thead>
            <tr>
                <th>Équipement</th>
                <th>Référence</th>
                <th>Pris le</th>
                <th>Retour prévu</th>
                <th>État remise</th>
                <th style="text-align:center">Décharge</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $affectations_actives; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="font-weight:500"><?php echo e($aff->equipement->designation ?? '—'); ?></td>
                <td class="mono"><?php echo e($aff->equipement->reference ?? '—'); ?></td>
                <td style="color:#64748b;font-size:12px"><?php echo e(optional($aff->date_affectation)->format('d/m/Y')); ?></td>
                <td style="color:#64748b;font-size:12px"><?php echo e($aff->date_retour_prevue ? optional($aff->date_retour_prevue)->format('d/m/Y') : 'Non défini'); ?></td>
                <td style="color:#64748b"><?php echo e($aff->etat_remise); ?></td>
                <td style="text-align:center">
                    <?php if($aff->decharge_signee): ?>
                        <span class="eq-badge b-green">Signée</span>
                    <?php else: ?>
                        <span class="eq-badge b-amber">En attente</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="6" style="text-align:center;color:#64748b;padding:24px;font-size:13px">
                    Aucun équipement actuellement affecté
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="eq-card">
    <div class="eq-card-title">Historique complet</div>
    <table class="eq-table">
        <thead>
            <tr>
                <th>Équipement</th>
                <th>Référence</th>
                <th>Pris le</th>
                <th>Rendu le</th>
                <th>État remise</th>
                <th>État retour</th>
                <th style="text-align:center">Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $historique; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="font-weight:500"><?php echo e($h->equipement->designation ?? '—'); ?></td>
                <td class="mono"><?php echo e($h->equipement->reference ?? '—'); ?></td>
                <td style="color:#64748b;font-size:12px"><?php echo e(optional($h->date_affectation)->format('d/m/Y')); ?></td>
                <td style="color:#64748b;font-size:12px"><?php echo e($h->date_retour_effectif ? optional($h->date_retour_effectif)->format('d/m/Y') : '—'); ?></td>
                <td style="color:#64748b"><?php echo e($h->etat_remise); ?></td>
                <td style="color:#64748b"><?php echo e($h->etat_retour ?? '—'); ?></td>
                <td style="text-align:center">
                    <?php
                        $badgeClass = match($h->statut) {
                            'Actif'     => 'b-blue',
                            'Restitué'  => 'b-green',
                            'Perdu'     => 'b-red',
                            default     => 'b-gray',
                        };
                    ?>
                    <span class="eq-badge <?php echo e($badgeClass); ?>"><?php echo e($h->statut); ?></span>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="7" style="text-align:center;color:#64748b;padding:24px;font-size:13px">
                    Aucun historique pour ce salarié
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/equipements/fiche_salarie.blade.php ENDPATH**/ ?>