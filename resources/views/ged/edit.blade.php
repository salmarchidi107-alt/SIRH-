@extends('layouts.app')

@section('title', 'Modifier le Document')
@section('page-title', 'Modifier le Document')

@section('content')
<div class="container-fluid py-4 px-4">

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('ged.index') }}" style="color:#64748b;font-size:13px;text-decoration:none;">
            <i class="fas fa-folder-open me-1"></i>Documents
        </a>
        <span style="color:#cbd5e1;">›</span>
        <span style="color:#0d2238;font-size:13px;font-weight:600;">{{ $document->nom }}</span>
    </div>

    <form method="POST" action="{{ route('ged.update', $document) }}" id="editForm">
        @csrf
        @method('PUT')

        <div style="display:grid;grid-template-columns:380px 1fr;gap:24px;align-items:start;">

            {{-- ══ COLONNE GAUCHE ══ --}}
            <div style="display:flex;flex-direction:column;gap:20px;">

                {{-- Informations --}}
                <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                    <div class="card-header border-0 py-3 px-4" style="background:#f0fdfa;">
                        <h6 class="mb-0 fw-semibold" style="color:#0d2238;">
                            <i class="fas fa-info-circle me-2" style="color:#14b8a6;"></i>
                            Informations du document
                        </h6>
                    </div>
                    <div class="card-body p-4" style="display:flex;flex-direction:column;gap:16px;">

                        <div>
                            <label class="ged-label">Nom du document <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="ged-input"
                                   value="{{ old('nom', $document->nom) }}"
                                   placeholder="Ex: Attestation de travail" required>
                        </div>

                        <div>
                            <label class="ged-label">Employé <span class="text-danger">*</span></label>
                            <div class="ged-select-wrap">
                                <select name="employe_id" class="ged-select" id="selectEmploye" required>
                                    <option value="">— Sélectionner —</option>
                                    @foreach($employes as $emp)
                                        <option value="{{ $emp->id }}"
                                            {{ old('employe_id', $document->employe_id) == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->last_name }} {{ $emp->first_name }}
                                            @if($emp->matricule)({{ $emp->matricule }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down ged-select-icon"></i>
                            </div>
                        </div>

                        <div>
                            <label class="ged-label">Modèle <span class="text-danger">*</span></label>
                            <div class="ged-select-wrap">
                                <select name="modele_id" class="ged-select" id="selectModele" required>
                                    <option value="">— Choisir un modèle —</option>
                                    @foreach($modeles as $mod)
                                        <option value="{{ $mod->id }}"
                                            {{ old('modele_id', $document->modele_id) == $mod->id ? 'selected' : '' }}>
                                            {{ $mod->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down ged-select-icon"></i>
                            </div>
                        </div>

                        <div>
                            <label class="ged-label">Date du document <span class="text-danger">*</span></label>
                            <input type="date" name="date_document" class="ged-input"
                                   value="{{ old('date_document', $document->date_document?->format('Y-m-d')) }}"
                                   required>
                        </div>

                        {{-- Bouton Appliquer --}}
                        <div class="pt-2" style="border-top:1px solid #e2e8f0;">
                            <button type="button" onclick="appliquerDonneesEmploye()"
                                    class="btn fw-semibold w-100 py-2"
                                    style="background:#0d2238;color:#fff;border-radius:10px;font-size:13px;">
                                <i class="fas fa-user-check me-2"></i>Appliquer
                            </button>
                        </div>

                    </div>
                </div>

                {{-- Boutons actions --}}
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <button type="submit" onclick="syncAvantSoumission()"
                            class="btn py-2 fw-semibold w-100"
                            style="background:#14b8a6;color:#fff;border-radius:10px;">
                        <i class="fas fa-save me-2"></i>Valider les modifications
                    </button>
                    <a href="{{ route('ged.index') }}"
                       class="btn py-2 fw-semibold w-100"
                       style="background:#f1f5f9;color:#0d2238;border-radius:10px;">
                        <i class="fas fa-times me-2"></i>Annuler
                    </a>
                </div>

            </div>

            {{-- ══ COLONNE DROITE — TinyMCE ══ --}}
            <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                <div class="card-header border-0 py-3 px-4 d-flex justify-content-between align-items-center"
                     style="background:#f0fdfa;">
                    <h6 class="mb-0 fw-semibold" style="color:#0d2238;">
                        <i class="fas fa-pen-nib me-2" style="color:#14b8a6;"></i>
                        Contenu du document
                    </h6>
                </div>
                <div class="card-body p-4">
                    {{-- Textarea vide — contenu injecté via JS --}}
                    <textarea name="contenu" id="tinymceEditor"></textarea>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.ged-label { display:block;font-size:.82rem;font-weight:600;color:#0d2238;margin-bottom:6px; }
.ged-input {
    display:block;width:100%;height:46px;padding:0 14px;font-size:.875rem;
    color:#0d2238;background:#fff;border:2px solid #e2e8f0;border-radius:10px;
    transition:border-color .2s,box-shadow .2s;outline:none;
}
.ged-input:focus { border-color:#14b8a6;box-shadow:0 0 0 3px rgba(20,184,166,.12); }
.ged-input::placeholder { color:#94a3b8; }
.ged-select-wrap { position:relative; }
.ged-select {
    display:block;width:100%;height:46px;padding:0 40px 0 14px;font-size:.875rem;
    color:#0d2238;background:#fff;border:2px solid #e2e8f0;border-radius:10px;
    appearance:none;-webkit-appearance:none;cursor:pointer;
    transition:border-color .2s,box-shadow .2s;outline:none;
}
.ged-select:focus { border-color:#14b8a6;box-shadow:0 0 0 3px rgba(20,184,166,.12); }
.ged-select-icon {
    position:absolute;right:14px;top:50%;transform:translateY(-50%);
    font-size:11px;color:#94a3b8;pointer-events:none;
}
.tox-tinymce { border-radius:10px !important;border:2px solid #e2e8f0 !important; }
.tox .tox-statusbar__text-container,
.tox .tox-statusbar__path,
.tox .tox-statusbar__wordcount,
.tox .tox-statusbar__branding,
.tox-promotion { display:none !important; }
@media (max-width:1024px) {
    form > div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
}
</style>
@endpush

@push('scripts')
<script>
// Les contenus sont en base64 — atob() décode côté JS, Blade ne voit jamais @{{nom}}
const contenuDocument = atob(@json($contenuInitial));
const modelesContenuB64 = @json($modelesContenu);
const modelesContenu = {};
for (const [id, b64] of Object.entries(modelesContenuB64)) {
    modelesContenu[id] = atob(b64);
}
const employesData = @json($employesJson);
const tenantData   = @json($tenantJson);
</script>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
tinymce.init({
    selector:    '#tinymceEditor',
    license_key: 'gpl',
    language:    'fr_FR',
    height:      600,
    menubar:     true,
    statusbar:   false,
    branding:    false,
    elementpath: false,
    promotion:   false,
    plugins: 'anchor autolink charmap codesample emoticons image lists searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline | table | align lineheight | numlist bullist indent outdent | charmap | removeformat',
    content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 14px; color: #0d2238; }',
    setup: function(editor) {
        editor.on('init', function() {
            editor.setContent(contenuDocument);
        });
        editor.on('change', function() { editor.save(); });
    }
});

function syncAvantSoumission() {
    const editor = tinymce.get('tinymceEditor');
    if (editor) editor.save();
}

function appliquerDonneesEmploye() {
    const empId = document.getElementById('selectEmploye').value;
    if (!empId || !employesData[empId]) {
        alert('Veuillez d\'abord sélectionner un employé.');
        return;
    }
    const editor = tinymce.get('tinymceEditor');
    if (!editor) return;

    const emp     = employesData[empId];
    const today   = new Date();
    const dateStr = String(today.getDate()).padStart(2,'0') + '/' +
                    String(today.getMonth()+1).padStart(2,'0') + '/' +
                    today.getFullYear();

    let contenu = editor.getContent();

    const remplacements = {
        '@{{nom}}'          : emp.nom,
        '@{{prenom}}'       : emp.prenom,
        '@{{matricule}}'    : emp.matricule,
        '@{{poste}}'        : emp.poste,
        '@{{departement}}'  : emp.departement,
        '@{{contrat}}'      : emp.contrat,
        '@{{date_embauche}}': emp.date_embauche,
        '@{{salaire}}'      : emp.salaire,
        '@{{date}}'         : dateStr,
        '@{{aujourd_hui}}'  : dateStr,
        '@{{societe}}'      : tenantData.societe,
        '@{{adresse}}'      : tenantData.adresse,
        '@{{telephone}}'    : tenantData.telephone,
        '@{{email_societe}}': tenantData.email,
    };

    for (const [variable, valeur] of Object.entries(remplacements)) {
        const escaped = variable.replace(/[{}]/g, '\\$&');
        contenu = contenu.replace(new RegExp(escaped, 'g'), valeur || '—');
    }

    editor.setContent(contenu);
}

</script>
@endpush