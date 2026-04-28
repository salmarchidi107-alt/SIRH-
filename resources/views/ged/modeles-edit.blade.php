@extends('layouts.app')

@section('title', 'Modifier le Modèle')
@section('page-title', 'Modifier le Modèle')

@section('content')
<div class="container-fluid py-4 px-4">

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('ged.modeles.index') }}" style="color:#64748b;font-size:13px;text-decoration:none;">
            <i class="fas fa-layer-group me-1"></i>Modèles
        </a>
        <span style="color:#cbd5e1;">›</span>
        <span style="color:#0d2238;font-size:13px;font-weight:600;">{{ $modele->nom }}</span>
    </div>

    <form method="POST" action="{{ route('ged.modeles.update', $modele) }}" id="editForm">
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
                            Informations
                        </h6>
                    </div>
                    <div class="card-body p-4" style="display:flex;flex-direction:column;gap:16px;">
                        <div>
                            <label class="ged-label">Nom du modèle <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="ged-input"
                                   value="{{ old('nom', $modele->nom) }}"
                                   placeholder="Ex: Attestation de travail" required>
                        </div>
                        <div>
                            <label class="ged-label">Catégorie <span class="text-danger">*</span></label>
                            <input type="text" name="categorie" class="ged-input"
                                   value="{{ old('categorie', $modele->categorie) }}"
                                   placeholder="Ex: Attestation, Contrat…" required>
                        </div>
                    </div>
                </div>

                {{-- Variables cliquables --}}
                <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                    <div class="card-header border-0 py-3 px-4" style="background:#f0fdfa;">
                        <h6 class="mb-0 fw-semibold" style="color:#0d2238;">
                            <i class="fas fa-code me-2" style="color:#14b8a6;"></i>
                            Variables — cliquez pour insérer
                        </h6>
                    </div>
                    <div class="card-body p-3" style="display:flex;flex-direction:column;gap:14px;">

                        <div>
                            <div class="var-group-label">Employé</div>
                            <div class="var-chips-row">
                                <span class="var-chip" onclick="insererVariable(this,'@{{nom}}')"><span class="var-name">@{{nom}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{prenom}}')"><span class="var-name">@{{prenom}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{matricule}}')"><span class="var-name">@{{matricule}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{poste}}')"><span class="var-name">@{{poste}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{departement}}')"><span class="var-name">@{{departement}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{contrat}}')"><span class="var-name">@{{contrat}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{date_embauche}}')"><span class="var-name">@{{date_embauche}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{salaire}}')"><span class="var-name">@{{salaire}}</span></span>
                            </div>
                        </div>

                        <div>
                            <div class="var-group-label">Société</div>
                            <div class="var-chips-row">
                                <span class="var-chip" onclick="insererVariable(this,'@{{societe}}')"><span class="var-name">@{{societe}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{adresse}}')"><span class="var-name">@{{adresse}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{telephone}}')"><span class="var-name">@{{telephone}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{email_societe}}')"><span class="var-name">@{{email_societe}}</span></span>
                            </div>
                        </div>

                        <div>
                            <div class="var-group-label">Date</div>
                            <div class="var-chips-row">
                                <span class="var-chip" onclick="insererVariable(this,'@{{date}}')"><span class="var-name">@{{date}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{aujourd_hui}}')"><span class="var-name">@{{aujourd_hui}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{mois_annee}}')"><span class="var-name">@{{mois_annee}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{annee}}')"><span class="var-name">@{{annee}}</span></span>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Boutons --}}
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <button type="submit" onclick="syncAvantSoumission()"
                            class="btn py-2 fw-semibold w-100"
                            style="background:#14b8a6;color:#fff;border-radius:10px;">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                    <a href="{{ route('ged.modeles.index') }}"
                       class="btn py-2 fw-semibold w-100"
                       style="background:#f1f5f9;color:#374151;border-radius:10px;">
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
                        Contenu du modèle
                    </h6>
                </div>
                <div class="card-body p-4">
                    {{-- Textarea vide — contenu injecté via JS --}}
                    <textarea name="contenu" id="tinymceEditor"></textarea>

                    <div style="display:flex;align-items:center;gap:10px;margin-top:12px;">
                        <button type="button" onclick="restaurerOriginal()"
                                style="background:#fff8f0;border:1.5px solid #fed7aa;color:#c2410c;
                                       border-radius:8px;padding:5px 12px;font-size:12px;
                                       font-weight:600;cursor:pointer;">
                            <i class="fas fa-rotate-left me-1"></i> Restaurer le contenu original
                        </button>
                        <span style="font-size:11px;color:#94a3b8;">
                            Annule toutes vos modifications dans l'éditeur
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<div id="varToast" style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
     background:#0d2238;color:#fff;font-size:12px;padding:8px 18px;border-radius:8px;
     opacity:0;transition:opacity .2s;pointer-events:none;z-index:9999;white-space:nowrap;"></div>

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
.var-group-label { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;margin-bottom:6px; }
.var-chips-row { display:flex;flex-wrap:wrap;gap:6px; }
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
const contenuOriginal = atob(@json($contenuModele));
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
            editor.setContent(contenuOriginal);
        });
        editor.on('change', function() { editor.save(); });
    }
});

function insererVariable(el, variable) {
    const editor = tinymce.get('tinymceEditor');
    if (editor) { editor.insertContent(variable); editor.focus(); }
    el.classList.add('flash');
    setTimeout(() => el.classList.remove('flash'), 400);
    showToast(variable + ' inséré ✓');
}

function restaurerOriginal() {
    if (!confirm('Restaurer le contenu original ? Vos modifications seront perdues.')) return;
    const editor = tinymce.get('tinymceEditor');
    if (editor) editor.setContent(contenuOriginal);
    showToast('Contenu restauré');
}

function syncAvantSoumission() {
    const editor = tinymce.get('tinymceEditor');
    if (editor) editor.save();
}

document.getElementById('editForm').addEventListener('submit', function(e) {
    if (!document.getElementById('confirmEdit').checked) {
        e.preventDefault();
        showToast('⚠ Cochez "Confirmer les modifications" pour sauvegarder');
        document.getElementById('confirmEdit').closest('label').style.borderColor = '#ef4444';
        setTimeout(() => {
            document.getElementById('confirmEdit').closest('label').style.borderColor = '#e2e8f0';
        }, 2000);
    }
});

function showToast(msg) {
    const t = document.getElementById('varToast');
    t.textContent = msg; t.style.opacity = '1';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => { t.style.opacity = '0'; }, 1800);
}
</script>
@endpush