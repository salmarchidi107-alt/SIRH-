{{-- ✅ CORRECTION BUG 1 : @extends + @section au lieu d'un fichier HTML autonome
     La sidebar réapparaît car le layout superadmin (avec sidebar) est réintégré. --}}
@extends('layouts.superadmin')

@section('title', 'Paramètres')

@section('content')

{{-- Styles spécifiques à cette page --}}
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
/* ══════════════════════════════════════════
   VARIABLES
══════════════════════════════════════════ */
:root {
    --bg:        #f4f5f7;
    --surface:   #ffffff;
    --surface2:  #f9fafb;
    --border:    #e5e7eb;
    --border2:   #d1d5db;
    --text-1:    #111827;
    --text-2:    #374151;
    --text-3:    #6b7280;
    --text-4:    #9ca3af;
    --accent:    #4f46e5;
    --accent-bg: #eef2ff;
    --accent-h:  #4338ca;
    --green:     #059669;
    --green-bg:  #ecfdf5;
    --green-b:   #a7f3d0;
    --blue:      #2563eb;
    --blue-bg:   #eff6ff;
    --blue-b:    #bfdbfe;
    --warn:      #d97706;
    --warn-bg:   #fffbeb;
    --warn-b:    #fde68a;
    --red:       #dc2626;
    --red-bg:    #fef2f2;
    --red-b:     #fecaca;
    --radius:    12px;
    --shadow:    0 1px 3px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.05);
    --shadow-md: 0 4px 12px rgba(0,0,0,.08), 0 1px 3px rgba(0,0,0,.06);
}

.page-header {
    padding: auto;
    margin: auto;
}
.page-title {
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--text-1);
    letter-spacing: -.03em;
}
.page-sub {
    font-size: 13px;
    color: var(--text-3);
    margin-top: 3px;
}
.main {
    margin:auto;
    padding: auto;
}

/* ── CARD ── */
.sa-card { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-md); overflow: hidden; }
.sa-card-header { display: flex; align-items: center; gap: 14px; padding: 18px 22px; border-bottom: 1px solid var(--border); background: var(--surface2); }
.sa-card-header-icon { width: 40px; height: 40px; border-radius: 10px; background: var(--accent-bg); display: flex; align-items: center; justify-content: center; color: var(--accent); flex-shrink: 0; border: 1px solid #c7d2fe; }
.sa-card-title { font-size: 14px; font-weight: 700; color: var(--text-1); letter-spacing: -.01em; }
.sa-card-sub { font-size: 12px; color: var(--text-3); margin-top: 2px; }
.sa-card-body { padding: 20px 22px; }

/* ── SEARCH ── */
.sa-search-wrap { position: relative; margin-bottom: 16px; }
.sa-search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--text-4); pointer-events: none; }
.sa-search-input { width: 100%; box-sizing: border-box; padding: 9px 14px 9px 36px !important; background: var(--surface2) !important; border: 1px solid var(--border2) !important; border-radius: 9px !important; color: var(--text-1) !important; font-family: 'DM Sans', sans-serif; font-size: 13px; transition: border-color .15s, box-shadow .15s; }
.sa-search-input::placeholder { color: var(--text-4); }
.sa-search-input:focus { border-color: var(--accent) !important; outline: none; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }

/* ── TENANT GROUPS ── */
.sa-tenant-group { background: var(--surface2); border-radius: 10px; overflow: hidden; margin-bottom: 10px; border: 1px solid var(--border); }
.sa-tenant-row { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-bottom: 1px solid var(--border); background: #fff; }
.sa-tenant-avatar { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; letter-spacing: .5px; }
.sa-tenant-info { flex: 1; min-width: 0; }
.sa-tenant-name { font-size: 13px; font-weight: 600; color: var(--text-1); }
.sa-tenant-domain { font-size: 11px; color: var(--text-4); margin-top: 1px; font-family: 'DM Mono', monospace; }

/* ── STATUS BADGES ── */
.sa-badge-status { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 99px; white-space: nowrap; flex-shrink: 0; }
.sa-badge-blue  { background: var(--blue-bg); color: var(--blue); border: 1px solid var(--blue-b); }
.sa-badge-green { background: var(--green-bg); color: var(--green); border: 1px solid var(--green-b); }

/* ── USER ROWS ── */
.sa-user-row { display: flex; align-items: center; gap: 12px; padding: 10px 14px 10px 28px; border-bottom: 1px solid var(--border); cursor: pointer; transition: background .12s; }
.sa-user-row:last-child { border-bottom: none; }
.sa-user-row:hover { background: #f0f1ff; }
.sa-user-row.selected { background: var(--accent-bg); border: 1px solid #ffffff !important; border-radius: 8px; margin: 4px 8px; padding: 9px 12px 9px 20px; }
.sa-user-avatar { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }
.sa-user-info { flex: 1; min-width: 0; }
.sa-user-name { font-size: 13px; font-weight: 600; color: rgb(9, 10, 10)!important }
.sa-user-email { font-size: 11px; color: var(--text-3); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 1px; font-family: 'DM Mono', monospace; }
.sa-role-label { font-size: 11px; font-weight: 600; color: var(--text-3); background: var(--bg); border: 1px solid var(--border2); padding: 3px 9px; border-radius: 99px; flex-shrink: 0; }
.sa-chevron { width: 14px; height: 14px; color: var(--text-4); flex-shrink: 0; }

/* ── EMPTY STATE ── */
.sa-empty-state { text-align: center; color: var(--text-4); font-size: 13px; padding: 32px; }

/* ── EDIT FORM ── */
.sa-divider { border: none; border-top: 1px solid var(--border); margin: 22px 0; }
.sa-edit-form { display: none; }
.sa-edit-form.visible { display: block; }
.sa-edit-form-header { display: flex; align-items: center; gap: 14px; margin-bottom: 22px; padding: 14px 16px; background: var(--surface2); border: 1px solid var(--border); border-radius: 10px; }
.sa-ef-name { font-size: 15px; font-weight: 700; color: var(--text-1); }
.sa-ef-company { font-size: 12px; color: var(--text-3); margin-top: 2px; font-family: 'DM Mono', monospace; }
.sa-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 4px; }
.sa-form-group { display: flex; flex-direction: column; gap: 6px; }
.sa-form-group > label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--text-3); }

/* ── INPUTS ── */
.sa-input { background: var(--surface2); border: 1px solid var(--border2); border-radius: 8px; padding: 9px 13px; color: var(--text-1); font-family: 'DM Sans', sans-serif; font-size: 13px; width: 100%; box-sizing: border-box; transition: border-color .15s, box-shadow .15s; }
.sa-input:focus { border-color: var(--accent); outline: none; box-shadow: 0 0 0 3px rgba(79,70,229,.12); background: #fff; }
.sa-input::placeholder { color: var(--text-4); }
.sa-hint { font-size: 11px; color: var(--text-4); }
.sa-pwd-wrap { position: relative; }
.sa-pwd-wrap .sa-input { padding-right: 40px; }
.sa-pwd-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-4); background: none; border: none; display: flex; align-items: center; padding: 0; }
.sa-pwd-toggle:hover { color: var(--text-2); }

/* ── WARNINGS + ALERTS ── */
.sa-warning-box { background: var(--warn-bg); border: 1px solid var(--warn-b); border-radius: 8px; padding: 11px 14px; margin-top: 18px; font-size: 12px; color: var(--warn); display: flex; gap: 9px; align-items: flex-start; }
.sa-js-alert { display: none; padding: 11px 14px; border-radius: 8px; font-size: 13px; font-weight: 500; margin-top: 12px; align-items: center; gap: 9px; }
.sa-js-alert.error   { display: flex; background: var(--red-bg);   color: var(--red);   border: 1px solid var(--red-b);   }
.sa-js-alert.success { display: flex; background: var(--green-bg); color: var(--green); border: 1px solid var(--green-b); }

/* ── BUTTONS ── */
.sa-form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 22px; }
.sa-btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 9px; font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: opacity .15s, transform .1s, background .12s; }
.sa-btn:active { transform: scale(0.98); }
.sa-btn-primary { background: var(--accent); color: #fff; box-shadow: 0 1px 3px rgba(79,70,229,.3); }
.sa-btn-primary:hover { background: var(--accent-h); }
.sa-btn-ghost { background: var(--surface2); color: var(--text-2); border: 1px solid var(--border2); }
.sa-btn-ghost:hover { background: var(--bg); }
.sa-btn-sm { padding: 6px 12px; font-size: 12px; }

@media (max-width: 640px) {
    .sa-form-grid { grid-template-columns: 1fr; }
    .main, .page-header { padding: 0 14px; }
}
</style>
@endpush

{{-- ════════════════════════════════════
     PAGE HEADER
════════════════════════════════════ --}}
<div class="page-header">
    <div class="page-title">Paramètres</div>
    <div class="page-sub">Gestion des accès clients</div>
</div>

{{-- ════════════════════════════════════
     FLASH MESSAGES
════════════════════════════════════ --}}
@if (session('success'))
    <div class="main" style="padding-bottom:0;margin-bottom:0;">
        <div class="sa-js-alert success" style="display:flex;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="main" style="padding-bottom:0;margin-bottom:0;">
        <div class="sa-js-alert error" style="display:flex;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    </div>
@endif

{{-- ════════════════════════════════════
     MAIN CONTENT
════════════════════════════════════ --}}
<div class="main">
    <div class="sa-card">

        {{-- CARD HEADER --}}
        <div class="sa-card-header">
            <div class="sa-card-header-icon">
                <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828A2 2 0 0110.414 16H9v-1.414a2 2 0 01.586-1.414z"/>
                    <path d="M3 21h18"/>
                </svg>
            </div>
            <div>
                <div class="sa-card-title">Modifier les accès d'un client</div>
                <div class="sa-card-sub">Rechercher un client et modifier son email ou mot de passe</div>
            </div>
        </div>

        {{-- CARD BODY --}}
        <div class="sa-card-body">

            {{-- SEARCH --}}
            <div class="sa-search-wrap">
                <svg class="sa-search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input
                    type="text"
                    id="client-search"
                    class="sa-input sa-search-input"
                    placeholder="Rechercher par nom, email ou tenant…"
                    oninput="filterClients(this.value)"
                />
            </div>

            {{-- ════════════════════════════════════
                 CLIENT LIST
                  CORRECTION BUG 2 :
                 $clients est désormais une Collection groupée par tenant_id
                 (via ->groupBy('tenant_id') dans le service).
                 On itère sur chaque groupe → les tenants s'affichent correctement.
            ════════════════════════════════════ --}}
            <div id="client-list">

                @forelse ($clients as $tenantId => $users)
                    @php
                        $tenant       = optional($users->first())->tenant;
                        $tenantName   = $tenant?->name ?? 'Sans tenant';
                        $tenantDomain = $tenant?->domain ?? '';
                        $userCount    = $users->count();
                        $isActive     = $tenant?->status === 'active';
                        $tenant_id    = $tenantId ?? '0';

                        $colors = ['#4f46e5','#059669','#0891b2','#d97706','#db2777','#7c3aed','#0369a1'];
                        $colorIndex = crc32($tenant_id) % count($colors);
                        $avatarColor = $colors[abs($colorIndex)];

                        $initials = collect(explode(' ', $tenantName))
                            ->take(2)
                            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                            ->join('');
                    @endphp

                    <div class="sa-tenant-group"
                         data-tenant-search="{{ strtolower($tenantName) }}">

                        {{-- TENANT HEADER ROW --}}
                        <div class="sa-tenant-row">
                            <div class="sa-tenant-avatar" style="background: {{ $avatarColor }};">
                                {{ $initials }}
                            </div>
                            <div class="sa-tenant-info">
                                <div class="sa-tenant-name">{{ $tenantName }}</div>
                                @if($tenantDomain)
                                    <div class="sa-tenant-domain">{{ $tenantDomain }}</div>
                                @endif
                            </div>
                            <span class="sa-badge-status {{ $isActive ? 'sa-badge-green' : 'sa-badge-blue' }}">
                                {{ $isActive ? 'Actif' : 'Essai' }} · {{ $userCount }} utilisateur{{ $userCount > 1 ? 's' : '' }}
                            </span>
                        </div>

                        {{-- USER ROWS --}}
                        @foreach ($users as $user)
                            @php
                                $userInitials = collect(explode(' ', $user->name))
                                    ->take(2)
                                    ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                                    ->join('');
                                $roleLabel = $user->getRoleDisplayName();
                            @endphp
                            <div class="sa-user-row"
                                 data-name="{{ strtolower($user->name) }}"
                                 data-email="{{ strtolower($user->email) }}"
                                 data-tenant="{{ strtolower($tenantName) }}"
                                 onclick="selectClient(
                                     {{ $user->id }},
                                     '{{ addslashes($user->name) }}',
                                     '{{ addslashes($user->email) }}',
                                     '{{ addslashes($tenantName) }}',
                                     '{{ $avatarColor }}'
                                 )"
                                 role="button"
                                 tabindex="0">
                                <div class="sa-user-avatar" style="background: {{ $avatarColor }};">
                                    {{ $userInitials }}
                                </div>
                                <div class="sa-user-info">
                                    <div class="sa-user-name">{{ $user->name }}</div>
                                    <div class="sa-user-email">{{ $user->email }}</div>
                                </div>
                                <span class="sa-role-label">{{ $roleLabel }}</span>
                                <svg class="sa-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </div>
                        @endforeach

                    </div>{{-- /.sa-tenant-group --}}

                @empty
                    <div class="sa-empty-state">Aucun client trouvé.</div>
                @endforelse

            </div>{{-- /#client-list --}}

            {{-- ════════════════════════════════════
                 EDIT FORM
            ════════════════════════════════════ --}}
            <div id="edit-form" class="sa-edit-form" aria-hidden="true">
                <hr class="sa-divider" />

                <div class="sa-edit-form-header">
                    <div class="sa-user-avatar" id="ef-avatar"
                         style="width:44px;height:44px;font-size:15px;border-radius:10px;background:#4f46e5;">??</div>
                    <div>
                        <div class="sa-ef-name" id="ef-name">–</div>
                        <div class="sa-ef-company" id="ef-company">–</div>
                    </div>
                    <button type="button" class="sa-btn sa-btn-ghost sa-btn-sm"
                            onclick="closeForm()" style="margin-left:auto;">
                        ✕ Fermer
                    </button>
                </div>

                <form id="access-form" method="POST" action="" novalidate>
                    @csrf
                    @method('POST')

                    <div class="sa-form-grid">
                        <div class="sa-form-group">
                            <label for="field-email">Nouvel email</label>
                            <input class="sa-input" type="email" id="field-email"
                                   name="email" placeholder="ex: admin@societe.com" />
                            <span class="sa-hint">Laisser vide pour ne pas modifier</span>
                        </div>
                        <div class="sa-form-group">
                            <label for="field-email-confirm">Confirmer l'email</label>
                            <input class="sa-input" type="email" id="field-email-confirm"
                                   name="email_confirmation" placeholder="Répéter l'email" />
                        </div>
                        <div class="sa-form-group">
                            <label for="field-pwd">Nouveau mot de passe</label>
                            <div class="sa-pwd-wrap">
                                <input class="sa-input" type="password" id="field-pwd"
                                       name="password" placeholder="Nouveau mot de passe" />
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
                                <input class="sa-input" type="password" id="field-pwd-confirm"
                                       name="password_confirmation" placeholder="Répéter le mot de passe" />
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
                        Cette action est irréversible. Le client sera notifié par email de tout changement.
                    </div>

                    <div class="sa-js-alert" id="js-alert">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M15 9l-6 6M9 9l6 6"/>
                        </svg>
                        <span id="js-alert-msg"></span>
                    </div>

                    <div class="sa-form-actions">
                        <button type="button" class="sa-btn sa-btn-ghost" onclick="closeForm()">Annuler</button>
                        <button type="submit" class="sa-btn sa-btn-primary">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>{{-- /#edit-form --}}

        </div>{{-- /.sa-card-body --}}
    </div>{{-- /.sa-card --}}
</div>{{-- /.main --}}

@push('scripts')
<script src="{{ asset('js/superadmin/settings.js') }}"></script>
<script>
function filterClients(query) {
    const q = query.trim().toLowerCase();
    document.querySelectorAll('#client-list .sa-tenant-group').forEach(group => {
        const tenantSearch = group.dataset.tenantSearch || '';
        let groupVisible = false;
        group.querySelectorAll('.sa-user-row').forEach(row => {
            const match = !q
                || (row.dataset.name   || '').includes(q)
                || (row.dataset.email  || '').includes(q)
                || (row.dataset.tenant || '').includes(q)
                || tenantSearch.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) groupVisible = true;
        });
        group.style.display = groupVisible ? '' : 'none';
    });
}

function selectClient(id, name, email, company, avatarColor) {
    document.querySelectorAll('.sa-user-row').forEach(r => r.classList.remove('selected'));
    document.querySelectorAll('.sa-user-row').forEach(row => {
        if (row.dataset.email === email.toLowerCase()) row.classList.add('selected');
    });
    const avatar = document.getElementById('ef-avatar');
    avatar.textContent = name.substring(0, 2).toUpperCase();
    avatar.style.background = avatarColor || '#1a8fa5';
    document.getElementById('ef-name').textContent    = name;
    document.getElementById('ef-company').textContent = company || email;

    document.getElementById('access-form').action =
        `/superadmin/settings/clients/${id}/access`;

    clearFields(); hideAlert();
    const form = document.getElementById('edit-form');
    form.classList.add('visible');
    form.setAttribute('aria-hidden', 'false');
    setTimeout(() => form.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 50);
}

function closeForm() {
    document.querySelectorAll('.sa-user-row').forEach(r => r.classList.remove('selected'));
    const form = document.getElementById('edit-form');
    if (form) { form.classList.remove('visible'); form.setAttribute('aria-hidden', 'true'); }
    clearFields(); hideAlert();
}

function clearFields() {
    ['field-email','field-email-confirm','field-pwd','field-pwd-confirm'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
}

function showAlert(msg, type) {
    const box = document.getElementById('js-alert');
    if (!box) return;
    document.getElementById('js-alert-msg').textContent = msg;
    box.className = 'sa-js-alert ' + type;
}

function hideAlert() {
    const box = document.getElementById('js-alert');
    if (box) box.className = 'sa-js-alert';
}

function togglePwd(fieldId) {
    const input = document.getElementById(fieldId);
    if (input) input.type = input.type === 'password' ? 'text' : 'password';
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('access-form')?.addEventListener('submit', e => {
        const email        = document.getElementById('field-email').value.trim();
        const emailConfirm = document.getElementById('field-email-confirm').value.trim();
        const pwd          = document.getElementById('field-pwd').value;
        const pwdConfirm   = document.getElementById('field-pwd-confirm').value;

        if (!email && !pwd) {
            e.preventDefault(); showAlert('Veuillez remplir au moins un champ.', 'error'); return;
        }
        if (email) {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault(); showAlert("L'adresse email n'est pas valide.", 'error'); return;
            }
            if (email !== emailConfirm) {
                e.preventDefault(); showAlert('Les adresses email ne correspondent pas.', 'error'); return;
            }
        }
        if (pwd) {
            if (pwd.length < 8) {
                e.preventDefault(); showAlert('Le mot de passe doit contenir au moins 8 caractères.', 'error'); return;
            }
            if (pwd !== pwdConfirm) {
                e.preventDefault(); showAlert('Les mots de passe ne correspondent pas.', 'error'); return;
            }
        }
    });
});
</script>
@endpush

@endsection
