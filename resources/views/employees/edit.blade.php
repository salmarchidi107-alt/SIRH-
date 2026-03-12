@extends('layouts.app')

@section('title', 'Modifier - '.$employee->full_name)
@section('page-title', 'Modifier un Employé')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1> Modifier : {{ $employee->full_name }}</h1>
        <p>Matricule : {{ $employee->matricule }}</p>
    </div>
    <a href="{{ route('employees.show', $employee) }}" class="btn btn-ghost">← Retour</a>
</div>

<form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    <!-- Informations personnelles -->
    <div class="card mb-4">
        <div class="card-header"><div class="card-title">Informations Personnelles</div></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Prénom *</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $employee->first_name) }}" required>
                    @error('first_name') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $employee->last_name) }}" required>
                    @error('last_name') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}" required>
                    @error('email') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
                </div>
                <div class="form-group">
                    <label>Date de naissance</label>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label>CIN</label>
                    <input type="text" name="cin" class="form-control" value="{{ old('cin', $employee->cin) }}">
                </div>
                <div class="form-group">
                    <label>Situation familiale</label>
                    <select name="family_situation" class="form-control">
                        <option value="">Sélectionner...</option>
                        <option value="célibataire" {{ old('family_situation', $employee->family_situation) == 'célibataire' ? 'selected' : '' }}>Célibataire</option>
                        <option value="marié(e)" {{ old('family_situation', $employee->family_situation) == 'marié(e)' ? 'selected' : '' }}>Marié(e)</option>
                        <option value="divorcé(e)" {{ old('family_situation', $employee->family_situation) == 'divorcé(e)' ? 'selected' : '' }}>Divorcé(e)</option>
                        <option value="veuf(ve)" {{ old('family_situation', $employee->family_situation) == 'veuf(ve)' ? 'selected' : '' }}>Veuf(ve)</option>
                        <option value="en instance de divorce" {{ old('family_situation', $employee->family_situation) == 'en instance de divorce' ? 'selected' : '' }}>En instance de divorce</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Adresse</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $employee->address) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Photo (laisser vide pour conserver)</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
            </div>
        </div>
    </div>

    <!-- Informations professionnelles -->
    <div class="card mb-4">
        <div class="card-header"><div class="card-title"> Informations Professionnelles</div></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Service *</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department', $employee->department) }}" required list="depts">
                    <datalist id="depts">
                        <option value="Médecine Générale"><option value="Chirurgie"><option value="Urgences">
                        <option value="Pédiatrie"><option value="Radiologie"><option value="Laboratoire">
                        <option value="Pharmacie"><option value="Administration"><option value="Ressources Humaines">
                    </datalist>
                </div>
                <div class="form-group">
                    <label>Poste *</label>
                    <input type="text" name="position" class="form-control" value="{{ old('position', $employee->position) }}" required>
                </div>
                <div class="form-group">
                    <label>Type de diplôme</label>
                    <select name="diploma_type" class="form-control">
                        <option value="">Sélectionner...</option>
                        <option value="Bac" {{ old('diploma_type', $employee->diploma_type) == 'Bac' ? 'selected' : '' }}>Bac</option>
                        <option value="Bac+2" {{ old('diploma_type', $employee->diploma_type) == 'Bac+2' ? 'selected' : '' }}>Bac+2 (DUT, BTS)</option>
                        <option value="Bac+3" {{ old('diploma_type', $employee->diploma_type) == 'Bac+3' ? 'selected' : '' }}>Bac+3 (Licence)</option>
                        <option value="Bac+4" {{ old('diploma_type', $employee->diploma_type) == 'Bac+4' ? 'selected' : '' }}>Bac+4 (Master 1)</option>
                        <option value="Bac+5" {{ old('diploma_type', $employee->diploma_type) == 'Bac+5' ? 'selected' : '' }}>Bac+5 (Master 2)</option>
                        <option value="Doctorat" {{ old('diploma_type', $employee->diploma_type) == 'Doctorat' ? 'selected' : '' }}>Doctorat</option>
                        <option value="Formation professionnelle" {{ old('diploma_type', $employee->diploma_type) == 'Formation professionnelle' ? 'selected' : '' }}>Formation professionnelle</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Compétences</label>
                    <select name="skills" class="form-control">
                        <option value="">Sélectionner...</option>
                        <option value="Médecine générale" {{ old('skills', $employee->skills) == 'Médecine générale' ? 'selected' : '' }}>Médecine générale</option>
                        <option value="Chirurgie générale" {{ old('skills', $employee->skills) == 'Chirurgie générale' ? 'selected' : '' }}>Chirurgie générale</option>
                        <option value="Urgences et soins intensifs" {{ old('skills', $employee->skills) == 'Urgences et soins intensifs' ? 'selected' : '' }}>Urgences et soins intensifs</option>
                        <option value="Pédiatrie" {{ old('skills', $employee->skills) == 'Pédiatrie' ? 'selected' : '' }}>Pédiatrie</option>
                        <option value="Gynécologie obstétrique" {{ old('skills', $employee->skills) == 'Gynécologie obstétrique' ? 'selected' : '' }}>Gynécologie obstétrique</option>
                        <option value="Cardiologie" {{ old('skills', $employee->skills) == 'Cardiologie' ? 'selected' : '' }}>Cardiologie</option>
                        <option value="Neurologie" {{ old('skills', $employee->skills) == 'Neurologie' ? 'selected' : '' }}>Neurologie</option>
                        <option value="Oncologie" {{ old('skills', $employee->skills) == 'Oncologie' ? 'selected' : '' }}>Oncologie</option>
                        <option value="Radiologie" {{ old('skills', $employee->skills) == 'Radiologie' ? 'selected' : '' }}>Radiologie</option>
                        <option value="Laboratoire" {{ old('skills', $employee->skills) == 'Laboratoire' ? 'selected' : '' }}>Laboratoire</option>
                        <option value="Pharmacie" {{ old('skills', $employee->skills) == 'Pharmacie' ? 'selected' : '' }}>Pharmacie</option>
                        <option value="Anesthésie" {{ old('skills', $employee->skills) == 'Anesthésie' ? 'selected' : '' }}>Anesthésie</option>
                        <option value="Management hospitalier" {{ old('skills', $employee->skills) == 'Management hospitalier' ? 'selected' : '' }}>Management hospitalier</option>
                        <option value="Informatique médicale" {{ old('skills', $employee->skills) == 'Informatique médicale' ? 'selected' : '' }}>Informatique médicale</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contrat *</label>
                    <select name="contract_type" class="form-control" required>
                        @foreach(['CDI','CDD','Interim','Stage'] as $ct)
                            <option value="{{ $ct }}" {{ old('contract_type', $employee->contract_type) == $ct ? 'selected' : '' }}>{{ $ct }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Date d'embauche *</label>
                    <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date', $employee->hire_date->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="status" class="form-control">
                        <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactif</option>
                        <option value="leave" {{ old('status', $employee->status) == 'leave' ? 'selected' : '' }}>En congé</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Salaire de base (MAD)</label>
                    <input type="number" name="base_salary" class="form-control" value="{{ old('base_salary', $employee->base_salary) }}" min="0" step="100">
                </div>
                <div class="form-group">
                    <label>N° CNSS</label>
                    <input type="text" name="cnss" class="form-control" value="{{ old('cnss', $employee->cnss) }}">
                </div>
                <div class="form-group">
                    <label>Contact d'urgence</label>
                    <input type="text" name="emergency_contact" class="form-control" value="{{ old('emergency_contact', $employee->emergency_contact) }}">
                </div>
                <div class="form-group">
                    <label>Téléphone urgence</label>
                    <input type="text" name="emergency_phone" class="form-control" value="{{ old('emergency_phone', $employee->emergency_phone) }}">
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
                        <option value="24" {{ old('work_hours', $employee->work_hours) == '24' ? 'selected' : '' }}>24h/semaine (Mi-temps)</option>
                        <option value="36" {{ old('work_hours', $employee->work_hours) == '36' ? 'selected' : '' }}>36h/semaine (3/4 temps)</option>
                        <option value="40" {{ old('work_hours', $employee->work_hours) == '40' ? 'selected' : '' }}>40h/semaine (Temps plein)</option>
                        <option value="44" {{ old('work_hours', $employee->work_hours) == '44' ? 'selected' : '' }}>44h/semaine (Surcroit)</option>
                        <option value="48" {{ old('work_hours', $employee->work_hours) == '48' ? 'selected' : '' }}>48h/semaine (Temps plein +)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Début du contrat</label>
                    <input type="date" name="contract_start_date" class="form-control" value="{{ old('contract_start_date', $employee->contract_start_date?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label>Date de fin (si CDD)</label>
                    <input type="date" name="contract_end_date" class="form-control" value="{{ old('contract_end_date', $employee->contract_end_date?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label>Compteur Congés Payés (jours)</label>
                    <input type="number" name="cp_days" class="form-control" value="{{ old('cp_days', $employee->cp_days ?? 0) }}" min="0">
                </div>
                <div class="form-group">
                    <label>Compteur de temps (heures)</label>
                    <input type="number" name="work_hours_counter" class="form-control" value="{{ old('work_hours_counter', $employee->work_hours_counter ?? 0) }}" min="0" step="0.5">
                </div>
            </div>
            <div class="form-group full" style="margin-top: 16px;">
                <label style="font-weight: 600; margin-bottom: 12px; display: block;">Jours de travail habituels</label>
                @php
                    $employeeWorkDays = is_array($employee->work_days) ? $employee->work_days : json_decode($employee->work_days ?? '[]', true) ?? [];
                @endphp
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #f1f5f9; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="lundi" {{ in_array('lundi', old('work_days', $employeeWorkDays)) ? 'checked' : '' }}>
                        Lun
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #f1f5f9; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="mardi" {{ in_array('mardi', old('work_days', $employeeWorkDays)) ? 'checked' : '' }}>
                        Mar
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #f1f5f9; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="mercredi" {{ in_array('mercredi', old('work_days', $employeeWorkDays)) ? 'checked' : '' }}>
                        Mer
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #f1f5f9; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="jeudi" {{ in_array('jeudi', old('work_days', $employeeWorkDays)) ? 'checked' : '' }}>
                        Jeu
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #f1f5f9; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="vendredi" {{ in_array('vendredi', old('work_days', $employeeWorkDays)) ? 'checked' : '' }}>
                        Ven
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #f1f5f9; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="samedi" {{ in_array('samedi', old('work_days', $employeeWorkDays)) ? 'checked' : '' }}>
                        Sam
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 8px 16px; background: #fee2e2; border-radius: 8px;">
                        <input type="checkbox" name="work_days[]" value="dimanche" {{ in_array('dimanche', old('work_days', $employeeWorkDays)) ? 'checked' : '' }}>
                        Dim (Day Off)
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
        <a href="{{ route('employees.show', $employee) }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary"> Enregistrer les modifications</button>
    </div>

    <!-- Liaison compte utilisateur -->
    <div class="card mb-4" style="margin-top: 20px;">
        <div class="card-header">
            <div class="card-title"> Liaison Compte Utilisateur</div>
        </div>
        <div class="card-body">
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
                Liez ce profil employé à un compte utilisateur pour permettre l'accès au tableau de bord "Temps & Activités".
            </p>
            <div class="form-grid">
                <div class="form-group full">
                    <label>Compte utilisateur lié</label>
                    @php
                        $linkedUser = \App\Models\User::find(old('user_id', $employee->user_id));
                    @endphp
                    @if($linkedUser)
                        <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--surface-2); border-radius: 8px; border: 1px solid var(--border);">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                {{ strtoupper(substr($linkedUser->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight: 600;">{{ $linkedUser->name }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $linkedUser->email }}</div>
                            </div>
                            <a href="{{ route('employees.edit', [$employee, 'remove_user' => true]) }}" class="btn btn-danger btn-sm" style="margin-left: auto;">Délier</a>
                        </div>
                    @else
                        <select name="user_id" class="form-control">
                            <option value="">Sélectionner un compte utilisateur...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $employee->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
