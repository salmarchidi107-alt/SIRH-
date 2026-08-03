@extends('layouts.app')
@section('title', 'LMS — Planning des Formations')
@section('page-title', 'Planning des Formations')

@php
    $isEmployee = auth()->user()->isEmployee();
@endphp

@section('content')
<div class="lp">

    {{-- ══ Header ══ --}}
    <div class="lp-header">
        <div>
            <div class="lp-title">Planning des Formations</div>
            <div class="lp-sub">Semaine du {{ $debutSem->locale('fr')->translatedFormat('d F') }} au {{ $finSem->locale('fr')->translatedFormat('d F Y') }}</div>
        </div>
        <div class="lp-acts">
            {{-- Toggle : visible par tous --}}
            <div class="lp-toggle">
                <a href="{{ route('lms.index') }}">Liste</a>
                <a href="{{ route('lms.planning') }}" class="active">Planning</a>
            </div>

            {{-- Boutons réservés admin/RH --}}
            @if(!$isEmployee)
                <a href="{{ route('referentiel.index') }}" class="btn-lp btn-ghost">Referentiel</a>
                <a href="{{ route('lms.exportPdf') }}" class="btn-lp btn-ghost">Exporter PDF</a>
                <button class="btn-lp btn-main" onclick="openModal()">
                    <i class="fas fa-plus"></i> Ajouter formation
                </button>
            @endif
        </div>
    </div>

    {{-- ══ Toolbar navigation semaine ══ --}}
    {{-- Navigation semaine : visible par tous (employé doit pouvoir changer de semaine) --}}
    {{-- Filtres avancés (formation, présence) : admin/RH seulement --}}
    <form method="GET" action="{{ route('lms.planning') }}" class="lp-bar">

        {{-- Navigation semaine : tous --}}
        <div style="display:flex;align-items:center;gap:6px;">
            <a href="{{ route('lms.planning', ['semaine'=>$semaine-1,'annee'=>$annee,'formation'=>request('formation'),'presence'=>request('presence')]) }}"
               class="lp-week-btn">
                <i class="fas fa-chevron-left" style="font-size:11px;"></i> Precedente
            </a>
            <div class="lp-week-label">Semaine {{ $semaine }} &mdash; {{ $annee }}</div>
            <a href="{{ route('lms.planning', ['semaine'=>$semaine+1,'annee'=>$annee,'formation'=>request('formation'),'presence'=>request('presence')]) }}"
               class="lp-week-btn">
                Suivante <i class="fas fa-chevron-right" style="font-size:11px;"></i>
            </a>
        </div>

        {{-- Filtres avancés : admin/RH seulement --}}
        @if(!$isEmployee)
            <select name="formation" class="lp-sel" onchange="this.form.submit()">
                <option value="">Toutes les formations</option>
                @foreach(\App\Models\Formation::getTitres() as $t)
                    <option value="{{ $t }}" {{ request('formation')===$t?'selected':'' }}>{{ $t }}</option>
                @endforeach
            </select>

            <select name="presence" class="lp-sel" onchange="this.form.submit()">
                <option value="">Tous les employes</option>
                <option value="present" {{ request('presence')==='present'?'selected':'' }}>Avec formation</option>
                <option value="absent"  {{ request('presence')==='absent' ?'selected':'' }}>Sans formation</option>
            </select>

            @if(request()->hasAny(['formation','presence']))
            <a href="{{ route('lms.planning', ['semaine'=>$semaine,'annee'=>$annee]) }}"
               class="btn-lp btn-ghost" style="padding:7px 12px;height:34px;">
                <i class="fas fa-times"></i>
            </a>
            @endif
        @endif

        <input type="hidden" name="semaine" value="{{ $semaine }}">
        <input type="hidden" name="annee"   value="{{ $annee }}">
    </form>

    {{-- ══ Grille ══ --}}
    <div class="lp-card">
        <div class="lp-scroll">
            <table class="pg-table">
                <thead>
                    <tr>
                        <th class="col-emp">Employe</th>
                        <th class="col-form">Formation</th>
                        @foreach($joursSemaine as $jour)
                            @php
                                $isToday = $jour->isToday();
                                $joursFr = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
                                $dowFr   = $joursFr[$jour->dayOfWeekIso - 1] ?? $jour->format('D');
                            @endphp
                            <th class="col-day {{ $isToday ? 'th-today' : '' }}">
                                <span class="th-num">{{ $jour->format('d') }}</span>
                                <span class="th-day">{{ $dowFr }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @php
                    $avC = [
                        ['#1D9E75','#E1F5EE'],['#378ADD','#E6F1FB'],['#D85A30','#FAECE7'],
                        ['#BA7517','#FAEEDA'],['#7F77DD','#EEEDFE'],['#D4537E','#FBEAF0'],
                        ['#888780','#F1EFE8'],
                    ];
                @endphp
                @forelse($employees as $idx => $emp)
                    @php
                        $c        = $avC[$idx % 7];
                        $prenom   = $emp->prenom ?? $emp->first_name ?? '';
                        $nom      = $emp->nom    ?? $emp->last_name  ?? $emp->name ?? '';
                        $full     = trim("$prenom $nom") ?: '—';
                        $ini      = strtoupper(mb_substr($prenom?:$nom,0,1).mb_substr($nom,0,1));
                        $dept     = $emp->getAttribute('dept_name') ?? $emp->dept_name ?? '—';
                        $empForms = $formationsSemaine->where('employee_id', $emp->id);
                        $mainF    = $empForms->first()?->titre ?? '—';
                    @endphp
                    <tr>
                        <td>
                            <div class="pg-emp">
                                <div class="pg-av" style="background:{{ $c[1] }};color:{{ $c[0] }};">{{ $ini }}</div>
                                <div>
                                    <div class="pg-name">{{ $full }}</div>
                                    <div class="pg-dept">{{ $dept }}</div>
                                </div>
                            </div>
                        </td>
                        <td><div class="pg-form-cell">{{ $mainF }}</div></td>

                        @foreach($joursSemaine as $jour)
                            @php
                                $dateStr = $jour->format('Y-m-d');
                                $session = $empForms->first(fn($f) => $f->date->format('Y-m-d') === $dateStr);
                            @endphp
                            <td>
                                <div class="pg-day">
                                    @if($session)
                                        @php
                                            $hDeb = substr($session->heure_debut, 0, 5);
                                            $hFin = substr($session->heure_fin,   0, 5);
                                        @endphp
                                        {{-- Session existante : cliquable seulement pour admin/RH --}}
                                        @if(!$isEmployee)
                                            <span class="pg-session"
                                                  title="{{ $session->titre }} — {{ $hDeb }} à {{ $hFin }}"
                                                  onclick='prefillModal({{ $emp->id }}, "{{ $dateStr }}", @json($session), {{ $emp->department_id ?? 'null' }})'>
                                                <span class="pg-sess-titre">{{ Str::limit($session->titre, 14) }}</span>
                                                <span class="pg-sess-heure">{{ $hDeb }} – {{ $hFin }}</span>
                                            </span>
                                        @else
                                            {{-- Employé : affichage lecture seule --}}
                                            <span class="pg-session" style="cursor:default;"
                                                  title="{{ $session->titre }} — {{ $hDeb }} à {{ $hFin }}">
                                                <span class="pg-sess-titre">{{ Str::limit($session->titre, 14) }}</span>
                                                <span class="pg-sess-heure">{{ $hDeb }} – {{ $hFin }}</span>
                                            </span>
                                        @endif

                                    @elseif(!$isEmployee)
                                        {{-- ✅ Bouton Créer : admin/RH seulement --}}
                                        <button type="button" class="pg-create"
                                                onclick='prefillModal({{ $emp->id }}, "{{ $dateStr }}", null, {{ $emp->department_id ?? 'null' }})'>
                                            <i class="fas fa-plus" style="font-size:10px;"></i> Créer
                                        </button>
                                    @endif
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 2+7 }}">
                            <div class="lp-empty">
                                <i class="fas fa-calendar-times"></i>
                                <p>Aucun employe trouve pour ce filtre</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ══ MODAL (admin/RH seulement) ══ --}}
@if(!$isEmployee)
<div class="lms-ov" id="lmsOv" onclick="backdropClose(event)">
<div class="lms-modal" id="lmsModal">

    <div class="m-head">
        <div class="m-title">
            <div class="m-icon"><i class="fas fa-graduation-cap"></i></div>
            <span id="mTitle">Ajouter une formation</span>
        </div>
        <button class="m-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
    </div>

    <form id="lmsForm" action="{{ route('lms.store') }}" method="POST">
        @csrf
        <input type="hidden" name="_method" id="fMethod" value="POST">

        <div class="m-body">

            <div class="fsec">Affectation</div>
            <div class="frow fr2">
                <div class="fg">
                    <label>Departement <span class="req">*</span></label>
                    <select name="departement_id" id="fDept" class="fi" required onchange="loadEmployees(this.value)">
                        <option value="">Choisir un departement</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fg">
                    <label>Employe <span class="req">*</span></label>
                    <select name="employee_id" id="fEmp" class="fi" required disabled>
                        <option value="">Choisir d'abord un departement</option>
                    </select>
                </div>
            </div>

            <hr class="fdiv">
            <div class="fsec">
                Contenu de la formation
                <a href="{{ route('referentiel.index') }}" target="_blank"
                   style="font-size:11px;color:var(--teal);text-decoration:none;margin-left:10px;font-weight:400;text-transform:none;letter-spacing:0;">
                    <i class="fas fa-external-link-alt" style="font-size:10px;"></i> Gerer le referentiel
                </a>
            </div>

            <div class="fg">
                <label>Formation <span class="req">*</span></label>
                <select name="titre" id="fTitre" class="fi" required>
                    <option value="">Chargement...</option>
                </select>
                <input type="text" name="titre_libre" id="fTitreLib" class="fi mt hidden" placeholder="Nom de la formation...">
            </div>

            <div class="frow fr2">
                <div class="fg">
                    <label>Formateur <span class="req">*</span></label>
                    <select name="formateur" id="fFormateur" class="fi" required>
                        <option value="">Chargement...</option>
                    </select>
                    <input type="text" name="formateur_libre" id="fFormateurLib" class="fi mt hidden" placeholder="Nom du formateur...">
                </div>
                <div class="fg">
                    <label>Organisme <span class="req">*</span></label>
                    <select name="organisme" id="fOrganisme" class="fi" required>
                        <option value="">Chargement...</option>
                    </select>
                    <input type="text" name="organisme_libre" id="fOrganismeLib" class="fi mt hidden" placeholder="Nom de l'organisme...">
                </div>
            </div>

            <hr class="fdiv">
            <div class="fsec">Planification</div>

            <div class="frow fr3">
                <div class="fg">
                    <label>Date <span class="req">*</span></label>
                    <input type="date" name="date" id="fDate" class="fi" required>
                </div>
                <div class="fg">
                    <label>Heure debut</label>
                    <input type="time" name="heure_debut" id="fDebut" class="fi" required value="08:00">
                </div>
                <div class="fg">
                    <label>Heure fin</label>
                    <input type="time" name="heure_fin" id="fFin" class="fi" required value="17:00">
                </div>
            </div>

            <div class="fg" style="margin-bottom:0">
                <label>Statut</label>
                <select name="statut" id="fStatut" class="fi">
                    @foreach(\App\Models\Formation::STATUTS as $s)
                        <option value="{{ $s }}" {{ $s==='Planifiee'?'selected':'' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="m-foot">
            <button type="button" class="btn-lp btn-ghost" onclick="closeModal()">Annuler</button>
            <button type="submit" class="btn-lp btn-main">
                <i class="fas fa-check"></i> <span id="mSubmit">Enregistrer</span>
            </button>
        </div>
    </form>

</div>
</div>
@endif

@endsection

@push('scripts')
@if(!auth()->user()->isEmployee())
<script>
/* ─── Modal open/close ─── */
function openModal()  { resetModal(); document.getElementById('lmsOv').classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal() { document.getElementById('lmsOv').classList.remove('open'); document.body.style.overflow=''; }
function backdropClose(e) { if(e.target===document.getElementById('lmsOv')) closeModal(); }
document.addEventListener('keydown', e => { if(e.key==='Escape') closeModal(); });

function resetModal() {
    document.getElementById('lmsForm').reset();
    document.getElementById('lmsForm').action = '{{ route('lms.store') }}';
    document.getElementById('fMethod').value   = 'POST';
    document.getElementById('mTitle').textContent  = 'Ajouter une formation';
    document.getElementById('mSubmit').textContent = 'Enregistrer';
    const emp = document.getElementById('fEmp');
    emp.innerHTML = '<option value="">Choisir d\'abord un departement</option>';
    emp.disabled  = true;
    document.getElementById('fDate').value  = new Date().toISOString().slice(0,10);
    document.getElementById('fDebut').value = '08:00';
    document.getElementById('fFin').value   = '17:00';
    loadReferentiel();
}

/* ─── Referentiel ─── */
const rfCache = { formations: null, formateurs: null, organismes: null };

function loadReferentiel() {
    return Promise.all([
        loadSelect('fTitre',    '{{ route('referentiel.api.formations') }}', 'titre', 'formations', 'fTitreLib',    'titre',    'Selectionner une formation'),
        loadSelect('fFormateur','{{ route('referentiel.api.formateurs') }}', 'label', 'formateurs', 'fFormateurLib','formateur','Selectionner un formateur'),
        loadSelect('fOrganisme','{{ route('referentiel.api.organismes') }}', 'nom',   'organismes', 'fOrganismeLib','organisme','Selectionner un organisme'),
    ]);
}

function loadSelect(selId, url, labelKey, cacheKey, freeId, fieldName, placeholder) {
    const sel = document.getElementById(selId);
    if (!sel) return Promise.resolve();
    if (rfCache[cacheKey]) { buildOptions(sel, rfCache[cacheKey], labelKey, placeholder, freeId, fieldName); return Promise.resolve(); }
    sel.innerHTML = `<option value="">Chargement...</option>`;
    sel.disabled  = true;
    return fetch(url)
        .then(r => r.json())
        .then(data => { rfCache[cacheKey] = data; buildOptions(sel, data, labelKey, placeholder, freeId, fieldName); sel.disabled = false; })
        .catch(() => { sel.disabled = false; buildOptions(sel, [], labelKey, placeholder, freeId, fieldName); });
}

function buildOptions(sel, data, labelKey, placeholder, freeId, fieldName) {
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    data.forEach(item => {
        const val = item[labelKey] ?? item.titre ?? item.nom ?? item.label ?? '';
        const opt = document.createElement('option');
        opt.value = opt.textContent = val;
        sel.appendChild(opt);
    });
    const autre = document.createElement('option');
    autre.value = '__autre__'; autre.textContent = '— Autre (saisie libre) —';
    sel.appendChild(autre);
    sel.name = fieldName;
    sel.onchange = () => toggleAutre(sel.id, freeId, fieldName);
}

function toggleAutre(selId, inputId, fieldName) {
    const sel = document.getElementById(selId);
    const inp = document.getElementById(inputId);
    if (!sel || !inp) return;
    if (sel.value === '__autre__') {
        inp.classList.remove('hidden'); inp.required = true; inp.focus();
        sel.removeAttribute('name');
    } else {
        inp.classList.add('hidden'); inp.required = false; inp.value = '';
        sel.name = fieldName;
    }
}

function setSelectOrFree(selId, inputId, fieldName, val) {
    const sel = document.getElementById(selId);
    const inp = document.getElementById(inputId);
    if (!sel || !inp || !val) return;
    const found = Array.from(sel.options).some(o => o.value === val && o.value !== '' && o.value !== '__autre__');
    if (found) {
        sel.value = val; sel.name = fieldName;
        inp.classList.add('hidden'); inp.required = false; inp.value = '';
    } else {
        sel.value = '__autre__'; sel.removeAttribute('name');
        inp.classList.remove('hidden'); inp.required = true; inp.value = val;
    }
}

/* ─── AJAX employes ─── */
function loadEmployees(deptId, selectedId = null) {
    const empSel = document.getElementById('fEmp');
    if (!deptId) { empSel.innerHTML = '<option value="">Choisir d\'abord un departement</option>'; empSel.disabled = true; return; }
    empSel.disabled = true;
    empSel.innerHTML = '<option value="">Chargement...</option>';
    fetch(`{{ route('lms.employeesByDepartment') }}?departement_id=${deptId}`)
        .then(r => r.json())
        .then(list => {
            empSel.innerHTML = list.length
                ? list.map(e => {
                    const name = `${e.prenom??e.first_name??''} ${e.nom??e.last_name??e.name??''}`.trim();
                    return `<option value="${e.id}" ${e.id==selectedId?'selected':''}>${name}</option>`;
                  }).join('')
                : '<option value="">Aucun employe</option>';
            empSel.disabled = false;
        })
        .catch(() => { empSel.innerHTML = '<option value="">Erreur</option>'; });
}

/* ─── Pre-remplir depuis la grille ─── */
function prefillModal(empId, dateStr, session, deptId) {
    document.getElementById('fDate').value = dateStr;

    if (session) {
        document.getElementById('lmsForm').reset();
        document.getElementById('lmsForm').action = `/lms/${session.id}`;
        document.getElementById('fMethod').value  = 'PUT';
        document.getElementById('mTitle').textContent  = 'Modifier la formation';
        document.getElementById('mSubmit').textContent = 'Mettre à jour';
        document.getElementById('fDate').value    = (session.date  || '').slice(0, 10);
        document.getElementById('fDebut').value   = (session.heure_debut || '').slice(0, 5);
        document.getElementById('fFin').value     = (session.heure_fin   || '').slice(0, 5);
        document.getElementById('fStatut').value  = session.statut || 'Planifiée';

        ['fTitreLib','fFormateurLib','fOrganismeLib'].forEach(id => {
            const el = document.getElementById(id);
            el.classList.add('hidden'); el.required = false; el.value = '';
        });
        ['fTitre','fFormateur','fOrganisme'].forEach((id, i) => {
            document.getElementById(id).name = ['titre','formateur','organisme'][i];
        });

        const empSel = document.getElementById('fEmp');
        empSel.innerHTML = '<option value="">Chargement...</option>';
        empSel.disabled = true;

        document.getElementById('lmsOv').classList.add('open');
        document.body.style.overflow = 'hidden';

        loadReferentiel().then(() => {
            setSelectOrFree('fTitre',    'fTitreLib',    'titre',     session.titre);
            setSelectOrFree('fFormateur','fFormateurLib','formateur', session.formateur);
            setSelectOrFree('fOrganisme','fOrganismeLib','organisme', session.organisme);
        });

        if (deptId) {
            document.getElementById('fDept').value = deptId;
            loadEmployees(deptId, empId);
        }
    } else {
        resetModal();
        document.getElementById('fDate').value = dateStr;
        document.getElementById('lmsOv').classList.add('open');
        document.body.style.overflow = 'hidden';
        if (deptId) {
            document.getElementById('fDept').value = deptId;
            loadEmployees(deptId, empId);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => loadReferentiel());
</script>
@endif
@endpush
