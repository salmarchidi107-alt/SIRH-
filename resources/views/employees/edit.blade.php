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
                   {{-- ── Département — menu déroulant depuis la BDD ── --}}
<div class="form-group">
    <label>Service / Département *</label>

    <select name="department" class="form-control" required>
        <option value="">— Sélectionner un département —</option>

        @forelse($departments ?? [] as $dept)
            @php
                $deptName = is_object($dept) ? $dept->name : $dept;
            @endphp

            <option value="{{ $deptName }}"
                {{ old('department', $employee->department) == $deptName ? 'selected' : '' }}>
                {{ $deptName }}
            </option>

        @empty
            <option disabled>Aucun département disponible</option>
        @endforelse
    </select>

    @error('department')
        <span style="color:var(--danger);font-size:0.75rem">
            {{ $message }}
        </span>
    @enderror

    @if(empty($departments) || count($departments) === 0)
        <small style="color:#f59e0b;font-size:0.75rem">
            ⚠️ Aucun département configuré —
            <a href="{{ route('parametrage.index', ['tab' => 'departments']) }}"
               style="color:#f59e0b">
                créez-en un dans Paramétrage
            </a>
        </small>
    @endif
</div>
                </div>
                <div class="form-group">
                    <label>Poste *</label>
                    <input type="text" name="position" class="form-control" value="{{ old('position', $employee->position) }}" required>
                </div>
                <div class="form-group">
                    <label>Type de diplôme</label>
                   <input type="text" name="diploma_type" class="form-control"
       value="{{ old('diploma_type', $employee->diploma_type) }}"
       placeholder="">
                </div>
                <div class="form-group">
                    <label>Site de travail</label>
                    <input type="text" name="work_site" class="form-control" value="{{ old('work_site', $employee->work_site) }}" placeholder="ex: Hôpital Central, Clinique Sud">
                    @error('work_site') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Compétences</label>
                    <input type="text" name="skills" class="form-control"
       value="{{ old('skills', $employee->skills) }}"
       placeholder="">
                </div>
                <div class="form-group">
                    <label>Contrat *</label>
                    <input type="text" name="contract_type" class="form-control" value="{{ old('contract_type', $employee->contract_type) }}" required placeholder="ex: CDI, CDD">
                    @error('contract_type') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
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
                    <label>Nb. d'enfants</label>
                    <input type="number" name="children_count" class="form-control" value="{{ old('children_count', $employee->children_count ?? 0) }}" min="0">
                </div>
                <div class="form-group">
                    <label>Mode de paiement</label>
                    <select name="payment_method" class="form-control">
                        <option value="">Sélectionner...</option>
                        <option value="virement" {{ old('payment_method', $employee->payment_method) == 'virement' ? 'selected' : '' }}>Virement</option>
                        <option value="cash" {{ old('payment_method', $employee->payment_method) == 'cash' ? 'selected' : '' }}>Espèces</option>
                        <option value="chèque" {{ old('payment_method', $employee->payment_method) == 'chèque' ? 'selected' : '' }}>Chèque</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Banque</label>
                    <select name="bank" class="form-control">
                        <option value="">Sélectionner une banque...</option>
                        <optgroup label="Banques principales">
                            <option value="Attijariwafa Bank" {{ old('bank', $employee->bank) == 'Attijariwafa Bank' ? 'selected' : '' }}>Attijariwafa Bank</option>
                            <option value="Banque Populaire" {{ old('bank', $employee->bank) == 'Banque Populaire' ? 'selected' : '' }}>Banque Populaire (BCP)</option>
                            <option value="Bank of Africa" {{ old('bank', $employee->bank) == 'Bank of Africa' ? 'selected' : '' }}>Bank of Africa (BOA)</option>
                            <option value="CIH Bank" {{ old('bank', $employee->bank) == 'CIH Bank' ? 'selected' : '' }}>CIH Bank</option>
                            <option value="Crédit Agricole du Maroc" {{ old('bank', $employee->bank) == 'Crédit Agricole du Maroc' ? 'selected' : '' }}>Crédit Agricole du Maroc</option>
                            <option value="BMCE Bank" {{ old('bank', $employee->bank) == 'BMCE Bank' ? 'selected' : '' }}>BMCE Bank</option>
                            <option value="CFG Bank" {{ old('bank', $employee->bank) == 'CFG Bank' ? 'selected' : '' }}>CFG Bank</option>
                            <option value="Société Générale Maroc" {{ old('bank', $employee->bank) == 'Société Générale Maroc' ? 'selected' : '' }}>Société Générale Maroc</option>
                            <option value="Al Barid Bank" {{ old('bank', $employee->bank) == 'Al Barid Bank' ? 'selected' : '' }}>Al Barid Bank</option>
                        </optgroup>
                        <option value="Autre">Autre...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>RIB</label>
                    <input type="text" name="rib" class="form-control" value="{{ old('rib', $employee->rib) }}" placeholder="XX 12 3456 7890 1234 5678 90">
                </div>
                <div class="form-group full">
                    <label>Avantages contractuels</label>
                    <textarea name="contractual_benefits" class="form-control" rows="2">{{ old('contractual_benefits', $employee->contractual_benefits) }}</textarea>
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
                    <input type="number" name="work_hours" class="form-control" value="{{ old('work_hours', $employee->work_hours) }}" min="0" step="0.5" placeholder="ex: 40">
                    @error('work_hours') <span style="color:var(--danger);font-size:0.75rem">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Début du contrat</label>
<input type="date" name="contract_start_date" class="form-control"
                    value="{{ old('contract_start_date', $employee->contract_start_date?->format('Y-m-d')) }}" readonly                </div>                </div>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hireDate  = document.querySelector('[name="hire_date"]');
    const startDate = document.querySelector('[name="contract_start_date"]');

    hireDate.addEventListener('input', function () {
        startDate.value = this.value;
    });
    startDate.addEventListener('input', function () {
        hireDate.value = this.value;
    });
});
</script>
@endsection
