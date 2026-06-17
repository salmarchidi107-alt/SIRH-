<?php $__env->startSection('title', 'Nouvel Employé'); ?>
<?php $__env->startSection('page-title', 'Ajouter un Employé'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ══════════════════════════════════════════════
   Permissions — styles
══════════════════════════════════════════════ */
.perm-col-labels {
    display: grid;
    grid-template-columns: 220px repeat(4, 1fr);
    padding: 8px 20px;
    border-bottom: 1px solid var(--border);
    background: var(--bg-secondary, #f8fafc);
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.perm-col-labels div:first-child { text-align: left; }
.perm-col-labels div:not(:first-child) { text-align: center; }

.perm-group-label {
    padding: 6px 20px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .06em;
    color: #94a3b8;
    text-transform: uppercase;
    background: #f1f5f9;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
}

.perm-row {
    display: grid;
    grid-template-columns: 220px repeat(4, 1fr);
    padding: 10px 20px;
    border-bottom: 1px solid var(--border);
    align-items: center;
    transition: background .15s;
}
.perm-row:last-child { border-bottom: none; }
.perm-row:hover { background: var(--bg-secondary, #f8fafc); }
.perm-row--sub { background: #fafbfc; }
.perm-row--sub:hover { background: #f1f5f9; }

.perm-mod-name {
    display: flex;
    align-items: center;
    gap: 9px;
    font-size: 13px;
    color: var(--text-primary, #1e293b);
}
.perm-row--sub .perm-mod-name {
    padding-left: 24px;
    color: #64748b;
    font-size: 12px;
}

.perm-cell {
    display: flex;
    justify-content: center;
    align-items: center;
}
.perm-cell input[type=checkbox] {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #14b8a6;
    border-radius: 3px;
}
.perm-na {
    color: #e2e8f0;
    font-size: 14px;
    font-weight: 500;
    user-select: none;
}

.perm-toolbar {
    padding: 10px 20px;
    border-bottom: 1px solid var(--border);
    background: var(--bg-secondary, #f8fafc);
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}
.perm-toolbar-label {
    font-size: 12px;
    color: #64748b;
    margin-right: 4px;
}
.perm-quick-btn {
    font-size: 12px;
    padding: 4px 12px;
    border-radius: 6px;
    border: 1px solid var(--border, #e2e8f0);
    background: #fff;
    color: #475569;
    cursor: pointer;
    transition: all .15s;
    font-weight: 500;
}
.perm-quick-btn:hover {
    background: #0d9488;
    color: #fff;
    border-color: #0d9488;
}
.perm-footer-note {
    padding: 10px 20px;
    font-size: 12px;
    color: #94a3b8;
    border-top: 1px solid var(--border);
    background: var(--bg-secondary, #f8fafc);
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ══════════════════════════════════════════════
   Misc
══════════════════════════════════════════════ */
.input-group { display: flex; gap: 8px; }
.input-group .form-control { flex: 1; }
.input-group .btn { white-space: nowrap; padding: 8px 16px; }
#pin_field { text-transform: uppercase; letter-spacing: 2px; font-family: monospace; font-weight: 600; }

.password-group { position: relative; }
.password-group .form-control { padding-right: 40px; }
.toggle-password {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; padding: 4px;
    opacity: .6; transition: opacity .2s;
}
.toggle-password:hover { opacity: 1; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php if($errors->any()): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?>

<div class="page-header">
    <div class="page-header-left">
        <h1>+ Nouvel Employé</h1>
        <p>Remplissez les informations du collaborateur</p>
    </div>
    <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-ghost">← Retour</a>
</div>

<form action="<?php echo e(route('employees.store')); ?>" method="POST" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Informations Personnelles</div>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Prénom *</label>
                    <input type="text" name="first_name" class="form-control"
                           value="<?php echo e(old('first_name')); ?>" required placeholder="Mohamed">
                    <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="last_name" class="form-control"
                           value="<?php echo e(old('last_name')); ?>" required>
                    <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?php echo e(old('email')); ?>" autocomplete="new-email">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" name="phone" class="form-control"
                        value="<?php echo e(old('phone')); ?>"
                        pattern="[0-9]{10}"
                        maxlength="10"
                        title="10 chiffres exacts requis"
                        inputmode="numeric">
                        <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Date de naissance</label>
                    <input type="date" name="birth_date" class="form-control" value="<?php echo e(old('birth_date')); ?>">
                </div>
                <div class="form-group">
    <label>Genre</label>
    <select name="gender" class="form-control">
        <option value="">Sélectionner...</option>
        <option value="homme" <?php echo e(old('gender') == 'homme' ? 'selected' : ''); ?>>Homme</option>
        <option value="femme" <?php echo e(old('gender') == 'femme' ? 'selected' : ''); ?>>Femme</option>
    </select>
    <?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
                <div class="form-group">
                    <label>CIN</label>
                    <input type="text" name="cin" class="form-control"
                        value="<?php echo e(old('cin')); ?>"
                        placeholder="AB123456"
                        pattern="[A-Za-z]{1,2}[0-9]{5,6}"
                        maxlength="8"
                        title="Format CIN invalide (ex : AB123456)"
                        oninput="this.value = this.value.toUpperCase()">
                    <?php $__errorArgs = ['cin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Situation familiale</label>
                    <select name="family_situation" class="form-control">
                        <option value="">Sélectionner...</option>
                        <?php $__currentLoopData = ['célibataire','marié(e)','divorcé(e)','veuf(ve)','en instance de divorce']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($sit); ?>" <?php echo e(old('family_situation') == $sit ? 'selected' : ''); ?>>
                                <?php echo e(ucfirst($sit)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="form-group full">
                    <label>Adresse</label>
                    <textarea name="address" class="form-control" rows="2"
                              placeholder="Adresse complète..."><?php echo e(old('address')); ?></textarea>
                    <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Photo de profil</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Code PIN Badge</label>
                    <div class="input-group">
                        <input type="text" name="pin" id="pin_field" class="form-control"
                               placeholder="1234AB" pattern="[0-9]{4}[A-Z]{2}"
                               maxlength="6" readonly value="<?php echo e(old('pin')); ?>">
                        <button type="button" id="generate_pin" class="btn btn-outline-primary">Générer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Informations Professionnelles</div>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Service / Département *</label>
                    <select name="department" class="form-control" required>
                        <option value="">— Sélectionner un département —</option>
                        <?php $__empty_1 = true; $__currentLoopData = $departments ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $deptName = is_object($dept) ? $dept->name : $dept; ?>
                            <option value="<?php echo e($deptName); ?>"
                                <?php echo e(old('department') == $deptName ? 'selected' : ''); ?>>
                                <?php echo e($deptName); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php endif; ?>
                    </select>
                    <?php $__errorArgs = ['department'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php if(($departments ?? collect())->isEmpty()): ?>
                        <small style="color:#f59e0b;font-size:.75rem">
                            ⚠️ Aucun département configuré —
                            <a href="<?php echo e(route('parametrage.index', ['tab'=>'departments'])); ?>"
                               style="color:#f59e0b">créez-en un dans Paramétrage</a>
                        </small>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Poste / Fonction *</label>
                    <input type="text" name="position" class="form-control"
                           value="<?php echo e(old('position')); ?>" required>
                    <?php $__errorArgs = ['position'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Type de diplôme</label>
                    <input type="text" name="diploma_type" class="form-control"
                           value="<?php echo e(old('diploma_type')); ?>" placeholder="ex: Bac+5, Doctorat...">
                </div>
                <div class="form-group">
                    <label>Site de travail</label>
                    <input type="text" name="work_site" class="form-control"
                           value="<?php echo e(old('work_site')); ?>">
                    <?php $__errorArgs = ['work_site'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Compétences et expérience</label>
                    <input type="text" name="skills" class="form-control" value="<?php echo e(old('skills')); ?>">
                </div>
                <div class="form-group">
                    <label>Type de contrat *</label>
                    <input type="text" name="contract_type" class="form-control"
                           value="<?php echo e(old('contract_type')); ?>" required
                           placeholder="ex: CDI, CDD, Freelance">
                    <?php $__errorArgs = ['contract_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Date d'embauche *</label>
                    <input type="date" name="hire_date" class="form-control"
                           value="<?php echo e(old('hire_date')); ?>" required>
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="status" class="form-control">
                        <option value="active"   <?php echo e(old('status','active') == 'active'   ? 'selected':''); ?>>Actif</option>
                        <option value="inactive" <?php echo e(old('status') == 'inactive'           ? 'selected':''); ?>>Inactif</option>
                        <option value="leave"    <?php echo e(old('status') == 'leave'              ? 'selected':''); ?>>En congé</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Responsable direct</label>
                    <select name="manager_id" class="form-control">
                        <option value="">Aucun</option>
                        <?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mgr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($mgr->id); ?>"
                                <?php echo e(old('manager_id') == $mgr->id ? 'selected' : ''); ?>>
                                <?php echo e($mgr->full_name); ?> — <?php echo e($mgr->position); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Pièces jointes à fournir</div>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Casier judiciaire</label>
                    <input type="file" name="doc_casier" class="form-control" accept="application/pdf">
                    <?php $__errorArgs = ['doc_casier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Relevé bancaire (RIB)</label>
                    <input type="file" name="doc_rib" class="form-control" accept="application/pdf">
                    <?php $__errorArgs = ['doc_rib'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Copies des diplômes</label>
                    <input type="file" name="doc_diplomes" class="form-control" accept="application/pdf">
                    <?php $__errorArgs = ['doc_diplomes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Copie CIN / Carte d'identité</label>
                    <input type="file" name="doc_cin" class="form-control" accept="application/pdf">
                    <?php $__errorArgs = ['doc_cin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group full">
                    <label>Contrat de travail</label>
                    <input type="file" name="doc_contrat" class="form-control" accept="application/pdf">
                    <?php $__errorArgs = ['doc_contrat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Rémunération &amp; Informations Sociales</div>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Salaire de base (MAD)</label>
                    <input type="number" name="base_salary" class="form-control"
                           value="<?php echo e(old('base_salary')); ?>" min="0" step="50" placeholder="8000">
                </div>
                <div class="form-group">
                    <label>N° CNSS</label>
                    <input type="text" name="cnss" class="form-control"
                           value="<?php echo e(old('cnss')); ?>" placeholder="1234567">
                </div>
                <div class="form-group">
                    <label>Nb. d'enfants</label>
                    <input type="number" name="children_count" class="form-control"
                           value="<?php echo e(old('children_count', 0)); ?>" min="0" placeholder="0">
                </div>
                <div class="form-group">
                    <label>Mode de paiement</label>
                    <select name="payment_method" class="form-control">
                        <option value="">Sélectionner...</option>
                        <option value="virement" <?php echo e(old('payment_method')=='virement'?'selected':''); ?>>Virement</option>
                        <option value="cash"     <?php echo e(old('payment_method')=='cash'    ?'selected':''); ?>>Espèces</option>
                        <option value="chèque"   <?php echo e(old('payment_method')=='chèque'  ?'selected':''); ?>>Chèque</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Banque</label>
                    <select name="bank" class="form-control">
                        <option value="">Sélectionner une banque...</option>
                        <optgroup label="Banques principales">
                            <?php $__currentLoopData = [
                                'Attijariwafa Bank','Banque Populaire','Bank of Africa',
                                'CIH Bank','Crédit Agricole du Maroc','BMCE Bank',
                                'CFG Bank','Société Générale Maroc','Al Barid Bank'
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($bank); ?>"
                                    <?php echo e(old('bank')==$bank?'selected':''); ?>><?php echo e($bank); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </optgroup>
                        <option value="Autre">Autre...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>RIB</label>
                    <input type="text" name="rib" class="form-control"
                           value="<?php echo e(old('rib')); ?>" placeholder="XX 12 3456 7890 1234 5678 90">
                </div>
                <div class="form-group full">
                    <label>Avantages contractuels</label>
                    <textarea name="contractual_benefits" class="form-control" rows="2"
                              placeholder="Primes, avantages..."><?php echo e(old('contractual_benefits')); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Contact d'urgence</label>
                    <input type="text" name="emergency_contact" class="form-control"
                           value="<?php echo e(old('emergency_contact')); ?>" placeholder="Nom du contact">
                </div>
                <div class="form-group">
                    <label>Téléphone urgence</label>
                    <input type="text" name="emergency_phone" class="form-control"
                           value="<?php echo e(old('emergency_phone')); ?>" placeholder="0612345678">
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Créer un compte utilisateur</div>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Rôle utilisateur</label>
                    <select name="user_role" class="form-control" id="user_role_select">
                        <option value="">Sélectionner rôle</option>
                        <option value="employee" <?php echo e(old('user_role','employee')=='employee'?'selected':''); ?>>Employé</option>
                        <option value="rh"       <?php echo e(old('user_role')=='rh'                ?'selected':''); ?>>Responsable RH</option>
                        <option value="admin"    <?php echo e(old('user_role')=='admin'             ?'selected':''); ?>>Administrateur</option>
                    </select>
                    <?php $__errorArgs = ['user_role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <div class="password-group">
                        <input type="password" name="user_password" id="user_password"
                               class="form-control" autocomplete="new-password">
                        <button type="button" class="toggle-password" data-target="user_password">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" opacity=".5"/>
                                <circle cx="12" cy="12" r="3.5"/>
                            </svg>
                        </button>
                    </div>
                    <?php $__errorArgs = ['user_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Confirmer mot de passe</label>
                    <div class="password-group">
                        <input type="password" name="user_password_confirmation"
                               id="user_password_confirmation" class="form-control">
                        <button type="button" class="toggle-password"
                                data-target="user_password_confirmation">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" opacity=".5"/>
                                <circle cx="12" cy="12" r="3.5"/>
                            </svg>
                        </button>
                    </div>
                    <?php $__errorArgs = ['user_password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <div class="form-group" style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="create_account" value="1"
                           id="create_account" <?php echo e(old('create_account')?'checked':''); ?>>
                    <span>Créer un compte utilisateur pour cet employé</span>
                </label>
            </div>
        </div>
    </div>

    
    <div class="card mb-4" id="perm-card">
        <div class="card-header" style="display:flex;align-items:center;gap:10px;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2" style="color:#14b8a6;flex-shrink:0">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6
                         11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623
                         5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152
                         c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
            <div class="card-title" style="margin:0;">Gestion des permissions</div>
            <span style="font-size:11px;background:#f1f5f9;color:#64748b;
                         border:1px solid #e2e8f0;border-radius:20px;padding:2px 10px;">
                Personnalisable par utilisateur
            </span>
        </div>

        
        <div class="perm-toolbar">
            <span class="perm-toolbar-label">Sélection rapide :</span>
            <button type="button" class="perm-quick-btn" onclick="Perms.selectAll()">Tout cocher</button>
            <button type="button" class="perm-quick-btn" onclick="Perms.deselectAll()">Tout décocher</button>
            <button type="button" class="perm-quick-btn" onclick="Perms.selectView()">Lecture seule</button>
            <button type="button" class="perm-quick-btn" onclick="Perms.selectRH()">Profil RH</button>
            <button type="button" class="perm-quick-btn" onclick="Perms.selectAdmin()">Profil Admin</button>
        </div>

        
        <div class="perm-col-labels">
            <div>Module</div>
            <div>Voir</div>
            <div>Créer</div>
            <div>Modifier</div>
            <div>Supprimer</div>
        </div>

        <div id="perm-table">

            
            <div class="perm-group-label">Principal</div>

            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'dashboard',
                'label'   => 'Tableau de bord',
                'actions' => ['view'],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="perm-group-label">Personnel</div>

            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'employees',
                'label'   => 'Employés',
                'actions' => ['view','create','edit','delete'],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'trombinoscope',
                'label'   => 'Trombinoscope',
                'actions' => ['view'],
                'sub'     => true,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'news',
                'label'   => 'Actualités',
                'actions' => ['view','create','edit','delete'],
                'sub'     => true,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="perm-group-label">Temps &amp; Présence</div>

            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'planning',
                'label'   => 'Planning',
                'actions' => ['view','create','edit','delete'],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'temps_vue',
                'label'   => "Vue d'ensemble",
                'actions' => ['view'],
                'sub'     => true,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'pointage',
                'label'   => 'Pointage',
                'actions' => ['view','create','edit','delete'],
                'sub'     => true,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="perm-group-label">Absences &amp; Congés</div>

            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'absences',
                'label'   => "Demandes d'absences",
                'actions' => ['view','create','edit','delete'],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'absences_calendar',
                'label'   => 'État visuel absences',
                'actions' => ['view'],
                'sub'     => true,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'absences_counters',
                'label'   => 'Compteurs &amp; droits',
                'actions' => ['view','edit'],
                'sub'     => true,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="perm-group-label">Formations (LMS)</div>

            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'lms',
                'label'   => 'Formations',
                'actions' => ['view','create','edit','delete'],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'referentiel',
                'label'   => 'Référentiel formations',
                'actions' => ['view','create','edit','delete'],
                'sub'     => true,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'lms_planning',
                'label'   => 'Planning formations',
                'actions' => ['view','create','edit','delete'],
                'sub'     => true,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="perm-group-label">Paie</div>

            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'salary',
                'label'   => 'Salaires',
                'actions' => ['view','create','edit','delete'],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
             <?php echo $__env->make('employees._perm_row', [
                'key'     => 'reporting',
                'label'   => 'Rapport RH',
                'actions' => ['view'],
                'sub'     => true,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="perm-group-label">GED</div>

            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'ged',
                'label'   => 'Documents',
                'actions' => ['view','create','edit','delete'],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'ged_modeles',
                'label'   => 'Modèles',
                'actions' => ['view','create','edit','delete'],
                'sub'     => true,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'ged_entete',
                'label'   => 'Entêtes',
                'actions' => ['view','create','edit','delete'],
                'sub'     => true,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="perm-group-label">Paramétrage &amp; Rapports</div>

            <?php echo $__env->make('employees._perm_row', [
                'key'     => 'parametrage',
                'label'   => 'Paramétrage',
                'actions' => ['view','create','edit','delete'],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="perm-group-label">Équipements</div>

<?php echo $__env->make('employees._perm_row', [
    'key'     => 'equipment',
    'label'   => 'Gestion des équipements',
    'actions' => ['view','create','edit','delete'],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        </div>

        <div class="perm-footer-note">
            Les colonnes grisées (—) indiquent qu'une action n'est pas applicable pour ce module.
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Détails du Contrat de Travail</div>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Temps de travail (h/semaine)</label>
                    <input type="number" name="work_hours" class="form-control"
                           value="<?php echo e(old('work_hours')); ?>" min="0" step=".5" placeholder="40">
                    <?php $__errorArgs = ['work_hours'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label>Début du contrat</label>
                    <input type="date" name="contract_start_date" class="form-control"
                           value="<?php echo e(old('contract_start_date')); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Date de fin (si CDD)</label>
                    <input type="date" name="contract_end_date" class="form-control"
                           value="<?php echo e(old('contract_end_date')); ?>">
                </div>
                <div class="form-group">
    <label>
        Congés antérieurs (jours déjà consommés)
    </label>
    <input type="number"
           name="conges_anterieurs"
           class="form-control"
           value="<?php echo e(old('conges_anterieurs', 0)); ?>"
           min="0"
           step="0"
           placeholder="0"
           title="Jours de congés déjà consommés avant la création du compte dans l'application">
    <small style="color:#64748b;font-size:0.72rem;margin-top:4px;display:block;">
        Ces jours seront automatiquement déduits du solde de congés calculé par le système.
    </small>
    <?php $__errorArgs = ['conges_anterieurs'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <span style="color:var(--danger);font-size:.75rem"><?php echo e($message); ?></span>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
                <div class="form-group">
                    <label>Compteur de temps (heures)</label>
                    <input type="number" name="work_hours_counter" class="form-control"
                           value="<?php echo e(old('work_hours_counter', 0)); ?>" min="0" step=".5" placeholder="0">
                </div>
            </div>
            <div class="form-group full" style="margin-top:16px;">
                <label style="font-weight:600;margin-bottom:12px;display:block;">
                    Jours de travail habituels
                </label>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <?php $__currentLoopData = ['lundi'=>'Lun','mardi'=>'Mar','mercredi'=>'Mer','jeudi'=>'Jeu','vendredi'=>'Ven','samedi'=>'Sam']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;
                                      padding:8px 16px;background:#f1f5f9;border-radius:8px;">
                            <input type="checkbox" name="work_days[]" value="<?php echo e($val); ?>"
                                <?php echo e(is_array(old('work_days')) && in_array($val, old('work_days')) ? 'checked' : ''); ?>>
                            <?php echo e($lbl); ?>

                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;
                                  padding:8px 16px;background:#fee2e2;border-radius:8px;">
                        <input type="checkbox" name="work_days[]" value="dimanche"
                            <?php echo e(is_array(old('work_days')) && in_array('dimanche', old('work_days')) ? 'checked' : ''); ?>>
                        Dim (Day Off)
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
        <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-ghost">Annuler</a>
        <button type="submit"  id="saveBtn" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2.5">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
            </svg>
            Enregistrer l'employé
        </button>
    </div>
</form>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── PIN Badge ─────────────────────────────────────────── */
    document.getElementById('generate_pin').addEventListener('click', function () {
        const digits  = Math.floor(1000 + Math.random() * 9000);
        const letters = Array.from({length:2}, () =>
            String.fromCharCode(65 + Math.floor(Math.random() * 26))
        ).join('');
        document.getElementById('pin_field').value = digits + letters;
        this.textContent = '✓ Généré';
        this.style.cssText = 'background:#10b981;color:#fff;';
        setTimeout(() => {
            this.textContent = 'Générer';
            this.style.cssText = '';
        }, 2000);
    });

    /* ── Toggle mot de passe ───────────────────────────────── */
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            input.type  = input.type === 'text' ? 'password' : 'text';
        });
    });

    /* ── Sync hire_date ↔ contract_start_date ─────────────── */
    const hireDate  = document.querySelector('[name="hire_date"]');
    const startDate = document.querySelector('[name="contract_start_date"]');
    if (hireDate && startDate) {
        hireDate.addEventListener('input',  () => startDate.value = hireDate.value);
        startDate.addEventListener('input', () => hireDate.value  = startDate.value);
    }

    /* ── Pré-remplissage permissions selon le rôle ─────────── */
    const roleSelect = document.getElementById('user_role_select');
    if (roleSelect) {
        roleSelect.addEventListener('change', function () {
            if (this.value === 'admin')         Perms.selectAdmin();
            else if (this.value === 'rh')       Perms.selectRH();
            else if (this.value === 'employee') Perms.selectEmployee();
        });
    }

    /* ── Afficher/masquer le bloc permissions selon la case ── */
    const createAccountCb = document.getElementById('create_account');
    const permCard        = document.getElementById('perm-card');
    function togglePermCard() {
        permCard.style.display = createAccountCb.checked ? '' : 'none';
    }
    togglePermCard();
    createAccountCb.addEventListener('change', togglePermCard);
});

/* ═══════════════════════════════════════════════════════
   Gestionnaire de permissions
═══════════════════════════════════════════════════════ */
const Perms = {

    all() {
        return document.querySelectorAll('#perm-table input[type=checkbox]:not([disabled])');
    },

    byAction(action) {
        return document.querySelectorAll(
            `#perm-table input[name*="[${action}]"]:not([disabled])`
        );
    },
    byModule(keys, actions = ['view','create','edit','delete']) {
        keys.forEach(key => {
            actions.forEach(action => {
                const cb = document.querySelector(
                    `#perm-table input[name="permissions[${key}][${action}]"]`
                );
                if (cb) cb.checked = true;
            });
        });
    },

    selectAll()   { this.all().forEach(c => c.checked = true);  },
    deselectAll() { this.all().forEach(c => c.checked = false); },

    selectView() {
        this.deselectAll();
        this.byAction('view').forEach(c => c.checked = true);
    },

    selectEmployee() {
        this.deselectAll();
        this.byModule(
            ['dashboard','trombinoscope','absences','lms','salary'],
            ['view']
        );
        const abCreate = document.querySelector(
            '#perm-table input[name="permissions[absences][create]"]'
        );
        if (abCreate) abCreate.checked = true;
    },

    selectRH() {
        this.deselectAll();
        this.byModule(
            [
                'dashboard','employees','trombinoscope','news',
                'planning','temps_vue','pointage',
                'absences','absences_calendar','absences_counters',
                'lms','referentiel','lms_planning',
                'salary','ged','ged_modeles','ged_entete',
                'reporting' ,'equipment' ,
            ],
            ['view','create','edit']
        );
    },

    selectAdmin() {
        this.selectAll();
    },
};
/* ── Limiter les années des champs date à 4 chiffres ── */
document.querySelectorAll('input[type="date"]').forEach(function(input) {
    input.addEventListener('change', function () {
        const val = this.value;
        if (!val) return;
        const parts = val.split('-');
        if (parts[0] && parts[0].length > 4) {
            parts[0] = parts[0].slice(0, 4);
            this.value = parts.join('-');
        }
    });
    input.addEventListener('input', function () {
        const year = this.value.split('-')[0];
        if (year && year.length > 4) {
            const [y, m, d] = this.value.split('-');
            this.value = `${y.slice(0, 4)}-${m || ''}-${d || ''}`;
        }
    });
    // Bloquer la soumission si l'année dépasse 4 chiffres
    input.closest('form')?.addEventListener('submit', function(e) {
        document.querySelectorAll('input[type="date"]').forEach(function(d) {
            const year = d.value.split('-')[0];
            if (year && year.length > 4) {
                e.preventDefault();
                d.setCustomValidity("L'année ne peut pas dépasser 4 chiffres.");
                d.reportValidity();
            } else {
                d.setCustomValidity('');
            }
        });
    }, { once: false });
});
document.querySelector("form").addEventListener("submit", function () {
    const btn = document.getElementById("saveBtn");
    btn.disabled = true;
    btn.innerHTML = "Enregistrement...";
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/employees/create.blade.php ENDPATH**/ ?>