@extends('layouts.superadmin')

@section('title', $tenant->name)

@section('content')
<div class="sa-main">
    <div class="sa-main__head">
        <h1 class="sa-main__title">
            {{ $tenant->name }}
            <small class="sa-main__subtitle">({{ $tenant->slug }})</small>
        </h1>
    </div>

    <div class="sa-main__body">
        <div class="sa-card">
            <div class="sa-card__body">
                <div class="sa-row -gap-4">
                    <div class="sa-col">
                        <div class="sa-profile-header" style="background-color: {{ $tenant->brand_color ?? '#4f46e5' }};">
                            <div class="sa-profile-header__avatar">
                                {{ $tenant->initials }}
                            </div>
                            <div class="sa-profile-header__content">
                                <h3 class="sa-profile-header__name">{{ $tenant->name }}</h3>
                                <div class="sa-profile-header__meta">
                                    <span class="sa-badge sa-badge--{{ $tenant->status->badgeClass() }}">
                                        {{ $tenant->status->label() }}
                                    </span>
                                    <span class="sa-badge sa-badge--{{ $tenant->plan->badgeClass() }}">
                                        {{ $tenant->plan->label() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sa-col--auto">
                        <a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="sa-btn sa-btn--primary">
                            <i class="sa-btn__icon mdi mdi-pencil"></i>
                            Éditer
                        </a>
                    </div>
                </div>

                <div class="sa-tabs" data-sa-tabs="tenant-details">
                    <div class="sa-tabs__nav">
                        <button class="sa-tabs__nav-item is-active" data-sa-tab="overview">Aperçu</button>
                        <button class="sa-tabs__nav-item" data-sa-tab="users">Utilisateurs ({{ $tenant->users_count }})</button>
                        <button class="sa-tabs__nav-item" data-sa-tab="stats">Stats</button>
                    </div>

                    <div class="sa-tabs__content">
                        <div class="sa-tabs__pane is-active" id="overview">
                            <div class="sa-row -gap-4 -mt-6">
                                <div class="sa-col">
                                    <div class="sa-card sa-card--summary">
                                        <div class="sa-card__body">
                                            <div class="sa-stat">
                                                <div class="sa-stat__label">Domaine</div>
                                                <div class="sa-stat__value">
                                                    <code>{{ $tenant->domain }}</code>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="sa-col">
                                    <div class="sa-card sa-card--summary">
                                        <div class="sa-card__body">
                                            <div class="sa-stat">
                                                <div class="sa-stat__label">Secteur</div>
                                                <div class="sa-stat__value">{{ $tenant->sector ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="sa-col">
                                    <div class="sa-card sa-card--summary">
                                        <div class="sa-card__body">
                                            <div class="sa-stat">
                                                <div class="sa-stat__label">Région</div>
                                                <div class="sa-stat__value">{{ $tenant->region }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sa-tabs__pane" id="users">
                            @if($tenant->users->count())
                            <div class="sa-table-container">
                                <table class="sa-table">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th>Rôle</th>
                                            <th>Créé le</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tenant->users as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                <span class="sa-badge sa-badge--{{ strtolower($user->role) }}">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </td>
                                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="sa-empty-state">
                                <i class="sa-empty-state__icon mdi mdi-account-outline"></i>
                                <h3>Aucun utilisateur</h3>
                                <p>Ajoutez le premier administrateur.</p>
                            </div>
                            @endif
                        </div>

                        <div class="sa-tabs__pane" id="stats">
                            <div class="sa-row -gap-4">
                                <div class="sa-col">
                                    <div class="sa-card sa-card--metric">
                                        <div class="sa-card__body">
                                            <div class="sa-metric">
                                                <div class="sa-metric__label">Stockage</div>
                                                <div class="sa-metric__value">{{ $tenant->storage_usage }} MB</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="sa-col">
                                    <div class="sa-card sa-card--metric">
                                        <div class="sa-card__body">
                                            <div class="sa-metric">
                                                <div class="sa-metric__label">API Calls</div>
                                                <div class="sa-metric__value">{{ $tenant->api_usage }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = new SimpleTabs(document.querySelector('[data-sa-tabs="tenant-details"]'));
});
</script>
@endsection

