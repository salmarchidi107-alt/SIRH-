@extends('layouts.app')

@section('title', 'Éléments Variables')
@section('page-title', 'Éléments Variables')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Éléments Variables</h1>
        <p>{{ \Carbon\Carbon::create($year,$month)->locale('fr')->isoFormat('MMMM YYYY') }} — Primes, absences, avances</p>
    </div>
    <a href="{{ route('salary.index', ['month'=>$month,'year'=>$year]) }}" class="btn btn-ghost">← Retour</a>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:380px 1fr;gap:20px">

    {{-- Formulaire --}}
    <div class="card">
        <div class="card-header"><div class="card-title">Ajouter un élément</div></div>
        <div class="card-body">
            <form action="{{ route('variables.store') }}" method="POST">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
                    <div class="form-group">
                        <label>Mois</label>
                        <select name="month" class="form-control">
                            @for($m=1; $m<=12; $m++)
                                <option value="{{ $m }}" {{ $m==$month?'selected':'' }}>
                                    {{ \Carbon\Carbon::create(null,$m)->locale('fr')->monthName }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Année</label>
                        <select name="year" class="form-control">
                            @for($y=now()->year; $y>=now()->year-2; $y--)
                                <option value="{{ $y }}" {{ $y==$year?'selected':'' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label>Employé</label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label>Catégorie</label>
                    <select name="category" class="form-control" id="cat-select" onchange="updateRubriques()">
                        <option value="gain">Gain (s'ajoute au brut)</option>
                        <option value="retenue">Retenue (se déduit du brut)</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label>Rubrique</label>
                    <select name="rubrique" class="form-control" id="rub-select" onchange="updateLabel()">
                        <optgroup label="Gains" id="og-gains">
                            <option value="rendement">Prime de rendement</option>
                            <option value="transport">Prime de transport</option>
                            <option value="panier">Prime de panier</option>
                            <option value="logement">Indemnité logement</option>
                            <option value="responsabilite">Indemnité responsabilité</option>
                            <option value="commission">Commission</option>
                            <option value="13eme">13ème mois</option>
                            <option value="bilan">Prime de bilan</option>
                            <option value="autre_gain">Autre gain</option>
                        </optgroup>
                        <optgroup label="Retenues" id="og-retenues" style="display:none">
                            <option value="absence">Absence non justifiée</option>
                            <option value="avance">Avance sur salaire</option>
                            <option value="pret">Remboursement prêt</option>
                            <option value="saisie">Saisie sur salaire</option>
                            <option value="autre_ret">Autre retenue</option>
                        </optgroup>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label>Libellé (sur le bulletin)</label>
                    <input type="text" name="label" id="label-input" class="form-control"
                           value="Prime de rendement" required maxlength="150">
                </div>

                <div style="display:grid;grid-template-columns:2fr 1fr;gap:10px;margin-bottom:16px">
                    <div class="form-group">
                        <label>Montant (MAD)</label>
                        <input type="number" name="amount" class="form-control"
                               placeholder="0.00" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Unité</label>
                        <select name="unit" class="form-control">
                            <option value="MAD">MAD</option>
                            <option value="jours">Jours</option>
                            <option value="heures">Heures</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full">Ajouter l'élément</button>
            </form>
        </div>
    </div>

    {{-- Tableau des éléments --}}
    <div>
        {{-- Résumé --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px">
            <div class="salary-card" style="padding:12px 16px">
                <div class="salary-label">Total gains ajoutés</div>
                <div class="salary-net bonus" style="font-size:1.3rem">
                    +{{ number_format($elements->where('category','gain')->sum('amount'),0,',',' ') }} MAD
                </div>
                <div style="font-size:0.75rem;opacity:0.6">{{ $elements->where('category','gain')->count() }} éléments</div>
            </div>
            <div class="salary-card" style="padding:12px 16px">
                <div class="salary-label">Total retenues ajoutées</div>
                <div class="salary-net deduction" style="font-size:1.3rem">
                    -{{ number_format($elements->where('category','retenue')->sum('amount'),0,',',' ') }} MAD
                </div>
                <div style="font-size:0.75rem;opacity:0.6">{{ $elements->where('category','retenue')->count() }} éléments</div>
            </div>
            <div class="salary-card" style="padding:12px 16px">
                <div class="salary-label">Impact net sur la paie</div>
                <div class="salary-net" style="font-size:1.3rem">
                    {{ number_format($elements->where('category','gain')->sum('amount') - $elements->where('category','retenue')->sum('amount'),0,',',' ') }} MAD
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    Éléments du mois ({{ $elements->count() }})
                </div>
            </div>
            <div class="card-body" style="padding:0">
                @if($elements->isEmpty())
                    <div style="padding:60px;text-align:center;color:var(--text-muted)">
                        <div style="font-size:2.5rem;margin-bottom:12px">📋</div>
                        <div>Aucun élément variable pour cette période.</div>
                        <div style="font-size:0.8rem;margin-top:6px">Utilisez le formulaire pour ajouter des primes ou retenues.</div>
                    </div>
                @else
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Employé</th>
                                <th>Catégorie</th>
                                <th>Rubrique</th>
                                <th>Libellé</th>
                                <th>Montant</th>
                                <th>Unité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($elements as $el)
                            <tr>
                                <td class="font-semibold">{{ $el->employee->full_name }}</td>
                                <td>
                                    <span class="badge badge-{{ $el->category==='gain'?'success':'danger' }}">
                                        {{ $el->category_label }}
                                    </span>
                                </td>
                                <td style="font-size:0.82rem;color:var(--text-muted)">{{ $el->rubrique }}</td>
                                <td>{{ $el->label }}</td>
                                <td class="{{ $el->category==='gain'?'bonus':'deduction' }} font-semibold">
                                    {{ $el->category==='gain'?'+':'-' }}{{ number_format($el->amount,2,',',' ') }}
                                </td>
                                <td style="font-size:0.82rem">{{ $el->unit ?? 'MAD' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('variables.destroy',$el) }}"
                                          onsubmit="return confirm('Supprimer cet élément ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        <div style="margin-top:12px;padding:10px 14px;background:#eff6ff;border-radius:8px;font-size:0.82rem;color:#1e3a5f">
            Ces éléments seront automatiquement intégrés au calcul de la paie lors de la prochaine génération des bulletins pour ce mois.
        </div>
    </div>

</div>

<script>
const rubriquesGain = {
    rendement: 'Prime de rendement', transport: 'Indemnité de transport',
    panier: 'Prime de panier', logement: 'Indemnité logement',
    responsabilite: 'Indemnité de responsabilité', commission: 'Commission mensuelle',
    '13eme': '13ème mois', bilan: 'Prime de bilan', autre_gain: 'Autre gain',
};
const rubriquesRet = {
    absence: 'Absence non justifiée', avance: 'Avance sur salaire',
    pret: 'Remboursement prêt salarié', saisie: 'Saisie sur salaire', autre_ret: 'Autre retenue',
};
function updateRubriques() {
    const cat = document.getElementById('cat-select').value;
    const rub = document.getElementById('rub-select');
    document.getElementById('og-gains').style.display   = cat==='gain'?'':'none';
    document.getElementById('og-retenues').style.display = cat==='retenue'?'':'none';
    rub.selectedIndex = 0;
    updateLabel();
}
function updateLabel() {
    const cat = document.getElementById('cat-select').value;
    const rub = document.getElementById('rub-select').value;
    const map = cat === 'gain' ? rubriquesGain : rubriquesRet;
    document.getElementById('label-input').value = map[rub] || '';
}
</script>

@endsection
