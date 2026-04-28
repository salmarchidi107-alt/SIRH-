@extends('layouts.app')

@section('title','Entête Documents')
@section('page-title','Entête')

@section('content')
<div class="container-fluid py-4 px-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('ged.entete.store') }}" id="enteteForm">
        @csrf

        <div style="display:grid;grid-template-columns:340px 1fr;gap:24px;align-items:start;">

            {{-- ══ COLONNE GAUCHE — Variables ══ --}}
            <div style="display:flex;flex-direction:column;gap:20px;">

                <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                    <div class="card-header border-0 py-3 px-4" style="background:#f0fdfa;">
                        <h6 class="mb-0 fw-semibold" style="color:#0d2238;">
                            <i class="fas fa-code me-2" style="color:#14b8a6;"></i>
                            Variables — cliquez pour insérer
                        </h6>
                    </div>
                    <div class="card-body p-3" style="display:flex;flex-direction:column;gap:14px;">
                        <div>
                            <div class="var-group-label">Société</div>
                            <div class="var-chips-row">
                                <span class="var-chip" onclick="insererVariable(this,'@{{logo_societe}}')">
                                    <i class="fas fa-image me-1" style="color:#14b8a6;font-size:10px;"></i>
                                    <span class="var-name">@{{logo_societe}}</span>
                                </span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{societe}}')"><span class="var-name">@{{societe}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{adresse}}')"><span class="var-name">@{{adresse}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{telephone}}')"><span class="var-name">@{{telephone}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{email_societe}}')"><span class="var-name">@{{email_societe}}</span></span>
                            </div>
                        </div>

                        <div>
                            <div class="var-group-label">Employé</div>
                            <div class="var-chips-row">
                                <span class="var-chip" onclick="insererVariable(this,'@{{nom}}')"><span class="var-name">@{{nom}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{prenom}}')"><span class="var-name">@{{prenom}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{matricule}}')"><span class="var-name">@{{matricule}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{poste}}')"><span class="var-name">@{{poste}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{departement}}')"><span class="var-name">@{{departement}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{contrat}}')"><span class="var-name">@{{contrat}}</span></span>
                                <span class="var-chip" onclick="insererVariable(this,'@{{salaire}}')"><span class="var-name">@{{salaire}}</span></span>
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

                <div style="display:flex;flex-direction:column;gap:8px;">
                    <button type="submit" onclick="syncTinyMCE()"
                            class="btn py-2 fw-semibold w-100"
                            style="background:#14b8a6;color:#fff;border-radius:10px;">
                        <i class="fas fa-save me-2"></i>Sauvegarder le modèle
                    </button>
                    <a href="{{ url()->previous() }}"
                       class="btn py-2 fw-semibold w-100"
                       style="background:#f1f5f9;color:#374151;border-radius:10px;">
                        <i class="fas fa-times me-2"></i>Annuler
                    </a>
                </div>

            </div>

            {{-- ══ COLONNE DROITE — Éditeur ══ --}}
            <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                <div class="card-header border-0 py-3 px-4 d-flex align-items-center gap-3"
                     style="background:#f0fdfa;">

                    {{-- Aperçu logo + société --}}
                    @if($entete?->logo_path)
                        <img src="{{ asset('storage/'.$entete->logo_path) }}"
                             style="height:40px;object-fit:contain;border-radius:6px;">
                    @endif
                    <div>
                        <span style="font-size:11px;color:#14b8a6;">
                            <i class="fas fa-pen-nib me-1"></i>Contenu de l'entête
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <textarea name="contenu_libre" id="tinymceEntete"></textarea>
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
/* Partagé avec modeles/edit */
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
.tox-tinymce { border-radius:10px !important;border:2px solid #e2e8f0 !important; }
</style>
@endpush

@push('scripts')
<script>
const contenuLibreInitial = atob(@json($contenuLibre));
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
        editor.on('init',   function() { editor.setContent(contenuLibreInitial); });
        editor.on('change', function() { editor.save(); });
    }
});

function syncTinyMCE() {
    const editor = tinymce.get('tinymceEntete');
    if (editor) editor.save();
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
</script>
@endpush