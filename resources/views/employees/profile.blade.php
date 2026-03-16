@extends('layouts.app')

@section('title', 'Mon Profil')
@section('page-title', 'Mon Profil')

@section('content')

<div class="profile-hero mb-6">
    <div class="profile-photo">
        @if($employee->photo)
            <img src="{{ $employee->photo_url }}" alt="{{ $employee->full_name }}">
        @else
            {{ strtoupper(substr($employee->first_name,0,1).substr($employee->last_name,0,1)) }}
        @endif
    </div>
    <div>
        <div style="font-size:0.7rem;opacity:0.4;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Matricule: {{ $employee->matricule }}</div>
        <div class="profile-name">{{ $employee->full_name }}</div>
        <div class="profile-role">{{ $employee->position }}</div>
        <div class="profile-dept">🏥 {{ $employee->department }}</div>
        <div style="margin-top:10px;display:flex;gap:8px">
            @if($employee->status == 'active')
                <span class="badge badge-success">● Actif</span>
            @elseif($employee->status == 'leave')
                <span class="badge badge-warning">◐ En congé</span>
            @else
                <span class="badge badge-neutral">○ Inactif</span>
            @endif
        </div>
    </div>
    <div clajn ss="profile-meta">
        <div class="profile-meta-item">
            
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start">
    <div>
        <!-- Mes Informations personnelles -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title">👤 Mes Informations Personnelles</div>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">📧 Email</div>
                        <div class="detail-value">{{ $employee->email }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">📱 Téléphone</div>
                        <div class="detail-value">{{ $employee->phone ?: '—' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Date de naissance</div>
                        <div class="detail-value">{{ $employee->birth_date ? $employee->birth_date->format('d/m/Y') : '—' }}</div>
                    </div>
                    @if($employee->address)
                    <div class="detail-item" style="grid-column:1/-1">
                        <div class="detail-label"> Adresse</div>
                        <div class="detail-value">{{ $employee->address }}</div>
                    </div>
                    @endif
                    @if($employee->emergency_contact)
                    <div class="detail-item">
                        <div class="detail-label"> Contact d'urgence</div>
                        <div class="detail-value">{{ $employee->emergency_contact }} — {{ $employee->emergency_phone }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Mes Informations Professionnelles -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title">💼 Mes Informations Professionnelles</div>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">🏢 Département</div>
                        <div class="detail-value">{{ $employee->department }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">💼 Poste</div>
                        <div class="detail-value">{{ $employee->position }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">📅 Date d'embauche</div>
                        <div class="detail-value">{{ $employee->hire_date->format('d/m/Y') }}</div>
                    </div>
                    @if($employee->manager)
                    <div class="detail-item">
                        <div class="detail-label"> Responsable</div>
                        <div class="detail-value">{{ $employee->manager->full_name }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Mes Congés -->
        <div class="card mb-4" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd;">
            <div class="card-header">
                <div class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 8px;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Mes Congés
                </div>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                    <div style="text-align: center; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="width: 56px; height: 56px; margin: 0 auto 12px; background: #d1fae5; border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Congés Payés</div>
                        <div style="font-size: 2rem; font-weight: 700; color: #059669; margin-top: 8px;">{{ $employee->cp_days ?? 0 }} <span style="font-size: 1rem; font-weight: 400; color: #64748b;">jours</span></div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="width: 56px; height: 56px; margin: 0 auto 12px; background: #fef3c7; border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Heures supplémentaire</div>
                        <div style="font-size: 2rem; font-weight: 700; color: #f59e0b; margin-top: 8px;">{{ $employee->work_hours_counter ?? 0 }} <span style="font-size: 1rem; font-weight: 400; color: #64748b;">heures</span></div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="width: 56px; height: 56px; margin: 0 auto 12px; background: #dbeafe; border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Jours de travail</div>
                        <div style="font-size: 0.9rem; font-weight: 600; color: #1e293b; margin-top: 8px;">
                            @php
                                $days = ['lundi' => 'Lun', 'mardi' => 'Mar', 'mercredi' => 'Mer', 'jeudi' => 'Jeu', 'vendredi' => 'Ven', 'samedi' => 'Sam', 'dimanche' => 'Dim'];
                                $workDays = is_array($employee->work_days) ? $employee->work_days : json_decode($employee->work_days ?? '[]', true) ?? [];
                            @endphp
                            {{ count($workDays) }} jours/sem
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <!-- Actions rapides -->
        <div class="card mb-4">
            <div class="card-header"><div class="card-title"> Mes Actions</div></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                <a href="{{ route('absences.create') }}?employee_id={{ $employee->id }}" class="btn btn-outline w-full">
                    📅 Demander un congé
                </a>
                <a href="{{ route('planning.weekly') }}" class="btn btn-outline w-full">
                    📊 Voir mon planning
                </a>
            </div>
        </div>

        <!-- Mon Responsable -->
        @if($employee->manager)
        <div class="card">
            <div class="card-header"><div class="card-title">Mon Responsable</div></div>
            <div class="card-body">
                <div style="display:flex;align-items:center;gap:12px">
                    <div class="table-avatar" style="width:44px;height:44px;font-size:15px">
                        {{ strtoupper(substr($employee->manager->first_name,0,1).substr($employee->manager->last_name,0,1)) }}
                    </div>
                    <div>
                        <div class="table-name">{{ $employee->manager->full_name }}</div>
                        <div class="table-sub">{{ $employee->manager->position }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
