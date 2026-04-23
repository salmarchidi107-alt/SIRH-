<?php $__env->startSection('title', $employee->full_name); ?>
<?php $__env->startSection('page-title', 'Fiche Employé'); ?>

<?php $__env->startSection('content'); ?>
<!-- Hero Profile Banner -->
<div class="profile-hero mb-6">
    <div class="profile-photo">
        <?php if($employee->photo): ?>
            <img src="<?php echo e($employee->photo_url); ?>" alt="<?php echo e($employee->full_name); ?>">
        <?php else: ?>
            <?php echo e(strtoupper(substr($employee->first_name,0,1).substr($employee->last_name,0,1))); ?>

        <?php endif; ?>
    </div>
    <div>
        <div style="font-size:0.7rem;opacity:0.4;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Matricule: <?php echo e($employee->matricule); ?></div>
        <div class="profile-name"><?php echo e($employee->full_name); ?></div>
        <div class="profile-role"><?php echo e($employee->position); ?></div>
        <div class="profile-dept"> <?php echo e($employee->department); ?></div>
        <div style="margin-top:10px;display:flex;gap:8px">
            <?php if($employee->status == 'active'): ?>
                <span class="badge badge-success">● Actif</span>
            <?php elseif($employee->status == 'leave'): ?>
                <span class="badge badge-warning">◐ En congé</span>
            <?php else: ?>
                <span class="badge badge-neutral">○ Inactif</span>
            <?php endif; ?>
            <span class="badge badge-info"><?php echo e($employee->contract_type); ?></span>
        </div>
    </div>
    <div class="profile-meta">
            <div class="profile-meta-item">
            <div class="profile-meta-value"><?php echo e($employee->hire_date ? round(\Carbon\Carbon::parse($employee->hire_date)->floatDiffInYears(now()), 1) : 'N/A'); ?></div>
            <div class="profile-meta-label">Années d'ancienneté</div>
        </div>
        <div class="profile-meta-item">
            <div class="profile-meta-value"><?php echo e($employee->absences->count()); ?></div>
            <div class="profile-meta-label">Absences</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;min-height:100vh;">
    <div>
        <!-- Informations personnelles -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title">Informations Personnelles</div>
<?php if(in_array(auth()->user()->role ?? '', ['admin', 'rh'])): ?>
<a href="<?php echo e(route('employees.edit', $employee)); ?>" class="btn btn-outline btn-sm">Modifier</a>
<?php endif; ?>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label"> Email</div>
                        <div class="detail-value"><?php echo e($employee->email); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"> Téléphone</div>
                        <div class="detail-value"><?php echo e($employee->phone ?: '—'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"> Date de naissance</div>
                        <div class="detail-value"><?php echo e($employee->birth_date ? \Carbon\Carbon::parse($employee->birth_date)->format('d/m/Y') : '—'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"> CIN</div>
                        <div class="detail-value"><?php echo e($employee->cin ?: '—'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"> N° CNSS</div>
                        <div class="detail-value"><?php echo e($employee->cnss ?: '—'); ?></div>
                    </div>
<?php if(in_array(auth()->user()->role ?? '', ['admin', 'rh'])): ?>
                    <div class="detail-item">
                        <div class="detail-label"> PIN Badge</div>
                        <div class="detail-value">
                            <?php if($employee->plain_pin): ?>
                                <span id="pin-<?php echo e($employee->id); ?>" class="text-muted font-mono font-bold text-lg" style="letter-spacing: 2px;"><?php echo e($employee->plain_pin); ?></span>

                                <button onclick="regeneratePin(<?php echo e($employee->id); ?>)" class="btn btn-sm btn-warning ml-1">🔄 Regénérer</button>
                            <?php else: ?>
                                <button onclick="generatePin(<?php echo e($employee->id); ?>)" class="btn btn-sm btn-primary">Générer PIN</button>
                            <?php endif; ?>
                        </div>
                    </div>
<?php endif; ?>
                    <div class="detail-item">
                        <div class="detail-label"> Situation familiale</div>
                        <div class="detail-value"><?php echo e($employee->family_situation ?: '—'); ?></div>
                    </div>
                    <?php if($employee->address): ?>
                    <div class="detail-item" style="grid-column:1/-1">
                        <div class="detail-label"> Adresse</div>
                        <div class="detail-value"><?php echo e($employee->address); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if($employee->emergency_contact): ?>
                    <div class="detail-item">
                        <div class="detail-label"> Contact d'urgence</div>
                        <div class="detail-value"><?php echo e($employee->emergency_contact); ?> — <?php echo e($employee->emergency_phone); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Rest of the content unchanged -->
        <!-- Informations Professionnelles -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title"> Informations Professionnelles</div>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label"> Département</div>
                        <div class="detail-value"><?php echo e($employee->department); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"> Poste</div>
                        <div class="detail-value"><?php echo e($employee->position); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"> Type de contrat</div>
                        <div class="detail-value"><?php echo e($employee->contract_type ?: '—'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"> Date d'embauche</div>
                        <div class="detail-value"><?php echo e($employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y') : '—'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"> Diplôme</div>
                        <div class="detail-value"><?php echo e($employee->diploma_type ?: '—'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"> Compétences</div>
                        <div class="detail-value"><?php echo e($employee->skills ?: '—'); ?></div>
                    </div>
                    <?php if($employee->manager): ?>
                    <div class="detail-item">
                        <div class="detail-label"> Responsable</div>
                        <div class="detail-value"><?php echo e($employee->manager->full_name); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Contrat de Travail -->
        <div class="card mb-4" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd;">
            <div class="card-header">
                <div class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 8px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    Contrat de Travail
                </div>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                    <!-- Type de contrat -->
                    <div style="text-align: center; padding: 16px 8px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="width: 48px; height: 48px; margin: 0 auto 8px; background: #eff6ff; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        </div>
                        <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Type de contrat</div>
                        <div style="font-size: 0.9rem; font-weight: 600; color: #1e293b; margin-top: 4px;"><?php echo e($employee->contract_type ?: '—'); ?></div>
                    </div>

                    <!-- Temps de travail -->
                    <div style="text-align: center; padding: 16px 8px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="width: 48px; height: 48px; margin: 0 auto 8px; background: #fef3c7; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Temps de travail</div>
                        <div style="font-size: 0.9rem; font-weight: 600; color: #1e293b; margin-top: 4px;"><?php echo e($employee->work_hours ? $employee->work_hours . 'h/sem' : '—'); ?></div>
                    </div>

                    <!-- Début du contrat -->
                    <div style="text-align: center; padding: 16px 8px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="width: 48px; height: 48px; margin: 0 auto 8px; background: #dcfce7; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </div>
                        <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Début du contrat</div>
                        <div style="font-size: 0.9rem; font-weight: 600; color: #1e293b; margin-top: 4px;"><?php echo e($employee->contract_start_date ? $employee->contract_start_date->format('d/m/Y') : '—'); ?></div>
                    </div>

                    <!-- Fin du contrat -->
                    <div style="text-align: center; padding: 16px 8px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="width: 48px; height: 48px; margin: 0 auto 8px; background: #fee2e2; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><line x1="10" y1="14" x2="14" y2="14"></line></svg>
                        </div>
                        <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Date de fin</div>
                        <div style="font-size: 0.9rem; font-weight: 600; color: #1e293b; margin-top: 4px;"><?php echo e($employee->contract_end_date ? $employee->contract_end_date->format('d/m/Y') : 'CDI'); ?></div>
                    </div>
                </div>

                <!-- Jours de travail -->
                <div style="margin-top: 20px; padding: 16px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <div style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        Jours de travail habituels
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <?php
                            $days = ['lundi' => 'Lun', 'mardi' => 'Mar', 'mercredi' => 'Mer', 'jeudi' => 'Jeu', 'vendredi' => 'Ven', 'samedi' => 'Sam', 'dimanche' => 'Dim'];
                            $workDays = is_array($employee->work_days) ? $employee->work_days : json_decode($employee->work_days ?? '[]', true) ?? [];
                        ?>
                        <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(in_array($key, $workDays)): ?>
                                <span style="padding: 6px 12px; background: #dbeafe; color: #1e40af; border-radius: 20px; font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 4px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <?php echo e($label); ?>

                                </span>
                            <?php elseif($key == 'dimanche'): ?>
                                <span style="padding: 6px 12px; background: #fee2e2; color: #991b1b; border-radius: 20px; font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 4px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                                    <?php echo e($label); ?> (Day Off)
                                </span>
                            <?php else: ?>
                                <span style="padding: 6px 12px; background: #f1f5f9; color: #94a3b8; border-radius: 20px; font-size: 0.85rem; font-weight: 500;"><?php echo e($label); ?></span>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <!-- Compteurs -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 16px;">
                    <div style="padding: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); text-align: center;">
                        <div style="width: 56px; height: 56px; margin: 0 auto 12px; background: #d1fae5; border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Compteur Congés Payés (CP)</div>
                        <div style="font-size: 2rem; font-weight: 700; color: #059669; margin-top: 8px;"><?php echo e($employee->cp_days ?? 0); ?> <span style="font-size: 1rem; font-weight: 400; color: #64748b;">jours</span></div>
                    </div>
                    <div style="padding: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); text-align: center;">
                        <div style="width: 56px; height: 56px; margin: 0 auto 12px; background: #cffafe; border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div style="font-size: 0.7rem; color: #64748b; text-transform:uppercase; letter-spacing: 0.5px;">Compteur de Temps</div>
                        <div style="font-size: 2rem; font-weight: 700; color: #0891b2; margin-top: 8px;"><?php echo e($employee->work_hours_counter ?? 0); ?> <span style="font-size: 1rem; font-weight: 400; color: #64748b;">heures</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique absences -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title"> Historique des Absences</div>
                <a href="<?php echo e(route('absences.index', ['employee_id' => $employee->id])); ?>" class="btn btn-ghost btn-sm">Voir tout</a>
            </div>
            <div class="card-body" style="padding:0">
                <?php if($employee->absences->isEmpty()): ?>
                    <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:0.875rem">Aucune absence enregistrée</div>
                <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr><th>Type</th><th>Du</th><th>Au</th><th>Jours</th><th>Statut</th></tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $employee->absences->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="text-sm"><?php echo e(\App\Models\Absence::TYPES[$ab->type] ?? $ab->type); ?></td>
                                <td class="text-sm"><?php echo e($ab->start_date->format('d/m/Y')); ?></td>
                                <td class="text-sm"><?php echo e($ab->end_date->format('d/m/Y')); ?></td>
                                <td class="text-sm font-semibold"><?php echo e($ab->days); ?>j</td>
                                <td>
                                    <?php if($ab->status == 'approved'): ?> <span class="badge badge-success">Approuvé</span>
                                    <?php elseif($ab->status == 'pending'): ?> <span class="badge badge-warning">En attente</span>
                                    <?php elseif($ab->status == 'rejected'): ?> <span class="badge badge-danger">Rejeté</span>
                                    <?php else: ?> <span class="badge badge-neutral">Annulé</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
           <!-- Actions rapides -->
        <div class="card mb-4">
            <div class="card-header"><div class="card-title"> Actions Rapides</div></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                <a href="<?php echo e(route('absences.create')); ?>?employee_id=<?php echo e($employee->id); ?>" class="btn btn-outline w-full">
                    Déclarer une absence
                </a>
                <a href="<?php echo e(route('planning.show', $employee)); ?>" class="btn btn-outline w-full">
                    Voir le planning
                </a>
                <a href="<?php echo e(route('salary.show', $employee)); ?>" class="btn btn-outline w-full">
                     Dossier salaire
                </a>
                <?php if(in_array(auth()->user()->role ?? '', ['admin', 'rh'])): ?>
                <a href="<?php echo e(route('employees.edit', $employee)); ?>" class="btn btn-primary w-full">
                     Modifier le profil
                </a>
                <?php endif; ?>
            </div>
        </div>
        <!-- Manager -->
        <?php if($employee->manager): ?>
        <div class="card">
            <div class="card-header"><div class="card-title"> Responsable</div></div>
            <div class="card-body">
                <div style="display:flex;align-items:center;gap:12px">
                    <div class="table-avatar" style="width:44px;height:44px;font-size:15px">
                        <?php echo e(strtoupper(substr($employee->manager->first_name,0,1).substr($employee->manager->last_name,0,1))); ?>

                    </div>
                    <div>
                        <div class="table-name"><?php echo e($employee->manager->full_name); ?></div>
                        <div class="table-sub"><?php echo e($employee->manager->position); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<script>
function regeneratePin(id) {
  if (confirm('Regénérer le PIN?')) {
    fetch(`/employees/${id}/regenerate-pin`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json',
      }
    })
    .then(res => res.json())
    .then(data => {
      document.getElementById('pin-' + id).textContent = data.pin;
      alert('PIN regénéré: ' + data.pin);
    })
    .catch(err => alert('Erreur'));
  }
}
</script>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/employees/show.blade.php ENDPATH**/ ?>