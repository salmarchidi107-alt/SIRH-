@extends('layouts.app')

@section('title', 'Import Groupé — Notes de Frais')
@section('page-title', 'Import Groupé')

@section('content')
<div class="nf-wrapper">
    <div class="nf-top-tabs">
        <a href="{{ route('expenses.index') }}" class="nf-top-tab"> Liste des notes</a>
        <a href="{{ route('expenses.create') }}" class="nf-top-tab"> Nouvelle note (OCR)</a>
        <a href="{{ route('expenses.import') }}" class="nf-top-tab active"> Import groupé</a>
    </div>

    <div class="nf-view">
        <div class="page-header">
            <div class="page-header-left">
                <h1>Import groupé — Notes de frais</h1>
                <p>Uploadez plusieurs reçus, ils seront analysés par OCR et transformés en brouillons à vérifier</p>
            </div>
            <a href="{{ route('expenses.index') }}" class="btn btn-ghost">← Retour</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="nf-grid-2 mb-4">
                    <div class="form-group">
                        <label>Employé</label>
                        @if ($employee)
                            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                            <div style="padding:10px 12px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius)">
                                {{ $employee->full_name }} — {{ $employee->department }}
                            </div>
                        @else
                            <select name="employee_id" class="form-control" id="importEmployeeId" required>
                                <option value="">Sélectionner un employé</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }} — {{ $emp->department }}</option>
                                @endforeach
                            </select>
                        @endif
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
// SUPPRIMEZ ces lignes de votre fichier actuel :
//   var mockReceipts = [ ... ];
//   function simulateImportResults() { ... }

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
        .then(function (r) {
            // On ne présume JAMAIS que le corps est du JSON valide juste parce que la
            // requête a abouti réseau-parlant : un 500 Laravel en mode debug renvoie
            // une page HTML, pas du JSON. On distingue explicitement les deux cas.
            return r.json().catch(function () {
                throw new Error('server_returned_non_json');
            }).then(function (data) {
                return { ok: r.ok, status: r.status, data: data };
            });
        })
        .then(function (response) {
            if (!response.ok) {
                showImportError(
                    (response.data && (response.data.error || response.data.message))
                        || "L'import a échoué (erreur " + response.status + "). Aucun brouillon n'a été créé."
                );
                return;
            }
            showImportResults(response.data.created || []);
        })
        .catch(function () {
            // Erreur réseau réelle (pas de connexion, timeout...) : on l'annonce clairement,
            // on n'invente JAMAIS de faux résultats pour "faire joli".
            showImportError("Impossible de contacter le serveur. Vérifiez votre connexion et réessayez.");
        });
});

function showImportError(message) {
    var results = document.getElementById('importResults');
    results.style.display = 'block';
    results.innerHTML = '<div style="font-weight:700;color:#991b1b;background:#fef2f2;'
        + 'border:1px solid #fecaca;border-radius:8px;padding:10px 14px;">✕ ' + message + '</div>';

    btnImportProcess.textContent = "Lancer l'analyse OCR et créer les brouillons";
    btnImportProcess.disabled = selectedImportFiles.length === 0;
}

function showImportResults(items) {
    var results = document.getElementById('importResults');
    results.style.display = 'block';

    if (!items.length) {
        results.innerHTML = '<div style="font-weight:700;color:#92400e;background:#fffbeb;'
            + 'border:1px solid #fde68a;border-radius:8px;padding:10px 14px;">⚠ Aucun brouillon n\'a pu être créé à partir des fichiers fournis.</div>';
    } else {
        var html = '<div style="font-weight:700;color:#059669;margin-bottom:8px">✓ ' + items.length + ' brouillon(s) créé(s)</div>';
        items.forEach(function (it) {
            html += '<div class="nf-result-line">— ' + it.title + ' (' + it.amount + ' ' + (it.currency || 'MAD') + ') — depuis ' + (it.source || '') + '</div>';
        });
        results.innerHTML = html;
    }

    selectedImportFiles = [];
    renderImportFileList();
    btnImportProcess.textContent = "Lancer l'analyse OCR et créer les brouillons";
    btnImportProcess.disabled = true;
}
</script>

@endsection
