@extends('layouts.app')

@section('title', 'Notes de frais')
@section('page-title', 'Notes de frais')

@section('content')
<div class="nf-wrapper">
    <div class="nf-top-tabs">
        <a href="{{ route('expenses.index') }}" class="nf-top-tab active">Liste des notes</a>
        <a href="{{ route('expenses.create') }}" class="nf-top-tab">Nouvelle note (OCR)</a>
        <a href="{{ route('expenses.import') }}" class="nf-top-tab">Import groupé</a>
    </div>

    <div class="nf-view">
        <div class="page-header">
            <div class="page-header-left">
                <h1>Notes de frais</h1>
                <p>{{ now()->translatedFormat('F Y') }}</p>
            </div>
            <div style="display:flex;gap:10px">
                <a class="btn btn-ghost" href="{{ route('expenses.import') }}">Import OCR</a>
                <a class="btn btn-ghost" href="{{ route('expenses.export', request()->only(['month','year','employee_id','status'])) }}">📥 Export CSV</a>
                <a class="btn btn-primary" href="{{ route('expenses.create') }}">+ Nouvelle note</a>
            </div>
        </div>

        @if (session('success'))
            <div class="card mb-4">
                <div class="card-body" style="color:#065f46;background:#f0fdf4">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('expenses.index') }}" style="display:flex;gap:16px;align-items:center;flex-wrap:wrap">
                    <select name="month" class="form-control" style="width:auto" onchange="this.form.submit()">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ (int) request('month', now()->month) === $m ? 'selected' : '' }}>
                                {{ \Illuminate\Support\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                    <select name="year" class="form-control" style="width:auto" onchange="this.form.submit()">
                        @foreach (range(now()->year, now()->year - 3) as $y)
                            <option value="{{ $y }}" {{ (int) request('year', now()->year) === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>

                    @unless ($isEmployeeMode)
                        <select name="employee_id" class="form-control" style="width:auto" onchange="this.form.submit()">
                            <option value="">Tous les employés</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}" {{ (int) request('employee_id') === $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="status" class="form-control" style="width:auto" onchange="this.form.submit()">
                            <option value="">Tous statuts</option>
                            @foreach ($statusLabels as $value => $label)
                                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    @endunless

                    <div class="nf-stats-row" style="margin-left:auto">
                        <div><span style="color:var(--text-muted)">Total</span><strong>{{ $stats['total'] }}</strong></div>
                        <div><span style="color:var(--text-muted)">Montant</span><strong>{{ $stats['montant'] }}</strong></div>
                        <div><span style="color:#059669">Validé</span><strong>{{ $stats['valide'] }}</strong></div>
                        <div><span style="color:#dc2626">Rejeté</span><strong>{{ $stats['rejete'] }}</strong></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding:0">
                <table>
                    <thead>
                        <tr>
                            <th>Employé</th><th>Titre</th><th>Catégorie</th><th>Date</th>
                            <th style="text-align:right">Montant</th><th style="text-align:center">Statut</th>
                            <th style="text-align:center">Reçu</th><th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td>{{ $expense->employee->full_name ?? '—' }}</td>
                                <td style="font-weight:600">{{ $expense->title }}</td>
                                <td>{{ $expense->category_label }}</td>
                                <td>{{ $expense->expense_date->locale('fr')->translatedFormat('d F Y') }}</td>                                <td style="text-align:right;font-weight:600">{{ number_format($expense->amount, 2, ',', ' ') }} {{ $expense->currency }}</td>
                                <td style="text-align:center">
                                    <span class="nf-badge nf-badge-{{ $expense->status }}">{{ $expense->status_label }}</span>
                                </td>
                                <td style="text-align:center">
                                    @if ($expense->receipt_path)
                                        <a href="{{ Storage::url($expense->receipt_path) }}" target="_blank" rel="noopener" title="Voir le justificatif" style="display:inline-flex;color:var(--text-muted)">
                                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                            </svg>
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="text-align:right">
                                    <div style="display:flex;gap:6px;justify-content:flex-end">
                                        <a class="btn btn-ghost" style="padding:4px 10px;font-size:0.78rem" href="{{ route('expenses.edit', $expense) }}">Modifier</a>

                                        @unless ($isEmployeeMode)
                                            @if ($expense->status !== \App\Models\Expense::STATUS_VALIDE)
                                                <form action="{{ route('expenses.approve', $expense) }}" method="POST" style="display:inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-ghost" style="padding:4px 10px;font-size:0.78rem;color:#059669">✓ Valider</button>
                                                </form>
                                            @endif
                                            @if ($expense->status !== \App\Models\Expense::STATUS_REJETE)
                                                <form action="{{ route('expenses.reject', $expense) }}" method="POST" style="display:inline"
                                                      onsubmit="return confirm('Rejeter cette note de frais ?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-ghost" style="padding:4px 10px;font-size:0.78rem;color:#dc2626">✕ Rejeter</button>
                                                </form>
                                            @endif
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:24px">Aucune note de frais pour cette période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.nf-top-tabs {
    background:var(--surface); border-bottom:1px solid var(--border);
    padding:0 24px; display:flex; gap:4px; margin-bottom:24px;
}
.nf-top-tab {
    padding:14px 18px; font-size:0.88rem; font-weight:600; color:var(--text-muted);
    cursor:pointer; border-bottom:2px solid transparent; transition:all .15s;
    text-decoration:none; display:inline-block;
}
.nf-top-tab:hover { color:var(--primary); }
.nf-top-tab.active { color:var(--primary); border-bottom-color:var(--primary); }

.nf-view { padding:0; }

.nf-badge { padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:700; white-space:nowrap; }
.nf-badge-valide { background:#d1fae5; color:#065f46; }
.nf-badge-rejete { background:#fee2e2; color:#991b1b; }

.nf-stats-row { display:flex; gap:20px; font-size:0.85rem; flex-wrap:wrap; }
.nf-stats-row div strong { margin-left:4px; }
</style>
@endsection
