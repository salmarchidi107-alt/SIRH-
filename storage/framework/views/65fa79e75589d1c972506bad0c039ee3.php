<?php $__env->startSection('title', 'Documents'); ?>
<?php $__env->startSection('page-title', 'Documents'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4 px-4">

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <ul class="mb-0"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0 fw-bold" style="color:#0d2238;">Documents</h5>
            <small class="text-muted"><?php echo e($documents->total()); ?> document(s) enregistré(s)</small>
        </div>
        <button class="btn px-4 py-2 fw-semibold"
                style="background:#14b8a6;color:#fff;border-radius:10px;"
                onclick="toggleForm()">
            <i class="fas fa-plus me-2"></i>Nouveau Document
        </button>
    </div>

    
    <div id="docFormContainer" style="display:none;" class="mb-4">
        <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
            <div class="card-header border-0 py-3 px-4" style="background:#f0fdfa;">
                <h6 class="mb-0 fw-semibold" style="color:#0d2238;">
                    <span id="formTitle">Nouveau Document</span>
                </h6>
            </div>
            <div class="card-body p-4">
                <form id="docForm" method="POST" action="<?php echo e(route('ged.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method" id="methodInput" value="POST">

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <label class="ged-label">Nom de Document <span class="text-danger">*</span></label>
                            <input type="text" name="nom" id="inputNom" class="ged-input"
                                   placeholder="Ex: Attestation de travail - Dupont" required>
                        </div>
                        <div class="col-lg-6">
                            <label class="ged-label">Employé <span class="text-danger">*</span></label>
                            <div class="ged-select-wrap">
                                <select name="employe_id" id="inputEmploye" class="ged-select" required>
                                    <option value="">— Sélectionner un employé —</option>
                                    <?php $__currentLoopData = $employes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($emp->id); ?>">
                                            <?php echo e($emp->prenom); ?> <?php echo e($emp->nom); ?>

                                            <?php if($emp->matricule): ?> (<?php echo e($emp->matricule); ?>) <?php endif; ?>
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <i class="fas fa-chevron-down ged-select-icon"></i>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="ged-label">Modèle <span class="text-danger">*</span></label>
                            <div class="ged-select-wrap">
                                <select name="modele_id" id="inputModele" class="ged-select" required>
                                    <option value="">— Choisir un modèle —</option>
                                    <?php $__currentLoopData = $modeles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modele): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($modele->id); ?>"><?php echo e($modele->nom); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <i class="fas fa-chevron-down ged-select-icon"></i>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="ged-label">Date du Document <span class="text-danger">*</span></label>
                            <input type="date" name="date_document" id="inputDate" class="ged-input"
                                   value="<?php echo e(now()->format('Y-m-d')); ?>" required>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-4 pt-3" style="border-top:1px solid #e2e8f0;">
                        <button type="submit" class="btn px-4 py-2 fw-semibold"
                                style="background:#14b8a6;color:#fff;border-radius:10px;min-width:180px;">
                            <i class="fas fa-magic me-2"></i>Générer le document
                        </button>
                        <button type="button" class="btn px-4 py-2 fw-semibold"
                                onclick="toggleForm()"
                                style="background:#f1f5f9;color:#0d2238;border-radius:10px;min-width:120px;">
                            <i class="fas fa-times me-2"></i>Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
        <div class="card-header border-0 px-4 d-flex justify-content-between align-items-center"
             style="background:#f0fdfa;height:54px;">
            <span class="fw-semibold" style="color:#0d2238;font-size:14px;">Liste des Documents</span>
            <form method="GET" action="<?php echo e(route('ged.index')); ?>" style="margin:0;">
                <div style="display:flex;align-items:center;background:#fff;
                            border-radius:50px;border:1.5px solid #e2e8f0;
                            padding:3px 3px 3px 14px;gap:4px;">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                           style="border:none;outline:none;background:transparent;
                                  font-size:12px;color:#374151;width:160px;line-height:1;"
                           placeholder="Search...">
                    <button type="submit"
                            style="background:#14b8a6;border:none;border-radius:50%;
                                   width:26px;height:26px;display:flex;align-items:center;
                                   justify-content:center;flex-shrink:0;cursor:pointer;padding:0;">
                        <i class="fas fa-search" style="color:#fff;font-size:10px;"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="py-3 px-4" style="color:#0d2238;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">Nom</th>
                        <th class="py-3"       style="color:#0d2238;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">Employé</th>
                        <th class="py-3"       style="color:#0d2238;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">Modèle</th>
                        <th class="py-3"       style="color:#0d2238;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">Date</th>
                        <th class="py-3 text-center" style="color:#0d2238;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;width:130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="py-3 px-4">
                            <span class="fw-semibold" style="color:#0d2238;font-size:14px;"><?php echo e($doc->nom); ?></span>
                        </td>
                        <td class="py-3">
                            <?php if($doc->employe): ?>
                                <span style="background:#e0f2f1;color:#0d2238;font-weight:600;
                                             padding:5px 10px;border-radius:8px;font-size:13px;">
                                    <?php echo e($doc->employe->prenom); ?> <?php echo e($doc->employe->nom); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3">
                            <?php if($doc->modele): ?>
                                <span style="background:#f0f9ff;color:#0369a1;font-weight:600;
                                             padding:5px 10px;border-radius:8px;font-size:13px;">
                                    <?php echo e($doc->modele->nom); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-muted" style="font-size:13px;">
                            <?php echo e($doc->date_document->format('d/m/Y')); ?>

                        </td>

                        
                        <td class="py-3 px-4" style="white-space:nowrap;">
                            <div style="display:flex;flex-direction:row;align-items:center;
                                        justify-content:center;gap:6px;flex-wrap:nowrap;">

                                
                                <a href="<?php echo e(route('ged.download', $doc)); ?>"
                                   title="Exporter"
                                   style="display:inline-flex;align-items:center;justify-content:center;
                                          width:32px;height:32px;border-radius:8px;flex-shrink:0;
                                          background:#f0f9ff;color:#0369a1;text-decoration:none;">
                                    <i class="fas fa-file-export" style="font-size:12px;"></i>
                                </a>

                                

                                <a href="<?php echo e(route('ged.edit', $doc)); ?>"
                                    title="Modifier"
                                    style="display:inline-flex;align-items:center;justify-content:center;
                                        width:32px;height:32px;border-radius:8px;flex-shrink:0;
                                        border:1.5px solid #e2e8f0;background:#fff;
                                        color:#374151;text-decoration:none;">
                                    <i class="fas fa-pen" style="font-size:12px;"></i>
                                </a>

                                
                                <form action="<?php echo e(route('ged.destroy', $doc)); ?>" method="POST"
                                      style="display:inline-flex;margin:0;"
                                      onsubmit="return confirm('Supprimer «<?php echo e(addslashes($doc->nom)); ?>» ?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" title="Supprimer"
                                            style="display:inline-flex;align-items:center;justify-content:center;
                                                   width:32px;height:32px;border-radius:8px;flex-shrink:0;
                                                   background:#fee2e2;color:#dc2626;border:none;cursor:pointer;">
                                        <i class="fas fa-trash" style="font-size:12px;"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x mb-3 d-block" style="color:#cbd5e1;"></i>
                            <p class="mb-0 fw-semibold text-muted">Aucun document pour le moment</p>
                            <small class="text-muted">Cliquez sur "Nouveau Document" pour commencer</small>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($documents->hasPages()): ?>
        <div class="card-footer bg-white border-0 py-3 px-4">
            <?php echo e($documents->links('pagination::bootstrap-5')); ?>

        </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.ged-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #0d2238;
    margin-bottom: 6px;
}
.ged-input {
    display: block;
    width: 100%;
    height: 46px;
    padding: 0 14px;
    font-size: 0.875rem;
    color: #0d2238;
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
}
.ged-input:focus {
    border-color: #14b8a6;
    box-shadow: 0 0 0 3px rgba(20,184,166,.12);
}
.ged-input::placeholder { color: #94a3b8; }

.ged-select-wrap { position: relative; }
.ged-select {
    display: block;
    width: 100%;
    height: 46px;
    padding: 0 40px 0 14px;
    font-size: 0.875rem;
    color: #0d2238;
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
}
.ged-select:focus {
    border-color: #14b8a6;
    box-shadow: 0 0 0 3px rgba(20,184,166,.12);
}
.ged-select-icon {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 11px;
    color: #94a3b8;
    pointer-events: none;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleForm() {
    const c = document.getElementById('docFormContainer');
    const isOpen = c.style.display === 'block';
    if (isOpen) { c.style.display = 'none'; resetForm(); }
    else {
        c.style.display = 'block';
        document.getElementById('formTitle').textContent = 'Nouveau Document';
        document.getElementById('inputDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('inputNom').focus();
        c.scrollIntoView({ behavior: 'smooth' });
    }
}

function resetForm() {
    const form = document.getElementById('docForm');
    form.reset();
    form.action = "<?php echo e(route('ged.store')); ?>";
    document.getElementById('methodInput').value = 'POST';
    document.getElementById('formTitle').textContent = 'Nouveau Document';
}

function editDoc(id, nom, employeId, modeleId, date) {
    document.getElementById('docFormContainer').style.display = 'block';
    document.getElementById('formTitle').textContent = 'Modifier le Document';
    document.getElementById('inputNom').value  = nom;
    document.getElementById('inputDate').value = date;
    if (employeId) document.getElementById('inputEmploye').value = employeId;
    if (modeleId)  document.getElementById('inputModele').value  = modeleId;
    const form = document.getElementById('docForm');
    form.action = '/ged/' + id;
    document.getElementById('methodInput').value = 'PUT';
    document.getElementById('docFormContainer').scrollIntoView({ behavior: 'smooth' });
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/ged/index.blade.php ENDPATH**/ ?>