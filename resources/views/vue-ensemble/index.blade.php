@extends('layouts.app')

@section('title', 'Vue d\'ensemble')
@section('page-title', 'Vue d\'ensemble du temps de travail')

@push('styles')
<style>
.employee-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.employee-avatar {
    width: 60px; height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0f6b7c, #00c9a7);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    font-size: 1.2rem;
}
.employee-info h3 { font-size: 1.1rem; margin-bottom: 2px; }
.employee-info p { color: #64748b; font-size: 0.85rem; }
.employee-meta { margin-left: auto; display: flex; gap: 20px; }
.meta-value { font-weight: 700; color: #0f6b7c; font-size: 1rem; }
.meta-label { font-size: 0.7rem; color: #64748b; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}
.stat-card {
    background: white;
    padding: 16px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.stat-card.primary { border-top: 3px solid #0f6b7c; }
.stat-card.info { border-top: 3px solid #3b82f6; }
.stat-card.warning { border-top: 3px solid #f59e0b; }
.stat-card.success { border-top: 3px solid #10b981; }
.stat-value { font-size: 1.5rem; font-weight: 800; }
.stat-label { font-size: 0.75rem; color: #64748b; }

.content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
.card { background: white; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.card-header { padding: 14px; border-bottom: 1px solid #e2e8f0; font-weight: 700; }
.card-body { padding: 16px; }
.chart-container { height: 240px; }

.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: #f8fafc; padding: 10px; font-size: 0.65rem; text-transform: uppercase; }
.data-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; text-align: center; }
.data-table td:first-child { text-align: left; font-weight: 600; }
.total-row { background: #e6f4f7; font-weight: 700; }
.positive { color: #10b981; font-weight: 600; }
.negative { color: #ef4444; font-weight: 600; }

.weeks-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; margin-top: 20px; }
.week-card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.week-header { background: #0f6b7c; color: white; padding: 10px; display: flex; justify-content: space-between; font-weight: 600; font-size: 0.85rem; }
.week-table { width: 100%; font-size: 0.8rem; }
.week-table td { padding: 8px; text-align: center; border-bottom: 1px solid #eee; }
.week-balance { padding: 10px; font-weight: 700; display: flex; justify-content: space-between; }
.week-balance.positive { background: #ecfdf5; color: #10b981; }
.week-balance.negative { background: #fef2f2; color: #ef4444; }

.filters-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
.filters-bar select, .filters-bar input { padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; }
.period-nav { display: flex; gap: 8px; align-items: center; margin-left: auto; }
.period-nav a { padding: 8px 12px; background: #f1f5f9; border-radius: 6px; text-decoration: none; color: #333; font-size: 0.85rem; }
.period-current { padding: 8px 16px; background: #0f6b7c; color: white; border-radius: 6px; font-weight: 600; }

@media (max-width: 1024px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .content-grid { grid-template-columns: 1fr; }
    .employee-meta { margin-left: 0; margin-top: 12px; }
}
</style>
@endpush

@section('content')
{{-- FILTRES --}}
<div class="filters-bar">
    <form method="GET" action="{{ route('temps.vue-ensemble') }}" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; flex: 1;">
        <select name="employee_id" onchange="this.form.submit()" style="min-width: 180px;">
            <option value="">Selection employe...</option>
            @foreach($listeEmployesSelect as $emp)
                <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>
                    {{ $emp->first_name }} {{ $emp->last_name }}
                </option>
            @endforeach
        </select>

        <select name="department" onchange="this.form.submit()" style="min-width: 180px;">
            <option value="">Tous les departements</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ $department == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>

        <input type="number" name="annee" value="{{ $annee }}" min="2020" max="2030" style="width: 80px;">
        <input type="hidden" name="mois" value="{{ $mois }}">
        <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
    </form>

    <div class="period-nav">
        <a href="{{ route('temps.vue-ensemble', ['mois' => $moisPrecedent->month, 'annee' => $moisPrecedent->year, 'employee_id' => $employeeId]) }}">&larr; Prec.</a>
        <span class="period-current">{{ \Carbon\Carbon::create($annee, $mois, 1)->translatedFormat('F Y') }}</span>
        <a href="{{ route('temps.vue-ensemble', ['mois' => $moisSuivant->month, 'annee' => $moisSuivant->year, 'employee_id' => $employeeId]) }}">Suiv. &rarr;</a>
    </div>
</div>

{{-- SI AUCUN EMPLOYE --}}
@if(!$employee || !$employee->id)
<div class="card">
    <div class="card-body" style="text-align: center; padding: 40px;">
        <div style="font-size: 1.1rem; margin-bottom: 8px;">Selectionnez un employe</div>
        <div style="color: #64748b;">Utilisez les filtres ci-dessus pour selectionner un employe.</div>
    </div>
</div>
@else

{{-- INFORMATIONS EMPLOYE --}}
<div class="employee-card">
    <div class="employee-avatar">
        {{ strtoupper(substr($employee->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($employee->last_name ?? 'U', 0, 1)) }}
    </div>
    <div class="employee-info">
        <h3>{{ $employee->first_name }} {{ $employee->last_name }}</h3>
        <p>{{ $employee->position ?? 'Employe' }} | {{ $employee->department ?? 'Service' }} | {{ $employee->contract_type ?? 'CDI' }}</p>
    </div>
    <div class="employee-meta">
        <div>
            <div class="meta-value">{{ $employee->work_hours ?? 35 }}h</div>
            <div class="meta-label">Heures/semaine</div>
        </div>
        <div>
            <div class="meta-value">{{ number_format($compteurMois->heures_planifiees ?? 0, 0) }}</div>
            <div class="meta-label">Planning</div>
        </div>
    </div>
</div>

{{-- STATS --}}
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-value" style="color: #0f6b7c;">{{ number_format($compteurMois->heures_realisees ?? 0, 1) }}h</div>
        <div class="stat-label">Heures realisees</div>
    </div>
    <div class="stat-card info">
        <div class="stat-value">{{ number_format($compteurMois->heures_planifiees ?? 0, 0) }}h</div>
        <div class="stat-label">Heures planifiees</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-value" style="color: #f59e0b;">{{ number_format($compteurMois->heures_supplementaires ?? 0, 1) }}h</div>
        <div class="stat-label">Heures supp.</div>
    </div>
    <div class="stat-card {{ ($compteurMois->ecart ?? 0) >= 0 ? 'success' : 'warning' }}">
        <div class="stat-value {{ ($compteurMois->ecart ?? 0) >= 0 ? 'positive' : 'negative' }}">
            {{ ($compteurMois->ecart ?? 0) >= 0 ? '+' : '' }}{{ number_format($compteurMois->ecart ?? 0, 1) }}h
        </div>
        <div class="stat-label">Ecart</div>
    </div>
</div>

{{-- CONTENU PRINCIPAL --}}
<div class="content-grid">
    <div class="card">
        <div class="card-header">Heures travaillees par jour</div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="dailyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Recapitulatif du mois</div>
        <div class="card-body" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr><th>Type</th><th>Plan.</th><th>Realise</th><th>Ecart</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Travaillees</td>
                        <td>{{ number_format($compteurMois->heures_planifiees ?? 0, 1) }}h</td>
                        <td style="font-weight: 700; color: #0f6b7c;">{{ number_format($compteurMois->heures_realisees ?? 0, 1) }}h</td>
                        <td class="{{ ($compteurMois->heures_realisees - $compteurMois->heures_planifiees) >= 0 ? 'positive' : 'negative' }}">
                            {{ ($compteurMois->heures_realisees - $compteurMois->heures_planifiees) >= 0 ? '+' : '' }}{{ number_format(($compteurMois->heures_realisees ?? 0) - ($compteurMois->heures_planifiees ?? 0), 1) }}h
                        </td>
                    </tr>
                    <tr>
                        <td>Non trav.</td>
                        <td>0h</td>
                        <td>0h</td>
                        <td>0h</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total</td>
                        <td style="font-weight: 700;">{{ number_format($compteurMois->heures_planifiees ?? 0, 1) }}h</td>
                        <td style="font-weight: 700;">{{ number_format(($compteurMois->heures_realisees ?? 0) + ($compteurMois->heures_supplementaires ?? 0), 1) }}h</td>
                        <td style="font-weight: 700;" class="{{ ($compteurMois->ecart ?? 0) >= 0 ? 'positive' : 'negative' }}">
                            {{ ($compteurMois->ecart ?? 0) >= 0 ? '+' : '' }}{{ number_format($compteurMois->ecart ?? 0, 1) }}h
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- COMPTEURS HEBDOMADAIRES --}}
<div style="font-weight: 700; margin: 20px 0 14px;">Compteurs hebdomadaires</div>
<div class="weeks-grid">
    @foreach($semaines as $semaine)
    <div class="week-card">
        <div class="week-header">
            <span>Semaine {{ $semaine['numero'] }}</span>
            <span>{{ $semaine['debut'] }} - {{ $semaine['fin'] }}</span>
        </div>
        <table class="week-table">
            <tr><th>Plan</th><th>Real</th><th>Solde</th></tr>
            <tr>
                <td>{{ $semaine['heures_planifiees'] }}h</td>
                <td>{{ number_format($semaine['heures_realisees'], 1) }}h</td>
                <td class="{{ $semaine['solde'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $semaine['solde'] >= 0 ? '+' : '' }}{{ number_format($semaine['solde'], 1) }}h
                </td>
            </tr>
        </table>
        <div class="week-balance {{ $semaine['solde'] >= 0 ? 'positive' : 'negative' }}">
            <span>Solde</span>
            <span>{{ $semaine['solde'] >= 0 ? '+' : '' }}{{ number_format($semaine['solde'], 1) }}h</span>
        </div>
    </div>
    @endforeach
</div>

{{-- EVOLUTION ANNUELLE --}}
<div class="card" style="margin-top: 20px;">
    <div class="card-header">Evolution annuelle {{ $annee }}</div>
    <div class="card-body">
        <div style="height: 260px;">
            <canvas id="annualChart"></canvas>
        </div>
    </div>
</div>

@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique quotidien
    const joursDetails = @json($joursDetails);
    const joursLabels = joursDetails.map(d => d.jour);
    const joursHeures = joursDetails.map(d => d.total);

    const dailyCtx = document.getElementById('dailyChart');
    if (dailyCtx && joursDetails.length > 0) {
        new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: joursLabels,
                datasets: [{
                    label: 'Heures',
                    data: joursHeures,
                    backgroundColor: joursHeures.map(h => h > 8 ? '#10b981' : h > 0 ? '#0f6b7c' : '#e2e8f0'),
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 12 },
                    x: { title: { display: true, text: 'Jours du mois' } }
                }
            }
        });
    }

    // Graphique annuel
    const annualData = @json($graphiqueMois);
    const annualLabels = annualData.map(d => d.mois);
    const annualRealisees = annualData.map(d => d.heures_realisees);
    const annualPlanif = annualData.map(d => d.heures_planifiees);

    const annualCtx = document.getElementById('annualChart');
    if (annualCtx) {
        new Chart(annualCtx, {
            type: 'bar',
            data: {
                labels: annualLabels,
                datasets: [
                    { label: 'Heures realisees', data: annualRealisees, backgroundColor: '#0f6b7c', borderRadius: 4 },
                    { label: 'Planning', data: annualPlanif, backgroundColor: '#e2e8f0', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});
</script>
@endpush
