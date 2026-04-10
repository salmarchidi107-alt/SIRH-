@extends('layouts.superadmin')
@section('title', 'Modifier '.$tenant->name)
@section('page-title', 'Modifier le tenant')

@section('page-header')
    <div class="sa-breadcrumb">
        <a href="{{ route('superadmin.tenants.index') }}">Tenants</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        <span style="color:var(--text);font-weight:600;">{{ $tenant->name }}</span>
    </div>
    <div class="sa-page-title">Modifier le tenant</div>
@endsection

@section('content')
<form method="POST" action="{{ route('superadmin.tenants.update', $tenant) }}" enctype="multipart/form-data">
@csrf @method('PUT')

<div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:20px;align-items:start;">
    <div>
        <div class="sa-card" style="margin-bottom:16px;">
            <div class="sa-card-header"><div class="sa-card-title">Identité</div></div>
            <div class="sa-card-body" style="display:flex;flex-direction:column;gap:0;">

                <div class="sa-field">
                    <label class="sa-label">Nom de la société *</label>
                    <input type="text" name="company_name" id="company-name" class="sa-input"
                           value="{{ old('company_name',$tenant->name) }}" oninput="updatePreview()" required>
                    @error('company_name')<div class="sa-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="sa-field">
                    <div>
                        <label class="sa-label">Slug URL *</label>
                        <input type="text" name="slug" id="slug" class="sa-input"
                               value="{{ old('slug',$tenant->slug) }}" oninput="updatePreview()" required>
                        @error('slug')<div class="sa-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="sa-label">Secteur</label>
                        <select name="sector" class="sa-input">
                            @foreach(['SaaS / Tech','Finance','Santé','Éducation','Retail','Autre'] as $s)
                            <option {{ old('sector',$tenant->sector)===$s?'selected':'' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="sa-field">
                    <label class="sa-label">Logo (vide = conserver l'actuel)</label>
                    @if($tenant->logo_path)
                    <div style="margin-bottom:8px;padding:8px;background:var(--surface-2);border-radius:8px;display:inline-block;">
                        <img src="{{ Storage::url($tenant->logo_path) }}" style="height:36px;object-fit:contain;border-radius:6px;">
                    </div>
                    @endif
                    <div class="sa-upload" onclick="document.getElementById('file-input').click()">
                        <div id="upload-text" style="font-size:12px;color:var(--text-muted);">Cliquer pour changer le logo</div>
                    </div>
                    <input type="file" id="file-input" name="logo" accept="image/*" style="display:none" onchange="handleFile(this)">
                </div>

                <div class="sa-field">
                    <label class="sa-label">Couleur principale *</label>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <input type="color" id="brand-color" name="brand_color"
                               value="{{ old('brand_color',$tenant->brand_color) }}" oninput="updatePreview()"
                               style="width:38px;height:34px;padding:2px;cursor:pointer;border:1.5px solid var(--border);border-radius:8px;background:var(--surface);">
                        <input type="text" id="brand-hex" class="sa-input" style="width:100px;font-family:monospace;"
                               value="{{ old('brand_color',$tenant->brand_color) }}" oninput="syncColor()">
                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                            @foreach(['#1a8fa5','#4f46e5','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#0f172a'] as $col)
                            <span class="sa-swatch {{ old('brand_color',$tenant->brand_color)===$col?'active':'' }}"
                                  style="background:{{ $col }};" onclick="setColor('{{ $col }}')"></span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sa-card" style="margin-bottom:16px;">
            <div class="sa-card-header"><div class="sa-card-title">Plan & Statut</div></div>
            <div class="sa-card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="sa-label">Plan</label>
                        <select name="plan" class="sa-input">
@foreach($plans as $p)
<option value="{{ $p->value }}" {{ old('plan',$tenant->plan?->value ?? '') === $p->value ? 'selected' : '' }}>{{ $p->label() }}</option>
@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sa-label">Statut</label>
                        <select name="status" class="sa-input">
@foreach($statuses as $s)
<option value="{{ $s->value }}" {{ old('status',$tenant->status?->value ?? '') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
@endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:10px;">
            <a href="{{ route('superadmin.tenants.index') }}" class="sa-btn sa-btn-ghost">Annuler</a>
            <button type="submit" class="sa-btn sa-btn-primary" style="flex:1;">Enregistrer les modifications</button>
        </div>
    </div>

    {{-- Aperçu --}}
    <div class="sa-card">
        <div class="sa-card-header"><div class="sa-card-title">Aperçu</div></div>
        <div style="padding:16px;">
            <div class="sa-preview-sidebar">
                <div class="sa-preview-topbar">
                    <div class="sa-preview-logo" id="prev-logo" style="background:{{ $tenant->brand_color }};">{{ $tenant->initials }}</div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#fff;" id="prev-name">{{ $tenant->name }}</div>
                        <div style="font-size:10px;color:rgba(255,255,255,.35);font-family:monospace;" id="prev-slug">{{ $tenant->domain }}</div>
                    </div>
                </div>
                <div class="sa-preview-item act">Tableau de bord</div>
                <div class="sa-preview-item">Liste du Personnel</div>
            </div>
            <div style="margin-top:12px;">
                <div class="sa-preview-login">
                    <div class="sa-preview-login-logo" id="prev-login-logo" style="background:{{ $tenant->brand_color }};">{{ $tenant->initials }}</div>
                    <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:2px;" id="prev-title">{{ $tenant->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-bottom:10px;">Connectez-vous à votre espace</div>
                    <div class="sa-fake-input"></div>
                    <div class="sa-fake-input"></div>
                    <div class="sa-fake-btn" id="prev-btn" style="background:{{ $tenant->brand_color }};">Se connecter</div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
function getInitials(name){ return name.trim().split(/\s+/).slice(0,2).map(w=>w[0]||'').join('').toUpperCase()||'?'; }
function updatePreview(){
    const name=document.getElementById('company-name').value||'App';
    const slug=document.getElementById('slug').value||'app';
    const color=document.getElementById('brand-color').value;
    const ini=getInitials(name);
    ['prev-logo','prev-login-logo'].forEach(id=>{document.getElementById(id).textContent=ini;document.getElementById(id).style.background=color;});
    document.getElementById('prev-name').textContent=name;
    document.getElementById('prev-slug').textContent=slug+'.tenantes.io';
    document.getElementById('prev-title').textContent=name;
    document.getElementById('prev-btn').style.background=color;
    document.getElementById('brand-hex').value=color;
}
function syncColor(){
    const hex=document.getElementById('brand-hex').value;
    if(/^#[0-9a-fA-F]{6}$/.test(hex)){document.getElementById('brand-color').value=hex;updatePreview();}
}
function setColor(c){
    document.getElementById('brand-color').value=c;document.getElementById('brand-hex').value=c;
    document.querySelectorAll('.sa-swatch').forEach(s=>s.classList.remove('active'));
    event.target.classList.add('active');updatePreview();
}
function handleFile(input){
    if(!input.files||!input.files[0])return;
    document.getElementById('upload-text').textContent=input.files[0].name+' ✓';
}
</script>
@endpush
