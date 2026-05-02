@extends('layouts.superadmin')

@section('title', 'Clients')

@section('content')
<div class="sa-main">
    <div class="sa-main__head">
        <h1 class="sa-main__title sa-main__title--with-icon">
            <i class="sa-main__title-icon mdi mdi-account-group-outline"></i>
            Clients / Tenants ({{ $clients->total() }})
        </h1>
    </div>

    <div class="sa-main__body">
        <div class="sa-card">
            <div class="sa-card__body">
                <table class="sa-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Slug</th>
                            <th>Sector</th>
                            <th>Users</th>
                            <th>Créé le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                        <tr>
                            <td>
                                <div class="sa-table__avatar" style="background-color: {{ $client->brand_color ?? '#4f46e5' }};">
                                    {{ $client->initials }}
                                </div>
                                <div>
                                    <div class="sa-table__name">{{ $client->name }}</div>
                                    <div class="sa-table__meta">{{ $client->admin?->name ?? 'No admin' }}</div>
                                </div>
                            </td>
                            <td>
                                <code>{{ $client->slug }}</code>
                                <div class="sa-table__meta">{{ $client->domain }}</div>
                            </td>
                            <td>{{ $client->sector ?? 'N/A' }}</td>
                            <td>{{ $client->users_count }}</td>
                            <td>{{ $client->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-muted">
                                Aucun client trouvé.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="sa-pagination">
                    {{ $clients->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection