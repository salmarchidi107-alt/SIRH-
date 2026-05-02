<?php $__env->startSection('title', 'Liste du Personnel'); ?>
<?php $__env->startSection('page-title', 'Liste du Personnel'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Employés</h1>
        <p>
            <?php if(method_exists($employees, 'total')): ?>
                <?php echo e($employees->total()); ?> collaborateurs enregistrés
            <?php else: ?>
                <?php echo e($employees->count()); ?> collaborateurs enregistrés
            <?php endif; ?>
        </p>
    </div>
<?php if(in_array(auth()->user()->role ?? '', ['admin', 'rh'])): ?>
    <div style="display:flex;gap:8px;align-items:center;">
        <a href="<?php echo e(route('employees.create')); ?>" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nouvel Employé
        </a>

        
        <a href="<?php echo e(route('employees.export-pdf', request()->query())); ?>"
           class="btn btn-success"
           title="Exporter en PDF">
            📄 PDF
        </a>

        
        <?php if(request('department')): ?>
            <a href="<?php echo e(route('employees.export-pdf-dept', request('department'))); ?>"
               class="btn btn-outline"
               title="PDF — <?php echo e(request('department')); ?> uniquement">
                📄 <?php echo e(request('department')); ?>

            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
</div>

<!-- Filter Buttons: Tous / Actifs -->
<div class="filters-bar">

    <div style="display:flex;gap:8px;flex-direction:row-reverse">
        <a href="<?php echo e(route('employees.index', ['filter' => 'all'])); ?>"
           class="btn <?php echo e(($filter ?? 'all') == 'all' ? 'btn-primary' : 'btn-outline'); ?>">
            Tous
        </a>
        <a href="<?php echo e(route('employees.index', ['filter' => 'active'])); ?>"
           class="btn <?php echo e(($filter ?? 'all') == 'active' ? 'btn-primary' : 'btn-outline'); ?>">
            Actifs
        </a>
    </div>

    <form method="GET" action="<?php echo e(route('employees.index')); ?>" class="filters-bar" style="margin:0;flex-wrap:wrap;gap:12px">
        <input type="hidden" name="filter" value="<?php echo e($filter ?? 'all'); ?>">
        <div class="search-bar">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" placeholder="Rechercher un employé..." value="<?php echo e(request('search')); ?>">
        </div>
        <select name="department" class="filter-select" onchange="this.form.submit()">
            <option value="">Tous les services</option>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($dept); ?>" <?php echo e(request('department') == $dept ? 'selected' : ''); ?>><?php echo e($dept); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php if(request()->hasAny(['search','department'])): ?>
            <a href="<?php echo e(route('employees.index', ['filter' => $filter ?? 'all'])); ?>" class="btn btn-ghost btn-sm">✕ Réinitialiser</a>
        <?php endif; ?>
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
                <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <span style="font-family:monospace;font-size:0.8rem;background:var(--surface-2);padding:2px 8px;border-radius:4px;border:1px solid var(--border)">
                            <?php echo e($employee->matricule); ?>

                        </span>
                    </td>
                    <td>
                        <div class="table-employee">
                            <div class="table-avatar">
                                <?php if($employee->photo): ?>
                                    <img src="<?php echo e($employee->photo_url); ?>" alt="">
                                <?php else: ?>
                                    <?php echo e(strtoupper(substr($employee->first_name,0,1).substr($employee->last_name,0,1))); ?>

                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="table-name"><?php echo e($employee->full_name); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-neutral"><?php echo e($employee->department ?? 'N/A'); ?></span>
                    </td>
                    <td class="text-sm"><?php echo e($employee->position); ?></td>
                    <td>
                        <span class="badge <?php echo e($employee->contract_type == 'CDI' ? 'badge-success' : ($employee->contract_type == 'CDD' ? 'badge-warning' : 'badge-neutral')); ?>">
                            <?php echo e($employee->contract_type); ?>

                        </span>
                    </td>
                    <td>
                        <?php echo e($employee->status_label ?? $employee->status); ?>

                    </td>
                    <td class="text-sm text-muted"><?php echo e($employee->hire_date->format('d/m/Y')); ?></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="<?php echo e(route('employees.show', $employee)); ?>" class="btn btn-ghost btn-sm btn-icon" title="Voir">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            <?php if(in_array(auth()->user()->role ?? '', ['admin', 'rh'])): ?>
                            <a href="<?php echo e(route('employees.edit', $employee)); ?>" class="btn btn-outline btn-sm btn-icon" title="Modifier">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            <form action="<?php echo e(route('employees.destroy', $employee)); ?>" method="POST"
                                onsubmit="return confirm('Supprimer cet employé ?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Supprimer">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" style="text-align:center;padding:48px;color:var(--text-muted)">
                        <div style="font-size:2.5rem;margin-bottom:12px">👥</div>
                        <div style="font-weight:600;margin-bottom:4px">Aucun employé trouvé</div>
                        <div style="font-size:0.875rem">Modifiez vos critères de recherche ou ajoutez un employé</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div id="employees-pagination" style="padding:16px;display:flex;gap:12px;align-items:center;justify-content:center;">
        <div id="loading-spinner" class="spinner" style="display:none;">
            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" stroke-linecap="round"/>
            </svg>
            Chargement...
        </div>

        <?php if(method_exists($employees, 'hasMorePages')): ?>
        <button id="load-more-btn" class="btn btn-outline"
                style="display:<?php echo e($employees->hasMorePages() ? 'block' : 'none'); ?>;">
            Charger plus (Page <?php echo e(($employees->currentPage() + 1)); ?>)
        </button>
        <?php endif; ?>

        <span id="results-count" style="color:var(--text-muted);font-size:0.875rem;">
            <?php if(method_exists($employees, 'total')): ?>
                <?php echo e($employees->total()); ?> résultats
            <?php else: ?>
                <?php echo e($employees->count()); ?> résultats
            <?php endif; ?>
        </span>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
<?php if(method_exists($employees, 'currentPage')): ?>
let currentPage  = <?php echo e($employees->currentPage()); ?>;
let totalResults = <?php echo e($employees->total()); ?>;
let hasMore      = <?php echo e($employees->hasMorePages() ? 'true' : 'false'); ?>;
<?php else: ?>
let currentPage  = 1;
let totalResults = <?php echo e($employees->count()); ?>;
let hasMore      = false;
<?php endif; ?>

let isLoading   = false;
let searchParams = new URLSearchParams(window.location.search);

document.addEventListener('DOMContentLoaded', function () {

    // ── Drag & drop reordering ─────────────────────────────────
    const tbody = document.getElementById('employees-tbody');
    new Sortable(tbody, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        onEnd: function () {
            const order = Array.from(tbody.querySelectorAll('tr'))
                .map(tr => tr.dataset.employeeId)
                .filter(Boolean);
            fetch('<?php echo e(route("employees.reorder")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify({ order }),
            })
            .then(r => r.json())
            .then(data => { if (data.success) console.log('Ordre sauvegardé'); })
            .catch(err => console.error('Reorder error', err));
        },
    });

    // Attacher l'ID employé à chaque ligne
    tbody.querySelectorAll('tr').forEach(tr => {
        const link = tr.querySelector('a[href*="/employees/"]');
        if (link) {
            const parts = link.href.split('/');
            tr.dataset.employeeId = parts[parts.length - 1];
        }
    });

    // ── Formulaire de recherche / filtre ──────────────────────
    const filterForm = document.querySelector('form[action="<?php echo e(route('employees.index')); ?>"]');
    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            searchParams = new URLSearchParams(new FormData(this));
            ajaxEmployees(1, false);
        });
        filterForm.querySelectorAll('select').forEach(el => {
            el.addEventListener('change', () => filterForm.dispatchEvent(new Event('submit')));
        });
    }

    // ── Bouton "Charger plus" ─────────────────────────────────
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function () {
            ajaxEmployees(currentPage + 1, true);
        });
    }
});

async function ajaxEmployees(page = 1, append = false) {
    if (isLoading) return;
    isLoading = true;

    const spinner    = document.getElementById('loading-spinner');
    const loadMoreBtn = document.getElementById('load-more-btn');

    spinner.style.display = 'flex';
    if (loadMoreBtn) {
        loadMoreBtn.disabled    = true;
        loadMoreBtn.textContent = 'Chargement…';
    }

    try {
        const url      = `/employees/ajax?page=${page}&${searchParams}`;
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data     = await response.json();
        const tbody    = document.getElementById('employees-tbody');

        if (!append) {
            tbody.innerHTML = '';
        }

        data.employees.forEach(emp => tbody.appendChild(buildEmployeeRow(emp)));

        currentPage  = data.pagination.current_page;
        totalResults = data.pagination.total;
        hasMore      = data.pagination.has_more;

        document.getElementById('results-count').textContent = `${totalResults} résultats`;

        if (loadMoreBtn) {
            loadMoreBtn.style.display = hasMore ? 'block' : 'none';
            loadMoreBtn.textContent   = `Charger plus (Page ${currentPage + 1})`;
        }

        if (page === 1) {
            history.replaceState({}, '', `<?php echo e(route('employees.index')); ?>?${searchParams}`);
        }
    } catch (error) {
        console.error('Erreur AJAX:', error);
    } finally {
        isLoading                     = false;
        spinner.style.display         = 'none';
        if (loadMoreBtn) loadMoreBtn.disabled = false;
    }
}

function buildEmployeeRow(employee) {
    const tr      = document.createElement('tr');
    const isAdmin = <?php echo json_encode(auth()->user()->isAdminOrRh() ?? false, 15, 512) ?>;

    tr.dataset.employeeId = employee.id;
    tr.innerHTML = `
        <td>
            <span style="font-family:monospace;font-size:0.8rem;background:var(--surface-2);padding:2px 8px;border-radius:4px;border:1px solid var(--border)">
                ${employee.matricule || 'N/A'}
            </span>
        </td>
        <td>
            <div class="table-employee">
                <div class="table-avatar">
                    ${employee.photo
                        ? `<img src="${employee.photo_url}" alt="">`
                        : `${(employee.full_name?.[0] || 'E').toUpperCase()}${(employee.full_name?.[1] || '').toUpperCase()}`
                    }
                </div>
                <div><div class="table-name">${employee.full_name}</div></div>
            </div>
        </td>
        <td><span class="badge badge-neutral">${employee.department || 'N/A'}</span></td>
        <td class="text-sm">${employee.position || ''}</td>
        <td>
            <span class="badge ${employee.contract_type === 'CDI' ? 'badge-success' : (employee.contract_type === 'CDD' ? 'badge-warning' : 'badge-neutral')}">
                ${employee.contract_type || 'N/A'}
            </span>
        </td>
        <td><span class="badge badge-${employee.status_color || 'neutral'}">${employee.status_label || employee.status}</span></td>
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
                <form action="/employees/${employee.id}" method="POST" style="display:inline"
                      onsubmit="return confirm('Supprimer cet employé ?')">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
                    <input type="hidden" name="_method" value="DELETE">
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
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/employees/index.blade.php ENDPATH**/ ?>