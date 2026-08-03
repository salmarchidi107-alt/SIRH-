@extends('layouts.app')

@section('title', 'Badges PIN — Employés')

@section('content')

<div class="pin-wrap">

    {{-- ── Topbar ── --}}
    <div class="pin-topbar">
        <div class="pin-title">
            Badges PIN — Employés
        </div>
        <a href="{{ route('pointage.index') }}" class="pin-back">← Retour Pointage</a>
    </div>

    {{-- ── Filtres ── --}}
    <div class="pin-filters">
        <strong style="font-size:13px;color:var(--pin-muted)">Filtrer :</strong>
        <input type="text" id="searchInput" class="pin-search"
               placeholder="Nom, prénom ou matricule…"
               value="{{ request('search') }}"
               oninput="filterTable()">
        <select id="deptFilter" class="pin-select" onchange="filterByDept()">
            <option value="">Tous les départements</option>
            @foreach($departments as $dept)
            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                {{ $dept }}
            </option>
            @endforeach
        </select>
    </div>

    {{-- ── Action bar ── --}}
    <div class="pin-action-bar">
        <button class="pin-btn pin-btn-regen-all" id="btnRegenAll" onclick="doRegenAll(null)">
            🔄 Régénérer tous les PINs
        </button>
        <button class="pin-btn pin-btn-regen-all" id="btnRegenDept"
                onclick="doRegenAll(this.dataset.dept)" style="background:#0f766e;display:none;">
             Régénérer ce département
        </button>
        <button class="pin-btn pin-btn-print" onclick="exportPdf()">
             Exporter PDF
        </button>
        <span class="pin-count-badge" id="totalCount">
            {{ $byDept->flatten()->count() }} employés
        </span>
    </div>

    {{-- ── Contenu ── --}}
    <div class="pin-content" id="pinContent">
        @forelse($byDept as $dept => $employees)
        <div class="dept-card" data-dept="{{ strtolower($dept) }}" id="dept-{{ Str::slug($dept) }}">

            <div class="dept-header" onclick="toggleDept(this)">
                <div class="dept-header-left">
                    <div class="dept-icon">{{ strtoupper(substr($dept ?: 'N', 0, 1)) }}</div>
                    <div>
                        <div class="dept-name">{{ $dept ?: 'Sans département' }}</div>
                        <div class="dept-count">{{ $employees->count() }} employé{{ $employees->count() > 1 ? 's' : '' }}</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.75rem">
                    <button class="dept-regen-btn"
                            onclick="event.stopPropagation(); doRegenDept('{{ $dept }}')"
                            title="Régénérer tous les PINs de ce département">
                        🔄 Régénérer dept.
                    </button>
                    <span class="dept-chevron">▾</span>
                </div>
            </div>

            <div class="dept-body">
                <table class="pin-table">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>Employé</th>
                            <th>Matricule</th>
                            <th style="width:160px">PIN Badge</th>
                            <th style="width:130px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $i => $emp)
                        <tr id="emp-row-{{ $emp->id }}" class="emp-row"
                            data-name="{{ strtolower($emp->first_name . ' ' . $emp->last_name) }}"
                            data-matricule="{{ strtolower($emp->matricule ?? '') }}"
                            data-dept="{{ strtolower($dept) }}">
                            <td style="color:var(--pin-muted);font-size:12px">{{ $i + 1 }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div class="pin-emp-avatar">
                                        {{ strtoupper(substr($emp->first_name,0,1).substr($emp->last_name,0,1)) }}
                                    </div>
                                    <div class="emp-name">{{ $emp->first_name }} {{ $emp->last_name }}</div>
                                </div>
                            </td>
                            <td><span class="emp-matricule">{{ $emp->matricule ?? '—' }}</span></td>
                            <td>
                                <span class="pin-code" id="pin-{{ $emp->id }}">
                                    {{ $emp->plain_pin ?? '——' }}
                                </span>
                            </td>
                            <td>
                                <button class="pin-regen-single"
                                        id="regen-btn-{{ $emp->id }}"
                                        onclick="regenSingle({{ $emp->id }})">
                                    🔄 Nouveau PIN
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="pin-empty">
            <div style="font-size:48px;margin-bottom:1rem">🔍</div>
            <div>Aucun employé trouvé.</div>
        </div>
        @endforelse
    </div>

</div>

{{-- ── Toast ── --}}
<div class="pin-toast" id="pinToast"></div>

<script>
var CSRF = document.querySelector('meta[name="csrf-token"]').content;
var REGEN_SINGLE_URL = '{{ route('pointage.regenerer-pin') }}';
var REGEN_ALL_URL    = '{{ route('pointage.regenerer-tous-pins') }}';
var EXPORT_PDF_URL   = '{{ route('pointage.export-badges-pin-pdf') }}';

/* ══════════════════════════════════════════
   TOGGLE DÉPARTEMENT
══════════════════════════════════════════ */
function toggleDept(header) {
    var body = header.nextElementSibling;
    var isCollapsed = header.classList.contains('collapsed');
    header.classList.toggle('collapsed', !isCollapsed);
    body.style.display = isCollapsed ? '' : 'none';
}

/* ══════════════════════════════════════════
   FILTRE NOM / MATRICULE
══════════════════════════════════════════ */
function filterTable() {
    var q = document.getElementById('searchInput').value.toLowerCase().trim();
    document.querySelectorAll('.emp-row').forEach(function(row) {
        var match = row.dataset.name.indexOf(q) !== -1 || row.dataset.matricule.indexOf(q) !== -1;
        row.style.display = match ? '' : 'none';
    });
    updateCount();
}

/* ══════════════════════════════════════════
   FILTRE DÉPARTEMENT
══════════════════════════════════════════ */
function filterByDept() {
    var dept = document.getElementById('deptFilter').value.toLowerCase();
    var regenDeptBtn = document.getElementById('btnRegenDept');

    document.querySelectorAll('.dept-card').forEach(function(card) {
        card.style.display = (!dept || card.dataset.dept === dept) ? '' : 'none';
    });

    if (dept) {
        regenDeptBtn.style.display = '';
        regenDeptBtn.dataset.dept = document.getElementById('deptFilter').value;
    } else {
        regenDeptBtn.style.display = 'none';
    }
    updateCount();
}

function updateCount() {
    var visible = document.querySelectorAll('.emp-row:not([style*="display: none"])').length;
    document.getElementById('totalCount').textContent = visible + ' employé' + (visible > 1 ? 's' : '');
}

/* ══════════════════════════════════════════
   EXPORT PDF
══════════════════════════════════════════ */
function exportPdf() {
    var deptFilter = document.getElementById('deptFilter').value;
    var searchFilter = document.getElementById('searchInput').value;
    var url = EXPORT_PDF_URL;
    if (deptFilter) url += '?department=' + encodeURIComponent(deptFilter);
    if (searchFilter) url += (deptFilter ? '&' : '?') + 'search=' + encodeURIComponent(searchFilter);
    window.location.href = url;
}

/* ══════════════════════════════════════════
   TOAST
══════════════════════════════════════════ */
var toastTimer = null;
function showToast(msg, isError) {
    var el = document.getElementById('pinToast');
    el.textContent = msg;
    el.className = 'pin-toast' + (isError ? ' error' : '') + ' show';
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(function() {
        el.className = 'pin-toast' + (isError ? ' error' : '');
    }, 3000);
}

/* ══════════════════════════════════════════
   RÉGÉNÉRER UN SEUL PIN
══════════════════════════════════════════ */
function regenSingle(empId) {
    var btn = document.getElementById('regen-btn-' + empId);
    var pinEl = document.getElementById('pin-' + empId);

    btn.disabled = true;
    btn.innerHTML = '<span class="spin">⟳</span> Génération…';

    fetch(REGEN_SINGLE_URL, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ employee_id: empId })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            pinEl.textContent = data.new_pin;
            pinEl.classList.add('updated');
            setTimeout(function() { pinEl.classList.remove('updated'); }, 2000);
            showToast('✓ PIN mis à jour : ' + data.new_pin);
        } else {
            showToast('Erreur lors de la régénération', true);
        }
    })
    .catch(function() { showToast('Erreur réseau', true); })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '🔄 Nouveau PIN';
    });
}

/* ══════════════════════════════════════════
   RÉGÉNÉRER DÉPARTEMENT (depuis bouton dept header)
══════════════════════════════════════════ */
function doRegenDept(dept) {
    doRegenAll(dept);
}

/* ══════════════════════════════════════════
   RÉGÉNÉRER TOUS (ou par département)
══════════════════════════════════════════ */
function doRegenAll(department) {
    var btnAll  = document.getElementById('btnRegenAll');
    var btnDept = document.getElementById('btnRegenDept');

    btnAll.disabled  = true;
    btnDept.disabled = true;
    btnAll.innerHTML  = '<span class="spin">⟳</span> En cours…';
    btnDept.innerHTML = '<span class="spin">⟳</span> En cours…';

    var body = department ? { department: department } : {};

    fetch(REGEN_ALL_URL, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(body)
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            data.pins.forEach(function(item) {
                var el = document.getElementById('pin-' + item.id);
                if (el) {
                    el.textContent = item.pin;
                    el.classList.add('updated');
                    setTimeout(function() { el.classList.remove('updated'); }, 2500);
                }
            });
            showToast('✓ ' + data.count + ' PINs régénérés avec succès');
        } else {
            showToast('Erreur lors de la régénération', true);
        }
    })
    .catch(function(err) {
        console.error('Erreur:', err);
        showToast('Erreur réseau', true);
    })
    .finally(function() {
        btnAll.disabled  = false;
        btnDept.disabled = false;
        btnAll.innerHTML  = '🔄 Régénérer tous les PINs';
        btnDept.innerHTML = '🔄 Régénérer ce département';
    });
}
</script>
@endsection
