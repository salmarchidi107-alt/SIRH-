@extends('layouts.app')

@section('title', 'Nouvel Employé')
@section('page-title', 'Ajouter un Employé')

@section('content')
@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="page-header">
    <div class="page-header-left">
        <h1>+ Nouvel Employé</h1>
        <p>Remplissez les informations du collaborateur</p>
    </div>
    <a href="{{ route('employees.index') }}" class="btn btn-ghost">← Retour</a>
</div>

<form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- ══════════════════════════════════════
         Informations personnelles
    ══════════════════════════════════════ --}}
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Informations Personnelles</div>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Prénom *</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required placeholder="Mohamed">
                    @error('first_name') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required placeholder="Alami">
                    @error('last_name') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="m.alami@hospitalrh.ma">
                    @error('email') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="0612345678">
                </div>
                <div class="form-group">
                    <label>Date de naissance</label>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date') }}">
                </div>
                <div class="form-group">
                    <label>CIN</label>
                    <input type="text" name="cin" class="form-control" value="{{ old('cin') }}" placeholder="AB123456">
                    @error('cin') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Situation familiale</label>
                    <select name="family_situation" class="form-control">
                        <option value="">Sélectionner...</option>
                        <option value="célibataire"           {{ old('family_situation') == 'célibataire'           ? 'selected' : '' }}>Célibataire</option>
                        <option value="marié(e)"              {{ old('family_situation') == 'marié(e)'              ? 'selected' : '' }}>Marié(e)</option>
                        <option value="divorcé(e)"            {{ old('family_situation') == 'divorcé(e)'            ? 'selected' : '' }}>Divorcé(e)</option>
                        <option value="veuf(ve)"              {{ old('family_situation') == 'veuf(ve)'              ? 'selected' : '' }}>Veuf(ve)</option>
                        <option value="en instance de divorce"{{ old('family_situation') == 'en instance de divorce'? 'selected' : '' }}>En instance de divorce</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Adresse</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Adresse complète...">{{ old('address') }}</textarea>
                    @error('address') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Photo de profil</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Code PIN Badge</label>
                    <div class="input-group">
                        <input type="text"
                               name="pin"
                               id="pin_field"
                               class="form-control"
                               placeholder="1234AB"
                               pattern="[0-9]{4}[A-Z]{2}"
                               maxlength="6"
                               readonly
                               value="{{ old('pin') }}">
                        <button type="button" id="generate_pin" class="btn btn-outline-primary">
                            Générer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         Informations professionnelles
    ══════════════════════════════════════ --}}
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Informations Professionnelles</div>
        </div>
        <div class="card-body">
            <div class="form-grid">

                {{-- ── Département — menu déroulant depuis la BDD ── --}}
                <div class="form-group">
                    <label>Service / Département *</label>
                    <select name="department" class="form-control" required>
                        <option value="">— Sélectionner un département —</option>
                        @forelse($departments ?? [] as $dept)
                            @php $deptName = is_object($dept) ? $dept->name : $dept; @endphp
                            <option value="{{ $deptName }}"
                                {{ old('department') == $deptName ? 'selected' : '' }}>
                                {{ $deptName }}
                            </option>
                        @empty
                            {{-- Fallback si aucun département créé --}}
                            <option value="Médecine Générale">Médecine Générale</option>
                            <option value="Chirurgie">Chirurgie</option>
                            <option value="Urgences">Urgences</option>
                            <option value="Pédiatrie">Pédiatrie</option>
                            <option value="Radiologie">Radiologie</option>
                            <option value="Laboratoire">Laboratoire</option>
                            <option value="Pharmacie">Pharmacie</option>
                            <option value="Administration">Administration</option>
                            <option value="Ressources Humaines">Ressources Humaines</option>
                        @endforelse
                    </select>
                    @error('department') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                    @if(($departments ?? collect())->isEmpty())
                    <small style="color:#f59e0b;font-size:0.75rem">
                        ⚠️ Aucun département configuré —
                        <a href="{{ route('parametrage.index', ['tab' => 'departments']) }}" style="color:#f59e0b">
                            créez-en un dans Paramétrage
                        </a>
                    </small>
                    @endif
                </div>

                <div class="form-group">
                    <label>Poste / Fonction *</label>
                    <input type="text" name="position" class="form-control" value="{{ old('position') }}" required placeholder="">
                    @error('position') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Type de diplôme</label>
                    <input type="text" name="diploma_type" class="form-control" value="{{ old('diploma_type') }}" placeholder="">
                </div>
                <div class="form-group">
                    <label>Site de travail</label>
                    <input type="text" name="work_site" class="form-control" value="{{ old('work_site') }}" placeholder="">
                    @error('work_site') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Compétences et expérience</label>
                    <input type="text" name="skills" class="form-control" value="{{ old('skills') }}" placeholder="">
                </div>
                <div class="form-group">
                    <label>Type de contrat *</label>
                    <input type="text" name="contract_type" class="form-control" value="{{ old('contract_type') }}" required placeholder="ex: CDI, CDD, Freelance">
                    @error('contract_type') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Date d'embauche *</label>
                    <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date') }}" required>
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="status" class="form-control">
                        <option value="active"   {{ old('status', 'active') == 'active'   ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ old('status') == 'inactive'            ? 'selected' : '' }}>Inactif</option>
                        <option value="leave"    {{ old('status') == 'leave'               ? 'selected' : '' }}>En congé</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Responsable direct</label>
                    <select name="manager_id" class="form-control">
                        <option value="">Aucun</option>
                        @foreach($managers as $mgr)
                            <option value="{{ $mgr->id }}" {{ old('manager_id') == $mgr->id ? 'selected' : '' }}>
                                {{ $mgr->full_name }} — {{ $mgr->position }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         Rémunération & Informations Sociales
    ══════════════════════════════════════ --}}
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Rémunération &amp; Informations Sociales</div>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Salaire de base (MAD)</label>
                    <input type="number" name="base_salary" class="form-control" value="{{ old('base_salary') }}" min="0" step="100" placeholder="8000">
                </div>
                <div class="form-group">
                    <label>N° CNSS</label>
                    <input type="text" name="cnss" class="form-control" value="{{ old('cnss') }}" placeholder="1234567">
                </div>
                <div class="form-group">
                    <label>Nb. d'enfants</label>
                    <input type="number" name="children_count" class="form-control" value="{{ old('children_count', 0) }}" min="0" placeholder="0">
                </div>
                <div class="form-group">
                    <label>Mode de paiement</label>
                    <select name="payment_method" class="form-control">
                        <option value="">Sélectionner...</option>
                        <option value="virement" {{ old('payment_method') == 'virement' ? 'selected' : '' }}>Virement</option>
                        <option value="cash"     {{ old('payment_method') == 'cash'     ? 'selected' : '' }}>Espèces</option>
                        <option value="chèque"   {{ old('payment_method') == 'chèque'   ? 'selected' : '' }}>Chèque</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Banque</label>
                    <select name="bank" class="form-control">
                        <option value="">Sélectionner une banque...</option>
                        <optgroup label="Banques principales">
                            <option value="Attijariwafa Bank"        {{ old('bank') == 'Attijariwafa Bank'        ? 'selected' : '' }}>Attijariwafa Bank</option>
                            <option value="Banque Populaire"         {{ old('bank') == 'Banque Populaire'         ? 'selected' : '' }}>Banque Populaire (BCP)</option>
                            <option value="Bank of Africa"           {{ old('bank') == 'Bank of Africa'           ? 'selected' : '' }}>Bank of Africa (BOA)</option>
                            <option value="CIH Bank"                 {{ old('bank') == 'CIH Bank'                 ? 'selected' : '' }}>CIH Bank</option>
                            <option value="Crédit Agricole du Maroc" {{ old('bank') == 'Crédit Agricole du Maroc' ? 'selected' : '' }}>Crédit Agricole du Maroc</option>
                            <option value="BMCE Bank"                {{ old('bank') == 'BMCE Bank'                ? 'selected' : '' }}>BMCE Bank</option>
                            <option value="CFG Bank"                 {{ old('bank') == 'CFG Bank'                 ? 'selected' : '' }}>CFG Bank</option>
                            <option value="Société Générale Maroc"   {{ old('bank') == 'Société Générale Maroc'   ? 'selected' : '' }}>Société Générale Maroc</option>
                            <option value="Al Barid Bank"            {{ old('bank') == 'Al Barid Bank'            ? 'selected' : '' }}>Al Barid Bank</option>
                        </optgroup>
                        <option value="Autre">Autre...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>RIB</label>
                    <input type="text" name="rib" class="form-control" value="{{ old('rib') }}" placeholder="XX 12 3456 7890 1234 5678 90">
                </div>
                <div class="form-group full">
                    <label>Avantages contractuels</label>
                    <textarea name="contractual_benefits" class="form-control" rows="2" placeholder="Primes, avantages...">{{ old('contractual_benefits') }}</textarea>
                </div>
                <div class="form-group">
                    <label>Contact d'urgence</label>
                    <input type="text" name="emergency_contact" class="form-control" value="{{ old('emergency_contact') }}" placeholder="Nom du contact">
                </div>
                <div class="form-group">
                    <label>Téléphone urgence</label>
                    <input type="text" name="emergency_phone" class="form-control" value="{{ old('emergency_phone') }}" placeholder="0612345678">
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         Créer compte utilisateur
    ══════════════════════════════════════ --}}
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Créer un compte utilisateur</div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="create_account" value="1" id="create_account">
                    Créer un compte utilisateur pour cet employé
                </label>
            </div>
            <div id="account_fields" style="display:none;">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Rôle utilisateur *</label>
                        <select name="user_role" class="form-control">
                            <option value="">Sélectionner rôle</option>
                            <option value="employee" selected>Employé</option>
                            <option value="rh">Responsable RH</option>
                            <option value="admin">Administrateur</option>
                        </select>
                        @error('user_role') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Mot de passe *</label>
                        <div class="password-group">
                            <input type="password" name="user_password" id="user_password" class="form-control" min="8">
                            <button type="button" class="toggle-password" data-target="user_password" title="Afficher/Masquer">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" opacity="0.5"/>
                                    <circle cx="12" cy="12" r="3.5"/>
                                </svg>
                            </button>
                        </div>
                        @error('user_password') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Confirmer mot de passe *</label>
                        <div class="password-group">
                            <input type="password" name="user_password_confirmation" id="user_password_confirmation" class="form-control" min="8">
                            <button type="button" class="toggle-password" data-target="user_password_confirmation" title="Afficher/Masquer">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" opacity="0.5"/>
                                    <circle cx="12" cy="12" r="3.5"/>
                                </svg>
                            </button>
                        </div>
                        @error('user_password_confirmation') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         Contrat de Travail
    ══════════════════════════════════════ --}}
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Détails du Contrat de Travail</div>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Temps de travail (h/semaine)</label>
                    <input type="number" name="work_hours" class="form-control" value="{{ old('work_hours') }}" min="0" step="0.5" placeholder="ex: 40">
                    @error('work_hours') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Début du contrat</label>
                    <input type="date" name="contract_start_date" class="form-control" value="{{ old('contract_start_date') }}">
                </div>
                <div class="form-group">
                    <label>Date de fin (si CDD)</label>
                    <input type="date" name="contract_end_date" class="form-control" value="{{ old('contract_end_date') }}">
                </div>
                <div class="form-group">
                    <label>Compteur Congés Payés (jours)</label>
                    <input type="number" name="cp_days" class="form-control" value="{{ old('cp_days', 0) }}" min="0" placeholder="0">
                </div>
                <div class="form-group">
                    <label>Compteur de temps (heures)</label>
                    <input type="number" name="work_hours_counter" class="form-control" value="{{ old('work_hours_counter', 0) }}" min="0" step="0.5" placeholder="0">
                </div>
            </div>
            <div class="form-group full" style="margin-top:16px;">
                <label style="font-weight:600;margin-bottom:12px;display:block;">Jours de travail habituels</label>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    @foreach([
                        'lundi'    => 'Lun',
                        'mardi'    => 'Mar',
                        'mercredi' => 'Mer',
                        'jeudi'    => 'Jeu',
                        'vendredi' => 'Ven',
                        'samedi'   => 'Sam',
                    ] as $val => $label)
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:8px 16px;background:#f1f5f9;border-radius:8px;">
                        <input type="checkbox" name="work_days[]" value="{{ $val }}"
                            {{ is_array(old('work_days')) && in_array($val, old('work_days')) ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                    @endforeach
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:8px 16px;background:#fee2e2;border-radius:8px;">
                        <input type="checkbox" name="work_days[]" value="dimanche"
                            {{ is_array(old('work_days')) && in_array('dimanche', old('work_days')) ? 'checked' : '' }}>
                        Dim (Day Off)
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
        <a href="{{ route('employees.index') }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
            </svg>
            Enregistrer l'employé
        </button>
    </div>
</form>

<style>
.input-group { display:flex; gap:8px; }
.input-group .form-control { flex:1; }
.input-group .btn { white-space:nowrap; padding:8px 16px; }
#pin_field { text-transform:uppercase; letter-spacing:2px; font-family:monospace; font-weight:600; }
.password-group { position:relative; }
.password-group .form-control { padding-right:40px; }
.toggle-password {
    position:absolute; right:10px; top:50%; transform:translateY(-50%);
    background:none; border:none; cursor:pointer; padding:4px;
    opacity:0.6; transition:opacity 0.2s;
}
.toggle-password:hover { opacity:1; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Générateur PIN Badge ─────────────────────────────────
    document.getElementById('generate_pin').addEventListener('click', function () {
        const digits  = Math.floor(1000 + Math.random() * 9000);
        const letters = Array.from({ length: 2 }, () =>
            String.fromCharCode(65 + Math.floor(Math.random() * 26))
        ).join('');
        const pin = digits + letters;
        document.getElementById('pin_field').value = pin;
        this.textContent = '✓ ' + pin;
        this.style.background = '#10b981';
        this.style.color = 'white';
        setTimeout(() => {
            this.textContent = 'Générer';
            this.style.background = '';
            this.style.color = '';
        }, 2000);
    });

    // ── Afficher les champs compte utilisateur ───────────────
    document.getElementById('create_account').addEventListener('change', function () {
        document.getElementById('account_fields').style.display = this.checked ? 'block' : 'none';
    });

    // ── Toggle visibilité mot de passe ───────────────────────
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            this.querySelector('svg').innerHTML = isText
                ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" opacity="0.5"/><circle cx="12" cy="12" r="3.5"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
        });
    });
});
</script>
@endsection