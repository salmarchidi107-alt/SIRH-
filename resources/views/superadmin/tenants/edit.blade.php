@extends('layouts.superadmin')
@section('title', 'Modifier '.$tenant->name)
@section('page-title', 'Modifier le tenant')

@section('page-header')
    <div class="sa-breadcrumb">
        <a href="{{ route('superadmin.tenants.index') }}">Tenants</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        <span style="color:var(--text);font-weight:600;">{{ $tenant->name }}</span>
    </div>
    <div class="sa-page-title" style="font-size:clamp(15px,2.5vw,22px);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;">
        Modifier le tenant
    </div>
@endsection

@section('content')


{{-- Erreurs de validation --}}
@if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:18px;">
        <div style="font-weight:600;color:#dc2626;margin-bottom:6px;">Veuillez corriger les erreurs suivantes :</div>
        <ul style="margin:0;padding-left:18px;color:#b91c1c;font-size:13px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('superadmin.tenants.update', $tenant) }}" enctype="multipart/form-data">
@csrf
@method('PUT')

    {{-- ── IDENTITÉ ─────────────────────────────────────────────── --}}
    <div class="sa-card" style="margin-bottom:16px;">
        <div class="sa-card-header"><div class="sa-card-title">Identité de la société</div></div>
        <div class="sa-card-body" style="display:flex;flex-direction:column;gap:0;">

            {{-- Nom société --}}
            <div class="sa-field">
                <label class="sa-label">Nom de la société *</label>
                <input type="text" name="company_name" class="sa-input"
                       value="{{ old('company_name', $tenant->name) }}" required maxlength="100">
                @error('company_name')<div class="sa-error">{{ $message }}</div>@enderror
            </div>

            {{-- Secteur --}}
            <div class="sa-field">
                <label class="sa-label">Secteur</label>
                <select name="sector" id="sector-select" class="sa-input"
                        onchange="toggleSectorOther(this.value)">
                    @foreach(['SaaS / Tech','Finance','Santé','Éducation','Retail','Autre'] as $s)
                    <option {{ old('sector', $tenant->sector) === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                <div id="sector-other-wrap"
                     style="margin-top:8px;display:{{ old('sector', $tenant->sector) === 'Autre' ? 'block' : 'none' }};">
                    <input type="text" name="sector_other" class="sa-input"
                           value="{{ old('sector_other', $tenant->sector_other ?? '') }}"
                           placeholder="Précisez le secteur...">
                </div>
            </div>

            {{-- Région --}}
            @php
                $regionsList = [
                    'Casablanca-Settat','Rabat-Salé-Kénitra','Fès-Meknès','Marrakech-Safi',
                    'Béni Mellal-Khénifra','Tanger-Tétouan-Al Hoceïma','Oriental',
                    'Drâa-Tafilalet','Souss-Massa','Guelmim-Oued Noun',
                    'Laâyoune-Sakia El Hamra','Dakhla-Oued Ed-Dahab','Autre'
                ];
                $currentRegion   = old('region', $tenant->region);
                $isCustomRegion  = $currentRegion && !in_array($currentRegion, $regionsList);
                $selectRegion    = $isCustomRegion ? 'Autre' : $currentRegion;
                $regionOtherVal  = $isCustomRegion ? $currentRegion : old('region_other', '');
            @endphp
            <div class="sa-field">
                <label class="sa-label">Région *</label>
                <select name="region" id="region-select" class="sa-input" required
                        onchange="toggleRegionOther(this.value)">
                    <option value="">Choisir une région...</option>
                    @foreach($regionsList as $r)
                    <option {{ $selectRegion == $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
                <div id="region-other-wrap"
                     style="margin-top:8px;display:{{ ($isCustomRegion || $selectRegion === 'Autre') ? 'block' : 'none' }};">
                    <input type="text" name="region_other" id="region-other-input" class="sa-input"
                           value="{{ $regionOtherVal }}"
                           placeholder="Précisez la région..."
                           {{ ($isCustomRegion || $selectRegion === 'Autre') ? 'required' : '' }}>
                </div>
                @error('region')<div class="sa-error">{{ $message }}</div>@enderror
                @error('region_other')<div class="sa-error">{{ $message }}</div>@enderror
            </div>

            {{-- Fuseau horaire --}}
<div class="sa-field">
    <label class="sa-label">Fuseau horaire *</label>
    <select name="timezone" class="sa-input" required>
        @php
            $timezones = \DateTimeZone::listIdentifiers();
            $selectedTz = old('timezone', $tenant->timezone ?? 'Africa/Casablanca');
        @endphp
        @foreach($timezones as $tz)
            <option value="{{ $tz }}" {{ $selectedTz === $tz ? 'selected' : '' }}>
                {{ $tz }} (UTC{{ (new \DateTime('now', new \DateTimeZone($tz)))->format('P') }})
            </option>
        @endforeach
    </select>
    @error('timezone')<div class="sa-error">{{ $message }}</div>@enderror
</div>

            {{-- Adresse --}}
            <div class="sa-field">
                <label class="sa-label">Adresse *</label>
                <textarea name="address" class="sa-input" rows="2" required maxlength="255"
                          style="resize:vertical;line-height:1.5;"
                          placeholder="Ex: 23 Rue Mohammed V, Casablanca">{{ old('address', $tenant->address) }}</textarea>
                @error('address')<div class="sa-error">{{ $message }}</div>@enderror
            </div>

            {{-- Téléphone + ICE --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="sa-field">
                <div>
                    <label class="sa-label">Téléphone *</label>
                    <input type="tel" name="phone" class="sa-input"
                           value="{{ old('phone', $tenant->phone) }}"
                           placeholder="+212 6XX XXX XXX" required maxlength="20">
                    @error('phone')<div class="sa-error">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="sa-label">ICE *
                        <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--text-muted);">(15 chiffres)</span>
                    </label>
                    <input type="text" name="ice" class="sa-input"
                           value="{{ old('ice', $tenant->ice) }}"
                           placeholder="000000000000000"
                           required maxlength="15" minlength="15" pattern="\d{15}"
                           oninput="this.value=this.value.replace(/\D/g,'')">
                    @error('ice')<div class="sa-error">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Email société + Site web --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="sa-field">
                <div>
                    <label class="sa-label">Email société *</label>
                    <input type="email" name="email_societe" class="sa-input"
                           value="{{ old('email_societe', $tenant->email_societe) }}"
                           placeholder="contact@societe.ma" required maxlength="100">
                    @error('email_societe')<div class="sa-error">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="sa-label">Site web
                        <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--text-muted);">(optionnel)</span>
                    </label>
                    <input type="url" name="website" class="sa-input"
                           value="{{ old('website', $tenant->website) }}"
                           placeholder="https://www.societe.ma" maxlength="100">
                    @error('website')<div class="sa-error">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Logo --}}
            <div class="sa-field">
                <label class="sa-label">Logo de la société
                </label>
                @if($tenant->logo_path)
                <div style="margin-bottom:10px;display:flex;align-items:center;gap:10px;">
                    <img src="{{ asset('storage/' . $tenant->logo_path) }}"
                         alt="Logo actuel"
                         style="width:52px;height:52px;object-fit:contain;border-radius:8px;border:1.5px solid var(--border);padding:4px;background:#fff;">
                    <span style="font-size:12px;color:var(--text-muted);">Logo actuel</span>
                </div>
                @endif
                <div class="sa-upload" onclick="document.getElementById('file-input').click()" style="cursor:pointer;">
                    <div id="logo-preview-icon" style="font-size:26px;margin-bottom:6px;">📁</div>
                    <div id="upload-text" style="font-size:12px;color:var(--text-muted);">Glisser-déposer un nouveau logo ou cliquer</div>
                    <div style="font-size:11px;color:var(--text-light);margin-top:3px;">PNG, SVG, JPG · Max 2 Mo</div>
                </div>
                <input type="file" id="file-input" name="logo" accept="image/*" style="display:none" onchange="handleFile(this)">
                @error('logo')<div class="sa-error">{{ $message }}</div>@enderror
            </div>

        </div>
    </div>

    {{-- ── ADMIN ────────────────────────────────────────────────── --}}
    <div class="sa-card" style="margin-bottom:16px;">
        <div class="sa-card-header"><div class="sa-card-title">Compte admin principal</div></div>
        <div class="sa-card-body" style="display:flex;flex-direction:column;gap:0;">

            {{-- Prénom + Nom pré-remplis avec les valeurs actuelles --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="sa-field">
                <div>
                    <label class="sa-label">Prénom
                    </label>
                    @php
                        $adminFirstName = $tenant->admin?->first_name
                            ?? (isset($tenant->admin->name)
                                ? explode(' ', trim($tenant->admin->name), 2)[0]
                                : '');
                    @endphp
                    <input type="text" name="first_name" class="sa-input"
                           value="{{ old('first_name', $adminFirstName) }}"
                           placeholder="Prénom admin"
                           maxlength="50" autocomplete="off">
                    @error('first_name')<div class="sa-error">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="sa-label">Nom
                    </label>
                    @php
                        $adminLastName = $tenant->admin?->last_name
                            ?? (isset($tenant->admin->name) && str_contains(trim($tenant->admin->name), ' ')
                                ? explode(' ', trim($tenant->admin->name), 2)[1]
                                : '');
                    @endphp
                    <input type="text" name="last_name" class="sa-input"
                           value="{{ old('last_name', $adminLastName) }}"
                           placeholder="Nom admin"
                           maxlength="50" autocomplete="off">
                    @error('last_name')<div class="sa-error">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Email admin --}}
            <div class="sa-field">
                <label class="sa-label">Email admin *</label>
                <input type="email" name="admin_email" class="sa-input"
                       value="{{ old('admin_email', $tenant->admin?->email ?? '') }}"
                       placeholder="admin@societe.ma" required maxlength="100">
                @error('admin_email')<div class="sa-error">{{ $message }}</div>@enderror
            </div>

            {{-- Mot de passe --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="sa-field">

    {{-- Mot de passe actuel (lecture seule) --}}
    <div>
        <label class="sa-label">Mot de passe actuel
            <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--text-muted);">(masqué par sécurité)</span>
        </label>
        <div style="position:relative;">
           <input type="password" id="current-password-field" class="sa-input"
       value="{{ $tenant->admin?->plain_password ?? '' }}"
       readonly
       style="padding-right:42px;background:var(--bg-muted,#f8fafc);cursor:default;color:var(--text-muted);">
            <button type="button" onclick="toggleCurrentPassword()"
                    style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:var(--text-muted);display:flex;align-items:center;">
                <svg id="eye-icon-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     style="width:18px;height:18px;pointer-events:none;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7
                             -1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Nouveau mot de passe --}}
    <div>
        <label class="sa-label">Nouveau mot de passe
        </label>
        <div style="position:relative;">
            <input type="password" id="password-field" name="temp_password" class="sa-input"
                   minlength="8" placeholder="laisser vide pour ne pas changer"
                   style="padding-right:42px;">
            <button type="button" onclick="togglePassword()"
                    style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:var(--text-muted);display:flex;align-items:center;">
                <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     style="width:18px;height:18px;pointer-events:none;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7
                             -1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
        </div>
        @error('temp_password')<div class="sa-error">{{ $message }}</div>@enderror
    </div>

</div>

        </div>
    </div>

    {{-- ── BOUTONS ──────────────────────────────────────────────── --}}
    <div style="display:flex;gap:10px;">
        <a href="{{ route('superadmin.tenants.index') }}" class="sa-btn sa-btn-ghost">Annuler</a>
        <button type="submit" class="sa-btn sa-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Enregistrer les modifications
        </button>
    </div>

</form>
@endsection

@push('scripts')
<script>
function handleFile(input) {
    if (!input.files || !input.files[0]) return;
    const r = new FileReader();
    r.onload = e => {
        document.getElementById('logo-preview-icon').innerHTML =
            `<img src="${e.target.result}" style="width:40px;height:40px;object-fit:contain;border-radius:8px;">`;
        document.getElementById('upload-text').textContent = input.files[0].name + ' ✓';
    };
    r.readAsDataURL(input.files[0]);
}
function toggleSectorOther(val) {
    const wrap = document.getElementById('sector-other-wrap');
    if (wrap) wrap.style.display = val === 'Autre' ? 'block' : 'none';
}
function toggleRegionOther(val) {
    const wrap  = document.getElementById('region-other-wrap');
    const input = document.getElementById('region-other-input');
    if (!wrap) return;
    if (val === 'Autre') {
        wrap.style.display = 'block';
        input.required = true;
        input.focus();
    } else {
        wrap.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}
function togglePassword() {
    const field = document.getElementById('password-field');
    const icon  = document.getElementById('eye-icon');
    const isHidden = field.type === 'password';
    field.type = isHidden ? 'text' : 'password';
    icon.innerHTML = isHidden
        ? `<path stroke-linecap="round" stroke-linejoin="round"
                 d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7
                    a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243
                    M9.878 9.878l4.242 4.242M9.88 9.88L6.59 6.59m7.532 7.532l3.29 3.29
                    M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7
                    a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`
        : `<path stroke-linecap="round" stroke-linejoin="round"
                 d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
           <path stroke-linecap="round" stroke-linejoin="round"
                 d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7
                    -1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
}
function toggleCurrentPassword() {
    const field = document.getElementById('current-password-field');
    const icon  = document.getElementById('eye-icon-current');
    const isHidden = field.type === 'password';
    field.type = isHidden ? 'text' : 'password';
    icon.innerHTML = isHidden
        ? `<path stroke-linecap="round" stroke-linejoin="round"
                 d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7
                    a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243
                    M9.878 9.878l4.242 4.242M9.88 9.88L6.59 6.59m7.532 7.532l3.29 3.29
                    M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7
                    a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`
        : `<path stroke-linecap="round" stroke-linejoin="round"
                 d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
           <path stroke-linecap="round" stroke-linejoin="round"
                 d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7
                    -1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
}
</script>
@endpush

@push('styles')
<style>
.sa-card { width:100% !important; max-width:100% !important; box-sizing:border-box; }
@media (max-width:640px) {
    .sa-page-title { font-size:15px !important; }
    div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns:1fr !important; }
}
</style>
@endpush
