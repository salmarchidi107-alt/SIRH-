

<?php $__env->startSection('title','Entête Documents'); ?>
<?php $__env->startSection('page-title','Mise en page'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4 px-4">
    <form method="POST" action="<?php echo e(route('ged.entete.store')); ?>" id="enteteForm">
        <?php echo csrf_field(); ?>

        <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:start;">

            
            <div style="display:flex;flex-direction:column;gap:16px;">

                
                <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                    <div class="card-header border-0 py-3 px-4" style="background:#f0fdfa;">
                        <h6 class="mb-0 fw-semibold" style="color:#0d2238;">
                            Mode d'édition
                        </h6>
                    </div>
                    <div class="card-body p-4" style="display:flex;flex-direction:column;gap:12px;">

                        <label class="mode-option" id="labelEntete">
                            <input type="radio" name="mode_edition" value="entete"
                                   id="modeEntete" checked onchange="switchMode('entete')">
                            <div>
                                <div class="mode-title">Entête</div>
                                <div class="mode-sub">Apparaît en haut du document</div>
                            </div>
                        </label>

                        <label class="mode-option" id="labelPied">
                            <input type="radio" name="mode_edition" value="pied_de_page"
                                   id="modePied" onchange="switchMode('pied_de_page')">
                            <div>
                                <div class="mode-title">Pied de page</div>
                                <div class="mode-sub">Apparaît en bas du document</div>
                            </div>
                        </label>

                    </div>
                </div>

                
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <button type="submit" onclick="syncTinyMCE()"
                            class="btn py-2 fw-semibold w-100"
                            style="background:#14b8a6;color:#fff;border-radius:10px;">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                    <a href="<?php echo e(url()->previous()); ?>"
                       class="btn py-2 fw-semibold w-100"
                       style="background:#f1f5f9;color:#374151;border-radius:10px;">
                        <i class="fas fa-times me-2"></i>Annuler
                    </a>
                </div>

            </div>

            
            <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                <div class="card-header border-0 py-3 px-4 d-flex align-items-center justify-content-between"
                     style="background:#f0fdfa;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <?php if($entete?->logo_path): ?>
                            <img src="<?php echo e(asset('storage/'.$entete->logo_path)); ?>"
                                 style="height:36px;object-fit:contain;border-radius:6px;">
                        <?php endif; ?>
                        <div>
                            <div id="modeLabel"
                                 style="font-size:13px;font-weight:600;color:#0d2238;">
                                Contenu de l'entête
                            </div>
                            <div id="modeSub" style="font-size:11px;color:#94a3b8;">
                                Apparaît en haut de chaque document exporté
                            </div>
                        </div>
                    </div>
                    
                    <span id="modeBadge"
                          style="background:#e0f2f1;color:#0f766e;font-size:11px;
                                 font-weight:700;padding:4px 12px;border-radius:20px;">
                        ENTÊTE
                    </span>
                </div>
                <div class="card-body p-4">
                    
                    <input type="hidden" name="contenu_libre"        id="hiddenEntete">
                    <input type="hidden" name="contenu_pied_de_page" id="hiddenPied">

                    <textarea id="tinymceEntete"></textarea>
                </div>
            </div>

        </div>
    </form>
</div>

<div id="varToast" style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
     background:#0d2238;color:#fff;font-size:12px;padding:8px 18px;border-radius:8px;
     opacity:0;transition:opacity .2s;pointer-events:none;z-index:9999;white-space:nowrap;"></div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.var-group-label { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;margin-bottom:6px; }
.var-chips-row   { display:flex;flex-wrap:wrap;gap:6px; }
.var-chip {
    display:inline-flex;align-items:center;
    padding:5px 10px;border-radius:8px;border:1.5px solid #e2e8f0;
    background:#fff;cursor:pointer;transition:all .15s;user-select:none;
}
.var-chip:hover  { background:#f0fdfa;border-color:#14b8a6; }
.var-chip:active { transform:scale(.96); }
.var-chip.flash  { background:#14b8a6;border-color:#14b8a6; }
.var-chip.flash .var-name { color:#fff; }
.var-name { font-family:monospace;font-size:11px;font-weight:600;color:#0d2238; }

/* Mode option radio */
.mode-option {
    display:flex;align-items:center;gap:12px;
    padding:12px 14px;border-radius:12px;
    border:2px solid #e2e8f0;background:#fff;
    cursor:pointer;transition:all .15s;
}
.mode-option:has(input:checked) {
    border-color:#14b8a6;background:#f0fdfa;
}
.mode-option input[type="radio"] { display:none; }
.mode-title { font-size:13px;font-weight:600;color:#0d2238; }
.mode-sub   { font-size:11px;color:#94a3b8;margin-top:2px; }

.tox-tinymce { border-radius:10px !important;border:2px solid #e2e8f0 !important; }
.tox .tox-statusbar,.tox-promotion { display:none !important; }

@media(max-width:1024px){
    form > div[style*="grid-template-columns"] { grid-template-columns:1fr !important; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Contenus initiaux en base64
const contenuEnteteInitial = atob(<?php echo json_encode($contenuLibre, 15, 512) ?>);
const contenuPiedInitial   = atob(<?php echo json_encode($contenuPiedDePage, 15, 512) ?>);

// État courant
let modeActuel   = 'entete';
let dataEntete   = contenuEnteteInitial;
let dataPied     = contenuPiedInitial;
</script>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
tinymce.init({
    selector:    '#tinymceEntete',
    license_key: 'gpl',
    language:    'fr_FR',
    height:      560,
    menubar:     true,
    statusbar:   false,
    branding:    false,
    promotion:   false,
    plugins: 'anchor autolink charmap codesample image lists table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline | table | align | numlist bullist | charmap | removeformat',
    content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; color: #0d2238; padding: 20px; }',
    setup: function(editor) {
        editor.on('init', function() {
            editor.setContent(dataEntete);
        });
        editor.on('change input', function() {
            // Sauvegarder dans la variable du mode actuel
            if (modeActuel === 'entete') {
                dataEntete = editor.getContent();
            } else {
                dataPied = editor.getContent();
            }
        });
    }
});

/* Changer de mode : sauvegarder l'actuel, charger l'autre */
function switchMode(mode) {
    const editor = tinymce.get('tinymceEntete');
    if (!editor) return;

    // Sauvegarder le contenu du mode actuel
    if (modeActuel === 'entete') {
        dataEntete = editor.getContent();
    } else {
        dataPied = editor.getContent();
    }

    modeActuel = mode;

    // Charger le contenu du nouveau mode
    editor.setContent(mode === 'entete' ? dataEntete : dataPied);

    // Mettre à jour les labels visuels
    const label  = document.getElementById('modeLabel');
    const sub    = document.getElementById('modeSub');
    const badge  = document.getElementById('modeBadge');

    if (mode === 'entete') {
        label.innerHTML = 'Contenu de l\'entête';
        sub.textContent = 'Apparaît en haut de chaque document exporté';
        badge.textContent = 'ENTÊTE';
        badge.style.background = '#e0f2f1';
        badge.style.color = '#0f766e';
    } else {
        label.innerHTML = 'Contenu du pied de page';
        sub.textContent = 'Apparaît en bas de chaque document exporté';
        badge.textContent = 'PIED DE PAGE';
        badge.style.background = '#fef3c7';
        badge.style.color = '#d97706';
    }
}

/* Avant soumission : écrire les deux contenus dans les champs cachés */
function syncTinyMCE() {
    const editor = tinymce.get('tinymceEntete');
    if (!editor) return;

    // Sauvegarder le mode actuel
    if (modeActuel === 'entete') {
        dataEntete = editor.getContent();
    } else {
        dataPied = editor.getContent();
    }

    document.getElementById('hiddenEntete').value = dataEntete;
    document.getElementById('hiddenPied').value   = dataPied;
}

function insererVariable(el, variable) {
    const editor = tinymce.get('tinymceEntete');
    if (editor) { editor.insertContent(variable); editor.focus(); }
    el.classList.add('flash');
    setTimeout(() => el.classList.remove('flash'), 400);
    showToast(variable + ' inséré ✓');
}

function showToast(msg) {
    const t = document.getElementById('varToast');
    t.textContent = msg; t.style.opacity = '1';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => { t.style.opacity = '0'; }, 1800);
}

// Sync avant submit via le bouton normal aussi
document.getElementById('enteteForm').addEventListener('submit', function() {
    syncTinyMCE();
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/ged/entete.blade.php ENDPATH**/ ?>