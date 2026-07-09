@extends('layouts.app')

@section('title', 'Modifier la Note de Frais')
@section('page-title', 'Modifier la Note de Frais')

@section('content')
<div class="nf-wrapper">
    <div class="nf-top-tabs">
        <a href="{{ route('expenses.index') }}" class="nf-top-tab active">Liste des notes</a>
        <a href="{{ route('expenses.create') }}" class="nf-top-tab">Nouvelle note (OCR)</a>
        <a href="{{ route('expenses.import') }}" class="nf-top-tab">Import groupé</a>
    </div>

    <div class="nf-view">
        <div class="page-header">
            <div class="page-header-left">
                <h1>Modifier la note de frais</h1>
                <p>Vous pouvez re-scanner un nouveau reçu pour re-remplir les champs automatiquement</p>
            </div>
            <a href="{{ route('expenses.index') }}" class="btn btn-ghost">← Retour</a>
        </div>

        @if ($errors->any())
            <div style="
                background: #fef2f2; border: 2px solid #ef4444; border-radius: 12px;
                padding: 16px 20px; margin-bottom: 24px; box-shadow: 0 4px 16px rgba(239,68,68,0.10);
            ">
                <div style="font-weight:700;color:#991b1b;margin-bottom:8px;font-size:0.95rem;">
                     Veuillez corriger les erreurs suivantes :
                </div>
                <ul style="margin:0;padding-left:20px;color:#7f1d1d;font-size:0.9rem;line-height:1.7">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="nf-two-col">
            <div class="card" style="align-self:start">
                <div class="card-header" style="background:#eff6ff;border-bottom:2px solid #bfdbfe">
                    <div class="card-title" style="color:#1e3a5f">Scan automatique (OCR)</div>
                    <div style="font-size:0.78rem;color:#2563eb">Uploadez un nouveau reçu pour re-remplir les champs</div>
                </div>
                <div class="card-body">
                    @if ($expense->receipt_path)
                        <div style="margin-bottom:12px">
                            <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:6px">Reçu actuel :</div>
                            <a href="{{ Storage::url($expense->receipt_path) }}" target="_blank" class="btn btn-ghost" style="font-size:0.8rem">📎 Voir le reçu actuel</a>
                        </div>
                    @endif

                    <div class="nf-dropzone" id="dropZone">
                        <div class="nf-dropzone-text">Cliquer ou glisser une image/PDF du nouveau reçu</div>
                        <input type="file" id="ocrFileInput" accept=".jpg,.jpeg,.png,.pdf" style="display:none">
                    </div>
                    <div id="ocrPreview" style="margin-top:12px;display:none">
                        <img id="ocrPreviewImg" style="max-width:100%;border-radius:8px;border:1px solid var(--border)">
                    </div>
                    <div class="nf-ocr-status" id="ocrStatus"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title">Détails de la dépense</div></div>
                <div class="card-body">
                    <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data" id="expense-form">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>Employé</label>
                            @if ($employee)
                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                <div style="padding:16px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius)">
                                    <h3 style="margin:0 0 8px 0;color:var(--primary);font-size:1.1rem">{{ $employee->full_name }}</h3>
                                    <div style="color:var(--text-muted);font-size:0.875rem">{{ $employee->department }} — {{ $employee->position }}</div>
                                </div>
                            @else
                                <select name="employee_id" class="form-control {{ $errors->has('employee_id') ? 'is-invalid' : '' }}" id="field_employee_id" required>                                    <option value="">Sélectionner un employé</option>
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ (int) old('employee_id', $expense->employee_id) === $emp->id ? 'selected' : '' }}>
                                            {{ $emp->full_name }} — {{ $emp->department }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Titre / Libellé</label>
                            <input type="text" name="title" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                                   id="field_title" value="{{ old('title', $expense->title) }}" placeholder="Ex: Taxi aéroport, Déjeuner client…">
                            @error('title')
                                <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="nf-grid-2">
                            <div class="form-group">
                                <label>Catégorie</label>
                                <select name="category" class="form-control" id="field_category">
                                    @foreach ($categories as $value => $label)
                                        <option value="{{ $value }}" {{ old('category', $expense->category) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date de la dépense</label>
                                <input type="date" name="date" class="form-control {{ $errors->has('date') ? 'is-invalid' : '' }}"
                                       id="field_date" value="{{ old('date', optional($expense->expense_date)->format('Y-m-d')) }}">
                                @error('date')
                                    <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="nf-grid-2-100">
                            <div class="form-group">
                                <label>Montant</label>
                                <input type="number" name="amount" class="form-control {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                                       id="field_amount" step="0.01" value="{{ old('amount', $expense->amount) }}">
                                @error('amount')
                                    <div class="invalid-feedback" style="color:#ef4444;font-size:0.82rem;margin-top:4px">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Devise</label>
                                <select name="currency" class="form-control">
                                    <option {{ old('currency', $expense->currency) == 'MAD' ? 'selected' : '' }}>MAD</option>
                                    <option {{ old('currency', $expense->currency) == 'MRU' ? 'selected' : '' }}>MRU</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description (optionnel)</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $expense->description) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Remplacer le justificatif (optionnel)</label>
                            <input type="file" name="receipt" class="form-control">
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

<style>
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

.nf-ocr-status { margin-top:10px; font-size:0.82rem; padding:8px 12px; border-radius:8px; display:none; }
.nf-ocr-status.loading { display:block; background:#eff6ff; color:#1e40af; }
.nf-ocr-status.success { display:block; background:#f0fdf4; color:#166534; }
.nf-ocr-status.error { display:block; background:#fef2f2; color:#991b1b; }

.nf-field-filled { animation: nfFillPulse 1s ease; }
@keyframes nfFillPulse { 0% { background:#fef3c7; } 100% { background:transparent; } }

.nf-two-col { display:grid; grid-template-columns:360px 1fr; gap:20px; }
@media (max-width:840px) { .nf-two-col { grid-template-columns:1fr; } }

.nf-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.nf-grid-2-100 { display:grid; grid-template-columns:1fr 100px; gap:14px; }
</style>

<script>
/* ── Notes de frais : scan OCR d'un reçu unique, réutilisé pour l'édition ── */
(function () {
    var ROUTE_OCR_SCAN = "{{ route('expenses.ocr.scan') }}";
    var CSRF = "{{ csrf_token() }}";

    var dropZone = document.getElementById('dropZone');
    var ocrFileInput = document.getElementById('ocrFileInput');
    var ocrPreview = document.getElementById('ocrPreview');
    var ocrPreviewImg = document.getElementById('ocrPreviewImg');
    var ocrStatus = document.getElementById('ocrStatus');
    var receiptInput = document.querySelector('input[name="receipt"]');

    dropZone.addEventListener('click', function () { ocrFileInput.click(); });
    dropZone.addEventListener('dragover', function (e) { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', function () { dropZone.classList.remove('dragover'); });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        if (e.dataTransfer.files.length) handleOcrFile(e.dataTransfer.files[0]);
    });
    ocrFileInput.addEventListener('change', function () {
        if (this.files.length) handleOcrFile(this.files[0]);
    });

    function handleOcrFile(file) {
        if (receiptInput) {
            var dt = new DataTransfer();
            dt.items.add(file);
            receiptInput.files = dt.files;
        }

        if (file.type.startsWith('image/')) {
            ocrPreview.style.display = 'block';
            ocrPreviewImg.src = URL.createObjectURL(file);
        }
        ocrStatus.className = 'nf-ocr-status loading';
        ocrStatus.textContent = 'Analyse OCR en cours…';


        var formData = new FormData();
        formData.append('receipt', file);
        fetch(ROUTE_OCR_SCAN, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: formData
        })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (result) {
                if (!result.ok || result.data.error) {
                    ocrStatus.className = 'nf-ocr-status error';
                    ocrStatus.textContent = '✕ ' + (result.data.error || "Analyse OCR indisponible — saisissez les champs manuellement.");
                    return;
                }
                applyOcrResult(result.data);
            })
            .catch(function () {
                ocrStatus.className = 'nf-ocr-status error';
                ocrStatus.textContent = "✕ Impossible de contacter le service OCR — saisissez les champs manuellement.";
            });
    }

    function applyOcrResult(data) {
        fillField('field_title', data.title || '');
        fillField('field_amount', data.amount || '');
        fillField('field_date', data.date || '');
        var catEl = document.getElementById('field_category');
        if (catEl && data.category) {
            catEl.value = data.category;
            catEl.classList.add('nf-field-filled');
        }
        var empEl = document.getElementById('field_employee_id');
        if (empEl && data.employee_id) {
            empEl.value = data.employee_id;
            empEl.classList.remove('nf-field-filled');
            void empEl.offsetWidth;
            empEl.classList.add('nf-field-filled');
        }
        ocrStatus.className = 'nf-ocr-status success';
        ocrStatus.textContent = "✓ Champs pré-remplis automatiquement — vérifiez avant d'enregistrer";
    }

    function fillField(id, val) {
        var el = document.getElementById(id);
        if (!el || !val) return;
        el.value = val;
        el.classList.remove('nf-field-filled');
        void el.offsetWidth;
        el.classList.add('nf-field-filled');
    }
})();
</script>
@endsection
