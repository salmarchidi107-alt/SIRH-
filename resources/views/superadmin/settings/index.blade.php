@extends('layouts.superadmin')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres')

@section('page-header')
    <div class="sa-page-title">Paramètres</div>
    <div class="sa-page-sub">Gestion des accès clients</div>
@endsection

@section('content')

{{-- ══════════════════════════════════════════════════
     ACCÈS CLIENTS
══════════════════════════════════════════════════ --}}
<div class="sa-card">
    <div class="sa-card-header">
        <div>
            <div class="sa-card-title">Modifier les accès d'un client</div>
            <div class="sa-card-sub">Rechercher un client et modifier son email ou mot de passe</div>
        </div>
        <div style="width:38px;height:38px;border-radius:10px;background:#f5f3ff;
                    display:flex;align-items:center;justify-content:center;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#8b5cf6" stroke-width="2">
                <path d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828A2 2 0 0110.414 16H9v-1.414a2 2 0 01.586-1.414z"/>
                <path d="M3 21h18"/>
            </svg>
        </div>
    </div>
    <div class="sa-card-body">

        {{-- Recherche --}}
        <div style="position:relative; margin-bottom:16px;">
            <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
            </svg>
            <input
                type="text"
                id="client-search"
                class="sa-input"
                style="padding-left:36px;"
                placeholder="Rechercher par nom, email ou tenant…"
                oninput="filterClients(this.value)"
            />
        </div>

        {{-- Liste clients --}}
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
                    <svg style="color:#94a3b8;flex-shrink:0;" width="16" height="16" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </div>
            @empty
                <p style="text-align:center;color:#94a3b8;font-size:13px;padding:24px;">
                    Aucun client trouvé.
                </p>
            @endforelse
        </div>

        {{-- Formulaire modification --}}
        <div id="edit-form" class="sa-edit-form" aria-hidden="true">
            <hr style="border:none;border-top:1px solid var(--border-light);margin:20px 0;" />

            <div class="sa-edit-form-header">
                <div class="sa-client-avatar sa-avatar-0" id="ef-avatar">??</div>
                <div>
                    <div class="sa-ef-name"    id="ef-name">–</div>
                    <div class="sa-ef-company" id="ef-company">–</div>
                </div>
                <button type="button" class="sa-btn sa-btn-ghost sa-btn-sm"
                        onclick="closeForm()" style="margin-left:auto;">
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
                        <input class="sa-input" type="email" id="field-email-confirm"
                            name="email_confirmation" placeholder="Répéter l'email" />
                    </div>
                    <div class="sa-form-group">
                        <label for="field-pwd">Nouveau mot de passe</label>
                        <div class="sa-pwd-wrap">
                            <input class="sa-input" type="password" id="field-pwd" name="password"
                                placeholder="Nouveau mot de passe" />
                            <button type="button" class="sa-pwd-toggle" onclick="togglePwd('field-pwd')">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
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
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="sa-warning-box">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    Cette action est irréversible. Le client sera notifié par email de tout changement.
                </div>

                <div class="sa-js-alert" id="js-alert">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M15 9l-6 6M9 9l6 6"/>
                    </svg>
                    <span id="js-alert-msg"></span>
                </div>

                <div class="sa-form-actions">
                    <button type="button" class="sa-btn sa-btn-ghost" onclick="closeForm()">
                        Annuler
                    </button>
                    <button type="submit" class="sa-btn sa-btn-primary">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path d="M5 13l4 4L19 7"/>
                        </svg>
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
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
.sa-client-email { font-size: 12px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sa-client-meta  { display: flex; align-items: center; gap: 6px; }

.sa-chip-gray  { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px; background: var(--surface-2); color: var(--text-muted); }
.sa-chip-green { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px; background: var(--success-bg); color: var(--success); }
.sa-chip-blue  { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px; background: var(--info-bg); color: var(--info); }

.sa-edit-form         { display: none; }
.sa-edit-form.visible { display: block; }
.sa-edit-form-header  { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.sa-ef-name    { font-size: 15px; font-weight: 700; color: var(--text); }
.sa-ef-company { font-size: 12px; color: var(--text-muted); }

.sa-form-grid   { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 4px; }
.sa-form-group  { display: flex; flex-direction: column; gap: 5px; }
.sa-form-group > label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); }
.sa-hint { font-size: 11px; color: var(--text-light); }
.sa-form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }

.sa-pwd-wrap { position: relative; }
.sa-pwd-wrap .sa-input { padding-right: 38px; }
.sa-pwd-toggle {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    cursor: pointer; color: var(--text-light); background: none; border: none;
    display: flex; align-items: center;
}

.sa-warning-box {
    background: var(--warning-bg); border: 1px solid #fde68a;
    border-radius: 8px; padding: 10px 14px; margin-top: 16px;
    font-size: 12px; color: #92400e;
    display: flex; gap: 8px; align-items: flex-start;
}

.sa-js-alert {
    display: none; padding: 11px 14px; border-radius: 8px;
    font-size: 13px; font-weight: 500; margin-top: 12px;
    align-items: center; gap: 8px;
}
.sa-js-alert.error   { display: flex; background: var(--danger-bg); color: var(--danger); border: 1px solid #fecaca; }
.sa-js-alert.success { display: flex; background: var(--success-bg); color: var(--success); border: 1px solid #bbf7d0; }

@media (max-width: 768px) {
    .sa-form-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
function filterClients(query) {
    const q = query.trim().toLowerCase();
    document.querySelectorAll('#client-list .sa-client-row').forEach(row => {
        const match = !q || (row.dataset.name||'').includes(q) || (row.dataset.email||'').includes(q) || (row.dataset.tenant||'').includes(q);
        row.style.display = match ? '' : 'none';
    });
}

function selectClient(id, name, email, company) {
    document.querySelectorAll('.sa-client-row').forEach(r => r.classList.remove('selected'));
    document.querySelector(`.sa-client-row[onclick*="selectClient(${id},"]`)?.classList.add('selected');
    document.getElementById('ef-avatar').textContent  = name.substring(0, 2).toUpperCase();
    document.getElementById('ef-name').textContent    = name;
    document.getElementById('ef-company').textContent = company || email;
    document.getElementById('access-form').action     = `/superadmin/settings/clients/${id}/access`;
    clearFields(); hideAlert();
    const form = document.getElementById('edit-form');
    form.classList.add('visible');
    form.setAttribute('aria-hidden', 'false');
    form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeForm() {
    document.querySelectorAll('.sa-client-row').forEach(r => r.classList.remove('selected'));
    const form = document.getElementById('edit-form');
    if (form) { form.classList.remove('visible'); form.setAttribute('aria-hidden', 'true'); }
    clearFields(); hideAlert();
}

function clearFields() {
    ['field-email','field-email-confirm','field-pwd','field-pwd-confirm'].forEach(id => {
        const el = document.getElementById(id); if (el) el.value = '';
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

document.getElementById('access-form')?.addEventListener('submit', e => {
    const email        = document.getElementById('field-email').value.trim();
    const emailConfirm = document.getElementById('field-email-confirm').value.trim();
    const pwd          = document.getElementById('field-pwd').value;
    const pwdConfirm   = document.getElementById('field-pwd-confirm').value;

    if (!email && !pwd) { e.preventDefault(); showAlert('Remplissez au moins un champ.', 'error'); return; }
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { e.preventDefault(); showAlert("Email invalide.", 'error'); return; }
    if (email && email !== emailConfirm) { e.preventDefault(); showAlert('Les emails ne correspondent pas.', 'error'); return; }
    if (pwd && pwd.length < 8) { e.preventDefault(); showAlert('Mot de passe min. 8 caractères.', 'error'); return; }
    if (pwd && pwd !== pwdConfirm) { e.preventDefault(); showAlert('Les mots de passe ne correspondent pas.', 'error'); return; }
});
</script>
@endpush
