@extends('layouts.superadmin')

@section('title', 'Alertes de fuite de données')
@section('page-title', 'Alertes de fuite de données')

@section('page-header')
    <div class="sa-page-title">Alertes de fuite de données</div>
    <div class="sa-page-sub">{{ $alerts->total() }} alerte(s) enregistrée(s)</div>
@endsection

@section('content')

<div class="sa-card" style="margin-bottom:18px;">
    <div class="sa-card-body">
        <form method="GET" action="{{ route('superadmin.data-leak-alerts.index') }}"
              style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">

            <div>
                <label class="sa-label">Tenant</label>
                <select name="tenant" class="sa-input" style="min-width:180px;">
                    <option value="">Tous</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ request('tenant') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="sa-label">Module</label>
                <input type="text" name="module" class="sa-input" style="width:150px;"
                       value="{{ request('module') }}" placeholder="ex: employees">
            </div>

            <div>
                <label class="sa-label">Utilisateur</label>
                <input type="text" name="user" class="sa-input" style="width:200px;"
                       value="{{ request('user') }}" placeholder="email...">
            </div>

            <div>
                <label class="sa-label">Du</label>
                <input type="date" name="date_from" class="sa-input" value="{{ request('date_from') }}">
            </div>

            <div>
                <label class="sa-label">Au</label>
                <input type="date" name="date_to" class="sa-input" value="{{ request('date_to') }}">
            </div>

            <button type="submit" class="sa-btn sa-btn-primary">Filtrer</button>

            @if(request()->hasAny(['tenant','module','user','date_from','date_to']))
            <a href="{{ route('superadmin.data-leak-alerts.index') }}" class="sa-btn sa-btn-ghost">✕ Réinitialiser</a>
            @endif
        </form>
    </div>
</div>

<div class="sa-card">
    <div class="table-container" style="overflow-x:auto;">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Tenant attendu</th>
                    <th>Tenant fuité</th>
                    <th>Module</th>
                    <th>Route</th>
                    <th>Contrôleur / Méthode</th>
                    <th>Lignes</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $alert)
                <tr>
                    <td class="text-sm text-muted">{{ $alert->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div style="font-weight:600;">{{ $alert->user_name ?? '—' }}</div>
                        <div class="text-sm text-muted">{{ $alert->user_email ?? '' }}</div>
                    </td>
                    <td>
                        <span class="sa-tag">{{ $alert->expected_tenant_name ?? ('#'.$alert->expected_tenant_id) }}</span>
                    </td>
                    <td>
                        <span class="sa-badge sa-badge-suspended">{{ $alert->leaked_tenant_name ?? ('#'.$alert->leaked_tenant_id) }}</span>
                    </td>
                    <td>{{ $alert->module ?? '—' }}</td>
                    <td class="text-sm" style="font-family:monospace;">{{ $alert->route_name ?? '—' }}</td>
                    <td class="text-sm" style="font-family:monospace;">{{ $alert->controller_action ?? '—' }}</td>
                    <td style="text-align:center;">
                        <span class="sa-badge sa-badge-suspended">{{ $alert->rows_count }}</span>
                    </td>
                    <td class="text-sm text-muted">{{ $alert->ip_address ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);">
                        Aucune alerte de fuite enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($alerts->hasPages())
    <div style="padding:16px;display:flex;justify-content:center;">
        {{ $alerts->links() }}
    </div>
    @endif
</div>

@endsection
