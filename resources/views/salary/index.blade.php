@extends('layouts.app')

@section('title', 'Gestion de la Paie')
@section('page-title', 'Paie')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Gestion de la Paie</h1>
        <p>Période : {{ \Carbon\Carbon::create($year, $month)->locale('fr')->isoFormat('MMMM YYYY') }}</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <form method="GET" action="{{ route('salary.index') }}" style="display:flex;gap:8px">
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
            <button type="submit" class="btn btn-ghost">Filtrer</button>
        </form>
        <form method="POST" action="{{ route('salary.generate-all') }}">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <button type="submit" class="btn btn-primary"
                    onclick="return confirm('Générer la paie pour tous les employés ?')">
                Générer tout le mois
            </button>
        </form>
        <a href="{{ route('variables.index', ['month'=>$month,'year'=>$year]) }}" class="btn btn-ghost">
            Éléments variables
        </a>
        
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

{{-- ═══ KPIs ══════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    <div class="salary-card">
        <div class="salary-label">Masse salariale brute</div>
        <div class="salary-net">{{ number_format($summary['total_gross'],0,',',' ') }} MAD</div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">
        Coût total employeur : 0 MAD
        </div>
    </div>
    <div class="salary-card">
        <div class="salary-label">Charges salariales</div>
        <div class="salary-net" style="font-size:1.4rem">
            {{ number_format($summary['total_cnss_sal']+$summary['total_amo_sal'],0,',',' ') }} MAD
        </div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">
            CNSS : {{ number_format($summary['total_cnss_sal'],0,',',' ') }} |
            AMO : {{ number_format($summary['total_amo_sal'],0,',',' ') }}
        </div>
    </div>
    <div class="salary-card">
        <div class="salary-label">IR retenu à la source</div>
        <div class="salary-net" style="font-size:1.4rem">
            {{ number_format($summary['total_ir'],0,',',' ') }} MAD
        </div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">DGI — déclaration mensuelle</div>
    </div>
    <div class="salary-card">
        <div class="salary-label">Net à payer total</div>
        <div class="salary-net">{{ number_format($summary['total_net'],0,',',' ') }} MAD</div>
        <div style="font-size:0.75rem;opacity:0.6;margin-top:4px">
            <span style="color:var(--success)">{{ $summary['count_validated'] }} validés</span> /
            {{ $summary['count'] }} bulletins
        </div>
    </div>
</div>



{{-- ═══ Tableau employés ══════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header">
<div class="card-title">Employés — {{ $employees->count() }} {{ $status ? ucfirst($status) : 'au total' }}</div>
        <div style="display:flex;gap:8px">
            <a href="{{ route('salary.index', array_merge(request()->only(['month', 'year']), ['status' => null])) }}" class="badge badge-neutral {{ ($status ?? null) === null ? 'active' : '' }}" style="{{ ($status ?? null) === null ? 'font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);' : '' }}">Tous ({{ $summary['count'] }})</a>
            <a href="{{ route('salary.index', array_merge(request()->only(['month', 'year']), ['status' => 'draft'])) }}" class="badge badge-warning {{ $status == 'draft' ? 'active' : '' }}" style="{{ $status == 'draft' ? 'font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);' : '' }}">{{ $summary['count_draft'] }} brouillons</a>
            <a href="{{ route('salary.index', array_merge(request()->only(['month', 'year']), ['status' => 'validated'])) }}" class="badge badge-success {{ $status == 'validated' ? 'active' : '' }}" style="{{ $status == 'validated' ? 'font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);' : '' }}">{{ $summary['count_validated'] }} validés</a>
            <a href="{{ route('salary.index', array_merge(request()->only(['month', 'year']), ['status' => 'paid'])) }}" class="badge badge-info {{ $status == 'paid' ? 'active' : '' }}" style="{{ $status == 'paid' ? 'font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);' : '' }}">{{ $summary['count_paid'] }} rémunérer</a>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Employé</th>
                        <th>Département</th>
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
                        @php $sal = $emp->salaries->first(); @endphp
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $emp->full_name }}</div>
                                <div style="font-size:0.78rem;color:var(--text-muted)">{{ $emp->position }}</div>
                            </td>
                            <td>{{ $emp->department }}</td>
                            <td style="font-size:0.82rem">
@if($emp->payment_method == 'virement')
    Virement {{ $emp->bank ?? '—' }}
@else
    {{ ucfirst($emp->payment_method ?? '—') }}
@endif
</td>
                            <td>{{ number_format($emp->base_salary,0,',',' ') }}</td>

                            <td class="font-semibold">
                                {{ $sal ? number_format($sal->gross_salary,0,',',' ') : '—' }}
                            </td>
                            <td class="deduction" style="font-size:0.85rem">
                                @if($sal)
                                    {{ number_format($sal->cnss_deduction + $sal->amo_deduction,0,',',' ') }}
                                @else —
                                @endif
                            </td>
                            <td class="deduction" style="font-size:0.85rem">
                                {{ $sal ? number_format($sal->ir_deduction,0,',',' ') : '—' }}
                            </td>
                            <td class="font-semibold" style="color:var(--success)">
                                {{ $sal ? number_format($sal->net_salary,0,',',' ').' MAD' : '—' }}
                            </td>
                            <td>
                                @if($sal)
                                    <span class="badge badge-{{ $sal->status_color }}">{{ $sal->status_label }}</span>
                                @else
                                    <span class="badge badge-secondary">Non généré</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    @unless(auth()->user()->isEmployee())
                                        <a href="{{ route('salary.create', [$emp,'month'=>$month,'year'=>$year]) }}"
                                           class="btn btn-sm btn-primary">Saisir</a>
                                    @endunless
                                    <a href="{{ route('salary.show', $emp) }}"
                                       class="btn btn-sm btn-ghost">Historique</a>
                                    @if($sal)
                                        <a href="{{ route('salary.pdf', $sal) }}"
                                           class="btn btn-sm btn-ghost">PDF</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center;padding:48px;color:var(--text-muted)">
                                Aucun employé trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
