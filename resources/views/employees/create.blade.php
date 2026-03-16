@extends('layouts.app')

@section('title', 'Nouvel Employé')
@section('page-title', 'Ajouter un Employé')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>+ Nouvel Employé</h1>
        <p>Remplissez les informations du collaborateur</p>
    </div>
    <a href="{{ route('employees.index') }}" class="btn btn-ghost">← Retour</a>
</div>

<form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Informations personnelles -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title"> Informations Personnelles</div>
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
                        <option value="célibataire" {{ old('family_situation') == 'célibataire' ? 'selected' : '' }}>Célibataire</option>
                        <option value="marié(e)" {{ old('family_situation') == 'marié(e)' ? 'selected' : '' }}>Marié(e)</option>
                        <option value="divorcé(e)" {{ old('family_situation') == 'divorcé(e)' ? 'selected' : '' }}>Divorcé(e)</option>
                        <option value="veuf(ve)" {{ old('family_situation') == 'veuf(ve)' ? 'selected' : '' }}>Veuf(ve)</option>
                        <option value="en instance de divorce" {{ old('family_situation') == 'en instance de divorce' ? 'selected' : '' }}>En instance de divorce</option>
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
            </div>
        </div>
    </div>

    <!-- Informations professionnelles -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title"> Informations Professionnelles</div>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Service / Département *</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department') }}" required
                        list="depts" placeholder="ex: Urgences">
                    <datalist id="depts">
                        <option value="Médecine Générale">
                        <option value="Chirurgie">
                        <option value="Urgences">
                        <option value="Pédiatrie">
                        <option value="Radiologie">
                        <option value="Laboratoire">
                        <option value="Pharmacie">
                        <option value="Administration">
                        <option value="Ressources Humaines">
                    </datalist>
                </div>
                <div class="form-group">
                    <label>Poste / Fonction *</label>
                    <input type="text" name="position" class="form-control" value="{{ old('position') }}" required placeholder="ex: Médecin Généraliste">
                </div>
                <div class="form-group">
                    <label>Type de diplôme</label>
                    <select name="diploma_type" class="form-control">
                        <option value="">Sélectionner...</option>
                        <option value="Bac" {{ old('diploma_type') == 'Bac' ? 'selected' : '' }}>Bac</option>
                        <option value="Bac+2" {{ old('diploma_type') == 'Bac+2' ? 'selected' : '' }}>Bac+2 (DUT, BTS)</option>
                        <option value="Bac+3" {{ old('diploma_type') == 'Bac+3' ? 'selected' : '' }}>Bac+3 (Licence)</option>
                        <option value="Bac+4" {{ old('diploma_type') == 'Bac+4' ? 'selected' : '' }}>Bac+4 (Master 1)</option>
                        <option value="Bac+5" {{ old('diploma_type') == 'Bac+5' ? 'selected' : '' }}>Bac+5 (Master 2)</option>
                        <option value="Doctorat" {{ old('diploma_type') == 'Doctorat' ? 'selected' : '' }}>Doctorat</option>
                        <option value="Formation professionnelle" {{ old('diploma_type') == 'Formation professionnelle' ? 'selected' : '' }}>Formation professionnelle</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Compétences / Expérience</label>
                    <select name="skills" class="form-control">
                        <option value="">Sélectionner...</option>
                        <option value="Médecine générale" {{ old('skills') == 'Médecine générale' ? 'selected' : '' }}>Médecine générale</option>
                        <option value="Chirurgie générale" {{ old('skills') == 'Chirurgie générale' ? 'selected' : '' }}>Chirurgie générale</option>
                        <option value="Urgences et soins intensifs" {{ old('skills') == 'Urgences et soins intensifs' ? 'selected' : '' }}>Urgences et soins intensifs</option>
                        <option value="Pédiatrie" {{ old('skills') == 'Pédiatrie' ? 'selected' : '' }}>Pédiatrie</option>
                        <option value="Gynécologie obstétrique" {{ old('skills') == 'Gynécologie obstétrique' ? 'selected' : '' }}>Gynécologie obstétrique</option>
                        <option value="Cardiologie" {{ old('skills') == 'Cardiologie' ? 'selected' : '' }}>Cardiologie</option>
                        <option value="Neurologie" {{ old('skills') == 'Neurologie' ? 'selected' : '' }}>Neurologie</option>
                        <option value="Oncologie" {{ old('skills') == 'Oncologie' ? 'selected' : '' }}>Oncologie</option>
                        <option value="Radiologie" {{ old('skills') == 'Radiologie' ? 'selected' : '' }}>Radiologie</option>
                        <option value="Laboratoire" {{ old('skills') == 'Laboratoire' ? 'selected' : '' }}>Laboratoire</option>
                        <option value="Pharmacie" {{ old('skills') == 'Pharmacie' ? 'selected' : '' }}>Pharmacie</option>
                        <option value="Anesthésie" {{ old('skills') == 'Anesthésie' ? 'selected' : '' }}>Anesthésie</option>
                        <option value="Management hospitalier" {{ old('skills') == 'Management hospitalier' ? 'selected' : '' }}>Management hospitalier</option>
                        <option value="Informatique médicale" {{ old('skills') == 'Informatique médicale' ? 'selected' : '' }}>Informatique médicale</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Type de contrat *</label>
                    <select name="contract_type" class="form-control" required>
                        <option value="">Choisir...</option>
                        @foreach(['CDI','CDD','Interim','Stage'] as $ct)
                            <option value="{{ $ct }}" {{ old('contract_type') == $ct ? 'selected' : '' }}>{{ $ct }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Date d'embauche *</label>
                    <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date') }}" required>
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="status" class="form-control">
                        <option value="active" {{ old('status','active') == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                        <option value="leave" {{ old('status') == 'leave' ? 'selected' : '' }}>En congé</option>
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

    <!-- Rémunération & Social -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title"> Rémunération & Informations Sociales</div>
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

    <!-- Créer compte utilisateur (Admin only) -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title"> Créer un compte utilisateur</div>
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
                        <input type="password" name="user_password" class="form-control" min="8">
                        @error('user_password') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Confirmer mot de passe *</label>
                        <input type="password" name="user_password_confirmation" class="form-control" min="8">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contrat de Travail -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title"> Détails du Contrat de Travail</div>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Temps de travail (h/semaine)</label>
                    <select name="work_hours" class="form-control">
                        <option value="">Sélectionner...</option>
                        <option value="24" {{ old('work_hours') == '24' ? 'selected' : '' }}>24h/semaine (Mi-temps)</option>
                        <option value="36" {{ old('work_hours') == '36' ? 'selected' : '' }}>36h/semaine (3/4 temps)</option>
                        <option value="40" {{ old('work_hours', '40') == '40' ? 'selected' : '' }}>40h/semaine (Temps plein)</option>
                        <option value="44" {{ old('work_hours') == '44' ? 'selected' : '' }}>44h/semaine (Surcroit)</option>
                        <option value="48" {{ old('work_hours') == '48' ? 'selected' : '' }}>48h/semaine (Temps plein +)</option>
                    </select>
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
            <div class="form-group full" style="margin-top: 16px;">
                <label style="font-weight: 600; margin-bottom: 12px; display: block;"> Jours de travail habituels</label>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #f1f5f9; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="lundi" {{ is_array(old('work_days')) && in_array('lundi', old('work_days')) ? 'checked' : '' }}>
                        Lun
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #f1f5f9; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="mardi" {{ is_array(old('work_days')) && in_array('mardi', old('work_days')) ? 'checked' : '' }}>
                        Mar
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #f1f5f9; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="mercredi" {{ is_array(old('work_days')) && in_array('mercredi', old('work_days')) ? 'checked' : '' }}>
                        Mer
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #f1f5f9; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="jeudi" {{ is_array(old('work_days')) && in_array('jeudi', old('work_days')) ? 'checked' : '' }}>
                        Jeu
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #f1f5f9; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="vendredi" {{ is_array(old('work_days')) && in_array('vendredi', old('work_days')) ? 'checked' : '' }}>
                        Ven
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #f1f5f9; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="samedi" {{ is_array(old('work_days')) && in_array('samedi', old('work_days')) ? 'checked' : '' }}>
                        Sam
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #fee2e2; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="dimanche" {{ is_array(old('work_days')) && in_array('dimanche', old('work_days')) ? 'checked' : '' }}>
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
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
            </svg>
            Enregistrer l'employé
        </button>
    </div>
</form>

<script>
document.getElementById('create_account').addEventListener('change', function() {
    document.getElementById('account_fields').style.display = this.checked ? 'block' : 'none';
});
</script>
@endsection

