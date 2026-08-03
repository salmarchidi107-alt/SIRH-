<?php $__env->startSection('title', 'Nouvelle Note de Frais'); ?>
<?php $__env->startSection('page-title', 'Nouvelle Note de Frais'); ?>

<?php $__env->startSection('content'); ?>
<div class="nf-wrapper">
    <div class="nf-top-tabs">
        <a href="<?php echo e(route('expenses.index')); ?>" class="nf-top-tab">Liste des notes</a>
        <a href="<?php echo e(route('expenses.create')); ?>" class="nf-top-tab active">Nouvelle note (OCR)</a>
    </div>

    <div class="nf-view">
        <div class="page-header">
            <div class="page-header-left">
                <h1>Nouvelle note de frais</h1>
                <p>Soumettre une note de frais, avec pré-remplissage automatique par OCR</p>
            </div>
            <a href="<?php echo e(route('expenses.index')); ?>" class="btn btn-ghost">← Retour</a>
        </div>

        <?php if($errors->any()): ?>
            <div style="
                background: #fef2f2; border: 2px solid #ef4444; border-radius: 12px;
                padding: 16px 20px; margin-bottom: 24px; box-shadow: 0 4px 16px rgba(239,68,68,0.10);
            ">
                <div style="font-weight:700;color:#991b1b;margin-bottom:8px;font-size:0.95rem;">
                     Veuillez corriger les erreurs suivantes :
                </div>
                <ul style="margin:0;padding-left:20px;color:#7f1d1d;font-size:0.9rem;line-height:1.7">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="nf-two-col">
            <div class="card" style="align-self:start">
                <div class="card-header" style="background:#eff6ff;border-bottom:2px solid #bfdbfe">
                    <div class="card-title" style="color:#1e3a5f">Scan automatique (OCR)</div>
                </div>
                <div class="card-body">
                    <div class="nf-dropzone" id="dropZone">
                        <div class="nf-dropzone-text">Cliquer ou glisser une image/PDF du reçu</div>
                        <input type="file" id="ocrFileInput" accept=".jpg,.jpeg,.png,.pdf" style="display:none">
                    </div>
                    <div id="ocrPreview" style="margin-top:12px;display:none">
                        <img id="ocrPreviewImg" style="max-width:100%;border-radius:8px;border:1px solid var(--border)">
                    </div>

                    <div id="ocrProgressWrap" style="display:none;margin-top:12px">
                        <div style="display:flex;align-items:center;gap:8px;font-size:0.82rem;color:#1e40af;margin-bottom:6px">
                            <span class="nf-spinner"></span>
                            <span>Analyse du justificatif en cours…</span>
                        </div>
                        <div style="background:#e5e7eb;border-radius:6px;height:6px;overflow:hidden">
                            <div id="ocrProgressBar" style="background:#3b82f6;height:100%;width:0%;transition:width .15s"></div>
                        </div>
                    </div>

                    <div class="nf-ocr-status" id="ocrStatus"></div>

                    <input type="hidden" name="receipt_path" id="field_receipt_path">
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title">Détails de la dépense</div></div>
                <div class="card-body">
                    <form action="<?php echo e(route('expenses.store')); ?>" method="POST" enctype="multipart/form-data" id="expense-form">
                        <?php echo csrf_field(); ?>

                        <div class="form-group">
    <label>Employé</label>
    <?php if($employee): ?>
        <input type="hidden" name="employee_id" value="<?php echo e($employee->id); ?>">
        <div style="padding:16px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius)">
            <h3 style="margin:0 0 8px 0;color:var(--primary);font-size:1.1rem"><?php echo e($employee->full_name); ?></h3>
            <div style="color:var(--text-muted);font-size:0.875rem"><?php echo e($employee->department); ?> — <?php echo e($employee->position); ?></div>
        </div>
    <?php else: ?>
        <select class="form-control" id="field_department_filter" style="margin-bottom:8px">
            <option value="">Tous les départements</option>
            <?php $__currentLoopData = $employees->pluck('department')->filter()->unique()->sort(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($dept); ?>"><?php echo e($dept); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <select name="employee_id" class="form-control <?php echo e($errors->has('employee_id') ? 'is-invalid' : ''); ?>" id="field_employee_id" required>
            <option value="">Sélectionner un employé</option>
            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($emp->id); ?>" data-department="<?php echo e($emp->department); ?>" <?php echo e((int) old('employee_id') === $emp->id ? 'selected' : ''); ?>>
                    <?php echo e($emp->full_name); ?> — <?php echo e($emp->department); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    <?php endif; ?>
</div>

                        <div class="form-group">
                            <label>Titre / Libellé</label>
                            <input type="text" name="title" class="form-control <?php echo e($errors->has('title') ? 'is-invalid' : ''); ?>"
                                   id="field_title" value="<?php echo e(old('title')); ?>" placeholder="Ex: Taxi aéroport, Déjeuner client…">
                            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="nf-grid-2">
                            <div class="form-group">
                                <label>Catégorie</label>
                                <select name="category" class="form-control" id="field_category">
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>" <?php echo e(old('category') == $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date de la dépense</label>
                                <input type="date" name="date" class="form-control <?php echo e($errors->has('date') ? 'is-invalid' : ''); ?>"
                                       id="field_date" value="<?php echo e(old('date')); ?>">
                                <?php $__errorArgs = ['date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        
                        <div class="nf-grid-4">
                            <div class="form-group">
                                <label>Montant HT</label>
                                <input type="number" name="amount_excluding_tax" class="form-control <?php echo e($errors->has('amount_excluding_tax') ? 'is-invalid' : ''); ?>"
                                       id="field_amount_ht" step="0.01" value="<?php echo e(old('amount_excluding_tax')); ?>" placeholder="0.00">
                                <?php $__errorArgs = ['amount_excluding_tax'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="form-group">
                                <label>TVA</label>
                                <input type="number" name="vat_amount" class="form-control <?php echo e($errors->has('vat_amount') ? 'is-invalid' : ''); ?>"
                                       id="field_vat" step="0.01" value="<?php echo e(old('vat_amount')); ?>" placeholder="0.00">
                                <?php $__errorArgs = ['vat_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="form-group">
                                <label>Montant TTC *</label>
                                <input type="number" name="amount" class="form-control <?php echo e($errors->has('amount') ? 'is-invalid' : ''); ?>"
                                       id="field_amount" step="0.01" value="<?php echo e(old('amount')); ?>" placeholder="0.00" required>
                                <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="form-group">
                                <label>Devise</label>
                                <select name="currency" class="form-control">
                                    <option>MAD</option><option>MRU</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description (optionnel)</label>
                            <textarea name="description" class="form-control" rows="2"><?php echo e(old('description')); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Justificatif</label>
                            <input type="file" name="receipt" class="form-control" id="field_receipt_manual">
                        </div>

                        <div style="display:flex;gap:10px;margin-top:8px">
                            <button type="submit" class="btn btn-primary" style="flex:1">✓ Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var dropZone = document.getElementById('dropZone');
var ocrFileInput = document.getElementById('ocrFileInput');
var receiptPathField = document.getElementById('field_receipt_path');
var ocrPreview = document.getElementById('ocrPreview');
var ocrPreviewImg = document.getElementById('ocrPreviewImg');
var ocrProgressWrap = document.getElementById('ocrProgressWrap');
var ocrProgressBar = document.getElementById('ocrProgressBar');
var ocrStatus = document.getElementById('ocrStatus');

// ── Filtre employés par département ──
var deptFilter = document.getElementById('field_department_filter');
var employeeSelect = document.getElementById('field_employee_id');

if (deptFilter && employeeSelect) {
    var allEmployeeOptions = Array.prototype.slice.call(employeeSelect.options);

    deptFilter.addEventListener('change', function () {
        var selectedDept = deptFilter.value;
        var currentValue = employeeSelect.value;
        employeeSelect.innerHTML = '';

        allEmployeeOptions.forEach(function (opt) {
            var dept = opt.getAttribute('data-department');
            if (opt.value === '' || !selectedDept || dept === selectedDept) {
                employeeSelect.appendChild(opt);
            }
        });

        // Si l'employé précédemment sélectionné n'appartient plus au département choisi,
        // on revient sur "Sélectionner un employé" plutôt que de garder une sélection incohérente
        if (employeeSelect.querySelector('option[value="' + currentValue + '"]') === null) {
            employeeSelect.value = '';
        }
    });
}

// ── Ouvrir le sélecteur de fichier au clic sur la dropzone ──
dropZone.addEventListener('click', function () {
    ocrFileInput.click();
});

// ── Drag & drop ──
dropZone.addEventListener('dragover', function (e) {
    e.preventDefault();
    dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', function () {
    dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', function (e) {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    if (e.dataTransfer.files && e.dataTransfer.files.length) {
        handleOcrFile(e.dataTransfer.files[0]);
    }
});

// ── Sélection via input file classique ──
ocrFileInput.addEventListener('change', function () {
    if (ocrFileInput.files.length) {
        handleOcrFile(ocrFileInput.files[0]);
    }
});

// ── Auto-calcul du TTC quand HT et TVA sont renseignés ──
// L'utilisateur garde la main : ceci ne fait que proposer une valeur,
// il peut toujours corriger le TTC lui-même après coup.
var fieldHt = document.getElementById('field_amount_ht');
var fieldVat = document.getElementById('field_vat');
var fieldTtc = document.getElementById('field_amount');

function recalcTtc() {
    var ht = parseFloat(fieldHt.value);
    var vat = parseFloat(fieldVat.value);
    if (!isNaN(ht) && !isNaN(vat)) {
        fieldTtc.value = (ht + vat).toFixed(2);
    }
}
fieldHt.addEventListener('input', recalcTtc);
fieldVat.addEventListener('input', recalcTtc);

function handleOcrFile(file) {
    // Aperçu si c'est une image (pas pour un PDF)
    if (file.type.indexOf('image') === 0) {
        var reader = new FileReader();
        reader.onload = function (e) {
            ocrPreviewImg.src = e.target.result;
            ocrPreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        ocrPreview.style.display = 'none';
    }

    // Copie le fichier dans le champ "Justificatif" manuel du formulaire,
    // pour qu'il soit soumis avec le reste des données au moment d'enregistrer
    try {
        var dt = new DataTransfer();
        dt.items.add(file);
        var manualInput = document.getElementById('field_receipt_manual');
        if (manualInput) {
            manualInput.files = dt.files;
        }
    } catch (e) {
        // Certains anciens navigateurs ne supportent pas DataTransfer ; sans gravité,
        // l'utilisateur pourra toujours choisir le fichier manuellement.
    }

    showOcrStatus('loading', '');
    ocrProgressWrap.style.display = 'block';
    ocrProgressBar.style.width = '0%';

    var formData = new FormData();
    formData.append('receipt', file);
    formData.append('_token', '<?php echo e(csrf_token()); ?>');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo e(route("expenses.ocr.scan")); ?>');
    xhr.setRequestHeader('Accept', 'application/json');

    xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
            var pct = Math.round((e.loaded / e.total) * 100);
            ocrProgressBar.style.width = pct + '%';
        }
    };

    xhr.onload = function () {
        ocrProgressWrap.style.display = 'none';
        var result;
        try {
            result = JSON.parse(xhr.responseText);
        } catch (err) {
            showOcrStatus('error', "Réponse invalide du serveur.");
            return;
        }

        if (xhr.status === 200 && result.success) {
            applyOcrResult(result);
        } else if (xhr.status === 429) {
            showOcrStatus('error', "Trop de scans OCR effectués, veuillez patienter une minute avant de réessayer.");
        } else {
            showOcrStatus('error', result.error || "Erreur lors de l'analyse OCR.");
        }
    };

    xhr.onerror = function () {
        ocrProgressWrap.style.display = 'none';
        showOcrStatus('error', "Erreur réseau lors de l'envoi du fichier.");
    };

    xhr.send(formData);
}

function applyOcrResult(result) {
    var data = result.data;

    fillField('field_title', data.title || '');
    fillField('field_amount_ht', data.amount_excluding_tax || '');
    fillField('field_vat', data.vat_amount || '');
    fillField('field_amount', data.amount || '');
    fillField('field_date', data.date || '');
    fillField('field_category', data.category || '');

    var descEl = document.querySelector('textarea[name="description"]');
    if (descEl && data.description) {
        descEl.value = data.description;
        pulse(descEl);
    }

    // Devise détectée par l'OCR : on sélectionne l'option correspondante si elle existe
    // dans la liste (MAD / MRU). Si l'OCR renvoie une devise absente de la liste,
    // on ne force rien : l'utilisateur garde la valeur par défaut du select.
    if (data.currency) {
        var currEl = document.querySelector('select[name="currency"]');
        if (currEl) {
            for (var i = 0; i < currEl.options.length; i++) {
                if (currEl.options[i].value === data.currency) {
                    currEl.selectedIndex = i;
                    break;
                }
            }
        }
    }

    if (receiptPathField) {
        receiptPathField.value = result.attachment_path;
    }

    if (result.warnings && result.warnings.length) {
        var labels = result.warnings.map(function (w) {
            return w === 'amount_not_detected' ? 'montant' : (w === 'date_not_detected' ? 'date' : w);
        });
        showToast('warning', "Analyse terminée, mais certains champs n'ont pas été détectés : " + labels.join(', ') + ". Vérifiez avant d'enregistrer.");
    } else {
        showToast('success', "✓ Champs pré-remplis automatiquement — vérifiez avant d'enregistrer.");
    }
}

function fillField(id, val) {
    var el = document.getElementById(id);
    if (!el || !val) return;
    el.value = val;
    pulse(el);
}

function showOcrStatus(type, message) {
    ocrStatus.className = 'nf-ocr-status ' + type;
    ocrStatus.textContent = message;
}

function showToast(type, message) {
    showOcrStatus(type, message);
}

function pulse(el) {
    el.classList.add('nf-field-filled');
    setTimeout(function () {
        el.classList.remove('nf-field-filled');
    }, 1000);
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/expenses/create.blade.php ENDPATH**/ ?>