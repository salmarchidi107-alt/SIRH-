<?php $__env->startSection('title', 'Modifier - '.$employee->full_name); ?>
<?php $__env->startSection('page-title', 'Modifier un Employé'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Modifier : <?php echo e($employee->full_name); ?></h1>
        <p>Matricule : <?php echo e($employee->matricule); ?></p>
    </div>
    <a href="<?php echo e(route('employees.show', $employee)); ?>" class="btn btn-ghost">← Retour</a>
</div>

<?php if($errors->any()): ?>
    <div class="alert alert-danger" style="margin-bottom:16px;">
        <ul style="margin:0;padding-left:16px;">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li style="font-size:0.85rem;"><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?php echo e(route('employees.update', $employee)); ?>" method="POST" enctype="multipart/form-data">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

    
    <div class="card mb-4">
        <div class="card-header"><div class="card-title">Informations Personnelles</div></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Prénom *</label>
                    <input type="text" name="first_name" class="form-control"
                           value="<?php echo e(old('first_name', $employee->first_name)); ?>" required>
                    <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color:var(--danger);font-size:0.75rem"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="last_name" class="form-control"
                           value="<?php echo e(old('last_name', $employee->last_name)); ?>" required>
                    <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color:var(--danger);font-size:0.75rem"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control"
                           value="<?php echo e(old('email', $employee->email)); ?>" required>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color:var(--danger);font-size:0.75rem"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?php echo e(old('phone', $employee->phone)); ?>">
                </div>
                <div class="form-group">
                    <label>Date de naissance</label>
                    <input type="date" name="birth_date" class="form-control"
                           value="<?php echo e(old('birth_date', $employee->birth_date?->format('Y-m-d'))); ?>">
                </div>
                <div class="form-group">
    <label>Genre</label>
    <select name="genre" class="form-control">
        <option value="">Sélectionner...</option>
        <option value="homme" <?php echo e(old('genre', $employee->genre) == 'homme' ? 'selected' : ''); ?>>Homme</option>
        <option value="femme" <?php echo e(old('genre', $employee->genre) == 'femme' ? 'selected' : ''); ?>>Femme</option>
    </select>
    <?php $__errorArgs = ['genre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color:var(--danger);font-size:0.75rem"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
                <div class="form-group">
                    <label>CIN</label>
                    <input type="text" name="cin" class="form-control"
                           value="<?php echo e(old('cin', $employee->cin)); ?>">
                </div>
                <div class="form-group">
                    <label>Situation familiale</label>
                    <select name="family_situation" class="form-control">
                        <option value="">Sélectionner...</option>
                        <?php $__currentLoopData = ['célibataire' => 'Célibataire', 'marié(e)' => 'Marié(e)', 'divorcé(e)' => 'Divorcé(e)', 'veuf(ve)' => 'Veuf(ve)', 'en instance de divorce' => 'En instance de divorce']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($val); ?>" <?php echo e(old('family_situation', $employee->family_situation) == $val ? 'selected' : ''); ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Adresse</label>
                    <textarea name="address" class="form-control" rows="2"><?php echo e(old('address', $employee->address)); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Photo (laisser vide pour conserver)</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header"><div class="card-title">Informations Professionnelles</div></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Service / Département *</label>
                    <select name="department" class="form-control" required>
                        <option value="">— Sélectionner un département —</option>
                        <?php $__empty_1 = true; $__currentLoopData = $departments ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $deptName = is_object($dept) ? $dept->name : $dept; ?>
                            <option value="<?php echo e($deptName); ?>"
                                <?php echo e(old('department', $employee->department) == $deptName ? 'selected' : ''); ?>>
                                <?php echo e($deptName); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <option disabled>Aucun département disponible</option>
                        <?php endif; ?>
                    </select>
                    <?php $__errorArgs = ['department'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color:var(--danger);font-size:0.75rem"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php if(empty($departments) || count($departments) === 0): ?>
                        <small style="color:#f59e0b;font-size:0.75rem">
                            ⚠️ Aucun département configuré —
                            <a href="<?php echo e(route('parametrage.index', ['tab' => 'departments'])); ?>" style="color:#f59e0b">créez-en un dans Paramétrage</a>
                        </small>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Poste *</label>
                    <input type="text" name="position" class="form-control"
                           value="<?php echo e(old('position', $employee->position)); ?>" required>
                    <?php $__errorArgs = ['position'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color:var(--danger);font-size:0.75rem"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Type de diplôme</label>
                    <input type="text" name="diploma_type" class="form-control"
                           value="<?php echo e(old('diploma_type', $employee->diploma_type)); ?>"
                           placeholder="ex: Bac+5, Doctorat...">
                </div>
                <div class="form-group">
                    <label>Site de travail</label>
                    <input type="text" name="work_site" class="form-control"
                           value="<?php echo e(old('work_site', $employee->work_site)); ?>"
                           placeholder="ex: Hôpital Central">
                    <?php $__errorArgs = ['work_site'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color:var(--danger);font-size:0.75rem"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Compétences</label>
                    <input type="text" name="skills" class="form-control"
                           value="<?php echo e(old('skills', $employee->skills)); ?>">
                </div>
                <div class="form-group">
                    <label>Contrat *</label>
<select name="contract_type" class="form-control" required>
    <option value="">— Sélectionner —</option>
    <option value="CDI"     <?php echo e(old('contract_type', $employee->contract_type) == 'CDI'     ? 'selected' : ''); ?>>CDI</option>
    <option value="CDD"     <?php echo e(old('contract_type', $employee->contract_type) == 'CDD'     ? 'selected' : ''); ?>>CDD</option>
    <option value="Interim" <?php echo e(old('contract_type', $employee->contract_type) == 'Interim' ? 'selected' : ''); ?>>Intérim</option>
    <option value="Stage"   <?php echo e(old('contract_type', $employee->contract_type) == 'Stage'   ? 'selected' : ''); ?>>Stage</option>
</select>
                    <?php $__errorArgs = ['contract_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color:var(--danger);font-size:0.75rem"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Date d'embauche *</label>
                    <input type="date" name="hire_date" class="form-control"
                           value="<?php echo e(old('hire_date', $employee->hire_date->format('Y-m-d'))); ?>" required>
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="status" class="form-control">
                        <option value="active"   <?php echo e(old('status', $employee->status) == 'active'   ? 'selected' : ''); ?>>Actif</option>
                        <option value="inactive" <?php echo e(old('status', $employee->status) == 'inactive' ? 'selected' : ''); ?>>Inactif</option>
                        <option value="leave"    <?php echo e(old('status', $employee->status) == 'leave'    ? 'selected' : ''); ?>>En congé</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Salaire de base (MAD)</label>
                    <input type="number" name="base_salary" class="form-control"
                           value="<?php echo e(old('base_salary', $employee->base_salary)); ?>" min="0" step="100">
                </div>
                <div class="form-group">
                    <label>N° CNSS</label>
                    <input type="text" name="cnss" class="form-control"
                           value="<?php echo e(old('cnss', $employee->cnss)); ?>">
                </div>
                <div class="form-group">
                    <label>Nb. d'enfants</label>
                    <input type="number" name="children_count" class="form-control"
                           value="<?php echo e(old('children_count', $employee->children_count ?? 0)); ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Mode de paiement</label>
                    <select name="payment_method" class="form-control">
                        <option value="">Sélectionner...</option>
                        <?php $__currentLoopData = ['virement' => 'Virement', 'cash' => 'Espèces', 'chèque' => 'Chèque']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($val); ?>" <?php echo e(old('payment_method', $employee->payment_method) == $val ? 'selected' : ''); ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Banque</label>
                    <select name="bank" class="form-control">
                        <option value="">Sélectionner une banque...</option>
                        <optgroup label="Banques principales">
                            <?php $__currentLoopData = [
                                'Attijariwafa Bank', 'Banque Populaire', 'Bank of Africa',
                                'CIH Bank', 'Crédit Agricole du Maroc', 'BMCE Bank',
                                'CFG Bank', 'Société Générale Maroc', 'Al Barid Bank'
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($bank); ?>" <?php echo e(old('bank', $employee->bank) == $bank ? 'selected' : ''); ?>>
                                    <?php echo e($bank); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </optgroup>
                        <option value="Autre">Autre...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>RIB</label>
                    <input type="text" name="rib" class="form-control"
                           value="<?php echo e(old('rib', $employee->rib)); ?>"
                           placeholder="XX 12 3456 7890 1234 5678 90">
                </div>
                <div class="form-group full">
                    <label>Avantages contractuels</label>
                    <textarea name="contractual_benefits" class="form-control" rows="2"><?php echo e(old('contractual_benefits', $employee->contractual_benefits)); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Contact d'urgence</label>
                    <input type="text" name="emergency_contact" class="form-control"
                           value="<?php echo e(old('emergency_contact', $employee->emergency_contact)); ?>">
                </div>
                <div class="form-group">
                    <label>Téléphone urgence</label>
                    <input type="text" name="emergency_phone" class="form-control"
                           value="<?php echo e(old('emergency_phone', $employee->emergency_phone)); ?>">
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header"><div class="card-title">Pièces jointes</div></div>
        <div class="card-body">
            <div class="form-grid">
                <?php $__currentLoopData = [
                    'doc_casier'   => 'Casier judiciaire',
                    'doc_rib'      => 'Relevé bancaire (RIB)',
                    'doc_diplomes' => 'Copies des diplômes',
                    'doc_cin'      => 'Copie CIN / Carte d\'identité',
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $docLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="form-group">
                        <label>
                            <?php echo e($docLabel); ?>

                            <?php if($employee->{$field.'_path'}): ?>
                                <a href="<?php echo e(asset('storage/'.$employee->{$field.'_path'})); ?>" target="_blank"
                                   style="margin-left:6px;font-size:0.72rem;color:#16a34a;text-decoration:none;font-weight:400;">↗ voir</a>
                            <?php endif; ?>
                        </label>
                        <input type="file" name="<?php echo e($field); ?>" accept="application/pdf"
                               class="form-control doc-file-input <?php echo e($employee->{$field.'_path'} ? 'has-doc' : ''); ?>">
                        <?php $__errorArgs = [$field];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color:var(--danger);font-size:0.75rem"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <div class="form-group full">
                    <label>
                        Contrat de travail
                        <?php if($employee->doc_contrat_path): ?>
                            <a href="<?php echo e(asset('storage/'.$employee->doc_contrat_path)); ?>" target="_blank"
                               style="margin-left:6px;font-size:0.72rem;color:#16a34a;text-decoration:none;font-weight:400;">↗ voir</a>
                        <?php endif; ?>
                    </label>
                    <input type="file" name="doc_contrat" accept="application/pdf"
                           class="form-control doc-file-input <?php echo e($employee->doc_contrat_path ? 'has-doc' : ''); ?>">
                    <?php $__errorArgs = ['doc_contrat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color:var(--danger);font-size:0.75rem"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header"><div class="card-title">Détails du Contrat de Travail</div></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Temps de travail (h/semaine)</label>
                    <input type="number" name="work_hours" class="form-control"
                           value="<?php echo e(old('work_hours', $employee->work_hours)); ?>"
                           min="0" step="0.5" placeholder="ex: 40">
                    <?php $__errorArgs = ['work_hours'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color:var(--danger);font-size:0.75rem"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Début du contrat</label>
                    <input type="date" name="contract_start_date" class="form-control"
                           value="<?php echo e(old('contract_start_date', $employee->contract_start_date?->format('Y-m-d'))); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Date de fin (si CDD)</label>
                    <input type="date" name="contract_end_date" class="form-control"
                           value="<?php echo e(old('contract_end_date', $employee->contract_end_date?->format('Y-m-d'))); ?>">
                </div>
                <div class="form-group">
                    <label>Compteur Congés Payés (jours)</label>
                    <input type="number" name="cp_days" class="form-control"
                           value="<?php echo e(old('cp_days', $employee->cp_days ?? 0)); ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Compteur de temps (heures)</label>
                    <input type="number" name="work_hours_counter" class="form-control"
                           value="<?php echo e(old('work_hours_counter', $employee->work_hours_counter ?? 0)); ?>"
                           min="0" step="0.5">
                </div>
            </div>
            <div class="form-group full" style="margin-top:16px;">
                <label style="font-weight:600;margin-bottom:12px;display:block;">Jours de travail habituels</label>
                <?php
                    $employeeWorkDays = is_array($employee->work_days)
                        ? $employee->work_days
                        : json_decode($employee->work_days ?? '[]', true) ?? [];
                ?>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <?php $__currentLoopData = ['lundi' => 'Lun', 'mardi' => 'Mar', 'mercredi' => 'Mer', 'jeudi' => 'Jeu', 'vendredi' => 'Ven', 'samedi' => 'Sam']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:8px 16px;background:#f1f5f9;border-radius:8px;">
                            <input type="checkbox" name="work_days[]" value="<?php echo e($val); ?>"
                                <?php echo e(in_array($val, old('work_days', $employeeWorkDays)) ? 'checked' : ''); ?>>
                            <?php echo e($label); ?>

                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:8px 16px;background:#fee2e2;border-radius:8px;">
                        <input type="checkbox" name="work_days[]" value="dimanche"
                            <?php echo e(in_array('dimanche', old('work_days', $employeeWorkDays)) ? 'checked' : ''); ?>>
                        Dim (Day Off)
                    </label>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header"><div class="card-title">Liaison Compte Utilisateur</div></div>
        <div class="card-body">
            <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:16px;">
                Liez ce profil employé à un compte utilisateur pour permettre l'accès au tableau de bord.
            </p>
            <?php $linkedUser = \App\Models\User::find(old('user_id', $employee->user_id)); ?>
            <?php if($linkedUser): ?>
                <div style="display:flex;align-items:center;gap:12px;padding:12px;background:var(--surface-2);border-radius:8px;border:1px solid var(--border);">
                    <div style="width:40px;height:40px;border-radius:50%;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;font-weight:600;">
                        <?php echo e(strtoupper(substr($linkedUser->name, 0, 1))); ?>

                    </div>
                    <div>
                        <div style="font-weight:600;"><?php echo e($linkedUser->name); ?></div>
                        <div style="font-size:0.8rem;color:var(--text-muted);"><?php echo e($linkedUser->email); ?></div>
                    </div>
                    <a href="<?php echo e(route('employees.edit', [$employee, 'remove_user' => true])); ?>"
                       class="btn btn-danger btn-sm" style="margin-left:auto;">Délier</a>
                </div>
            <?php else: ?>
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Compte utilisateur lié</label>
                        <select name="user_id" class="form-control">
                            <option value="">Sélectionner un compte utilisateur...</option>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>"
                                    <?php echo e(old('user_id', $employee->user_id) == $user->id ? 'selected' : ''); ?>>
                                    <?php echo e($user->name); ?> (<?php echo e($user->email); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if($employee->user): ?>
    <div class="card mb-4">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div class="card-title">Mot de passe du compte</div>
            <div style="display:flex;align-items:center;gap:6px;font-size:0.82rem;color:var(--text-muted);">
                <span style="width:8px;height:8px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
                Compte lié : <?php echo e($employee->user->email); ?>

            </div>
        </div>
        <div class="card-body">

            
            <div style="display:flex;align-items:center;gap:12px;cursor:pointer;" id="toggle-pwd-label">
                <div style="position:relative;width:44px;height:24px;flex-shrink:0;">
                    <input type="checkbox" name="change_password" value="1" id="change_password"
                           <?php echo e(old('change_password') ? 'checked' : ''); ?>

                           style="opacity:0;width:0;height:0;position:absolute;">
                    <span id="pwd-toggle-track" style="
                        position:absolute;inset:0;border-radius:12px;cursor:pointer;
                        background:<?php echo e(old('change_password') ? '#16a34a' : 'var(--border)'); ?>;
                        transition:background .2s;">
                        <span id="pwd-toggle-thumb" style="
                            position:absolute;top:3px;
                            left:<?php echo e(old('change_password') ? '23px' : '3px'); ?>;
                            width:18px;height:18px;border-radius:50%;background:white;
                            transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.2);"></span>
                    </span>
                </div>
                <span style="font-size:0.88rem;font-weight:500;user-select:none;">Modifier le mot de passe</span>
            </div>

            
            <div id="password-fields" style="margin-top:20px;display:<?php echo e(old('change_password') ? 'block' : 'none'); ?>;">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nouveau mot de passe *</label>
                        <div class="password-group">
                            <input type="password" name="new_password" id="new_password"
                                   class="form-control" autocomplete="new-password"
                                   placeholder="Min. 8 caractères">
                            <button type="button" class="toggle-password" data-target="new_password" title="Afficher/masquer">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color:var(--danger);font-size:0.75rem"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                        
                        <div style="margin-top:8px;">
                            <div style="display:flex;gap:4px;margin-bottom:4px;">
                                <div class="pwd-bar" style="height:4px;flex:1;border-radius:2px;background:var(--border);transition:background .3s;"></div>
                                <div class="pwd-bar" style="height:4px;flex:1;border-radius:2px;background:var(--border);transition:background .3s;"></div>
                                <div class="pwd-bar" style="height:4px;flex:1;border-radius:2px;background:var(--border);transition:background .3s;"></div>
                                <div class="pwd-bar" style="height:4px;flex:1;border-radius:2px;background:var(--border);transition:background .3s;"></div>
                            </div>
                            <span id="pwd-strength-label" style="font-size:0.72rem;color:var(--text-muted);"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirmer le mot de passe *</label>
                        <div class="password-group">
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                   class="form-control" autocomplete="new-password"
                                   placeholder="Répéter le mot de passe">
                            <button type="button" class="toggle-password" data-target="new_password_confirmation" title="Afficher/masquer">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        
                        <span id="pwd-match-label" style="font-size:0.72rem;margin-top:4px;display:none;"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <div style="display:flex;gap:12px;justify-content:flex-end;margin-bottom:32px;">
        <a href="<?php echo e(route('employees.show', $employee)); ?>" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    </div>

</form>

<style>
.doc-file-input.has-doc {
    border-color:#16a34a;background-color:#f0fdf4;color:#15803d;
}
.doc-file-input.has-doc::-webkit-file-upload-button,
.doc-file-input.has-doc::file-selector-button {
    background:#16a34a;color:white;border:none;
    padding:6px 12px;border-radius:4px;cursor:pointer;font-size:0.82rem;
}
.password-group { position:relative; }
.password-group .form-control { padding-right:42px; }
.toggle-password {
    position:absolute;right:10px;top:50%;transform:translateY(-50%);
    background:none;border:none;cursor:pointer;padding:4px;
    opacity:0.5;transition:opacity .2s;color:var(--text-primary);
}
.toggle-password:hover { opacity:1; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Sync hire_date ↔ contract_start_date ────────────────
    const hireDate  = document.querySelector('[name="hire_date"]');
    const startDate = document.querySelector('[name="contract_start_date"]');
    if (hireDate && startDate) {
        hireDate.addEventListener('input',  () => startDate.value = hireDate.value);
        startDate.addEventListener('input', () => hireDate.value  = startDate.value);
    }

    // ── Toggle switch mot de passe ───────────────────────────
    const checkbox = document.getElementById('change_password');
    if (!checkbox) return;

    const fields  = document.getElementById('password-fields');
    const track   = document.getElementById('pwd-toggle-track');
    const thumb   = document.getElementById('pwd-toggle-thumb');
    const wrapper = document.getElementById('toggle-pwd-label');

    wrapper.addEventListener('click', function (e) {
        if (e.target === checkbox) return;
        checkbox.checked = !checkbox.checked;
        applyToggle();
    });
    checkbox.addEventListener('change', applyToggle);

    function applyToggle() {
        const on = checkbox.checked;
        fields.style.display   = on ? 'block' : 'none';
        track.style.background = on ? '#16a34a' : 'var(--border)';
        thumb.style.left       = on ? '23px' : '3px';
        if (!on) {
            document.getElementById('new_password').value              = '';
            document.getElementById('new_password_confirmation').value = '';
            document.querySelectorAll('.pwd-bar').forEach(b => b.style.background = 'var(--border)');
            document.getElementById('pwd-strength-label').textContent  = '';
            document.getElementById('pwd-match-label').style.display   = 'none';
        }
    }

    // ── Toggle visibilité ────────────────────────────────────
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            input.type  = input.type === 'text' ? 'password' : 'text';
            this.style.opacity = input.type === 'text' ? '1' : '0.5';
        });
    });

    // ── Indicateur de force ──────────────────────────────────
    const pwdInput      = document.getElementById('new_password');
    const confirmInput  = document.getElementById('new_password_confirmation');
    const bars          = document.querySelectorAll('.pwd-bar');
    const strengthLabel = document.getElementById('pwd-strength-label');
    const matchLabel    = document.getElementById('pwd-match-label');

    const levels = [
        { color:'#ef4444', label:'Très faible' },
        { color:'#f97316', label:'Faible'      },
        { color:'#eab308', label:'Moyen'        },
        { color:'#16a34a', label:'Fort'         },
    ];

    pwdInput.addEventListener('input', function () {
        const v   = this.value;
        let score = 0;
        if (v.length >= 8)          score++;
        if (/[A-Z]/.test(v))        score++;
        if (/[0-9]/.test(v))        score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;

        bars.forEach((bar, i) => {
            bar.style.background = i < score ? levels[score - 1].color : 'var(--border)';
        });
        strengthLabel.textContent = v.length ? (levels[score - 1]?.label ?? '') : '';
        strengthLabel.style.color = score > 0 ? levels[score - 1].color : 'var(--text-muted)';
        checkMatch();
    });

    // ── Indicateur correspondance ────────────────────────────
    confirmInput.addEventListener('input', checkMatch);

    function checkMatch() {
        const pwd     = pwdInput.value;
        const confirm = confirmInput.value;
        if (!confirm) { matchLabel.style.display = 'none'; return; }
        matchLabel.style.display = 'inline';
        if (pwd === confirm) {
            matchLabel.textContent  = '✓ Les mots de passe correspondent';
            matchLabel.style.color  = '#16a34a';
        } else {
            matchLabel.textContent  = '✗ Les mots de passe ne correspondent pas';
            matchLabel.style.color  = '#ef4444';
        }
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/employees/edit.blade.php ENDPATH**/ ?>