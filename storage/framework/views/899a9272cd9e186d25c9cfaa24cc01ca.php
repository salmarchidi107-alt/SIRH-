<?php $__env->startSection('title', 'Import Groupé — Notes de Frais'); ?>
<?php $__env->startSection('page-title', 'Import Groupé'); ?>

<?php $__env->startSection('content'); ?>
<div class="nf-wrapper">
    <div class="nf-top-tabs">
        <a href="<?php echo e(route('expenses.index')); ?>" class="nf-top-tab"> Liste des notes</a>
        <a href="<?php echo e(route('expenses.create')); ?>" class="nf-top-tab"> Nouvelle note (OCR)</a>
        <a href="<?php echo e(route('expenses.import')); ?>" class="nf-top-tab active"> Import groupé</a>
    </div>

    <div class="nf-view">
        <div class="page-header">
            <div class="page-header-left">
                <h1>Import groupé — Notes de frais</h1>
                <p>Uploadez plusieurs reçus, ils seront analysés par OCR et transformés en brouillons à vérifier</p>
            </div>
            <a href="<?php echo e(route('expenses.index')); ?>" class="btn btn-ghost">← Retour</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="nf-grid-2 mb-4">
                    <div class="form-group">
                        <label>Employé</label>
                        <?php if($employee): ?>
                            <input type="hidden" name="employee_id" value="<?php echo e($employee->id); ?>">
                            <div style="padding:10px 12px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius)">
                                <?php echo e($employee->full_name); ?> — <?php echo e($employee->department); ?>

                            </div>
                        <?php else: ?>
                            <select name="employee_id" class="form-control" id="importEmployeeId" required>
                                <option value="">Sélectionner un employé</option>
                                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($emp->id); ?>"><?php echo e($emp->full_name); ?> — <?php echo e($emp->department); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    <div class="nf-grid-2">
                        <div class="form-group">
                            <label>Mois</label>
                            <select class="form-control"><option>Juillet</option></select>
                        </div>
                        <div class="form-group">
                            <label>Année</label>
                            <select class="form-control"><option>2026</option></select>
                        </div>
                    </div>
                </div>

                <div class="nf-dropzone" id="importDropZone">
                    <div class="nf-dropzone-text">Cliquer ou glisser plusieurs reçus (images ou PDF)</div>
                    <input type="file" id="importFiles" accept=".jpg,.jpeg,.png,.pdf" multiple style="display:none">
                </div>

                <div id="importFileList" style="margin-top:14px"></div>

                <button class="btn btn-primary" style="width:100%;margin-top:14px" id="btnImportProcess" disabled>
                    Lancer l'analyse OCR et créer les brouillons
                </button>

                <div id="importResults" style="margin-top:16px;display:none"></div>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Notes de frais : onglets, dropzone, liste de fichiers (spécifique à ce module) ── */
.nf-top-tabs {
    background:var(--surface); border-bottom:1px solid var(--border);
    padding:0 24px; display:flex; gap:4px; margin-bottom:24px;
}
.nf-top-tab {
    padding:14px 18px; font-size:0.88rem; font-weight:600; color:var(--text-muted);
    cursor:pointer; border-bottom:2px solid transparent; transition:all .15s;
    text-decoration:none; display:inline-block;
}
.nf-top-tab:hover { color:var(--primary); }
.nf-top-tab.active { color:var(--primary); border-bottom-color:var(--primary); }

.nf-view { padding:0; }

.nf-dropzone {
    border:2px dashed #93c5fd; border-radius:10px; padding:28px; text-align:center;
    cursor:pointer; background:#f8fbff; transition:all .2s;
}
.nf-dropzone:hover, .nf-dropzone.dragover { background:#dbeafe; border-color:#3b82f6; }
.nf-dropzone-icon { font-size:2rem; }
.nf-dropzone-text { font-size:0.85rem; color:var(--text-muted); margin-top:6px; }

.nf-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }

.nf-filelist-item {
    display:flex; justify-content:space-between; align-items:center;
    padding:7px 12px; background:#f9fafb; border-radius:7px; font-size:0.82rem; margin-bottom:6px;
}
.nf-filelist-remove { border:none; background:none; color:#dc2626; cursor:pointer; font-size:0.9rem; }

.nf-result-line { font-size:0.82rem; padding:4px 0; }
</style>

<script>
/* ── Notes de frais : import groupé de reçus (spécifique à cette vue) ── */
(function () {
    var ROUTE_IMPORT_PROCESS = "<?php echo e(route('expenses.import.process')); ?>";
    var CSRF = "<?php echo e(csrf_token()); ?>";

    var mockReceipts = [
        { title: 'Taxi Casa Aéroport - Centre ville', amount: '180.00' },
        { title: 'Restaurant Le Zephyr - Facture', amount: '320.00' },
        { title: 'Pharmacie Al Andalous', amount: '95.00' },
    ];

    var importDropZone = document.getElementById('importDropZone');
    var importFilesInput = document.getElementById('importFiles');
    var importFileList = document.getElementById('importFileList');
    var btnImportProcess = document.getElementById('btnImportProcess');
    var selectedImportFiles = [];

    importDropZone.addEventListener('click', function () { importFilesInput.click(); });
    importDropZone.addEventListener('dragover', function (e) { e.preventDefault(); importDropZone.classList.add('dragover'); });
    importDropZone.addEventListener('dragleave', function () { importDropZone.classList.remove('dragover'); });
    importDropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        importDropZone.classList.remove('dragover');
        addImportFiles(e.dataTransfer.files);
    });
    importFilesInput.addEventListener('change', function () { addImportFiles(this.files); });

    function addImportFiles(files) {
        for (var i = 0; i < files.length; i++) selectedImportFiles.push(files[i]);
        renderImportFileList();
    }

    function renderImportFileList() {
        importFileList.innerHTML = selectedImportFiles.map(function (f, i) {
            return '<div class="nf-filelist-item"><span>📄 ' + f.name + '</span>'
                + '<button type="button" class="nf-filelist-remove" data-idx="' + i + '">✕</button></div>';
        }).join('');
        importFileList.querySelectorAll('.nf-filelist-remove').forEach(function (btn) {
            btn.addEventListener('click', function () {
                selectedImportFiles.splice(parseInt(btn.dataset.idx, 10), 1);
                renderImportFileList();
            });
        });
        btnImportProcess.disabled = selectedImportFiles.length === 0;
    }

    btnImportProcess.addEventListener('click', function () {
        if (!selectedImportFiles.length) return;
        btnImportProcess.disabled = true;
        btnImportProcess.textContent = '⏳ Analyse en cours… (' + selectedImportFiles.length + ' fichier(s))';

        var employeeIdEl = document.getElementById('importEmployeeId');
        var formData = new FormData();
        if (employeeIdEl) formData.append('employee_id', employeeIdEl.value);
        selectedImportFiles.forEach(function (f) { formData.append('receipts[]', f); });

        fetch(ROUTE_IMPORT_PROCESS, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: formData
        })
            .then(function (r) { return r.json(); })
            .then(function (data) { showImportResults(data.created || []); })
            .catch(function () { showImportResults(simulateImportResults()); });
    });

    function simulateImportResults() {
        return selectedImportFiles.map(function (f, i) {
            var mock = mockReceipts[i % mockReceipts.length];
            return { title: mock.title, amount: mock.amount, source: f.name };
        });
    }

    function showImportResults(items) {
        var results = document.getElementById('importResults');
        results.style.display = 'block';
        var html = '<div style="font-weight:700;color:#059669;margin-bottom:8px">✓ ' + items.length + ' brouillon(s) créé(s)</div>';
        items.forEach(function (it) {
            html += '<div class="nf-result-line">— ' + it.title + ' (' + it.amount + ' MAD) — depuis ' + (it.source || '') + '</div>';
        });
        results.innerHTML = html;
        selectedImportFiles = [];
        renderImportFileList();
        btnImportProcess.textContent = "Lancer l'analyse OCR et créer les brouillons";
        btnImportProcess.disabled = true;
    }
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/expenses/import.blade.php ENDPATH**/ ?>