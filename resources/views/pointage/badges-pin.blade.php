@extends('layouts.app')

@section('title', 'Badges PIN — Employés')

@section('content')
<style>
    :root {
        --pin-bg:          #f8fafc;
        --pin-surface:     #ffffff;
        --pin-border:      #e2e8f0;
        --pin-border-soft: #f1f5f9;
        --pin-text:        #0f172a;
        --pin-muted:       #64748b;
        --pin-light:       #94a3b8;
        --pin-teal:        #0d9488;
        --pin-teal-bg:     #f0fdfa;
        --pin-teal-light:  #ccfbf1;
        --pin-accent:      #14b8a6;
        --pin-accent-bg:   #f0fdfa;
        --pin-accent-light:#ccfbf1;
        --pin-red:         #dc2626;
        --pin-red-bg:      #fef2f2;
        --pin-amber:       #d97706;
        --pin-amber-bg:    #fffbeb;
        --pin-green:       #16a34a;
        --pin-green-bg:    #f0fdf4;
    }

    .pin-wrap { min-height: calc(100vh - 64px); background: var(--pin-bg); display: flex; flex-direction: column; }

    /* ── Topbar ── */
    .pin-topbar { background: var(--pin-surface); border-bottom: 1px solid var(--pin-border); padding: 0 1.5rem; height: 52px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
    .pin-title { font-size: 15px; font-weight: 700; color: var(--pin-text); display: flex; align-items: center; gap: 8px; }
    .pin-title-icon { width: 30px; height: 30px; border-radius: 8px; background: var(--pin-accent-bg); color: var(--pin-accent); display: flex; align-items: center; justify-content: center; font-size: 16px; }
    .pin-back { display: flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 8px; background: var(--pin-bg); border: 1px solid var(--pin-border); color: var(--pin-muted); font-size: 13px; font-weight: 500; text-decoration: none; transition: all .15s; }
    .pin-back:hover { border-color: var(--pin-teal); color: var(--pin-teal); }

    /* ── Filtres ── */
    .pin-filters { background: var(--pin-surface); border-bottom: 1px solid var(--pin-border); padding: .75rem 1.5rem; display: flex; align-items: center; gap: .75rem; }
    .pin-search { flex: 1; padding: .45rem .75rem; border: 1px solid var(--pin-border); border-radius: 8px; font-size: 13px; color: var(--pin-text); outline: none; transition: border-color .15s; }
    .pin-search:focus { border-color: var(--pin-accent); }
    .pin-select { padding: .45rem .75rem; border: 1px solid var(--pin-border); border-radius: 8px; font-size: 13px; color: var(--pin-text); background: var(--pin-surface); outline: none; cursor: pointer; transition: border-color .15s; }
    .pin-select:focus { border-color: var(--pin-accent); }

    /* ── Action bar ── */
    .pin-action-bar { background: var(--pin-surface); border-bottom: 1px solid var(--pin-border); padding: .6rem 1.5rem; display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
    .pin-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .15s; text-decoration: none; }
    .pin-btn-regen-all { background: var(--pin-accent); color: #fff; }
    .pin-btn-regen-all:hover { background: #0f766e; }
    .pin-btn-regen-all:disabled { opacity: .6; cursor: not-allowed; }
    .pin-btn-print { background: var(--pin-teal); color: #fff; }
    .pin-btn-print:hover { background: #0f766e; }
    .pin-btn-export { background: var(--pin-bg); border: 1px solid var(--pin-border); color: var(--pin-muted); }
    .pin-btn-export:hover { border-color: var(--pin-teal); color: var(--pin-teal); }
    .pin-count-badge { background: var(--pin-accent-light); color: var(--pin-accent); font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; margin-left: auto; }

    /* ── Contenu ── */
    .pin-content { flex: 1; padding: 1.5rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1.5rem; }

    /* ── Département card ── */
    .dept-card { background: var(--pin-surface); border: 1px solid var(--pin-border); border-radius: 12px; overflow: hidden; }
    .dept-header { display: flex; align-items: center; justify-content: space-between; padding: .85rem 1.25rem; background: linear-gradient(135deg, var(--pin-accent-bg) 0%, var(--pin-teal-bg) 100%); border-bottom: 1px solid var(--pin-border); cursor: pointer; user-select: none; }
    .dept-header-left { display: flex; align-items: center; gap: 10px; }
    .dept-icon { width: 34px; height: 34px; border-radius: 8px; background: var(--pin-accent); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; flex-shrink: 0; }
    .dept-name { font-size: 14px; font-weight: 700; color: var(--pin-text); }
    .dept-count { font-size: 11px; color: var(--pin-muted); margin-top: 1px; }
    .dept-chevron { font-size: 16px; color: var(--pin-muted); transition: transform .2s; }
    .dept-header.collapsed .dept-chevron { transform: rotate(-90deg); }
    .dept-regen-btn { display: flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 7px; background: var(--pin-accent-bg); border: 1px solid var(--pin-accent-light); color: var(--pin-accent); font-size: 12px; font-weight: 600; cursor: pointer; transition: all .15s; }
    .dept-regen-btn:hover { background: var(--pin-accent); color: #fff; }

    /* ── Table ── */
    .pin-table { width: 100%; border-collapse: collapse; }
    .pin-table thead th { background: #fafafa; padding: 9px 14px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--pin-muted); border-bottom: 1px solid var(--pin-border); }
    .pin-table tbody tr { transition: background .12s; }
    .pin-table tbody tr:hover td { background: var(--pin-teal-bg); }
    .pin-table tbody tr:last-child td { border-bottom: none; }
    .pin-table td { padding: 10px 14px; border-bottom: 1px solid var(--pin-border-soft); vertical-align: middle; }

    /* Avatar */
    .emp-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--pin-teal-light); color: var(--pin-teal); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; }
    .emp-name { font-size: 13px; font-weight: 500; color: var(--pin-text); }
    .emp-matricule { font-size: 11px; color: var(--pin-muted); font-family: 'SF Mono', 'Fira Code', monospace; }

    /* PIN display */
    .pin-code { font-family: 'SF Mono', 'Fira Code', 'Courier New', monospace; font-size: 18px; font-weight: 700; letter-spacing: 4px; color: var(--pin-accent); background: var(--pin-accent-bg); padding: 4px 12px; border-radius: 8px; border: 1px solid var(--pin-accent-light); min-width: 80px; text-align: center; transition: all .3s; display: inline-block; }
    .pin-code.updated { background: var(--pin-green-bg); color: var(--pin-green); border-color: #bbf7d0; animation: pinFlash .6s ease; }
    @keyframes pinFlash { 0% { transform: scale(1.1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }

    /* Regen single */
    .pin-regen-single { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; background: transparent; border: 1px solid var(--pin-border); color: var(--pin-muted); font-size: 11px; font-weight: 500; cursor: pointer; transition: all .15s; }
    .pin-regen-single:hover { border-color: var(--pin-accent); color: var(--pin-accent); background: var(--pin-accent-bg); }
    .pin-regen-single:disabled { opacity: .6; pointer-events: none; }

    /* Spinner */
    .spin { display: inline-block; animation: spin .6s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Toast */
    .pin-toast { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 500; color: #fff; background: var(--pin-teal); box-shadow: 0 8px 32px rgba(0,0,0,.18); transform: translateY(80px); opacity: 0; transition: all .3s cubic-bezier(.16,1,.3,1); pointer-events: none; }
    .pin-toast.show { transform: translateY(0); opacity: 1; }
    .pin-toast.error { background: var(--pin-red); }

    /* Empty */
    .pin-empty { text-align: center; padding: 3rem; color: var(--pin-muted); font-size: 14px; }
</style>

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
                                    <div class="emp-avatar">
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
