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
                            Informations du document
                        </h6>
                    </div>
                    <div class="card-body p-4" style="display:flex;flex-direction:column;gap:16px;">

                        {{-- Modèle EN PREMIER pour logique d'auto-remplissage --}}
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
                            {{-- Indicateur de changement de modèle --}}
                            <div id="modeleChangeAlert" style="display:none;margin-top:8px;padding:8px 12px;
                                 background:#fef3c7;border:1.5px solid #f59e0b;border-radius:8px;
                                 font-size:12px;color:#92400e;">
                                <i class="fas fa-rotate me-1"></i>
                                Modèle changé — cliquez <strong>Appliquer</strong> pour mettre à jour le contenu.
                            </div>
                        </div>

                        {{-- ✅ Nom readonly, auto-rempli par le modèle sélectionné --}}
                        <div>
                            <label class="ged-label">
                                Nom du document <span class="text-danger">*</span>
                                <span style="font-size:11px;font-weight:400;color:#94a3b8;margin-left:6px;">(rempli selon le modèle)</span>
                            </label>
                            <input type="text" name="nom" id="inputNom" class="ged-input"
                                   value="{{ old('nom', $document->nom) }}"
                                   placeholder="Sera rempli selon le modèle choisi"
                                   readonly required
                                   style="background:#f8fafc;cursor:not-allowed;color:#64748b;border-color:#e2e8f0;">
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
                            <label class="ged-label">Date du document <span class="text-danger">*</span></label>
                            <input type="date" name="date_document" class="ged-input"
                                   value="{{ old('date_document', $document->date_document?->format('Y-m-d')) }}"
                                   required>
                        </div>

                        {{-- Bouton Appliquer --}}
                        <div class="pt-2" style="border-top:1px solid #e2e8f0;">
                            <button type="button" onclick="appliquerDonneesEmploye()"
                                    id="btnAppliquer"
                                    class="btn fw-semibold w-100 py-2"
                                    style="background:#0d2238;color:#fff;border-radius:10px;font-size:13px;transition:all .2s;">
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
                        Contenu du document
                    </h6>
                    <span id="editorStatut" style="font-size:11px;color:#94a3b8;"></span>
                </div>
                <div class="card-body p-4">
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
#btnAppliquer:hover { background:#1e3a5f !important; transform:translateY(-1px); }
@media (max-width:1024px) {
    form > div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
}
#modeleChangeAlert {
    transition: opacity 0.4s ease;
}
</style>
@endpush

@push('scripts')
<script>
// ── Données PHP → JS ──────────────────────────────────────────────────────────
const contenuDocument   = atob(@json($contenuInitial));
const modelesContenuB64 = @json($modelesContenu);
const employesData      = @json($employesJson);
const tenantData        = @json($tenantJson);

// Décode tous les contenus de modèles en avance
const modelesContenu = {};
for (const [id, b64] of Object.entries(modelesContenuB64)) {
    modelesContenu[id] = atob(b64);
}

// ✅ Map id → nom des modèles pour auto-remplissage du champ nom
const nomsModeles = {
    @foreach($modeles as $m)
    {{ $m->id }}: @json($m->nom),
    @endforeach
};

// ID du modèle initial (pour détecter les changements)
let modeleIdInitial = document.getElementById('selectModele').value;

// ✅ Au chargement, remplir le champ nom avec le modèle déjà sélectionné
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('selectModele');
    if (sel.value && nomsModeles[sel.value]) {
        document.getElementById('inputNom').value = nomsModeles[sel.value];
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
tinymce.init({
    selector:    '#tinymceEditor',
    license_key: 'gpl',
    language:    'fr_FR',
    height:      520,
    menubar:     true,
    statusbar:   false,
    promotion:   false,
    branding:    false,
    elementpath: false,
    plugins: 'anchor autolink charmap codesample emoticons image lists searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline | alignleft aligncenter alignright alignjustify | table | numlist bullist indent outdent | charmap | removeformat',
    content_style: `
        body {
            font-family: Inter, Arial, sans-serif;
            font-size: 14px;
            color: #0d2238;
            text-align: left;
        }
        p, h1, h2, h3, h4, h5, h6, li, td, th { text-align: inherit; }
    `,
    formats: {
        alignleft:    { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', styles: { textAlign: 'left'    }, defaultBlock: 'p' },
        aligncenter:  { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', styles: { textAlign: 'center'  }, defaultBlock: 'p' },
        alignright:   { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', styles: { textAlign: 'right'   }, defaultBlock: 'p' },
        alignjustify: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', styles: { textAlign: 'justify' }, defaultBlock: 'p' },
    },
    setup: function(editor) {
        // Injecter le contenu existant dès l'init
        editor.on('init', function() {
            if (contenuDocument) {
                editor.setContent(contenuDocument);
                editor.save();
            }
            setStatut('Contenu chargé');
        });
        // Sync textarea à chaque modification
        editor.on('change input undo redo', function() {
            editor.save();
        });
    }
});

// ── Listener changement de modèle ────────────────────────────────────────────
document.getElementById('selectModele').addEventListener('change', function () {
    const nouvelId = this.value;
    const alert    = document.getElementById('modeleChangeAlert');

    //  Met à jour le champ nom automatiquement
    document.getElementById('inputNom').value = nomsModeles[nouvelId] ?? '';

    if (nouvelId && nouvelId !== modeleIdInitial) {
        // Afficher l'alerte puis la masquer après 5 secondes
        alert.style.display = 'block';
        clearTimeout(alert._timer);
        alert._timer = setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => { alert.style.display = 'none'; alert.style.opacity = '1'; }, 400);
        }, 5000);

        const editor = tinymce.get('tinymceEditor');
        if (editor && modelesContenu[nouvelId]) {
            editor.setContent(modelesContenu[nouvelId]);
            editor.save();
            setStatut('Modèle « ' + this.options[this.selectedIndex].text + ' » chargé');
        }
    } else {
        alert.style.display = 'none';
        clearTimeout(alert._timer);

        if (nouvelId === modeleIdInitial) {
            const editor = tinymce.get('tinymceEditor');
            if (editor && contenuDocument) {
                editor.setContent(contenuDocument);
                editor.save();
                setStatut('Contenu original restauré');
            }
        }
    }
});

// ── Appliquer les données de l'employé ───────────────────────────────────────
function appliquerDonneesEmploye() {
    const empId  = document.getElementById('selectEmploye').value;
    const editor = tinymce.get('tinymceEditor');

    if (!empId || !employesData[empId]) {
        showToast('Veuillez sélectionner un employé.', 'warning');
        return;
    }
    if (!editor) return;

    const emp    = employesData[empId];
    const today  = new Date();
    const moisFr = ['janvier','février','mars','avril','mai','juin',
                    'juillet','août','septembre','octobre','novembre','décembre'];
    const dateStr   = String(today.getDate()).padStart(2,'0') + '/' +
                      String(today.getMonth()+1).padStart(2,'0') + '/' +
                      today.getFullYear();
    const moisAnnee = moisFr[today.getMonth()] + ' ' + today.getFullYear();

    const modeleId_selected = document.getElementById('selectModele').value;
    let contenuBase = editor.getContent();
    if (modeleId_selected && modelesContenu[modeleId_selected]) {
        contenuBase = modelesContenu[modeleId_selected];
    }

    const remplacements = {
        '@{{nom}}'          : emp.nom          || '—',
        '@{{prenom}}'       : emp.prenom        || '—',
        '@{{matricule}}'    : emp.matricule     || '—',
        '@{{poste}}'        : emp.poste         || '—',
        '@{{departement}}'  : emp.departement   || '—',
        '@{{contrat}}'      : emp.contrat       || '—',
        '@{{date_embauche}}': emp.date_embauche || '—',
        '@{{salaire}}'      : emp.salaire       || '—',
        '@{{date}}'         : dateStr,
        '@{{aujourd_hui}}'  : dateStr,
        '@{{mois_annee}}'   : moisAnnee,
        '@{{annee}}'        : String(today.getFullYear()),
        '@{{societe}}'      : tenantData.societe       || '—',
        '@{{adresse}}'      : tenantData.adresse       || '—',
        '@{{telephone}}'    : tenantData.telephone     || '—',
        '@{{email_societe}}': tenantData.email_societe || '—',
        '@{{site_web}}'     : tenantData.site_web      || '—',
        '@{{ice}}'          : tenantData.ice           || '—',
        '@{{rc}}'           : tenantData.rc            || '—',
    };

    let contenu = contenuBase;
    for (const [variable, valeur] of Object.entries(remplacements)) {
        const escaped = variable.replace(/[{}@]/g, '\\$&');
        contenu = contenu.replace(new RegExp(escaped, 'g'), valeur);
    }

    editor.setContent(contenu);
    editor.save();

    document.getElementById('modeleChangeAlert').style.display = 'none';
    setStatut('Variables appliquées pour ' + emp.prenom + ' ' + emp.nom);
    showToast('Variables appliquées avec succès ✓', 'success');
}

// ── Sync avant soumission ─────────────────────────────────────────────────────
function syncAvantSoumission() {
    const editor = tinymce.get('tinymceEditor');
    if (editor) editor.save();
}

// ── Utilitaires UI ────────────────────────────────────────────────────────────
function setStatut(msg) {
    const el = document.getElementById('editorStatut');
    if (el) { el.textContent = msg; }
}

function showToast(msg, type = 'success') {
    let toast = document.getElementById('gedToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'gedToast';
        toast.style.cssText = `
            position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
            font-size:13px;padding:10px 22px;border-radius:10px;
            opacity:0;transition:opacity .25s;pointer-events:none;
            z-index:9999;white-space:nowrap;font-weight:500;
            box-shadow:0 4px 16px rgba(0,0,0,.15);
        `;
        document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.style.background = type === 'success' ? '#0d2238' : '#f59e0b';
    toast.style.color = '#fff';
    toast.style.opacity = '1';
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => { toast.style.opacity = '0'; }, 2200);
}
</script>
@endpush