@extends('layouts.app')

@section('title', auth()->user()->employee->full_name ?? 'Mon Profil')
@section('page-title', 'Mon Profil')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ auth()->user()->employee->full_name ?? 'Profil' }}</h1>
    </div>
</div>

<div style="max-width: 600px; margin: 0 auto;">
    <div class="card">
        <div class="profile-photo-large">
            @if(auth()->user()->employee && auth()->user()->employee->photo)
                <img src="{{ auth()->user()->employee->photo_url }}" alt="{{ auth()->user()->employee->full_name }}">
            @else
                <div style="font-size:4rem;font-weight:700;color:white;background:var(--primary);width:100%;height:100%;border-radius:50%;display:flex;align-items:center;justify-content:center">
                    {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                </div>
            @endif
        </div>
        <div style="text-align:center;padding:32px 24px">
            <h1 style="font-size:2rem;font-weight:700;margin-bottom:8px">{{ auth()->user()->employee->full_name ?? auth()->user()->name }}</h1>
            <div style="font-size:1.1rem;color:var(--primary);font-weight:500;margin-bottom:24px">{{ auth()->user()->employee->position ?? '—' }}</div>
            <div style="color:var(--text-muted);font-size:0.95rem;margin-bottom:16px">{{ auth()->user()->employee->department ?? '—' }}</div>

            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
                @if(auth()->user()->employee && auth()->user()->employee->status == 'active')
                    <span class="badge badge-success">● Actif</span>
                @elseif(auth()->user()->employee && auth()->user()->employee->status == 'leave')
                    <span class="badge badge-warning">◐ En congé</span>
                @else
                    <span class="badge badge-neutral">○ Inactif</span>
                @endif
                <span class="badge badge-info">{{ auth()->user()->employee->contract_type ?? 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>

<div style="max-width:600px;margin:0 auto 40px auto">
    <div style="display:grid;gap:24px">
        <!-- Informations personnelles -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Informations personnelles</div>
                @if(in_array(auth()->user()->role ?? '', ['admin', 'rh']))
                <a href="{{ route('employees.edit', auth()->user()->employee) }}" class="btn btn-outline btn-sm">Modifier</a>
                @endif
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">{{ auth()->user()->email }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Matricule</div>
                        <div class="detail-value">{{ auth()->user()->employee->matricule ?? '—' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Téléphone</div>
                        <div class="detail-value">{{ auth()->user()->employee->phone ?? '—' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Date de naissance</div>
                        <div class="detail-value">{{ auth()->user()->employee->birth_date ? auth()->user()->employee->birth_date->format('d/m/Y') : '—' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">CIN</div>
                        <div class="detail-value">{{ auth()->user()->employee->cin ?? '—' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">N° CNSS</div>
                        <div class="detail-value">{{ auth()->user()->employee->cnss ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compteurs -->
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
            <div style="padding: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); text-align: center;">
                <div style="width: 56px; height: 56px; margin: 0 auto 12px; background: #d1fae5; border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Compteur Congés Payés</div>
                <div style="font-size: 2rem; font-weight: 700; color: #059669; margin-top: 8px;">{{ auth()->user()->employee->cp_days ?? 0 }} <span style="font-size: 1rem; font-weight: 400; color: #64748b;">jours</span></div>
            </div>
            <div style="padding: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); text-align: center;">
                <div style="width: 56px; height: 56px; margin: 0 auto 12px; background: #cffafe; border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Compteur de Temps</div>
                <div style="font-size: 2rem; font-weight: 700; color: #0891b2; margin-top: 8px;">{{ auth()->user()->employee->work_hours_counter ?? 0 }} <span style="font-size: 1rem; font-weight: 400; color: #64748b;">heures</span></div>
            </div>
        </div>
    </div>
</div>
@endsection

