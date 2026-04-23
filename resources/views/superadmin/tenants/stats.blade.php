@extends('layouts.superadmin')
@section('title', 'Tenants')
@section('page-title', 'Gestion des tenants')

@section('page-header')
    <div class="sa-page-title">Gestion des tenants</div>
    <div class="sa-page-sub">{{ $counts['all'] }} tenant(s) sur la plateforme</div>
@endsection

@section('page-actions')
    <a href="{{ route('superadmin.tenants.create') }}" class="sa-btn sa-btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouveau tenant
    </a>
@endsection

@section('content')

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:20px;">
@foreach([
    ['Total',     $counts['all'],       'var(--text)'],
    ['Actifs',    $counts['active'],    'var(--success)'],
    ['Essai',     $counts['trial'],     'var(--info)'],
    ['Suspendus', $counts['suspended'], 'var(--warning)'],
    ['Inactifs',  $counts['inactive'],  'var(--text-muted)'],
] as [$lbl, $val, $col])
    <div class="sa-stat" style="padding:13px 15px;">
        <div class="sa-stat-val" style="font-size:22px;color:{{ $col }};">{{ $val }}</div>
        <div class="sa-stat-lbl">{{ $lbl }}</div>
    </div>
@endforeach
</div>

{{-- Toolbar --}}
<form method="GET" action="{{ route('superadmin.tenants.index') }}"
      style="display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap;">

    <div class="sa-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input type="text" name="search" placeholder="Rechercher…"
               value="{{ request('search') }}" oninput="this.form.submit()">
    </div>

    <div style="display:flex;gap:6px;flex-wrap:wrap;">
        @foreach([
            '' => 'Tous ('.$counts['all'].')',
            'active' => 'Actifs', 'trial' => 'Essai',
            'suspended' => 'Suspendus', 'inactive' => 'Inactifs',
        ] as $val => $lbl)
        <a href="{{ route('superadmin.tenants.index', array_merge(request()->except('status','page'), $val ? ['status'=>$val] : [])) }}"
           class="sa-pill {{ request('status','') === $val ? 'on' : '' }}">{{ $lbl }}</a>
        @endforeach
    </div>

</form>

{{-- Cards --}}
@if($tenants->isEmpty())
    <div class="sa-card" style="padding:60px;text-align:center;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-light)" stroke-width="1.5"
             style="margin:0 auto 14px;display:block;">
            <rect x="2" y="7" width="20" height="14" rx="2"/>
            <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
        </svg>
        <div style="font-size:14px;font-weight:600;color:var(--text-muted);">Aucun tenant trouvé</div>
        <a href="{{ route('superadmin.tenants.create') }}" class="sa-btn sa-btn-primary" style="margin-top:14px;display:inline-flex;">
            Créer le premier tenant
        </a>
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
    @foreach($tenants as $t)
    @php
        $sto = $t->storage_usage;
        $api = $t->api_usage;
        $stoC = $sto > 80 ? 'var(--danger)' : ($sto > 60 ? 'var(--warning)' : 'var(--success)');
        $apiC = $api > 80 ? 'var(--danger)' : ($api > 60 ? 'var(--warning)' : 'var(--success)');
$sc = match(($t->status?->value ?? 'inactive')) {
            'active'    => 'sa-badge-active',
            'suspended' => 'sa-badge-suspended',
            'trial'     => 'sa-badge-trial',
            default     => 'sa-badge-inactive',
        };
$pc = match(($t->plan?->value ?? 'starter')) {
            'enterprise' => 'sa-badge-ent',
            'pro'        => 'sa-badge-pro',
            default      => 'sa-badge-starter',
        };
    @endphp
    <div class="sa-tenant-card">
        {{-- Header --}}
        <div style="padding:14px 16px 11px;display:flex;align-items:flex-start;gap:12px;border-bottom:1px solid var(--border-light);">
            <div style="width:42px;height:42px;border-radius:12px;background:{{ $t->brand_color }};display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:#fff;flex-shrink:0;box-shadow:0 4px 12px {{ $t->brand_color }}44;">
                {{ $t->initials }}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700;font-size:14px;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $t->name }}</div>
                <div style="font-size:11px;color:var(--text-muted);font-family:monospace;margin-top:1px;">{{ $t->domain }}</div>
            </div>
            <div style="display:flex;gap:4px;flex-shrink:0;">
                <a href="{{ route('superadmin.tenants.edit', $t) }}"
                   class="sa-btn sa-btn-ghost sa-btn-sm" style="padding:4px 8px;" title="Modifier">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </a>
                <form method="POST" action="{{ route('superadmin.tenants.destroy', $t) }}"
                      onsubmit="return confirm('Supprimer {{ addslashes($t->name) }} ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm" style="padding:4px 8px;" title="Supprimer">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Body --}}
        <div style="padding:12px 16px;">
            {{-- Mini stats --}}
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:12px;">
                <div style="background:var(--surface-2);border-radius:8px;padding:8px 10px;">
                    <div style="font-size:16px;font-weight:800;color:var(--text);">{{ $t->users_count }}</div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:1px;font-weight:600;">Utilisateurs</div>
                </div>
                <div style="background:var(--surface-2);border-radius:8px;padding:8px 10px;">
                    <div style="font-size:16px;font-weight:800;color:{{ $stoC }};">{{ $sto }}%</div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:1px;font-weight:600;">Stockage</div>
                </div>
                <div style="background:var(--surface-2);border-radius:8px;padding:8px 10px;">
                    <div style="font-size:16px;font-weight:800;color:{{ $apiC }};">{{ $api }}%</div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:1px;font-weight:600;">API</div>
                </div>
            </div>

            {{-- Usage bars --}}
            <div style="margin-bottom:12px;">
                @foreach([['Stockage', $sto, $stoC], ['API', $api, $apiC]] as [$lbl2, $pct, $col2])
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
                    <span style="font-size:10px;color:var(--text-muted);width:52px;font-weight:600;">{{ $lbl2 }}</span>
                    <div class="sa-usage-bar"><div class="sa-usage-fill" style="width:{{ $pct }}%;background:{{ $col2 }};"></div></div>
                    <span style="font-size:10px;color:var(--text-muted);width:28px;text-align:right;">{{ $pct }}%</span>
                </div>
                @endforeach
            </div>

            {{-- Footer --}}
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                <span class="sa-badge {{ $sc }}">
                    <span style="width:5px;height:5px;border-radius:50%;background:currentColor;"></span>
{{ $t->status?->label() ?? 'Inconnu' }}
                </span>
{{ $t->plan?->label() ?? 'Starter' }}

@if(($t->status?->value ?? '') === 'active')
                    <form method="POST" action="{{ route('superadmin.tenants.suspend', $t) }}" style="margin-left:auto;">
                        @csrf
                        <button type="submit" style="font-size:11px;font-weight:600;color:var(--warning);background:none;border:none;cursor:pointer;font-family:inherit;">Suspendre</button>
                    </form>
@elseif(($t->status?->value ?? '') === 'suspended')
                    <form method="POST" action="{{ route('superadmin.tenants.reactivate', $t) }}" style="margin-left:auto;">
                        @csrf
                        <button type="submit" style="font-size:11px;font-weight:600;color:var(--success);background:none;border:none;cursor:pointer;font-family:inherit;">Réactiver</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @endforeach
    </div>

    <div style="margin-top:20px;">{{ $tenants->links() }}</div>
@endif

@endsection
