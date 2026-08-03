@extends('layouts.app')

@section('title', 'LMS — Gestion des Formations')
@section('page-title', 'Gestion des Formations')

@php
    $isEmployee = auth()->user()->isEmployee();
@endphp

@section('content')
<div class="lms-page">

    @if(session('error'))
    <div style="display:flex;align-items:center;gap:8px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;font-size:13px;color:#991b1b;margin-bottom:16px;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- ══ Header ══ --}}
    <div class="lms-header">
        <div>
            <div class="lms-title">
                @if($isEmployee) Mes Formations @else Gestion des Formations @endif
            </div>
            <div class="lms-sub">Vue mensuelle — {{ now()->locale('fr')->translatedFormat('F Y') }}</div>
        </div>
        <div class="lms-actions">
            {{-- Toggle Liste / Planning : visible par tous --}}
            <div class="lms-toggle">
                <a href="{{ route('lms.index') }}" class="active">Liste</a>
                <a href="{{ route('lms.planning') }}">Planning</a>
            </div>

            {{--  Boutons réservés admin/RH uniquement --}}
            @if(!$isEmployee)
                <a href="{{ route('referentiel.index') }}" class="btn-lms btn-lms-ghost">Référentiel</a>
                <a href="{{ route('lms.exportPdf') }}" class="btn-lms btn-lms-ghost">Exporter PDF</a>
                <button class="btn-lms btn-lms-main" onclick="openModal()">
                    <i class="fas fa-plus"></i> Ajouter formation
                </button>
            @endif
        </div>
    </div>

    {{-- ══ Toolbar filtres : admin/RH seulement ══ --}}
    @if(!$isEmployee)
    <form method="GET" action="{{ route('lms.index') }}" class="lms-toolbar">
        <div class="lms-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher employé ou formation...">
        </div>
        <select name="departement_id" class="lms-sel" onchange="this.form.submit()">
            <option value="">Tous les services</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ request('departement_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
        <select name="formation" class="lms-sel" onchange="this.form.submit()">
            <option value="">Toutes les formations</option>
            @foreach(\App\Models\Formation::getTitres() as $t)
                <option value="{{ $t }}" {{ request('formation') === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
        <select name="statut" class="lms-sel" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            @foreach(\App\Models\Formation::STATUTS as $s)
                <option value="{{ $s }}" {{ request('statut') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['search','departement_id','formation','statut']))
            <a href="{{ route('lms.index') }}" class="btn-lms btn-lms-ghost" style="padding:7px 12px;">
                <i class="fas fa-times"></i>
            </a>
        @endif
        <button type="submit" class="btn-lms btn-lms-main" style="padding:8px 14px;">
            <i class="fas fa-search"></i>
        </button>
    </form>
    @endif
    {{--  Employé : pas de barre de recherche ni de filtres --}}

    {{-- ══ Tableau ══ --}}
    <div class="lms-card">
        <table class="lms-tbl">
            <thead>
                <tr>
                    <th>Employe</th>
                    <th>Formation</th>
                    <th>Formateur</th>
                    <th>Organisme</th>
                    <th>Date</th>
                    <th>Horaire</th>
                    <th>Statut</th>
                    {{-- Colonne actions : admin/RH seulement --}}
                    @if(!$isEmployee)
                    <th style="width:80px;"></th>
                    @endif
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
            @forelse($formations as $f)
                @php
                    $c      = $avC[$loop->index % 7];
                    $emp    = $f->employee;
                    $prenom = $emp->prenom ?? $emp->first_name ?? '';
                    $nom    = $emp->nom    ?? $emp->last_name  ?? $emp->name ?? '';
                    $full   = trim("$prenom $nom") ?: '—';
                    $ini    = strtoupper(mb_substr($prenom ?: $nom, 0, 1) . mb_substr($nom, 0, 1));
                    $dept   = $f->employee?->getAttribute('dept_name') ?? $f->dept_name ?? '—';
                    $bc     = match($f->statut) {
                        'Planifiee' => 'b-plan',
                        'En cours'  => 'b-cours',
                        'Terminee'  => 'b-term',
                        default     => 'b-annul'
                    };
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="lms-av" style="background:{{ $c[1] }};color:{{ $c[0] }};">{{ $ini }}</div>
                            <div>
                                <div style="font-weight:500;">{{ $full }}</div>
                                <div style="font-size:11px;color:#9ca3af;margin-top:1px;">{{ $dept }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-weight:500;">{{ $f->titre }}</td>
                    <td style="color:#6b7280;">{{ $f->formateur }}</td>
                    <td style="color:#6b7280;">{{ $f->organisme }}</td>
                    <td style="color:#6b7280;white-space:nowrap;">{{ $f->date->format('d/m/Y') }}</td>
                    <td style="color:#6b7280;white-space:nowrap;">{{ $f->horaire }}</td>
                    <td><span class="lms-badge {{ $bc }}">{{ $f->statut }}</span></td>

                    {{-- Boutons modifier/supprimer : admin/RH seulement --}}
                    @if(!$isEmployee)
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="lms-act" title="Modifier"
                                    onclick='editFormation(@json($f->load("employee")))'>
                                <i class="fas fa-pen" style="font-size:11px;"></i>
                            </button>
                            <form action="{{ route('lms.destroy', $f) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cette formation ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="lms-act" title="Supprimer">
                                    <i class="fas fa-trash-alt" style="font-size:11px;color:var(--red);"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isEmployee ? 7 : 8 }}">
                        <div class="lms-empty">
                            <i class="fas fa-inbox"></i>
                            <p>Aucune formation enregistree</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($formations->hasPages())
    <div style="display:flex;justify-content:center;margin-top:20px;">
        {{ $formations->links() }}
    </div>
    @endif

</div>

{{-- ══ MODAL AJOUTER / MODIFIER (admin/RH seulement) ══ --}}
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
            </div>

            <div class="frow fr2">
                <div class="fg">
                    <label>Formateur <span class="req">*</span></label>
                    <select name="formateur" id="fFormateur" class="fi" required>
                        <option value="">Chargement...</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Organisme <span class="req">*</span></label>
                    <select name="organisme" id="fOrganisme" class="fi" required>
                        <option value="">Chargement...</option>
                    </select>
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
            <button type="button" class="btn-lms btn-lms-ghost" onclick="closeModal()">Annuler</button>
            <button type="submit" class="btn-lms btn-lms-main">
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
function backdropClose(e) { if (e.target===document.getElementById('lmsOv')) closeModal(); }
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

function editFormation(f) {
    document.getElementById('lmsOv').classList.add('open');
    document.body.style.overflow = 'hidden';
    document.getElementById('lmsForm').reset();
    document.getElementById('lmsForm').action = `/lms/${f.id}`;
    document.getElementById('fMethod').value  = 'PUT';
    document.getElementById('mTitle').textContent  = 'Modifier la formation';
    document.getElementById('mSubmit').textContent = 'Mettre à jour';
    document.getElementById('fDate').value    = (f.date  || '').slice(0, 10);
    document.getElementById('fDebut').value   = (f.heure_debut || '').slice(0, 5);
    document.getElementById('fFin').value     = (f.heure_fin   || '').slice(0, 5);
    document.getElementById('fStatut').value  = f.statut || 'Planifiée';

    document.getElementById('fEmp').innerHTML = '<option value="">Chargement...</option>';
    document.getElementById('fEmp').disabled = true;

    loadReferentiel().then(() => {
        document.getElementById('fTitre').value     = f.titre     || '';
        document.getElementById('fFormateur').value = f.formateur || '';
        document.getElementById('fOrganisme').value = f.organisme || '';
    });

    const deptId = f.employee?.department_id ?? '';
    if (deptId) {
        document.getElementById('fDept').value = deptId;
        loadEmployees(deptId, f.employee_id);
    }
}

/* ─── Referentiel ─── */
const rfCache = { formations: null, formateurs: null, organismes: null };

function loadReferentiel() {
    return Promise.all([
        loadSelect('fTitre',    '{{ route('referentiel.api.formations') }}', 'titre', rfCache, 'formations', 'titre',    'Selectionner une formation'),
        loadSelect('fFormateur','{{ route('referentiel.api.formateurs') }}', 'label', rfCache, 'formateurs', 'formateur','Selectionner'),
        loadSelect('fOrganisme','{{ route('referentiel.api.organismes') }}', 'nom',   rfCache, 'organismes', 'organisme','Selectionner'),
    ]);
}

function loadSelect(selId, url, labelKey, cache, cacheKey, fieldName, placeholder) {
    const sel = document.getElementById(selId);
    if (!sel) return Promise.resolve();
    if (cache[cacheKey]) { buildOptions(sel, cache[cacheKey], labelKey, placeholder, fieldName); return Promise.resolve(); }
    sel.innerHTML = `<option value="">Chargement...</option>`;
    sel.disabled  = true;
    return fetch(url)
        .then(r => r.json())
        .then(data => { cache[cacheKey] = data; buildOptions(sel, data, labelKey, placeholder, fieldName); sel.disabled = false; })
        .catch(() => { sel.disabled = false; sel.innerHTML = `<option value="">${placeholder}</option>`; });
}

function buildOptions(sel, data, labelKey, placeholder, fieldName) {
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    data.forEach(item => {
        const val = item[labelKey] ?? item.titre ?? item.nom ?? item.label ?? '';
        const opt = document.createElement('option');
        opt.value = opt.textContent = val;
        sel.appendChild(opt);
    });
    sel.name = fieldName;
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
                : '<option value="">Aucun employe dans ce departement</option>';
            empSel.disabled = false;
        })
        .catch(() => { empSel.innerHTML = '<option value="">Erreur</option>'; });
}

document.addEventListener('DOMContentLoaded', () => loadReferentiel());
</script>
@endif
@endpush
