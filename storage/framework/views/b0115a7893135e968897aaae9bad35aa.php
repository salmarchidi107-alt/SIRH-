


<div id="modalFormation" class="modal fade" tabindex="-1" aria-labelledby="modalFormationLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px; border:0.5px solid var(--border-color, #e5e7eb);">

            <div class="modal-header" style="border-bottom:0.5px solid var(--border-color, #e5e7eb); padding:20px 24px 16px;">
                <h5 class="modal-title fw-500" id="modalFormationLabel" style="font-size:16px;">
                    <i class="ti ti-school me-2" style="color:#1D9E75;"></i>
                    Ajouter une formation
                </h5>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <form id="formFormation" action="<?php echo e(route('lms.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="formation_id" id="formationId">

                <div class="modal-body" style="padding:20px 24px;">

                    
                    <div class="mb-3">
                        <label class="form-label fw-500" style="font-size:13px;">
                            Département <span class="text-danger">*</span>
                        </label>
                        <select name="departement_id" id="selectDepartement" class="form-select form-select-sm" required>
                            <option value="">Choisir un département</option>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    
                    <div class="mb-3">
                        <label class="form-label fw-500" style="font-size:13px;">
                            Employé <span class="text-danger">*</span>
                        </label>
                        <select name="employee_id" id="selectEmployee" class="form-select form-select-sm" required disabled>
                            <option value="">Choisir d'abord un département</option>
                        </select>
                        <div class="spinner-border spinner-border-sm text-success d-none mt-1" id="empLoader" role="status"></div>
                    </div>

                    
                    <div class="mb-3">
                        <label class="form-label fw-500" style="font-size:13px;">
                            Formation <span class="text-danger">*</span>
                        </label>
                        <select name="titre" id="selectFormation" class="form-select form-select-sm" required onchange="toggleAutre(this, 'inputFormationAutre')">
                            <option value="">Sélectionner</option>
                            <?php $__currentLoopData = \App\Models\Formation::TITRES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $titre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($titre); ?>"><?php echo e($titre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <option value="autre">Autre…</option>
                        </select>
                        <input type="text" name="titre_autre" id="inputFormationAutre"
                               class="form-control form-control-sm mt-2 d-none"
                               placeholder="Nom de la formation">
                    </div>

                    
                    <div class="mb-3">
                        <label class="form-label fw-500" style="font-size:13px;">
                            Formateur <span class="text-danger">*</span>
                        </label>
                        <select name="formateur" id="selectFormateur" class="form-select form-select-sm" required onchange="toggleAutre(this, 'inputFormateurAutre')">
                            <option value="">Sélectionner</option>
                            <?php $__currentLoopData = \App\Models\Formation::FORMATEURS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($f); ?>"><?php echo e($f); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <option value="autre">Autre…</option>
                        </select>
                        <input type="text" name="formateur_autre" id="inputFormateurAutre"
                               class="form-control form-control-sm mt-2 d-none"
                               placeholder="Nom du formateur">
                    </div>

                    
                    <div class="mb-3">
                        <label class="form-label fw-500" style="font-size:13px;">
                            Organisme de formation <span class="text-danger">*</span>
                        </label>
                        <select name="organisme" id="selectOrganisme" class="form-select form-select-sm" required onchange="toggleAutre(this, 'inputOrganismeAutre')">
                            <option value="">Sélectionner</option>
                            <?php $__currentLoopData = \App\Models\Formation::ORGANISMES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($o); ?>"><?php echo e($o); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <option value="autre">Autre…</option>
                        </select>
                        <input type="text" name="organisme_autre" id="inputOrganismeAutre"
                               class="form-control form-control-sm mt-2 d-none"
                               placeholder="Nom de l'organisme">
                    </div>

                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-500" style="font-size:13px;">
                                Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="date" id="inputDate" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500" style="font-size:13px;">Heure début</label>
                            <input type="time" name="heure_debut" id="inputDebut" class="form-control form-control-sm" required value="08:00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500" style="font-size:13px;">Heure fin</label>
                            <input type="time" name="heure_fin" id="inputFin" class="form-control form-control-sm" required value="17:00">
                        </div>
                    </div>

                    
                    <div class="mb-0 mt-3">
                        <label class="form-label fw-500" style="font-size:13px;">Statut</label>
                        <select name="statut" class="form-select form-select-sm">
                            <?php $__currentLoopData = \App\Models\Formation::STATUTS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($s); ?>" <?php echo e($s === 'Planifiée' ? 'selected' : ''); ?>><?php echo e($s); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                </div>

                <div class="modal-footer" style="border-top:0.5px solid var(--border-color, #e5e7eb); padding:16px 24px; gap:8px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm" style="background:#1D9E75;color:#fff;border-color:#085041;">
                        <i class="ti ti-check me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\HP\SIRH-\resources\views/lms/partials/modal_add.blade.php ENDPATH**/ ?>