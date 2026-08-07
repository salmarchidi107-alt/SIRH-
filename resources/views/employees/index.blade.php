@extends('layouts.app')

@section('title', 'Liste du Personnel')
@section('page-title', 'Liste du Personnel')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Employés</h1>
        <p>
            @if(method_exists($employees, 'total'))
                {{ $employees->total() }} collaborateurs enregistrés
            @else
                {{ $employees->count() }} collaborateurs enregistrés
            @endif
        </p>
    </div>
@if(in_array(auth()->user()->role ?? '', ['admin', 'rh']))
    <div style="display:flex;gap:8px;align-items:center;">
        <a href="{{ route('employees.create') }}" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nouvel Employé
        </a>
        <a href="{{ route('employees.export-pdf', request()->query()) }}"
           class="btn btn-success"
           title="Exporter en PDF">
             PDF
        </a>
        @if(request('department'))
            <a href="{{ route('employees.export-pdf-dept', request('department')) }}"
               class="btn btn-outline"
               title="PDF — {{ request('department') }} uniquement">
                📄 {{ request('department') }}
            </a>
        @endif
    </div>
@endif
</div>

<!-- Filter Buttons: Tous / Actifs -->
<div class="filters-bar">
    <div style="display:flex;gap:8px;flex-direction:row-reverse">
        <a href="{{ route('employees.index', ['filter' => 'inactive']) }}"
           class="btn {{ ($filter ?? 'all') == 'inactive' ? 'btn-primary' : 'btn-outline' }}">
            Inactifs
        </a>
        <a href="{{ route('employees.index', ['filter' => 'active']) }}"
           class="btn {{ ($filter ?? 'all') == 'active' ? 'btn-primary' : 'btn-outline' }}">
            Actifs
        </a>
        <a href="{{ route('employees.index', ['filter' => 'all']) }}"
           class="btn {{ ($filter ?? 'all') == 'all' ? 'btn-primary' : 'btn-outline' }}">
            Tous
        </a>
    </div>

    <form method="GET" action="{{ route('employees.index') }}" class="filters-bar" style="margin:0;flex-wrap:wrap;gap:12px">
        <input type="hidden" name="filter" value="{{ $filter ?? 'all' }}">
        <div class="search-bar">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" placeholder="Rechercher un employé..." value="{{ request('search') }}">
        </div>
        <select name="department" class="filter-select" onchange="this.form.submit()">
            <option value="">Departements</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['search','department']))
            <a href="{{ route('employees.index', ['filter' => $filter ?? 'all']) }}" class="btn btn-ghost btn-sm">✕ Réinitialiser</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Collaborateur</th>
                    <th>Service</th>
                    <th>Fonction</th>
                    <th>Contrat</th>
                    <th>Statut</th>
                    <th>Entrée</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="employees-tbody">
                @forelse($employees as $employee)
                <tr data-employee-id="{{ $employee->id }}" data-row-tenant-id="{{ $employee->tenant_id }}">
                    <td>
                        <span style="font-family:monospace;font-size:0.8rem;background:var(--surface-2);padding:2px 8px;border-radius:4px;border:1px solid var(--border)">
                            {{ $employee->matricule }}
                        </span>
                    </td>
                    <td>
                        <div class="table-employee">
                            <div class="table-avatar">
                                @if($employee->photo)
                                    <img src="{{ $employee->photo_url }}" alt="">
                                @else
                                    {{ strtoupper(substr($employee->first_name,0,1).substr($employee->last_name,0,1)) }}
                                @endif
                            </div>
                            <div>
                                <div class="table-name">{{ $employee->full_name }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-neutral">{{ $employee->department ?? 'N/A' }}</span>
                    </td>
                    <td class="text-sm">{{ $employee->position }}</td>
                    <td>
                        <span class="badge {{ $employee->contract_type == 'CDI' ? 'badge-success' : ($employee->contract_type == 'CDD' ? 'badge-warning' : 'badge-neutral') }}">
                            {{ $employee->contract_type }}
                        </span>
                    </td>
                    <td>
                        {{ $employee->status_label ?? $employee->status }}
                    </td>
                    <td class="text-sm text-muted">{{ $employee->hire_date->format('d/m/Y') }}</td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-ghost btn-sm btn-icon" title="Voir">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            @if(in_array(auth()->user()->role ?? '', ['admin', 'rh']))
                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline btn-sm btn-icon" title="Modifier">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            <form action="{{ route('employees.destroy', $employee) }}" method="POST"
                                onsubmit="return confirm('Supprimer cet employé ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Supprimer">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:48px;color:var(--text-muted)">
                        <div style="font-weight:600;margin-bottom:4px">Aucun employé trouvé</div>
                        <div style="font-size:0.875rem">Modifiez vos critères de recherche ou ajoutez un employé</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination / Load more --}}
    <div id="employees-pagination" style="padding:16px;display:flex;gap:12px;align-items:center;justify-content:center;">
        <div id="loading-spinner" class="spinner" style="display:none;">
            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" stroke-linecap="round"/>
            </svg>
            Chargement...
        </div>

        @if(method_exists($employees, 'hasMorePages'))
        <button id="load-more-btn" class="btn btn-outline"
                style="display:{{ $employees->hasMorePages() ? 'block' : 'none' }};">
            Charger plus (Page {{ ($employees->currentPage() + 1) }})
        </button>
        @endif

        <span id="results-count" style="color:var(--text-muted);font-size:0.875rem;">
            @if(method_exists($employees, 'total'))
                {{ $employees->total() }} résultats
            @else
                {{ $employees->count() }} résultats
            @endif
        </span>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
@if(method_exists($employees, 'currentPage'))
var currentPage  = {{ $employees->currentPage() }};
var totalResults = {{ $employees->total() }};
var hasMore      = {{ $employees->hasMorePages() ? 'true' : 'false' }};
@else
var currentPage  = 1;
var totalResults = {{ $employees->count() }};
var hasMore      = false;
@endif

var isLoading    = false;
var searchParams = new URLSearchParams(window.location.search);
var isAdmin      = @json(in_array(auth()->user()->role ?? '', ['admin', 'rh']));

document.addEventListener('DOMContentLoaded', function () {

    // ── Drag & drop reordering ─────────────────────────────────
    var tbody = document.getElementById('employees-tbody');
    new Sortable(tbody, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        onEnd: function () {
            var order = Array.from(tbody.querySelectorAll('tr'))
                .map(function(tr) { return tr.dataset.employeeId; })
                .filter(Boolean);
            fetch('{{ route("employees.reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ order: order }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) { if (data.success) console.log('Ordre sauvegardé'); })
            .catch(function(err) { console.error('Reorder error', err); });
        },
    });

    // ── Formulaire de recherche / filtre ──────────────────────
    var filterForm = document.querySelector('form[action="{{ route('employees.index') }}"]');
    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            searchParams = new URLSearchParams(new FormData(this));
            ajaxEmployees(1, false);
        });
        filterForm.querySelectorAll('select').forEach(function(el) {
            el.addEventListener('change', function() {
                filterForm.dispatchEvent(new Event('submit'));
            });
        });
        // Recherche en temps réel sur le champ texte
        var searchInput = filterForm.querySelector('input[name="search"]');
        if (searchInput) {
            var searchTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    searchParams = new URLSearchParams(new FormData(filterForm));
                    ajaxEmployees(1, false);
                }, 350);
            });
        }
    }

    // ── Bouton "Charger plus" ─────────────────────────────────
    var loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function () {
            ajaxEmployees(currentPage + 1, true);
        });
    }
});

function ajaxEmployees(page, append) {
    if (isLoading) return;
    isLoading = true;

    var spinner    = document.getElementById('loading-spinner');
    var loadMoreBtn = document.getElementById('load-more-btn');

    spinner.style.display = 'flex';
    if (loadMoreBtn) {
        loadMoreBtn.disabled    = true;
        loadMoreBtn.textContent = 'Chargement…';
    }

    fetch('/employees/ajax?page=' + page + '&' + searchParams.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        var tbody = document.getElementById('employees-tbody');

        if (!append) {
            tbody.innerHTML = '';
        }

        if (data.employees.length === 0 && !append) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:48px;color:var(--text-muted)">'
                + '<div style="font-size:2.5rem;margin-bottom:12px">👥</div>'
                + '<div style="font-weight:600;margin-bottom:4px">Aucun employé trouvé</div>'
                + '<div style="font-size:0.875rem">Modifiez vos critères de recherche</div>'
                + '</td></tr>';
        } else {
            data.employees.forEach(function(emp) {
                tbody.appendChild(buildEmployeeRow(emp));
            });
        }

        currentPage  = data.pagination.current_page;
        totalResults = data.pagination.total;
        hasMore      = data.pagination.has_more;

        document.getElementById('results-count').textContent = totalResults + ' résultats';

        if (loadMoreBtn) {
            loadMoreBtn.style.display = hasMore ? 'block' : 'none';
            loadMoreBtn.textContent   = 'Charger plus (Page ' + (currentPage + 1) + ')';
        }

        if (page === 1) {
            history.replaceState({}, '', '{{ route('employees.index') }}?' + searchParams.toString());
        }
    })
    .catch(function(error) {
        console.error('Erreur AJAX:', error);
    })
    .finally(function() {
        isLoading             = false;
        spinner.style.display = 'none';
        if (loadMoreBtn) loadMoreBtn.disabled = false;
    });
}

function buildEmployeeRow(emp) {
    var tr = document.createElement('tr');
   tr.dataset.employeeId  = emp.id;
   tr.dataset.rowTenantId = emp.tenant_id;   // dépend de la modif EmployeeService ci-dessus

    // ── Avatar : photo si disponible, sinon initiales ────────
    // On utilise photo_url (renvoyé par ajaxIndex) pour l'URL réelle
    var avatarHtml;
    if (emp.photo_url) {
        avatarHtml = '<img src="' + emp.photo_url + '" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
    } else {
        // Initiales : première lettre du prénom + première lettre du nom
        var parts  = (emp.full_name || '').trim().split(' ');
        var init1  = (parts[0] || 'E').charAt(0).toUpperCase();
        var init2  = (parts[1] || '').charAt(0).toUpperCase();
        avatarHtml = init1 + init2;
    }

    // ── Badge contrat ────────────────────────────────────────
    var contractClass = emp.contract_type === 'CDI' ? 'badge-success'
                      : emp.contract_type === 'CDD' ? 'badge-warning'
                      : 'badge-neutral';

    // ── Boutons admin ────────────────────────────────────────
    var adminBtns = '';
    if (isAdmin) {
        adminBtns = ''
            + '<a href="/employees/' + emp.id + '/edit" class="btn btn-outline btn-sm btn-icon" title="Modifier">'
            + '<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">'
            + '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>'
            + '<path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>'
            + '</svg></a>'
            + '<form action="/employees/' + emp.id + '" method="POST" style="display:inline"'
            + ' onsubmit="return confirm(\'Supprimer cet employé ?\')">'
            + '<input type="hidden" name="_token" value="' + document.querySelector('meta[name=csrf-token]').content + '">'
            + '<input type="hidden" name="_method" value="DELETE">'
            + '<button type="submit" class="btn btn-danger btn-sm btn-icon" title="Supprimer">'
            + '<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">'
            + '<polyline points="3 6 5 6 21 6"/>'
            + '<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>'
            + '</svg></button></form>';
    }

    tr.innerHTML = ''
        + '<td>'
        +   '<span style="font-family:monospace;font-size:0.8rem;background:var(--surface-2);padding:2px 8px;border-radius:4px;border:1px solid var(--border)">'
        +     (emp.matricule || 'N/A')
        +   '</span>'
        + '</td>'
        + '<td>'
        +   '<div class="table-employee">'
        +     '<div class="table-avatar">' + avatarHtml + '</div>'
        +     '<div><div class="table-name">' + (emp.full_name || '') + '</div></div>'
        +   '</div>'
        + '</td>'
        + '<td><span class="badge badge-neutral">' + (emp.department || 'N/A') + '</span></td>'
        + '<td class="text-sm">' + (emp.position || '') + '</td>'
        + '<td><span class="badge ' + contractClass + '">' + (emp.contract_type || 'N/A') + '</span></td>'
        + '<td><span class="badge badge-' + (emp.status_color || 'neutral') + '">' + (emp.status_label || emp.status || '') + '</span></td>'
        + '<td class="text-sm text-muted">' + (emp.hire_date || '') + '</td>'
        + '<td>'
        +   '<div style="display:flex;gap:6px">'
        +     '<a href="/employees/' + emp.id + '" class="btn btn-ghost btn-sm btn-icon" title="Voir">'
        +       '<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">'
        +         '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'
        +       '</svg>'
        +     '</a>'
        +     adminBtns
        +   '</div>'
        + '</td>';

    return tr;
}
</script>
@endpush
@endsection
