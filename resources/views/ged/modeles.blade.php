@extends('layouts.app')

@section('title', 'Modèles de Documents')
@section('page-title', 'Modèles')

@section('content')
<div class="container-fluid py-4 px-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0 fw-bold" style="color:#0d2238;">Modèles de Documents</h5>
            <small class="text-muted">{{ $modeles->total() }} modèle(s) enregistré(s)</small>
        </div>
        <button class="btn px-4 py-2 fw-semibold"
                style="background:#14b8a6;color:#fff;border-radius:10px;"
                onclick="ouvrirEtape1()">
            <i class="fas fa-plus me-2"></i>Nouveau Modèle
        </button>
    </div>

    {{-- ══ ÉTAPE 1 — Nom + Catégorie ══ --}}
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

    {{-- ══ ÉTAPE 2 — TinyMCE + Variables ══ --}}
    <div id="etape2" style="display:none;" class="mb-4">
        <div style="display:grid;grid-template-columns:320px 1fr;gap:24px;align-items:start;">

            {{-- Colonne gauche : variables --}}
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

            {{-- Colonne droite : TinyMCE --}}
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
                    <form id="modeleForm" method="POST" action="{{ route('ged.modeles.store') }}">
                        @csrf
                        <input type="hidden" name="_method"  id="methodInput"      value="POST">
                        <input type="hidden" name="nom"       id="hiddenNom">
                        <input type="hidden" name="categorie" id="hiddenCategorie">

                        <textarea name="contenu" id="tinymceEditor"></textarea>

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
    </div>

    {{-- ══ TABLE ══ --}}
    <div id="tableModeles" class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
    <div class="card-header border-0 px-4 d-flex justify-content-between align-items-center"
         style="background:#f0fdfa;height:54px;">
        <span class="fw-semibold" style="color:#0d2238;font-size:14px;">Liste des Modèles</span>
            <form method="GET" action="{{ route('ged.modeles.index') }}" style="margin:0;">
                <div style="display:flex;align-items:center;background:#fff;
                            border-radius:50px;border:1.5px solid #e2e8f0;
                            padding:3px 3px 3px 14px;gap:4px;">
                    <input type="text" name="search" value="{{ request('search') }}"
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
                    @forelse($modeles as $modele)
                    <tr>
                        <td class="py-3 px-4">
                            <span class="fw-semibold" style="color:#0d2238;font-size:14px;">{{ $modele->nom }}</span>
                        </td>
                        <td class="py-3">
                            <span style="background:#f0fdf4;color:#16a34a;font-weight:600;
                                         padding:4px 10px;border-radius:8px;font-size:12px;text-transform:capitalize;">
                                {{ $modele->categorie ?? '—' }}
                            </span>
                        </td>
                        <td class="py-3 text-muted" style="font-size:13px;">
                            {{ $modele->created_at->format('d/m/Y') }}
                        </td>
                        <td class="py-3 px-4" style="white-space:nowrap;">
                            <div style="display:flex;flex-direction:row;align-items:center;gap:6px;flex-wrap:nowrap;">
                                <a href="{{ route('ged.modeles.edit', $modele) }}"
                                   title="Modifier"
                                   style="display:inline-flex;align-items:center;justify-content:center;
                                          width:32px;height:32px;border-radius:8px;flex-shrink:0;
                                          border:1.5px solid #e2e8f0;background:#fff;
                                          color:#374151;text-decoration:none;">
                                    <i class="fas fa-pen" style="font-size:12px;"></i>
                                </a>
                                <form action="{{ route('ged.modeles.destroy', $modele) }}" method="POST"
                                      style="display:inline-flex;margin:0;"
                                      onsubmit="return confirm('Supprimer ce modèle ?')">
                                    @csrf @method('DELETE')
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
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <i class="fas fa-layer-group fa-3x mb-3 d-block" style="color:#cbd5e1;"></i>
                            <p class="mb-0 fw-semibold text-muted">Aucun modèle pour le moment</p>
                            <small class="text-muted">Cliquez sur "Nouveau Modèle" pour commencer</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($modeles->hasPages())
<div class="card-footer bg-white border-0 py-3 px-4">
    <div class="d-flex justify-content-end">
        <nav>
            <ul class="pagination mb-0" style="list-style:none;padding-left:0;">
                @if($modeles->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $modeles->previousPageUrl() }}">&laquo;</a></li>
                @endif

                @foreach($modeles->getUrlRange(1, $modeles->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $modeles->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach

                @if($modeles->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $modeles->nextPageUrl() }}">&raquo;</a></li>
                @else
                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                @endif
            </ul>
        </nav>
    </div>
</div>
@endif
    </div>

</div>

<div id="varToast" style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
     background:#0d2238;color:#fff;font-size:12px;padding:8px 18px;border-radius:8px;
     opacity:0;transition:opacity .2s;pointer-events:none;z-index:9999;white-space:nowrap;"></div>

@endsection

@push('styles')
<style>
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
    display:inline-flex;align-items:center;padding:5px 10px;border-radius:8px;
    border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;transition:all .15s;user-select:none;
}
.var-chip:hover  { background:#f0fdfa;border-color:#14b8a6; }
.var-chip:active { transform:scale(.96); }
.var-chip.flash  { background:#14b8a6;border-color:#14b8a6; }
.var-chip.flash .var-name { color:#fff; }
.var-name { font-family:monospace;font-size:11px;font-weight:600;color:#0d2238; }
.tox-tinymce { border-radius:10px !important;border:2px solid #e2e8f0 !important; }
.tox-statusbar,.tox-promotion,.tox-statusbar__branding,.tox .tox-statusbar { display:none !important; }
@media (max-width:1024px) {
    #etape2 > div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
tinymce.init({
    selector:    '#tinymceEditor',
    license_key: 'gpl',
    language:    'fr_FR',
    height:      460,
    menubar:     true,
    statusbar:   false,
    promotion:   false,
    branding:    false,
    elementpath: false,
    plugins: 'anchor autolink charmap codesample emoticons image lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline | table | align lineheight | numlist bullist indent outdent | charmap | removeformat',
    content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 14px; color: #0d2238; }',
    setup: function(editor) {
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

function ouvrirEtape1() {
    document.getElementById('step1Nom').value       = '';
    document.getElementById('step1Categorie').value = '';
    document.getElementById('modeleForm').action    = "{{ route('ged.modeles.store') }}";
    document.getElementById('methodInput').value    = 'POST';
    document.getElementById('etape1').style.display = 'block';
    document.getElementById('etape2').style.display = 'none';
    document.getElementById('tableModeles').style.display = 'none';
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
    document.getElementById('etape1').style.display  = 'none';
    document.getElementById('etape2').style.display  = 'none';
    document.getElementById('tableModeles').style.display = 'block';
    if (tinymce.get('tinymceEditor')) tinymce.get('tinymceEditor').setContent('');
}

function syncAvantSoumission() {
    const editor = tinymce.get('tinymceEditor');
    if (editor) editor.save();
}

function showToast(msg) {
    const t = document.getElementById('varToast');
    t.textContent = msg; t.style.opacity = '1';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => { t.style.opacity = '0'; }, 1800);
}
</script>
@endpush