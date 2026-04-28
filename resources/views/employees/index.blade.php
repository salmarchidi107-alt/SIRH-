@extends('layouts.app')

@section('title', 'Liste du Personnel')
@section('page-title', 'Liste du Personnel')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Employés</h1>
        <p>{{ $employees->total() }} collaborateurs enregistrés</p>
    </div>
@if(in_array(auth()->user()->role ?? '', ['admin', 'rh']))
    <div style="display:flex;gap:8px;align-items:center;">
        <a href="{{ route('employees.create') }}" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nouvel Employé
        </a>

        {{-- PDF global ou filtré --}}
        <a href="{{ route('employees.export-pdf', request()->query()) }}" 
           class="btn btn-success " 
           title="Exporter en PDF">
            📄 PDF
        </a>

        {{-- PDF par département si un département est sélectionné --}}
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
        @if(in_array(auth()->user()->role ?? '', ['admin', 'rh']))
       
        @endif
        <a href="{{ route('employees.index', ['filter' => 'all']) }}"
           class="btn {{ $filter == 'all' ? 'btn-primary' : 'btn-outline' }}">
            Tous
        </a>
        <a href="{{ route('employees.index', ['filter' => 'active']) }}"
           class="btn {{ $filter == 'active' ? 'btn-primary' : 'btn-outline' }}">
            Actifs
        </a>
    </div>
    <form method="GET" action="{{ route('employees.index') }}" class="filters-bar" style="margin:0;flex-wrap:wrap;gap:12px">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <div class="search-bar">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" placeholder="Rechercher un employé..." value="{{ request('search') }}">
        </div>
        <select name="department" class="filter-select" onchange="this.form.submit()">
            <option value="">Tous les services</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['search','department']))
            <a href="{{ route('employees.index', ['filter' => $filter]) }}" class="btn btn-ghost btn-sm">✕ Réinitialiser</a>
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
                <tr>
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
                    <td colspan="7" style="text-align:center;padding:48px;color:var(--text-muted)">
                        <div style="font-size:2.5rem;margin-bottom:12px">👥</div>
                        <div style="font-weight:600;margin-bottom:4px">Aucun employé trouvé</div>
                        <div style="font-size:0.875rem">Modifiez vos critères de recherche ou ajoutez un employé</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="employees-pagination" style="padding:0 16px;display:flex;gap:12px;align-items:center;justify-content:center;">
        <div id="loading-spinner" class="spinner" style="display:none;">
            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" stroke-linecap="round"/>
            </svg>
            Chargement...
        </div>
        <button id="load-more-btn" class="btn btn-outline" style="display:{{ $employees->hasMorePages() ? 'block' : 'none' }};">
            Charger plus (Page {{ $employees->currentPage() + 1 ?? 2 }})
        </button>
        <span id="results-count" style="color:var(--text-muted);font-size:0.875rem;">
            {{ $employees->total() }} résultats
        </span>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
let currentPage = {{ $employees->currentPage() }};
let totalResults = {{ $employees->total() }};
let isLoading = false;
let searchParams = new URLSearchParams(window.location.search);

document.addEventListener('DOMContentLoaded', function() {
    // Drag & drop reordering
    const tbody = document.getElementById('employees-tbody');
    new Sortable(tbody, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        onEnd: function (evt) {
            const order = Array.from(tbody.querySelectorAll('tr')).map(tr => tr.dataset.employeeId || tr.querySelector('a')?.href.split('/').pop());
            fetch('{{ route("employees.reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ order: order })
            }).then(response => response.json())
              .then(data => {
                  if (data.success) console.log('Ordre sauvegardé');
              }).catch(err => console.error('Reorder error', err));
        }
    });
    
    // Set employee ID data attr (for loaded rows)
    tbody.querySelectorAll('tr').forEach(tr => {
        const link = tr.querySelector('a[href*="/employees/"]');
        if (link) tr.dataset.employeeId = link.href.split('/').pop();
    });
    // Handle search/filter form
    const filterForm = document.querySelector('form[action="{{ route('employees.index') }}"]');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            searchParams = new URLSearchParams(formData);
            ajaxEmployees(1, false);
        });

        // Auto-submit on select change
        filterForm.querySelectorAll('select, input[name="search"]').forEach(el => {
            el.addEventListener('change', () => filterForm.dispatchEvent(new Event('submit')));
        });
    }

    // Load more button
    document.getElementById('load-more-btn').addEventListener('click', function() {
        ajaxEmployees(currentPage + 1, true);
    });
});

async function ajaxEmployees(page = 1, append = false) {
    if (isLoading) return;
    isLoading = true;

    const spinner = document.getElementById('loading-spinner');
    const loadMoreBtn = document.getElementById('load-more-btn');
    spinner.style.display = 'flex';

    if (!append) {
        loadMoreBtn.style.display = 'none';
    }
    loadMoreBtn.disabled = true;
    loadMoreBtn.textContent = 'Chargement...';

    try {
        const url = `/employees/ajax?page=${page}&${searchParams}`;
        const response = await fetch(url);
        const data = await response.json();

        if (!append) {
            // Replace table content
            const tbody = document.getElementById('employees-tbody');
            tbody.innerHTML = '';
            data.employees.forEach(emp => {
                tbody.appendChild(buildEmployeeRow(emp));
            });
            currentPage = data.pagination.current_page;
            totalResults = data.pagination.total;
        } else {
            // Append new rows
            data.employees.forEach(emp => {
                document.getElementById('employees-tbody').appendChild(buildEmployeeRow(emp));
            });
            currentPage = data.pagination.current_page;
        }

        // Update UI
        document.getElementById('results-count').textContent = `${totalResults} résultats`;
        loadMoreBtn.style.display = data.pagination.has_more ? 'block' : 'none';
        loadMoreBtn.textContent = `Charger plus (Page ${data.pagination.current_page + 1})`;

        // Update URL without reload
        fetch(newUrl, {
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    }
})
       const newUrl = `{{ route('employees.ajax') }}?${searchParams.toString()}`;
        if (page === 1) history.replaceState({}, '', newUrl);
    } catch (error) {
        console.error('Erreur AJAX:', error);
        document.getElementById('loading-spinner').textContent = 'Erreur de chargement';
    } finally {
        isLoading = false;
        spinner.style.display = 'none';
        loadMoreBtn.disabled = false;
    }
}

function buildEmployeeRow(employee) {
    const tr = document.createElement('tr');
    const isAdmin = {{ auth()->user()->isAdminOrRh() ?? false }};
    tr.innerHTML = `
        <td>
            <span style="font-family:monospace;font-size:0.8rem;background:var(--surface-2);padding:2px 8px;border-radius:4px;border:1px solid var(--border)">
                ${employee.matricule || 'N/A'}
            </span>
        </td>
        <td>
            <div class="table-employee">
                <div class="table-avatar">
                    ${employee.photo ? `<img src="${employee.photo_url}" alt="">` : 
                      `<span style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;font-weight:700;font-size:0.75rem;padding:0.5rem;border-radius:50%;width:2rem;height:2rem;display:flex;align-items:center;justify-content:center">${(employee.full_name?.[0] || 'E') + (employee.full_name?.[1] || '')?.toUpperCase()}</span>`}
                </div>
                <div>
                    <div class="table-name">${employee.full_name}</div>
                </div>
            </div>
        </td>
        <td><span class="badge badge-neutral">${employee.department || 'N/A'}</span></td>
        <td class="text-sm">${employee.position || ''}</td>
        <td>
            <span class="badge ${employee.contract_type == 'CDI' ? 'badge-success' : (employee.contract_type == 'CDD' ? 'badge-warning' : 'badge-neutral')}">
                ${employee.contract_type || 'N/A'}
            </span>
        </td>
        <td>
            <span class="badge badge-${employee.status_color}">${employee.status_label}</span>
        </td>
        <td class="text-sm text-muted">${employee.hire_date || ''}</td>
        <td>
            <div style="display:flex;gap:6px">
                <a href="/employees/${employee.id}" class="btn btn-ghost btn-sm btn-icon" title="Voir">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                </a>
                ${isAdmin ? `
                <a href="/employees/${employee.id}/edit" class="btn btn-outline btn-sm btn-icon" title="Modifier">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </a>
                <form action="/employees/${employee.id}" method="POST" style="display:inline" onsubmit="return confirm('Supprimer cet employé ?')">
                    <input type="hidden" name="_token" value="${employee.csrf_token}">
                    <input type="hidden" name="_method" value="${employee._method}">
                    <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Supprimer">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                        </svg>
                    </button>
                </form>` : ''}
            </div>
        </td>
    `;
    return tr;
}
</script>
@endpush
@endsection
