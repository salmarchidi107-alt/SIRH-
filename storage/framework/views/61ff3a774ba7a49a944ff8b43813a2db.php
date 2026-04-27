<?php $__env->startSection('title', 'Modèles de Documents'); ?>
<?php $__env->startSection('page-title', 'Modèles'); ?>

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
            <h5 class="mb-0 fw-bold" style="color:#0d2238;">Modèles de Documents</h5>
            <small class="text-muted"><?php echo e($modeles->total()); ?> modèle(s) enregistré(s)</small>
        </div>
        <button class="btn px-4 py-2 fw-semibold"
                style="background:#14b8a6;color:#fff;border-radius:10px;"
                onclick="ouvrirEtape1()">
            <i class="fas fa-plus me-2"></i>Nouveau Modèle
        </button>
    </div>

    
    <div id="etape1" style="display:none;" class="mb-4">
        <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
            <div class="card-header border-0 py-3 px-4" style="background:#f0fdfa;">
                <div class="d-flex align-items-center gap-2">
                    <span style="background:#14b8a6;color:#fff;width:24px;height:24px;border-radius:50%;
                                 display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;">1</span>
                    <h6 class="mb-0 fw-semibold" style="color:#0d2238;">Informations du modèle</h6>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <label class="form-label fw-semibold mb-1" style="color:#0d2238;">
                            Nom du modèle <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="step1Nom" class="ged-input"
                               placeholder="Ex: Attestation de travail">
                        <div id="errNom" style="color:#dc2626;font-size:12px;margin-top:4px;display:none;">Ce champ est obligatoire.</div>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label fw-semibold mb-1" style="color:#0d2238;">
                            Catégorie <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="step1Categorie" class="ged-input"
                               placeholder="Ex: Attestation, Contrat, Certificat…">
                        <div id="errCat" style="color:#dc2626;font-size:12px;margin-top:4px;display:none;">Ce champ est obligatoire.</div>
                    </div>
                </div>
                <div class="d-flex gap-3 pt-3" style="border-top:1px solid #e2e8f0;">
                    <button type="button" class="btn px-4 py-2 fw-semibold"
                            style="background:#14b8a6;color:#fff;border-radius:10px;min-width:140px;"
                            onclick="allerEtape2()">
                        Suivant <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                    <button type="button" class="btn px-4 py-2 fw-semibold"
                            style="background:#f1f5f9;color:#0d2238;border-radius:10px;"
                            onclick="annuler()">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <div id="etape2" style="display:none;" class="mb-4">
        <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
            <div class="card-header border-0 py-3 px-4 d-flex justify-content-between align-items-center"
                 style="background:#f0fdfa;">
                <div class="d-flex align-items-center gap-2">
                    <span style="background:#14b8a6;color:#fff;width:24px;height:24px;border-radius:50%;
                                 display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;">2</span>
                    <h6 class="mb-0 fw-semibold" style="color:#0d2238;">
                        Contenu du modèle
                        <span id="labelNomCat" style="color:#14b8a6;font-size:13px;font-weight:400;margin-left:8px;"></span>
                    </h6>
                </div>
                <button type="button" onclick="retourEtape1()"
                        style="background:none;border:none;color:#64748b;font-size:13px;cursor:pointer;">
                    <i class="fas fa-arrow-left me-1"></i> Retour
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

                <form id="modeleForm" method="POST" action="<?php echo e(route('ged.modeles.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method"  id="methodInput"      value="POST">
                    <input type="hidden" name="nom"       id="hiddenNom">
                    <input type="hidden" name="categorie" id="hiddenCategorie">

                    <textarea name="contenu" id="tinymceEditor"><?php echo e(old('contenu')); ?></textarea>

                    <div class="d-flex gap-3 pt-4" style="border-top:1px solid #e2e8f0;margin-top:16px;">
                        <button type="submit" class="btn px-4 py-2 fw-semibold"
                                onclick="syncAvantSoumission()"
                                style="background:#14b8a6;color:#fff;border-radius:10px;min-width:180px;">
                            <i class="fas fa-save me-2"></i>Sauvegarder le modèle
                        </button>
                        <button type="button" class="btn px-4 py-2 fw-semibold"
                                onclick="annuler()"
                                style="background:#f1f5f9;color:#0d2238;border-radius:10px;">
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
            <span class="fw-semibold" style="color:#0d2238;font-size:14px;">Liste des Modèles</span>
            <form method="GET" action="<?php echo e(route('ged.modeles.index')); ?>" style="margin:0;">
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
                        <th class="py-3 px-4" style="color:#0d2238;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">Nom du modèle</th>
                        <th class="py-3"       style="color:#0d2238;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">Catégorie</th>
                        <th class="py-3"       style="color:#0d2238;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">Date de création</th>
                        <th class="py-3 text-center" style="color:#0d2238;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $modeles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modele): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="py-3 px-4">
                            <span class="fw-semibold" style="color:#0d2238;font-size:14px;"><?php echo e($modele->nom); ?></span>
                        </td>
                        <td class="py-3">
                            <span style="background:#f0fdf4;color:#16a34a;font-weight:600;
                                         padding:4px 10px;border-radius:8px;font-size:12px;text-transform:capitalize;">
                                <?php echo e($modele->categorie ?? '—'); ?>

                            </span>
                        </td>
                        <td class="py-3 text-muted" style="font-size:13px;">
                            <?php echo e($modele->created_at->format('d/m/Y')); ?>

                        </td>

                        
                        <td class="py-3 px-4" style="white-space:nowrap;">
                            <div style="display:flex;flex-direction:row;align-items:center;gap:6px;flex-wrap:nowrap;">

                                
                                <button title="Modifier"
                                        style="display:inline-flex;align-items:center;justify-content:center;
                                               width:32px;height:32px;border-radius:8px;
                                               border:1.5px solid #e2e8f0;background:#fff;
                                               color:#374151;cursor:pointer;flex-shrink:0;"
                                        onclick="editModele(
                                            <?php echo e($modele->id); ?>,
                                            '<?php echo e(addslashes($modele->nom)); ?>',
                                            '<?php echo e(addslashes($modele->categorie)); ?>',
                                            `<?php echo e(addslashes($modele->contenu ?? '')); ?>`)">
                                    <i class="fas fa-pen" style="font-size:12px;"></i>
                                </button>

                                
                                <form action="<?php echo e(route('ged.modeles.destroy', $modele)); ?>" method="POST"
                                      style="display:inline-flex;margin:0;"
                                      onsubmit="return confirm('Supprimer ce modèle ?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" title="Supprimer"
                                            style="display:inline-flex;align-items:center;justify-content:center;
                                                   width:32px;height:32px;border-radius:8px;
                                                   background:#fee2e2;color:#dc2626;
                                                   border:none;cursor:pointer;flex-shrink:0;">
                                        <i class="fas fa-trash" style="font-size:12px;"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <i class="fas fa-layer-group fa-3x mb-3 d-block" style="color:#cbd5e1;"></i>
                            <p class="mb-0 fw-semibold text-muted">Aucun modèle pour le moment</p>
                            <small class="text-muted">Cliquez sur "Nouveau Modèle" pour commencer</small>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($modeles->hasPages()): ?>
        <div class="card-footer bg-white border-0 py-3 px-4">
            <?php echo e($modeles->links('pagination::bootstrap-5')); ?>

        </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── Inputs uniformes ── */
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


.tox-statusbar,
.tox-promotion,
.tox-statusbar__branding,
.tox-statusbar__text-container { display: none !important; }
.tox-statusbar { border-top: 1px solid #e2e8f0 !important; background: #f8fafc !important; }

.tox-tinymce { border-radius: 10px !important; border: 2px solid #e2e8f0 !important; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
tinymce.init({
    selector:    '#tinymceEditor',
    license_key: 'gpl',
    language:    'fr_FR',
    height:      460,
    menubar:     true,
    statusbar: true,
    branding: false,
    elementpath: false,
    wordcount: false,
    promotion:   false,
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline | link image media table | align lineheight | numlist bullist indent outdent | charmap | removeformat',
    content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 14px; color: #0d2238; }',
    setup: function(editor) {
        editor.on('change', function() { editor.save(); });
    }
});

/* ── Navigation étapes ── */
function ouvrirEtape1() {
    document.getElementById('step1Nom').value      = '';
    document.getElementById('step1Categorie').value = '';
    document.getElementById('modeleForm').action   = "<?php echo e(route('ged.modeles.store')); ?>";
    document.getElementById('methodInput').value   = 'POST';
    document.getElementById('etape1').style.display = 'block';
    document.getElementById('etape2').style.display = 'none';
    document.getElementById('etape1').scrollIntoView({ behavior: 'smooth' });
}

function allerEtape2() {
    const nom = document.getElementById('step1Nom').value.trim();
    const cat = document.getElementById('step1Categorie').value.trim();
    document.getElementById('errNom').style.display = nom ? 'none' : 'block';
    document.getElementById('errCat').style.display = cat ? 'none' : 'block';
    if (!nom || !cat) return;

    document.getElementById('hiddenNom').value       = nom;
    document.getElementById('hiddenCategorie').value = cat;
    document.getElementById('labelNomCat').textContent = '— ' + nom + ' (' + cat + ')';

    if (document.getElementById('methodInput').value === 'POST') {
        tinymce.get('tinymceEditor').setContent('');
    }

    document.getElementById('etape1').style.display = 'none';
    document.getElementById('etape2').style.display = 'block';
    document.getElementById('etape2').scrollIntoView({ behavior: 'smooth' });
}

function retourEtape1() {
    document.getElementById('etape2').style.display = 'none';
    document.getElementById('etape1').style.display = 'block';
}

function annuler() {
    document.getElementById('etape1').style.display = 'none';
    document.getElementById('etape2').style.display = 'none';
    if (tinymce.get('tinymceEditor')) tinymce.get('tinymceEditor').setContent('');
}

function syncAvantSoumission() {
    const editor = tinymce.get('tinymceEditor');
    if (editor) editor.save();
}

/* ── Modifier : saute directement à l'étape 2 avec contenu chargé ── */
function editModele(id, nom, categorie, contenu) {
    document.getElementById('step1Nom').value        = nom;
    document.getElementById('step1Categorie').value  = categorie;
    document.getElementById('modeleForm').action     = '/ged/modeles/' + id;
    document.getElementById('methodInput').value     = 'PUT';
    document.getElementById('hiddenNom').value       = nom;
    document.getElementById('hiddenCategorie').value = categorie;
    document.getElementById('labelNomCat').textContent = '— ' + nom + ' (' + categorie + ')';

    setTimeout(function() {
        if (tinymce.get('tinymceEditor')) {
            tinymce.get('tinymceEditor').setContent(contenu || '');
        }
    }, 100);

    document.getElementById('etape1').style.display = 'none';
    document.getElementById('etape2').style.display = 'block';
    document.getElementById('etape2').scrollIntoView({ behavior: 'smooth' });
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/ged/modeles.blade.php ENDPATH**/ ?>