@extends('layouts.app')

@section('title', 'Gestion de la Paie')
@section('page-title', 'Paie')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Gestion de la Paie</h1>
        <p>
            Période :
            @if($dateDebut && $dateFin)
                {{ \Carbon\Carbon::parse($dateDebut)->locale('fr')->isoFormat('D MMM YYYY') }}
                → {{ \Carbon\Carbon::parse($dateFin)->locale('fr')->isoFormat('D MMM YYYY') }}
            @else
                {{ \Carbon\Carbon::create($year, $month)->locale('fr')->isoFormat('MMMM YYYY') }}
            @endif
        </p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">

        {{-- ── Switcher MAD / MRU ── --}}
        <div class="currency-switcher" id="currencySwitcher">
            <button id="btnMAD" class="active" onclick="setCurrency('MAD')">
                <span style="font-size:10px;opacity:.7;margin-right:2px;">MA</span>MAD
            </button>
            <button id="btnMRU" onclick="setCurrency('MRU')">
                <span style="font-size:10px;opacity:.7;margin-right:2px;">MR</span>MRU
            </button>
        </div>

        <form method="GET" action="{{ route('salary.index') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">

            <select name="month" class="form-control" style="width:130px">
                @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ $m==$month?'selected':'' }}>
                        {{ \Carbon\Carbon::create(null,$m)->locale('fr')->monthName }}
                    </option>
                @endfor
            </select>
            <select name="year" class="form-control" style="width:90px">
                @for($y=now()->year; $y>=now()->year-2; $y--)
                    <option value="{{ $y }}" {{ $y==$year?'selected':'' }}>{{ $y }}</option>
                @endfor
            </select>

            <select name="department" style="padding:10px 12px;border:1px solid var(--border);border-radius:8px;min-width:160px">
                <option value="">Départements</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ $department == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>

            <div style="height:22px;width:1px;background:var(--border);margin:0 2px;flex-shrink:0;"></div>

            <div class="period-filter-bar">
                <span style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;">Du</span>
                <input type="date" name="date_debut" value="{{ $dateDebut ?? '' }}" placeholder="jj/mm/aaaa" title="Date de début">
                <span style="font-size:13px;color:var(--text-muted);font-weight:600;">au</span>
                <input type="date" name="date_fin" value="{{ $dateFin ?? '' }}" placeholder="jj/mm/aaaa" title="Date de fin">
            </div>

            @if($search || $department || $dateDebut)
                <a href="{{ route('salary.index', ['year' => $year, 'month' => $month]) }}" class="btn btn-ghost">✕ Réinitialiser</a>
            @endif
            <button type="submit" class="btn btn-ghost">Filtrer</button>
        </form>


        <a href="{{ route('variables.index', ['month'=>$month,'year'=>$year]) }}" class="btn btn-ghost">
            Éléments variables
        </a>
        <a href="{{ route('salary.export-pdf', request()->query()) }}" class="btn btn-ghost" target="_blank">
            <svg width="14" height="14" ...>...</svg>
            Export PDF
        </a>
    </div>
</div>

{{-- ── Badge filtre actif ── --}}
@if($dateDebut && $dateFin)
<div style="margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
    <span class="period-active-tag">
        {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
        <a href="{{ route('salary.index', array_filter(['month' => $month, 'year' => $year, 'department' => $department, 'search' => $search, 'status' => request('status')])) }}"
           title="Réinitialiser la période">✕</a>
    </span>
    @php $moisConcernes = $periodesMois ?? []; @endphp
    @if(count($moisConcernes) > 0)
        <span style="font-size:12px;color:var(--text-muted);">
            Bulletins couverts :
            @foreach($moisConcernes as $mc)
                <strong>{{ \Carbon\Carbon::create($mc['year'], $mc['month'])->locale('fr')->isoFormat('MMMM YYYY') }}</strong>{{ !$loop->last ? ', ' : '' }}
            @endforeach
        </span>
    @endif
</div>
@endif

{{-- ═══ KPIs ══════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    <div class="salary-card">
        <div class="salary-label">Masse salariale brute</div>
        <div class="salary-net">
            {{ number_format($summary['total_gross'],0,',',' ') }}
            <span class="cur-label">MAD</span>
        </div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">
            Coût employeur : {{ number_format($summary['total_employer_cost'] ?? 0,0,',',' ') }}
            <span class="cur-label">MAD</span>
        </div>
    </div>
    <div class="salary-card">
        <div class="salary-label">Charges salariales</div>
        <div class="salary-net" style="font-size:1.4rem">
            {{ number_format($summary['total_cnss_sal']+$summary['total_amo_sal'],0,',',' ') }}
            <span class="cur-label">MAD</span>
        </div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">
            CNSS : {{ number_format($summary['total_cnss_sal'],0,',',' ') }} |
            AMO  : {{ number_format($summary['total_amo_sal'],0,',',' ') }}
        </div>
    </div>
    <div class="salary-card">
        <div class="salary-label">IR retenu à la source</div>
        <div class="salary-net" style="font-size:1.4rem">
            {{ number_format($summary['total_ir'],0,',',' ') }}
            <span class="cur-label">MAD</span>
        </div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">DGI — déclaration mensuelle</div>
    </div>
    <div class="salary-card">
        <div class="salary-label">Net à payer total</div>
        <div class="salary-net">
            {{ number_format($summary['total_net'],0,',',' ') }}
            <span class="cur-label">MAD</span>
        </div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">
            <span style="color:var(--success)">{{ $summary['count_validated'] }} validés</span> /
            {{ $summary['count'] }} bulletins
        </div>
    </div>
</div>

{{-- ═══ Tableau employés ══════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">
            Employés — {{ $employees->total() }}
            {{ $status ? ucfirst($status) : 'au total' }}
            @if($dateDebut && $dateFin)
                <span style="font-size:12px;font-weight:normal;color:var(--text-muted);margin-left:6px;">
                    (période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m') }} → {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }})
                </span>
            @endif
        </div>
        <div style="display:flex;gap:8px">
            <a href="{{ route('salary.index', array_merge(request()->only(['month','year','date_debut','date_fin','department']), ['status'=>null])) }}"
               class="badge badge-neutral {{ ($status??null)===null?'active':'' }}"
               style="{{ ($status??null)===null?'font-weight:bold;box-shadow:0 2px 4px rgba(0,0,0,0.1)':'' }}">
               Tous ({{ $summary['count'] }})
            </a>
            <a href="{{ route('salary.index', array_merge(request()->only(['month','year','date_debut','date_fin','department']), ['status'=>'draft'])) }}"
               class="badge badge-warning {{ $status=='draft'?'active':'' }}"
               style="{{ $status=='draft'?'font-weight:bold;box-shadow:0 2px 4px rgba(0,0,0,0.1)':'' }}">
               {{ $summary['count_draft'] }} brouillons
            </a>
            <a href="{{ route('salary.index', array_merge(request()->only(['month','year','date_debut','date_fin','department']), ['status'=>'validated'])) }}"
               class="badge badge-success {{ $status=='validated'?'active':'' }}"
               style="{{ $status=='validated'?'font-weight:bold;box-shadow:0 2px 4px rgba(0,0,0,0.1)':'' }}">
               {{ $summary['count_validated'] }} validés
            </a>
            <a href="{{ route('salary.index', array_merge(request()->only(['month','year','date_debut','date_fin','department']), ['status'=>'paid'])) }}"
               class="badge badge-info {{ $status=='paid'?'active':'' }}"
               style="{{ $status=='paid'?'font-weight:bold;box-shadow:0 2px 4px rgba(0,0,0,0.1)':'' }}">
               {{ $summary['count_paid'] }} rémunérés
            </a>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Employé</th>
                        <th>Département</th>
                        @if($dateDebut && $dateFin)
                        <th>Période</th>
                        @endif
                        <th>Mode paiement</th>
                        <th>Base</th>
                        <th>Brut</th>
                        <th>CNSS+AMO</th>
                        <th>IR</th>
                        <th style="color:var(--success)">Net à payer</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        @php $salList = $emp->salaries; @endphp
                        @if($salList->isEmpty())
                            <tr>
                                <td>
                                    <div class="font-semibold">{{ $emp->full_name }}</div>
                                    <div style="font-size:0.78rem;color:var(--text-muted)">{{ $emp->position }}</div>
                                </td>
                                <td>{{ $emp->department }}</td>
                                @if($dateDebut && $dateFin)<td>—</td>@endif
                                <td style="font-size:0.82rem">{{ ucfirst($emp->payment_method ?? '—') }}</td>
                                <td>{{ number_format($emp->base_salary,0,',',' ') }}</td>
                                <td>—</td><td>—</td><td>—</td><td>—</td>
                                <td><span class="badge badge-secondary">Non généré</span></td>
                                <td>
                                    @unless(auth()->user()->isEmployee())
                                    <a href="{{ route('salary.create', [$emp,'month'=>$month,'year'=>$year]) }}" class="btn btn-sm btn-primary">Saisir</a>
                                    @endunless
                                    <a href="{{ route('salary.show', $emp) }}" class="btn btn-sm btn-ghost">Historique</a>
                                </td>
                            </tr>
                        @else
                            @foreach($salList as $sal)
                            @php $cur = $sal->currency ?? 'MAD'; @endphp
                            <tr>
                                <td>
                                    <div class="font-semibold">{{ $emp->full_name }}</div>
                                    <div style="font-size:0.78rem;color:var(--text-muted)">{{ $emp->position }}</div>
                                </td>
                                <td>{{ $emp->department }}</td>

                                @if($dateDebut && $dateFin)
                                <td style="font-size:0.82rem;color:var(--text-muted);white-space:nowrap;">
                                    {{ \Carbon\Carbon::create($sal->year, $sal->month)->locale('fr')->isoFormat('MMMM YYYY') }}
                                </td>
                                @endif

                                <td style="font-size:0.82rem">
                                    @if($emp->payment_method == 'virement')
                                        Virement {{ $emp->bank ?? '—' }}
                                    @else
                                        {{ ucfirst($emp->payment_method ?? '—') }}
                                    @endif
                                </td>
                                <td>{{ number_format($emp->base_salary,0,',',' ') }}</td>
                                <td class="font-semibold">
                                    {{ number_format($sal->gross_salary,0,',',' ') }}
                                    <span class="cur-label">MAD</span>
                                </td>
                                <td class="deduction" style="font-size:0.85rem">
                                    {{ number_format($sal->cnss_deduction + $sal->amo_deduction,0,',',' ') }}
                                    <span class="cur-label">MAD</span>
                                </td>
                                <td class="deduction" style="font-size:0.85rem">
                                    {{ number_format($sal->ir_deduction,0,',',' ') }}
                                    <span class="cur-label">MAD</span>
                                </td>
                                <td class="font-semibold" style="color:var(--success)">
                                    {{ number_format($sal->net_salary,0,',',' ') }}
                                    <span class="cur-label">MAD</span>
                                </td>

                                <td>
                                    <div style="display:flex;flex-direction:column;gap:4px;">
                                        <span class="badge badge-{{ $sal->status_color }}">
                                            {{ $sal->status_label }}
                                        </span>
                                        @if($sal->created_by)
                                        <div style="font-size:0.7rem;color:#64748b;display:flex;align-items:center;gap:3px;white-space:nowrap;"
                                             title="Saisi le {{ \Carbon\Carbon::parse($sal->created_at)->format('d/m/Y à H:i') }}">
                                            <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            <span>Saisi : <strong>{{ $sal->createdBy?->name ?? '—' }}</strong></span>
                                            @if($sal->created_at)
                                            <span style="color:#94a3b8;">· {{ \Carbon\Carbon::parse($sal->created_at)->format('d/m H\hi') }}</span>
                                            @endif
                                        </div>
                                        @endif
                                        @if(in_array($sal->status, ['validated', 'paid']) && $sal->validated_by)
                                        <div style="font-size:0.7rem;color:#0d9488;display:flex;align-items:center;gap:3px;white-space:nowrap;"
                                             title="Validé le {{ \Carbon\Carbon::parse($sal->validated_at)->format('d/m/Y à H:i') }}">
                                            <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Validé : <strong>{{ $sal->validatedBy?->name ?? '—' }}</strong></span>
                                            @if($sal->validated_at)
                                            <span style="color:#94a3b8;">· {{ \Carbon\Carbon::parse($sal->validated_at)->format('d/m H\hi') }}</span>
                                            @endif
                                        </div>
                                        @endif
                                        @if($sal->status === 'paid' && $sal->paid_by)
                                        <div style="font-size:0.7rem;color:#1d4ed8;display:flex;align-items:center;gap:3px;white-space:nowrap;"
                                             title="Payé le {{ \Carbon\Carbon::parse($sal->paid_at)->format('d/m/Y à H:i') }}">
                                            <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                            </svg>
                                            <span>Payé : <strong>{{ $sal->paidBy?->name ?? '—' }}</strong></span>
                                            @if($sal->paid_at)
                                            <span style="color:#94a3b8;">· {{ \Carbon\Carbon::parse($sal->paid_at)->format('d/m H\hi') }}</span>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div style="display:flex;gap:4px">
                                        @unless(auth()->user()->isEmployee())
                                        <a href="{{ route('salary.create', [$emp,'month'=>$sal->month,'year'=>$sal->year]) }}"
                                           class="btn btn-sm btn-primary">Saisir</a>
                                        @endunless
                                        <a href="{{ route('salary.show', $emp) }}" class="btn btn-sm btn-ghost">Historique</a>
                                        <a href="{{ route('salary.pdf', $sal) }}" class="btn btn-sm btn-ghost">PDF</a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="11" style="text-align:center;padding:48px;color:var(--text-muted)">
                                Aucun bulletin trouvé pour cette période.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── Pagination custom en français (remplace ->links() par défaut) ── --}}
        @if($employees->hasPages())
        @php
            $currentPage = $employees->currentPage();
            $lastPage    = $employees->lastPage();
            $window      = 2;
            $rangeStart  = max(1, $currentPage - $window);
            $rangeEnd    = min($lastPage, $currentPage + $window);
        @endphp
        <div class="pagination-summary">
            Affichage de {{ $employees->firstItem() }} à {{ $employees->lastItem() }} sur {{ $employees->total() }} employés
        </div>
        <div class="custom-pagination">
            {{-- Précédent --}}
            @if($employees->onFirstPage())
                <span class="page-btn disabled">‹ Précédent</span>
            @else
                <a href="{{ $employees->appends(request()->query())->previousPageUrl() }}" class="page-btn">‹ Précédent</a>
            @endif

            {{-- Première page + ellipsis --}}
            @if($rangeStart > 1)
                <a href="{{ $employees->appends(request()->query())->url(1) }}" class="page-btn">1</a>
                @if($rangeStart > 2)
                    <span class="page-dots">…</span>
                @endif
            @endif

            {{-- Pages autour de la page courante --}}
            @for($p = $rangeStart; $p <= $rangeEnd; $p++)
                @if($p == $currentPage)
                    <span class="page-btn active">{{ $p }}</span>
                @else
                    <a href="{{ $employees->appends(request()->query())->url($p) }}" class="page-btn">{{ $p }}</a>
                @endif
            @endfor

            {{-- Ellipsis + dernière page --}}
            @if($rangeEnd < $lastPage)
                @if($rangeEnd < $lastPage - 1)
                    <span class="page-dots">…</span>
                @endif
                <a href="{{ $employees->appends(request()->query())->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
            @endif

            {{-- Suivant --}}
            @if($employees->hasMorePages())
                <a href="{{ $employees->appends(request()->query())->nextPageUrl() }}" class="page-btn">Suivant ›</a>
            @else
                <span class="page-btn disabled">Suivant ›</span>
            @endif
        </div>
        @endif
    </div>
</div>

<script>
var currentCurrency = localStorage.getItem('paie_currency') || 'MAD';

function setCurrency(cur) {
    currentCurrency = cur;
    localStorage.setItem('paie_currency', cur);
    applyLabels();
}

function applyLabels() {
    var labels = document.querySelectorAll('.cur-label');
    for (var i = 0; i < labels.length; i++) {
        labels[i].textContent = currentCurrency;
    }
    document.getElementById('btnMAD').classList.toggle('active', currentCurrency === 'MAD');
    document.getElementById('btnMRU').classList.toggle('active', currentCurrency === 'MRU');
}

// Applique au chargement
applyLabels();
</script>

@endsection
