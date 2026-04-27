<?php $__env->startSection('title', 'Modifier le Document'); ?>
<?php $__env->startSection('page-title', 'Modifier le Document'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4 px-4">

    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <ul class="mb-0"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="<?php echo e(route('ged.index')); ?>"
           style="color:#64748b;font-size:13px;text-decoration:none;">
            <i class="fas fa-folder-open me-1"></i>Documents
        </a>
        <span style="color:#cbd5e1;">›</span>
        <span style="color:#0d2238;font-size:13px;font-weight:600;">Modifier</span>
    </div>

    <form method="POST" action="<?php echo e(route('ged.update', $document)); ?>" id="editForm">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        
        <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
            <div class="card-header border-0 py-3 px-4" style="background:#f0fdfa;">
                <h6 class="mb-0 fw-semibold" style="color:#0d2238;">
                    <i class="fas fa-info-circle me-2" style="color:#14b8a6;"></i>
                    Informations du document
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">

                    
                    <div class="col-lg-6">
                        <label class="ged-label">Nom de Document <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="ged-input"
                               value="<?php echo e(old('nom', $document->nom)); ?>"
                               placeholder="Ex: Attestation de travail" required>
                    </div>

                    
                    <div class="col-lg-6">
                        <label class="ged-label">Employé <span class="text-danger">*</span></label>
                        <div class="ged-select-wrap">
                            <select name="employe_id" class="ged-select" required>
                                <option value="">— Sélectionner —</option>
                                <?php $__currentLoopData = $employes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($emp->id); ?>"
                                        <?php echo e(old('employe_id', $document->employe_id) == $emp->id ? 'selected' : ''); ?>>
                                        <?php echo e($emp->prenom); ?> <?php echo e($emp->nom); ?>

                                        <?php if($emp->matricule): ?>(<?php echo e($emp->matricule); ?>)<?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <i class="fas fa-chevron-down ged-select-icon"></i>
                        </div>
                    </div>

                    
                    <div class="col-lg-6">
                        <label class="ged-label">Modèle <span class="text-danger">*</span></label>
                        <div class="ged-select-wrap">
                            <select name="modele_id" class="ged-select" id="selectModele" required>
                                <option value="">— Choisir un modèle —</option>
                                <?php $__currentLoopData = $modeles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modele): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($modele->id); ?>"
                                            data-contenu="<?php echo e(addslashes($modele->contenu ?? '')); ?>"
                                        <?php echo e(old('modele_id', $document->modele_id) == $modele->id ? 'selected' : ''); ?>>
                                        <?php echo e($modele->nom); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <i class="fas fa-chevron-down ged-select-icon"></i>
                        </div>
                    </div>

                    
                    <div class="col-lg-6">
                        <label class="ged-label">Date du Document <span class="text-danger">*</span></label>
                        <input type="date" name="date_document" class="ged-input"
                               value="<?php echo e(old('date_document', $document->date_document->format('Y-m-d'))); ?>"
                               required>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
            <div class="card-header border-0 py-3 px-4 d-flex justify-content-between align-items-center"
                 style="background:#f0fdfa;">
                <h6 class="mb-0 fw-semibold" style="color:#0d2238;">
                    <i class="fas fa-pen-nib me-2" style="color:#14b8a6;"></i>
                    Contenu du document
                </h6>
                
                <button type="button" onclick="rechargerContenuModele()"
                        style="background:#f1f5f9;border:1.5px solid #e2e8f0;color:#0d2238;
                               border-radius:8px;padding:5px 12px;font-size:12px;
                               font-weight:600;cursor:pointer;">
                    <i class="fas fa-rotate-right me-1"></i> Recharger depuis le modèle
                </button>
            </div>
            <div class="card-body p-4">

                
                <div class="mb-3 p-3" style="background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
                    <span style="font-size:12px;color:#64748b;font-weight:600;">Variables disponibles : </span>
                    <code style="font-size:12px;">{{nom}}</code>
                    <code style="font-size:12px;margin-left:8px;">{{prenom}}</code>
                    <code style="font-size:12px;margin-left:8px;">{{matricule}}</code>
                    <code style="font-size:12px;margin-left:8px;">{{date}}</code>
                    <code style="font-size:12px;margin-left:8px;">{{poste}}</code>
                </div>

                <textarea name="contenu" id="tinymceEditor"><?php echo e(old('contenu', $document->contenu ?? $document->modele?->contenu ?? '')); ?></textarea>

            </div>
        </div>

        
        <div class="d-flex gap-3">
            <button type="submit" onclick="syncAvantSoumission()"
                    class="btn px-4 py-2 fw-semibold"
                    style="background:#14b8a6;color:#fff;border-radius:10px;min-width:180px;">
                <i class="fas fa-save me-2"></i>Sauvegarder les modifications
            </button>
            <a href="<?php echo e(route('ged.index')); ?>"
               class="btn px-4 py-2 fw-semibold"
               style="background:#f1f5f9;color:#0d2238;border-radius:10px;min-width:120px;">
                <i class="fas fa-times me-2"></i>Annuler
            </a>
        </div>

    </form>
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

/* ── TinyMCE — statusbar vide (juste le resize) ── */
.tox-tinymce { border-radius: 10px !important; border: 2px solid #e2e8f0 !important; }
.tox .tox-statusbar { background: #f8fafc !important; border-top: 1px solid #e2e8f0 !important; min-height: 20px !important; }
.tox .tox-statusbar__text-container,
.tox .tox-statusbar__path,
.tox .tox-statusbar__wordcount,
.tox .tox-statusbar__branding,
.tox-promotion { display: none !important; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
tinymce.init({
    selector:    '#tinymceEditor',
    license_key: 'gpl',
    language:    'fr_FR',
    height:      500,
    menubar:     true,
    statusbar:   true,
    branding:    false,
    elementpath: false,
    promotion:   false,
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline | link image media table | align lineheight | numlist bullist indent outdent | charmap | removeformat',
    content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 14px; color: #0d2238; }',
    setup: function(editor) {
        editor.on('change', function() { editor.save(); });
    }
});

function syncAvantSoumission() {
    const editor = tinymce.get('tinymceEditor');
    if (editor) editor.save();
}

/* Recharge le contenu TinyMCE depuis le modèle sélectionné */
function rechargerContenuModele() {
    const select  = document.getElementById('selectModele');
    const option  = select.options[select.selectedIndex];
    const contenu = option ? option.getAttribute('data-contenu') : '';
    if (!contenu) {
        alert('Aucun contenu trouvé pour ce modèle.');
        return;
    }
    if (confirm('Remplacer le contenu actuel par celui du modèle ?')) {
        const editor = tinymce.get('tinymceEditor');
        if (editor) editor.setContent(contenu);
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/ged/edit.blade.php ENDPATH**/ ?>