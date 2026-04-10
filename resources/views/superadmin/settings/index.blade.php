
@extends('layouts.superadmin')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres')

@section('page-header')
    <div class="sa-page-title">Paramètres</div>
    <div class="sa-page-sub">Configuration générale de la plateforme et gestion des accès clients</div>
@endsection

@push('styles')
<style>
/* ── Tabs ── */
.sa-tabs-bar {
    display: flex; gap: 4px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 10px; padding: 4px; width: fit-content; margin-bottom: 22px;
}
.sa-tab-btn {
    padding: 7px 18px; border-radius: 7px;
    font-size: 13px; font-weight: 600; font-family: inherit;
    cursor: pointer; border: none; background: none;
    color: var(--text-muted); transition: background .15s, color .15s;
}
.sa-tab-btn:hover:not(.active) { background: var(--surface-2); color: var(--text); }
.sa-tab-btn.active {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #fff; box-shadow: 0 2px 8px rgba(26,143,165,.3);
}
.sa-tab-panel        { display: none; }
.sa-tab-panel.active { display: block; }

/* ── Setting toggles ── */
.sa-setting-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 15px 0; border-bottom: 1px solid var(--border-light);
}
.sa-setting-row:last-of-type { border-bottom: none; }
.sa-setting-label { font-size: 13px; font-weight: 600; color: var(--text); }
.sa-setting-desc  { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

.sa-switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
.sa-switch input[type="checkbox"] { opacity: 0; width: 0; height: 0; position: absolute; }
.sa-switch input[type="hidden"]   { display: none; }
.sa-switch__slider {
    position: absolute; inset: 0;
    background: var(--border); border-radius: 22px;
    cursor: pointer; transition: background .2s;
}
.sa-switch__slider::after {
    content: ''; position: absolute;
    width: 16px; height: 16px; background: #fff;
    border-radius: 50%; top: 3px; left: 3px;
    transition: left .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.sa-switch input[type="checkbox"]:checked ~ .sa-switch__slider { background: var(--primary); }
.sa-switch input[type="checkbox"]:checked ~ .sa-switch__slider::after { left: 21px; }

/* ── Form ── */
.sa-form-grid   { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 20px; }
.sa-form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
.sa-form-group  { display: flex; flex-direction: column; gap: 5px; }
.sa-form-group > label {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: var(--text-muted);
}
.sa-form-actions      { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
.sa-form-actions-left { display: flex; gap: 10px; justify-content: flex-start; margin-top: 14px; }
.sa-hint { font-size: 11px; color: var(--text-light); }

/* ── Plans ── */
.sa-plan-block {
    border: 1px solid var(--border); border-radius: 10px;
    padding: 18px; margin-bottom: 14px; background: var(--surface-2);
}
.sa-plan-block:last-child { margin-bottom: 0; }
.sa-plan-block__header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
.sa-plan-name  { font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 9999px; background: var(--navy); color: #fff; }
.sa-plan-price { font-size: 13px; color: var(--text-muted); }

/* ── Client list ── */
.sa-search-row  { display: flex; gap: 10px; margin-bottom: 16px; }
.sa-search-wrap { flex: 1; position: relative; }
.sa-search-wrap svg {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    width: 15px; height: 15px; color: var(--text-light); pointer-events: none;
}
.sa-search-wrap input {
    width: 100%; padding: 9px 12px 9px 36px;
    border: 1.5px solid var(--border); border-radius: 9px;
    font-size: 13px; outline: none; background: var(--surface);
    color: var(--text); font-family: inherit; transition: border-color .2s;
}
.sa-search-wrap input:focus  { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(26,143,165,.1); }
.sa-search-wrap input::placeholder { color: var(--text-light); }

.sa-client-row {
    display: flex; align-items: center; gap: 14px;
    padding: 12px 16px; border: 1px solid var(--border); border-radius: 10px;
    margin-bottom: 8px; cursor: pointer;
    transition: border-color .15s, background .15s;
}
.sa-client-row:hover    { border-color: var(--primary); background: rgba(26,143,165,.03); }
.sa-client-row.selected { border-color: var(--primary); background: rgba(26,143,165,.06); }

.sa-client-avatar {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.sa-avatar-0 { background: linear-gradient(135deg,#6366f1,#8b5cf6); }
.sa-avatar-1 { background: linear-gradient(135deg,#1a8fa5,#00c9a7); }
.sa-avatar-2 { background: linear-gradient(135deg,#f59e0b,#f97316); }
.sa-avatar-3 { background: linear-gradient(135deg,#ec4899,#db2777); }
.sa-avatar-4 { background: linear-gradient(135deg,#10b981,#059669); }

.sa-client-info  { flex: 1; min-width: 0; }
.sa-client-name  { font-size: 14px; font-weight: 600; color: var(--text); }
.sa-client-email { font-size: 12px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sa-client-meta  { display: flex; align-items: center; gap: 6px; }
.sa-client-arrow { color: var(--text-light); flex-shrink: 0; }

.sa-chip-gray  { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px; background: var(--surface-2); color: var(--text-muted); }
.sa-chip-green { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px; background: var(--success-bg); color: var(--success); }
.sa-chip-blue  { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px; background: var(--info-bg); color: var(--info); }

/* ── Edit form ── */
.sa-edit-form         { display: none; }
.sa-edit-form.visible { display: block; }
.sa-edit-form-header  { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.sa-ef-name    { font-size: 15px; font-weight: 700; color: var(--text); }
.sa-ef-company { font-size: 12px; color: var(--text-muted); }

.sa-pwd-wrap { position: relative; }
.sa-pwd-wrap .sa-input { padding-right: 38px; }
.sa-pwd-toggle {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    cursor: pointer; color: var(--text-light); background: none; border: none;
    display: flex; align-items: center; transition: color .15s;
}
.sa-pwd-toggle:hover { color: var(--text-muted); }

.sa-warning-box {
    background: var(--warning-bg); border: 1px solid #fde68a;
    border-radius: 8px; padding: 10px 14px; margin-top: 16px;
    font-size: 12px; color: #92400e;
    display: flex; gap: 8px; align-items: flex-start;
}
.sa-warning-box svg { flex-shrink: 0; margin-top: 1px; }

.sa-js-alert {
    display: none; padding: 11px 14px; border-radius: 8px;
    font-size: 13px; font-weight: 500; margin-top: 12px;
    align-items: center; gap: 8px;
}
.sa-js-alert.error   { display: flex; background: var(--danger-bg); color: var(--danger); border: 1px solid #fecaca; }
.sa-js-alert.success { display: flex; background: var(--success-bg); color: var(--success); border: 1px solid #bbf7d0; }

.sa-empty { text-align: center; color: var(--text-light); font-size: 13px; padding: 24px; }

@media (max-width: 768px) {
    .sa-form-grid, .sa-form-grid-3 { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

{{-- ══════════ TABS ══════════ --}}
<div class="sa-tabs-bar" role="tablist">
    <button class="sa-tab-btn active" onclick="switchTab('global')"  id="btn-global"  role="tab">Général</button>
    <button class="sa-tab-btn"        onclick="switchTab('plans')"   id="btn-plans"   role="tab">Plans</button>
    <button class="sa-tab-btn"        onclick="switchTab('clients')" id="btn-clients" role="tab">Accès Clients</button>
</div>


{{-- ═══════════════════════════════════════════════════
     TAB 1 — GÉNÉRAL
═══════════════════════════════════════════════════ --}}
<div class="sa-tab-panel active" id="tab-global">
    <div class="sa-card">
        <div class="sa-card-header">
            <div>
                <div class="sa-card-title">Paramètres de la plateforme</div>
                <div class="sa-card-sub">Configuration globale du système</div>
            </div>
            <div style="width:38px;height:38px;border-radius:10px;background:var(--info-bg);display:flex;align-items:center;justify-content:center;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#3b82f6" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                </svg>
            </div>
        </div>
        <div class="sa-card-body">
            <form method="POST" action="{{ route('superadmin.settings.global.update') }}">
                @csrf
                @method('PUT')

                <div class="sa-setting-row">
                    <div>
                        <div class="sa-setting-label">Mode maintenance</div>
                        <div class="sa-setting-desc">Désactiver temporairement l'accès aux tenants</div>
                    </div>
                    <label class="sa-switch">
                        <input type="hidden"   name="mode_maintenance" value="0">
                        <input type="checkbox" name="mode_maintenance" value="1"
                            {{ ($parametres['mode_maintenance'] ?? false) ? 'checked' : '' }}>
                        <span class="sa-switch__slider"></span>
                    </label>
                </div>

                <div class="sa-setting-row">
                    <div>
                        <div class="sa-setting-label">Notifications email</div>
                        <div class="sa-setting-desc">Envoyer des alertes aux admins par email</div>
                    </div>
                    <label class="sa-switch">
                        <input type="hidden"   name="notifications_email" value="0">
                        <input type="checkbox" name="notifications_email" value="1"
                            {{ ($parametres['notifications_email'] ?? true) ? 'checked' : '' }}>
                        <span class="sa-switch__slider"></span>
                    </label>
                </div>

                <div class="sa-form-grid">
                    <div class="sa-form-group">
                        <label for="nom_plateforme">Nom de la plateforme</label>
                        <input class="sa-input" type="text" id="nom_plateforme" name="nom_plateforme"
                            value="{{ old('nom_plateforme', $parametres['nom_plateforme'] ?? 'HospitalRH') }}"
                            placeholder="HospitalRH" maxlength="100" required />
                    </div>
                    <div class="sa-form-group">
                        <label for="email_support">Email support</label>
                        <input class="sa-input" type="email" id="email_support" name="email_support"
                            value="{{ old('email_support', $parametres['email_support'] ?? '') }}"
                            placeholder="support@hospitalrh.ma" required />
                    </div>
                </div>

                <div class="sa-form-actions">
                    <button type="submit" class="sa-btn sa-btn-primary">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                        Enregistrer les paramètres
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════
     TAB 2 — PLANS
═══════════════════════════════════════════════════ --}}
<div class="sa-tab-panel" id="tab-plans">
    <div class="sa-card">
        <div class="sa-card-header">
            <div>
                <div class="sa-card-title">Gestion des plans</div>
                <div class="sa-card-sub">Modifier les limites et tarifs de chaque plan</div>
            </div>
            <div style="width:38px;height:38px;border-radius:10px;background:#f0fdfa;display:flex;align-items:center;justify-content:center;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#0d9488" stroke-width="2">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <div class="sa-card-body">
            @forelse($plans as $plan)
                <div class="sa-plan-block">
                    <div class="sa-plan-block__header">
                        <span class="sa-plan-name">{{ $plan->nom }}</span>
                        <span class="sa-plan-price">{{ number_format($plan->prix_mensuel, 2) }} MAD / mois</span>
                    </div>
                    <form method="POST" action="{{ route('superadmin.settings.plans.update', $plan) }}">
                        @csrf
                        @method('PUT')
                        <div class="sa-form-grid-3">
                            <div class="sa-form-group">
                                <label>Max utilisateurs</label>
                                <input class="sa-input" type="number" name="max_utilisateurs" min="1"
                                    value="{{ old('max_utilisateurs', $plan->max_utilisateurs) }}"
                                    placeholder="Illimité" />
                                <span class="sa-hint">Laisser vide = illimité</span>
                            </div>
                            <div class="sa-form-group">
                                <label>Durée essai (jours)</label>
                                <input class="sa-input" type="number" name="duree_essai_jours" min="1" max="365" required
                                    value="{{ old('duree_essai_jours', $plan->duree_essai_jours) }}" />
                            </div>
                            <div class="sa-form-group">
                                <label>Prix mensuel (MAD)</label>
                                <input class="sa-input" type="number" name="prix_mensuel" min="0" step="0.01" required
                                    value="{{ old('prix_mensuel', $plan->prix_mensuel) }}" />
                            </div>
                        </div>
                        <div class="sa-form-actions-left">
                            <button type="submit" class="sa-btn sa-btn-primary sa-btn-sm">
                                Mettre à jour « {{ $plan->nom }} »
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <p class="sa-empty">Aucun plan configuré.</p>
            @endforelse
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════
     TAB 3 — ACCÈS CLIENTS
═══════════════════════════════════════════════════ --}}
<div class="sa-tab-panel" id="tab-clients">
    <div class="sa-card">
        <div class="sa-card-header">
            <div>
                <div class="sa-card-title">Modifier les accès d'un client</div>
                <div class="sa-card-sub">Rechercher un client et modifier son email ou mot de passe</div>
            </div>
            <div style="width:38px;height:38px;border-radius:10px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#8b5cf6" stroke-width="2">
                    <path d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828A2 2 0 0110.414 16H9v-1.414a2 2 0 01.586-1.414z"/>
                    <path d="M3 21h18"/>
                </svg>
            </div>
        </div>
        <div class="sa-card-body">

            {{-- Recherche --}}
            <div class="sa-search-row">
                <div class="sa-search-wrap">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" id="client-search"
                        placeholder="Rechercher par nom, email ou tenant…"
                        oninput="filterClients(this.value)" />
                </div>
            </div>

            {{-- Liste --}}
            <div id="client-list">
                @forelse($clients as $index => $client)
                    <div
                        class="sa-client-row"
                        data-name="{{ strtolower($client->name) }}"
                        data-email="{{ strtolower($client->email) }}"
                        data-tenant="{{ strtolower($client->tenant?->nom ?? '') }}"
                        onclick="selectClient(
                            {{ $client->id }},
                            '{{ addslashes($client->name) }}',
                            '{{ addslashes($client->email) }}',
                            '{{ addslashes($client->tenant?->subdomain ?? '') }}'
                        )"
                        role="button" tabindex="0"
                        onkeydown="if(event.key==='Enter') this.click()"
                    >
                        <div class="sa-client-avatar sa-avatar-{{ $index % 5 }}">
                            {{ strtoupper(substr($client->name, 0, 2)) }}
                        </div>
                        <div class="sa-client-info">
                            <div class="sa-client-name">{{ $client->name }}</div>
                            <div class="sa-client-email">{{ $client->email }}</div>
                        </div>
                        <div class="sa-client-meta">
                            @if($client->tenant)
                                <span class="sa-chip-gray">{{ $client->tenant->subdomain }}</span>
                            @endif
                            <span class="sa-chip-{{ ($client->statut ?? 'essai') === 'actif' ? 'green' : 'blue' }}">
                                {{ ucfirst($client->statut ?? 'essai') }}
                            </span>
                        </div>
                        <svg class="sa-client-arrow" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </div>
                @empty
                    <p class="sa-empty">Aucun client trouvé.</p>
                @endforelse
            </div>

            {{-- Formulaire édition --}}
            <div id="edit-form" class="sa-edit-form" aria-hidden="true">
                <hr style="border:none;border-top:1px solid var(--border-light);margin:20px 0;" />

                <div class="sa-edit-form-header">
                    <div class="sa-client-avatar sa-avatar-0" id="ef-avatar">??</div>
                    <div>
                        <div class="sa-ef-name"    id="ef-name">–</div>
                        <div class="sa-ef-company" id="ef-company">–</div>
                    </div>
                    <button type="button" class="sa-btn sa-btn-ghost sa-btn-sm" onclick="closeForm()" style="margin-left:auto;">
                        ✕ Fermer
                    </button>
                </div>

                <form id="access-form" method="POST" action="" novalidate>
                    @csrf

                    <div class="sa-form-grid">
                        <div class="sa-form-group">
                            <label for="field-email">Nouvel email</label>
                            <input class="sa-input" type="email" id="field-email" name="email"
                                placeholder="ex: admin@societe.com" value="{{ old('email') }}" />
                            <span class="sa-hint">Laisser vide pour ne pas modifier</span>
                        </div>
                        <div class="sa-form-group">
                            <label for="field-email-confirm">Confirmer l'email</label>
                            <input class="sa-input" type="email" id="field-email-confirm" name="email_confirmation"
                                placeholder="Répéter l'email" />
                        </div>
                        <div class="sa-form-group">
                            <label for="field-pwd">Nouveau mot de passe</label>
                            <div class="sa-pwd-wrap">
                                <input class="sa-input" type="password" id="field-pwd" name="password"
                                    placeholder="Nouveau mot de passe" />
                                <button type="button" class="sa-pwd-toggle" onclick="togglePwd('field-pwd')">
                                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                            <span class="sa-hint">Min. 8 caractères</span>
                        </div>
                        <div class="sa-form-group">
                            <label for="field-pwd-confirm">Confirmer le mot de passe</label>
                            <div class="sa-pwd-wrap">
                                <input class="sa-input" type="password" id="field-pwd-confirm" name="password_confirmation"
                                    placeholder="Répéter le mot de passe" />
                                <button type="button" class="sa-pwd-toggle" onclick="togglePwd('field-pwd-confirm')">
                                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="sa-warning-box">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        Cette action est irréversible. Le client sera notifié par email de tout changement d'accès.
                    </div>

                    <div class="sa-js-alert" id="js-alert">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/>
                        </svg>
                        <span id="js-alert-msg"></span>
                    </div>

                    <div class="sa-form-actions">
                        <button type="button" class="sa-btn sa-btn-ghost" onclick="closeForm()">Annuler</button>
                        <button type="submit" class="sa-btn sa-btn-primary">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/superadmin/settings.js') }}"></script>
@endpush
