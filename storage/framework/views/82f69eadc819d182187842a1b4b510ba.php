<?php $__env->startSection('title', 'Parametrage'); ?>
<?php $__env->startSection('page-title', 'Parametrage'); ?>

<?php $__env->startSection('content'); ?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Parametrage</h1>
        <p>Gestion des salles, departements et Pieces jointes</p>
    </div>
</div>


<?php if(session('error')): ?>
<div class="param-alert error" style="max-width:700px;margin-bottom:20px;"><?php echo e(session('error')); ?></div>
<?php endif; ?>

<?php $activeTab = request('tab', 'rooms'); ?>

<div class="param-tabs">
    <a href="<?php echo e(route('parametrage.index', ['tab'=>'rooms'])); ?>" class="param-tab <?php echo e($activeTab=='rooms' ? 'active' : ''); ?>">
        Salles <span style="background:#e0f2fe;color:#0369a1;font-size:0.7rem;font-weight:700;padding:1px 7px;border-radius:10px;"><?php echo e($rooms->count()); ?></span>
    </a>
    <a href="<?php echo e(route('parametrage.index', ['tab'=>'departments'])); ?>" class="param-tab <?php echo e($activeTab=='departments' ? 'active' : ''); ?>">
        Departements <span style="background:#dcfce7;color:#15803d;font-size:0.7rem;font-weight:700;padding:1px 7px;border-radius:10px;"><?php echo e($departments->count()); ?></span>
    </a>
    <a href="<?php echo e(route('parametrage.index', ['tab'=>'documents'])); ?>" class="param-tab <?php echo e($activeTab=='documents' ? 'active' : ''); ?>">
        Pieces jointes <span style="background:#fef3c7;color:#d97706;font-size:0.7rem;font-weight:700;padding:1px 7px;border-radius:10px;"></span>
    </a>
    <a href="<?php echo e(route('parametrage.index', ['tab'=>'localisation'])); ?>" class="param-tab <?php echo e($activeTab=='localisation' ? 'active' : ''); ?>">
         Localisation
    </a>
</div>


<div class="param-panel <?php echo e($activeTab=='rooms' ? 'active' : ''); ?>">
    <div class="param-grid">
        <div class="param-form-card">
            <div class="param-form-card-header"><h3>Nouvelle salle</h3></div>
            <div class="param-form-card-body">
                <form method="POST" action="<?php echo e(route('rooms.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php if($errors->has('name') || $errors->has('department_id')): ?>
                    <div class="param-alert error"><?php echo e($errors->first()); ?></div>
                    <?php endif; ?>
                    <div class="param-input-group">
                        <label class="param-label">Nom de la salle</label>
                        <input type="text" name="name" class="param-input" value="<?php echo e(old('name')); ?>" placeholder="Ex: Salle des urgences A" required>
                    </div>
                    <div class="param-input-group">
                        <label class="param-label">Departement</label>
                        <select name="department_id" class="param-input" required>
                            <option value="">Selectionner...</option>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($dept->id); ?>" <?php echo e(old('department_id')==$dept->id ? 'selected' : ''); ?>><?php echo e($dept->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="param-input-group">
                        <label class="param-label">Capacite (optionnel)</label>
                        <input type="number" name="capacity" class="param-input" value="<?php echo e(old('capacity')); ?>" min="1">
                    </div>
                    <div class="param-input-group">
                        <label class="param-label">Description (optionnel)</label>
                        <textarea name="description" class="param-input" rows="2" style="resize:vertical;"><?php echo e(old('description')); ?></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Creer la salle</button>
                </form>
            </div>
        </div>
        <div class="param-list-card">
            <div class="param-list-header">
                <h3>Toutes les salles <span class="param-count-badge"><?php echo e($rooms->count()); ?></span></h3>
                <input type="text" class="param-search" placeholder="Rechercher..." oninput="filterList(this,'rooms-list')">
            </div>
            <div id="rooms-list">
                <?php $__empty_1 = true; $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="param-item" data-search="<?php echo e(strtolower($room->name.' '.($room->department?->name ?? ''))); ?>">
                    <div class="param-item-left">
                        <div>
                            <div class="param-item-name"><?php echo e($room->name); ?></div>
                            <div class="param-item-sub"><?php echo e($room->department?->name ?? '—'); ?><?php if($room->capacity): ?> · <?php echo e($room->capacity); ?> places <?php endif; ?></div>
                        </div>
                    </div>
                    <div class="param-item-actions">
                        <button class="param-btn-sm" onclick="openRoomModal(<?php echo e($room->id); ?>,'<?php echo e(addslashes($room->name)); ?>',<?php echo e($room->department_id); ?>,<?php echo e($room->capacity ?? 'null'); ?>,'<?php echo e(addslashes($room->description ?? '')); ?>')">Modifier</button>
                        <form method="POST" action="<?php echo e(route('rooms.destroy',$room)); ?>" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="param-btn-sm danger">Supprimer</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="param-empty"><p>Aucune salle creee.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="param-panel <?php echo e($activeTab=='departments' ? 'active' : ''); ?>">
    <div class="param-grid">
        <div class="param-form-card">
            <div class="param-form-card-header"><h3>Nouveau departement</h3></div>
            <div class="param-form-card-body">
                <form method="POST" action="<?php echo e(route('departments.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php if($errors->has('name')): ?>
                    <div class="param-alert error"><?php echo e($errors->first('name')); ?></div>
                    <?php endif; ?>
                    <div class="param-input-group">
                        <label class="param-label">Nom</label>
                        <input type="text" name="name" class="param-input" value="<?php echo e(old('name')); ?>" placeholder="Ex: Cardiologie" required>
                    </div>
                    <div class="param-input-group">
                        <label class="param-label">Chef de service (optionnel)</label>
                        <input type="text" name="chef" class="param-input" value="<?php echo e(old('chef')); ?>" placeholder="Ex: Dr. Martin">
                    </div>
                    <div class="param-input-group">
                        <label class="param-label">Description (optionnel)</label>
                        <textarea name="description" class="param-input" rows="2" style="resize:vertical;"><?php echo e(old('description')); ?></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Creer le departement</button>
                </form>
            </div>
        </div>
        <div class="param-list-card">
            <div class="param-list-header">
                <h3>Tous les departements <span class="param-count-badge" style="background:#14b8a6;"><?php echo e($departments->count()); ?></span></h3>
                <input type="text" class="param-search" placeholder="Rechercher..." oninput="filterList(this,'departments-list')">
            </div>
            <div id="departments-list">
                <?php $__empty_1 = true; $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="param-item" data-search="<?php echo e(strtolower($dept->name.' '.($dept->code ?? '').' '.($dept->chef ?? ''))); ?>">
                    <div class="param-item-left">
                        <div>
                            <div class="param-item-name"><?php echo e($dept->name); ?>

                                <?php if($dept->code): ?><span style="background:#f3f4f6;color:#6b7280;font-size:0.65rem;font-weight:700;padding:1px 7px;border-radius:4px;margin-left:6px;"><?php echo e($dept->code); ?></span><?php endif; ?>
                            </div>
                            <div class="param-item-sub"><?php if($dept->chef): ?>Chef : <?php echo e($dept->chef); ?> · <?php endif; ?><?php echo e($dept->rooms_count ?? 0); ?> salle(s)</div>
                        </div>
                    </div>
                    <div class="param-item-actions">
                        <button class="param-btn-sm" onclick="openDeptModal(<?php echo e($dept->id); ?>,'<?php echo e(addslashes($dept->name)); ?>','<?php echo e(addslashes($dept->code ?? '')); ?>','<?php echo e($dept->color ?? '#0ea5e9'); ?>','<?php echo e(addslashes($dept->chef ?? '')); ?>','<?php echo e(addslashes($dept->description ?? '')); ?>')">Modifier</button>
                        <form method="POST" action="<?php echo e(route('departments.destroy',$dept)); ?>" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="param-btn-sm danger">Supprimer</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="param-empty"><p>Aucun departement cree.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="param-panel <?php echo e($activeTab=='documents' ? 'active' : ''); ?>">
    <div class="param-list-card">
        <div class="docs-filter-bar">
            <label>Filtrer par departement :</label>
            <form method="GET" action="<?php echo e(route('parametrage.index')); ?>" style="display:flex;align-items:center;gap:8px;">
                <input type="hidden" name="tab" value="documents">
                <select name="dept_filter" class="docs-filter-select" onchange="this.form.submit()">
                    <option value="">Tous les departements</option>
                    <?php $__currentLoopData = $departmentNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($dn); ?>" <?php echo e(request('dept_filter')==$dn ? 'selected' : ''); ?>><?php echo e($dn); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php if(request('dept_filter')): ?>
                <a href="<?php echo e(route('parametrage.index',['tab'=>'documents'])); ?>" style="font-size:0.8rem;color:#ef4444;text-decoration:none;padding:6px 10px;border:1px solid #fecaca;border-radius:6px;background:#fef2f2;">Reset</a>
                <?php endif; ?>
            </form>
            <span style="margin-left:auto;font-size:0.82rem;color:#6b7280;"><?php echo e($employeesWithDocs->count()); ?> employe(s)</span>
            <button class="btn-add-doc" onclick="openModal('addDocModal')">+ Ajouter un document</button>
        </div>
        <div style="overflow-x:auto;">
            <table class="docs-table">
                <thead>
                    <tr>
                        <th>Employe</th><th>Departement</th><th style="text-align:center;width:160px;">Documents</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $employeesWithDocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $ini = strtoupper(substr($emp->first_name,0,1).substr($emp->last_name,0,1)); ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="emp-avatar"><?php echo e($ini); ?></div>
                                <button onclick="openDocsModal(<?php echo e($emp->id); ?>,'<?php echo e(addslashes($emp->first_name)); ?> <?php echo e(addslashes($emp->last_name)); ?>')"
                                        style="background:none;border:none;cursor:pointer;font-weight:600;font-size:0.875rem;color:#0ea5e9;text-decoration:underline;padding:0;font-family:inherit;">
                                    <?php echo e($emp->first_name); ?> <?php echo e($emp->last_name); ?></button>
                            </div>
                        </td>
                        <td><span style="font-size:0.78rem;background:#f3f4f6;padding:3px 8px;border-radius:5px;color:#374151;"><?php echo e($emp->department ?? '—'); ?></span></td>
                        <td style="text-align:center;">
                            <?php if($emp->doc_count > 0): ?>
                            <button onclick="openDocsModal(<?php echo e($emp->id); ?>,'<?php echo e(addslashes($emp->first_name)); ?> <?php echo e(addslashes($emp->last_name)); ?>')"
                                    style="background:#dcfce7;color:#16a34a;font-size:0.78rem;font-weight:700;padding:3px 14px;border-radius:20px;cursor:pointer;border:none;font-family:inherit;">
                                <?php echo e($emp->doc_count); ?> doc(s)</button>
                            <?php else: ?>
                            <span style="background:#f3f4f6;color:#9ca3af;font-size:0.78rem;padding:3px 14px;border-radius:20px;display:inline-block;">Aucun</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" style="text-align:center;padding:40px;color:#6b7280;">Aucun employe trouve.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="param-panel <?php echo e($activeTab=='localisation' ? 'active' : ''); ?>">
    <div class="param-grid">

        
        <div class="param-form-card">
            <div class="loc-card-header">
                <h3> Ajouter / modifier une localisation</h3>
                <p>Chaque localisation peut être globale (tous les départements) ou spécifique à un département.</p>
            </div>
            <div class="loc-card-body">

                <div class="param-input-group">
                    <label class="param-label">Nom du site</label>
                    <input type="text" id="loc_site_name" class="param-input" placeholder="Ex: Hôpital Ibn Sina — Bâtiment A">
                </div>

                <div class="param-input-group">
                    <label class="param-label">Département concerné</label>
                    <select id="loc_department" class="param-input">
                        <option value="">— Tous les départements (global) —</option>
                        <?php $__currentLoopData = $departmentNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($dn); ?>"><?php echo e($dn); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <p style="font-size:0.72rem;color:#9ca3af;margin-top:4px;">
                        Si un département spécifique est sélectionné, cette localisation sera prioritaire pour les employés de ce département.
                    </p>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="param-input-group">
                        <label class="param-label">Latitude</label>
                        <input type="number" step="0.0000001" id="loc_lat" class="param-input" placeholder="34.0331000">
                    </div>
                    <div class="param-input-group">
                        <label class="param-label">Longitude</label>
                        <input type="number" step="0.0000001" id="loc_lng" class="param-input" placeholder="-5.0003000">
                    </div>
                </div>

                <div class="param-input-group">
                    <label class="param-label">Rayon de tolérance (mètres)</label>
                    <input type="number" id="loc_radius" class="param-input" style="max-width:140px;" value="300" min="50" max="5000">
                </div>

                <button type="button" class="loc-btn-locate" id="btn-locate-me">
                     Utiliser ma position actuelle
                </button>
                <div id="locate-status" class="loc-status"></div>

                <button type="button" class="btn-submit" id="btn-save-location">
                    Enregistrer cette localisation
                </button>
                <div id="save-feedback" style="margin-top:10px;font-size:0.82rem;"></div>
            </div>
        </div>

        
        <div class="param-list-card">
            <div class="param-list-header">
                <h3>Localisations enregistrées</h3>
                <button type="button" class="param-btn-sm" id="btn-refresh-locs">↻ Rafraîchir</button>
            </div>
            <div id="locs-list-content">
                <div style="text-align:center;padding:30px;color:#9ca3af;font-size:0.82rem;">Chargement…</div>
            </div>
        </div>

    </div>
</div>


<div class="param-modal-overlay" id="roomModal">
    <div class="param-modal">
        <div class="param-modal-header"><h3>Modifier la salle</h3><button class="btn-close" onclick="closeModal('roomModal')">&#x2715;</button></div>
        <form id="roomForm" method="POST">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="param-input-group"><label class="param-label">Nom</label><input type="text" name="name" id="rName" class="param-input" required></div>
            <div class="param-input-group"><label class="param-label">Departement</label>
                <select name="department_id" id="rDept" class="param-input" required>
                    <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d->id); ?>"><?php echo e($d->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="param-input-group"><label class="param-label">Capacite</label><input type="number" name="capacity" id="rCap" class="param-input" min="1"></div>
            <div class="param-input-group"><label class="param-label">Description</label><textarea name="description" id="rDesc" class="param-input" rows="2" style="resize:vertical;"></textarea></div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" onclick="closeModal('roomModal')" style="padding:9px 18px;border:1px solid #e5e7eb;border-radius:8px;background:white;cursor:pointer;font-size:0.875rem;font-family:inherit;">Annuler</button>
                <button type="submit" class="btn-submit" style="width:auto;padding:9px 24px;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>


<div class="param-modal-overlay" id="deptModal">
    <div class="param-modal">
        <div class="param-modal-header"><h3>Modifier le departement</h3><button class="btn-close" onclick="closeModal('deptModal')">&#x2715;</button></div>
        <form id="deptForm" method="POST">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="param-input-group"><label class="param-label">Nom</label><input type="text" name="name" id="dName" class="param-input" required></div>
            <div class="param-input-group"><label class="param-label">Code</label><input type="text" name="code" id="dCode" class="param-input" maxlength="10" style="text-transform:uppercase;"></div>
            <div class="param-input-group"><label class="param-label">Couleur</label><input type="color" name="color" id="dColor" style="width:48px;height:40px;border-radius:8px;border:1px solid #e5e7eb;cursor:pointer;padding:2px;"></div>
            <div class="param-input-group"><label class="param-label">Chef de service</label><input type="text" name="chef" id="dChef" class="param-input"></div>
            <div class="param-input-group"><label class="param-label">Description</label><textarea name="description" id="dDesc" class="param-input" rows="2" style="resize:vertical;"></textarea></div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" onclick="closeModal('deptModal')" style="padding:9px 18px;border:1px solid #e5e7eb;border-radius:8px;background:white;cursor:pointer;font-size:0.875rem;font-family:inherit;">Annuler</button>
                <button type="submit" class="btn-submit" style="width:auto;padding:9px 24px;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>


<div class="param-modal-overlay" id="addDocModal">
    <div class="param-modal" style="max-width:560px;">
        <div class="param-modal-header"><h3>Ajouter un document</h3><button class="btn-close" onclick="closeModal('addDocModal')">&#x2715;</button></div>
        <form method="POST" action="<?php echo e(route('parametrage.documents.upload')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="param-input-group">
                <label class="param-label">Nom du document <span style="color:#ef4444;">*</span></label>
                <input type="text" name="doc_name" class="param-input" required placeholder="Ex: Attestation de travail...">
            </div>
            <div class="param-input-group">
                <label class="param-label">Assigner a <span style="color:#ef4444;">*</span></label>
                <button type="button" class="target-option" id="opt-employee" onclick="selTarget('employee')">
                    <input type="radio" name="target_type" value="employee" id="tEmp" style="accent-color:#0ea5e9;margin-top:3px;flex-shrink:0;">
                    <div><div style="font-size:0.875rem;font-weight:700;color:#111827;">Un employe specifique</div><div style="font-size:0.75rem;color:#6b7280;margin-top:2px;">Choisir un employe et uploader son fichier</div></div>
                </button>
                <button type="button" class="target-option" id="opt-department" onclick="selTarget('department')">
                    <input type="radio" name="target_type" value="department" id="tDept" style="accent-color:#0ea5e9;margin-top:3px;flex-shrink:0;">
                    <div><div style="font-size:0.875rem;font-weight:700;color:#111827;">Un departement entier</div><div style="font-size:0.75rem;color:#6b7280;margin-top:2px;">Un fichier different par employe du departement</div></div>
                </button>
                <button type="button" class="target-option" id="opt-all" onclick="selTarget('all')">
                    <input type="radio" name="target_type" value="all" id="tAll" style="accent-color:#0ea5e9;margin-top:3px;flex-shrink:0;">
                    <div><div style="font-size:0.875rem;font-weight:700;color:#111827;">Tous les employes</div><div style="font-size:0.75rem;color:#6b7280;margin-top:2px;">Un fichier different par employe</div></div>
                </button>
            </div>
            <div id="zone-emp" style="display:none;">
                <div class="param-input-group">
                    <label class="param-label">Employe</label>
                    <select id="empSel" class="param-input" onchange="showEmpFile(this)">
                        <option value="">Selectionner...</option>
                        <?php $__currentLoopData = $allEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($e->id); ?>"><?php echo e($e->first_name); ?> <?php echo e($e->last_name); ?><?php if($e->department): ?> — <?php echo e($e->department); ?><?php endif; ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="param-input-group" id="emp-file-zone" style="display:none;">
                    <label class="param-label">Fichier <span style="color:#ef4444;">*</span></label>
                    <input type="file" id="empFile" class="param-input" accept=".pdf,.jpg,.jpeg,.png" style="padding:8px 14px;cursor:pointer;" onchange="bindFile(this)">
                    <p style="font-size:0.72rem;color:#9ca3af;margin-top:4px;">PDF, JPG, PNG — max 10 Mo</p>
                </div>
            </div>
            <div id="zone-dept" style="display:none;">
                <div class="param-input-group">
                    <label class="param-label">Departement</label>
                    <select id="deptSel" class="param-input" onchange="loadDept(this.value)">
                        <option value="">Selectionner...</option>
                        <?php $__currentLoopData = $departmentNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($dn); ?>"><?php echo e($dn); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div id="dept-list"></div>
            </div>
            <div id="zone-all" style="display:none;"><div id="all-list"></div></div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;padding-top:16px;border-top:1px solid #f3f4f6;">
                <button type="button" onclick="closeModal('addDocModal')" style="padding:9px 20px;border:1px solid #e5e7eb;border-radius:8px;background:white;cursor:pointer;font-size:0.875rem;font-family:inherit;color:#6b7280;font-weight:600;">Annuler</button>
                <button type="submit" class="btn-submit" style="width:auto;padding:9px 24px;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>


<div class="param-modal-overlay" id="docsModal">
    <div class="param-modal" style="max-width:560px;">
        <div class="param-modal-header">
            <h3 id="docsTitle">Documents</h3>
            <button class="btn-close" onclick="closeModal('docsModal')">&#x2715;</button>
        </div>
        <div id="docsContent"><div style="text-align:center;padding:30px;color:#9ca3af;">Chargement...</div></div>
    </div>
</div>

<script>
var EMPS = <?php echo json_encode($allEmployeesJs, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;

function filterList(inp, lid) {
    var q = inp.value.toLowerCase();
    var items = document.querySelectorAll('#' + lid + ' .param-item');
    for (var i = 0; i < items.length; i++) {
        items[i].style.display = items[i].dataset.search.indexOf(q) !== -1 ? '' : 'none';
    }
}

function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }
function openModal(id)  { document.getElementById(id).classList.add('open');    document.body.style.overflow = 'hidden'; }

document.querySelectorAll('.param-modal-overlay').forEach(function(o) {
    o.addEventListener('click', function(e) { if (e.target === o) closeModal(o.id); });
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.querySelectorAll('.param-modal-overlay.open').forEach(function(m) { closeModal(m.id); });
});

function openRoomModal(id, name, deptId, cap, desc) {
    document.getElementById('roomForm').action = '/rooms/' + id;
    document.getElementById('rName').value = name; document.getElementById('rDept').value = deptId;
    document.getElementById('rCap').value  = cap || ''; document.getElementById('rDesc').value = desc || '';
    openModal('roomModal');
}
function openDeptModal(id, name, code, color, chef, desc) {
    document.getElementById('deptForm').action = '/departments/' + id;
    document.getElementById('dName').value  = name; document.getElementById('dCode').value  = code  || '';
    document.getElementById('dColor').value = color || '#0ea5e9'; document.getElementById('dChef').value  = chef  || '';
    document.getElementById('dDesc').value  = desc  || ''; openModal('deptModal');
}

function selTarget(type) {
    document.getElementById('tAll').checked  = (type === 'all');
    document.getElementById('tDept').checked = (type === 'department');
    document.getElementById('tEmp').checked  = (type === 'employee');
    document.getElementById('opt-employee').classList.remove('selected');
    document.getElementById('opt-department').classList.remove('selected');
    document.getElementById('opt-all').classList.remove('selected');
    if (type === 'employee')   document.getElementById('opt-employee').classList.add('selected');
    if (type === 'department') document.getElementById('opt-department').classList.add('selected');
    if (type === 'all')        document.getElementById('opt-all').classList.add('selected');
    document.getElementById('zone-emp').style.display  = (type === 'employee')   ? 'block' : 'none';
    document.getElementById('zone-dept').style.display = (type === 'department') ? 'block' : 'none';
    document.getElementById('zone-all').style.display  = (type === 'all')        ? 'block' : 'none';
    if (type === 'all') loadAll();
}
function showEmpFile(sel) {
    var z = document.getElementById('emp-file-zone');
    if (sel.value) { z.style.display = 'block'; document.getElementById('empFile').dataset.empId = sel.value; document.getElementById('empFile').name = 'files[' + sel.value + ']'; }
    else { z.style.display = 'none'; }
}
function bindFile(inp) { if (inp.dataset.empId) inp.name = 'files[' + inp.dataset.empId + ']'; }
function makeRow(emp) {
    return '<div class="emp-file-row" id="row-' + emp.id + '"><div class="emp-file-avatar">' + emp.initials + '</div>'
         + '<div style="flex:1;min-width:0;"><strong style="display:block;font-size:0.85rem;color:#111827;">' + emp.name + '</strong>'
         + '<span style="font-size:0.72rem;color:#9ca3af;">' + (emp.department || '—') + '</span></div>'
         + '<input type="file" name="files[' + emp.id + ']" accept=".pdf,.jpg,.jpeg,.png" required style="font-size:0.78rem;cursor:pointer;max-width:180px;" onchange="markOk(this,' + emp.id + ')"></div>';
}
function markOk(inp, id) { var r = document.getElementById('row-' + id); if (inp.files && inp.files[0]) r.classList.add('ok'); else r.classList.remove('ok'); }
function loadDept(dept) {
    var c = document.getElementById('dept-list'); if (!dept) { c.innerHTML = ''; return; }
    var emps = EMPS.filter(function(e) { return e.department === dept; });
    if (!emps.length) { c.innerHTML = '<p style="font-size:0.82rem;color:#9ca3af;">Aucun employe.</p>'; return; }
    c.innerHTML = '<div style="margin-bottom:8px;font-size:0.78rem;color:#6b7280;font-weight:600;">' + emps.length + ' employe(s) :</div>' + emps.map(makeRow).join('');
}
function loadAll() {
    var c = document.getElementById('all-list');
    c.innerHTML = '<div style="margin-bottom:8px;font-size:0.78rem;color:#6b7280;font-weight:600;">' + EMPS.length + ' employe(s) :</div>' + EMPS.map(makeRow).join('');
}
function openDocsModal(empId, empName) {
    document.getElementById('docsTitle').textContent = 'Documents de ' + empName;
    document.getElementById('docsContent').innerHTML = '<div style="text-align:center;padding:30px;color:#9ca3af;">Chargement...</div>';
    openModal('docsModal');
    fetch('/parametrage/employees/' + empId + '/documents', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.docs || !data.docs.length) { document.getElementById('docsContent').innerHTML = '<div style="text-align:center;padding:30px;color:#9ca3af;">Aucun document pour cet employe.</div>'; return; }
        var html = '<div style="display:flex;flex-direction:column;gap:8px;">';
        for (var i = 0; i < data.docs.length; i++) {
            var d = data.docs[i]; var isFixed = (d.type === 'fixed');
            var badgeColor = isFixed ? 'background:#fef3c7;color:#d97706;' : 'background:#e0f2fe;color:#0369a1;';
            var badgeLabel = isFixed ? 'Document employe' : 'Upload manuel'; var delBtn = '';
            if (!isFixed) { delBtn = '<button onclick="delDoc(' + d.id + ',' + empId + ',\'' + empName.replace(/'/g, "\\'") + '\')" style="padding:5px 12px;background:#fef2f2;color:#ef4444;border:1px solid #fecaca;border-radius:6px;font-size:0.75rem;font-weight:600;cursor:pointer;font-family:inherit;">Supprimer</button>'; }
            html += '<div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border:1px solid #e5e7eb;border-radius:8px;background:#fafafa;">'
                  + '<div style="width:36px;height:36px;border-radius:8px;background:#e0f2fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#0369a1" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>'
                  + '<div style="flex:1;min-width:0;"><div style="font-weight:600;font-size:0.875rem;color:#111827;">' + d.name + '</div>'
                  + '<div style="font-size:0.72rem;color:#9ca3af;">' + d.original_name + '</div>'
                  + '<span style="font-size:0.65rem;' + badgeColor + 'padding:1px 7px;border-radius:4px;font-weight:600;display:inline-block;margin-top:3px;">' + badgeLabel + '</span></div>'
                  + '<div style="display:flex;gap:6px;flex-shrink:0;"><a href="' + d.url + '" target="_blank" style="padding:5px 12px;background:#dcfce7;color:#16a34a;border:none;border-radius:6px;font-size:0.75rem;font-weight:600;text-decoration:none;">Voir</a>' + delBtn + '</div></div>';
        }
        html += '</div>'; document.getElementById('docsContent').innerHTML = html;
    })
    .catch(function() { document.getElementById('docsContent').innerHTML = '<div style="text-align:center;padding:30px;color:#ef4444;">Erreur de chargement.</div>'; });
}
function delDoc(docId, empId, empName) {
    if (!confirm('Supprimer ce document ?')) return;
    var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/parametrage/documents/' + docId, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r) { return r.json(); }).then(function(data) { if (data.success) openDocsModal(empId, empName); });
}

// ── ONGLET LOCALISATION ─────────────────────────────────────────────────────
var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

var btnLocate = document.getElementById('btn-locate-me');
if (btnLocate) {
    btnLocate.addEventListener('click', function () {
        var status = document.getElementById('locate-status');
        if (!navigator.geolocation) { status.textContent = "Géolocalisation non supportée."; status.style.color = '#dc2626'; return; }
        status.textContent = 'Récupération…'; status.style.color = '#6b7280';
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                document.getElementById('loc_lat').value = pos.coords.latitude.toFixed(7);
                document.getElementById('loc_lng').value = pos.coords.longitude.toFixed(7);
                status.textContent = '✓ Position récupérée (± ' + Math.round(pos.coords.accuracy) + ' m)';
                status.style.color = '#16a34a';
            },
            function (err) { status.textContent = 'Erreur : ' + err.message; status.style.color = '#dc2626'; },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });
}

var btnSaveLoc = document.getElementById('btn-save-location');
if (btnSaveLoc) {
    btnSaveLoc.addEventListener('click', function () {
        var btn = this;
        var feedback = document.getElementById('save-feedback');
        var payload = {
            site_name:      document.getElementById('loc_site_name').value,
            department:     document.getElementById('loc_department').value || null,
            latitude:       document.getElementById('loc_lat').value,
            longitude:      document.getElementById('loc_lng').value,
            radius_meters:  document.getElementById('loc_radius').value,
        };
        btn.disabled = true; feedback.textContent = '';

        fetch('<?php echo e(route("site-location.store")); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                feedback.textContent = '✓ ' + data.message; feedback.style.color = '#16a34a';
                document.getElementById('loc_site_name').value = '';
                document.getElementById('loc_department').value = '';
                document.getElementById('loc_lat').value = '';
                document.getElementById('loc_lng').value = '';
                document.getElementById('loc_radius').value = '300';
                document.getElementById('locate-status').textContent = '';
                fetchLocations();
            } else {
                feedback.textContent = '✕ ' + (data.message || 'Erreur.'); feedback.style.color = '#dc2626';
            }
        })
        .catch(function (err) { feedback.textContent = '✕ Erreur réseau : ' + err.message; feedback.style.color = '#dc2626'; })
        .finally(function () { btn.disabled = false; });
    });
}

function fetchLocations() {
    var c = document.getElementById('locs-list-content');
    c.innerHTML = '<div style="text-align:center;padding:24px;color:#9ca3af;font-size:0.82rem;">Chargement…</div>';

    fetch('<?php echo e(route("site-location.index")); ?>', { headers: { 'Accept': 'application/json' } })
    .then(function (res) { return res.json(); })
    .then(function (data) {
        if (!data.success || !data.locations || !data.locations.length) {
            c.innerHTML = '<div class="param-empty"><p>Aucune localisation enregistrée.<br>Utilisez le formulaire ci-contre pour en créer une.</p></div>';
            return;
        }
        var html = '';
        data.locations.forEach(function (loc) {
            var isGlobal = !loc.department;
            var deptBadge = isGlobal
                ? '<span class="loc-saved-dept global">Tous les départements</span>'
                : '<span class="loc-saved-dept specific">' + loc.department + '</span>';
            var mapsUrl = 'https://www.google.com/maps?q=' + loc.latitude + ',' + loc.longitude;
            html += '<div class="loc-saved-row">'
                  + '<div style="flex:1;min-width:0;">'
                  + deptBadge
                  + '<div class="loc-saved-name">' + loc.site_name + '</div>'
                  + '<div class="loc-saved-coords">' + parseFloat(loc.latitude).toFixed(5) + '°, ' + parseFloat(loc.longitude).toFixed(5) + '°</div>'
                  + '<div class="loc-saved-radius">Rayon : ' + loc.radius_meters + ' m</div>'
                  + '</div>'
                  + '<div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end;flex-shrink:0;">'
                  + '<a href="' + mapsUrl + '" target="_blank" style="font-size:0.72rem;color:#0ea5e9;text-decoration:none;font-weight:600;">🗺 Maps</a>'
                  + '<button onclick="prefillForm(' + JSON.stringify(loc).replace(/"/g, '&quot;') + ')" class="param-btn-sm" style="font-size:0.7rem;"> Modifier</button>'
                  + '<button onclick="deleteLoc(' + loc.id + ')" class="param-btn-sm danger" style="font-size:0.7rem;">🗑 Suppr.</button>'
                  + '</div>'
                  + '</div>';
        });
        c.innerHTML = html;
    })
    .catch(function () {
        c.innerHTML = '<div style="color:#dc2626;font-size:0.8rem;padding:16px 20px;">Erreur de chargement.</div>';
    });
}

function prefillForm(loc) {
    document.getElementById('loc_site_name').value = loc.site_name || '';
    document.getElementById('loc_department').value = loc.department || '';
    document.getElementById('loc_lat').value = loc.latitude || '';
    document.getElementById('loc_lng').value = loc.longitude || '';
    document.getElementById('loc_radius').value = loc.radius_meters || 300;
    document.getElementById('locate-status').textContent = '';
    document.getElementById('save-feedback').textContent = '';
    document.getElementById('loc_site_name').scrollIntoView({ behavior: 'smooth', block: 'center' });
    document.getElementById('loc_site_name').focus();
}

function deleteLoc(id) {
    if (!confirm('Supprimer cette localisation ?')) return;
    fetch('<?php echo e(url("parametrage/site-location")); ?>/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    })
    .then(function (res) { return res.json(); })
    .catch(function () { alert('Erreur réseau.'); });
}

var btnRefreshLocs = document.getElementById('btn-refresh-locs');
if (btnRefreshLocs) { btnRefreshLocs.addEventListener('click', fetchLocations); }

fetchLocations();
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/parametrage/index.blade.php ENDPATH**/ ?>