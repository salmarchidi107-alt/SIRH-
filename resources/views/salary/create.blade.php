@extends('layouts.app')

@section('title', 'Saisie Paie — '.$employee->full_name)
@section('page-title', 'Saisie de la Paie')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $employee->full_name }}</h1>
        <p>{{ $employee->department }} — {{ $employee->position }} — Paie {{ \Carbon\Carbon::create($year,$month)->locale('fr')->isoFormat('MMMM YYYY') }}</p>
    </div>
    <a href="{{ route('salary.index', ['month'=>$month,'year'=>$year]) }}" class="btn btn-ghost">← Retour</a>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<form action="{{ route('salary.update', $employee) }}" method="POST">
@csrf
<input type="hidden" name="month" value="{{ $month }}">
<input type="hidden" name="year"  value="{{ $year }}">

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

{{-- ════════════════════════════════════════════════════════════
     COLONNE GAUCHE — GAINS
════════════════════════════════════════════════════════════ --}}
<div>

    {{-- Infos employé --}}
    <div class="card mb-4" style="border-left:3px solid var(--primary)">
        <div class="card-body" style="padding:12px 16px">
            <div style="display:flex;gap:24px;font-size:0.85rem;flex-wrap:wrap">
                <div>
                    <span style="color:var(--text-muted)">Base</span>
                    <strong style="margin-left:8px">{{ number_format($employee->base_salary,0,',',' ') }} MAD</strong>
                </div>
                <div>
                    <span style="color:var(--text-muted)">Ancienneté</span>
                    <strong style="margin-left:8px">{{ $employee->seniority_label }}</strong>
                </div>
                <div>
                    <span style="color:var(--text-muted)">Situation familiale</span>
                    <strong style="margin-left:8px">{{ ucfirst($employee->family_status ?? 'Célibataire') }} — {{ $employee->children_count ?? 0 }} enfant(s)</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TABLEAU GAINS ─────────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header" style="background:#f0fff4;border-bottom:2px solid #d1fae5">
            <div class="card-title" style="color:#065f46">GAINS</div>
            <div style="font-size:0.8rem;color:#059669">Éléments constitutifs du salaire brut</div>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f9fafb">
                        <th style="padding:10px 14px;text-align:left;font-size:0.78rem;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border-color)">Rubrique</th>
                        <th style="padding:10px 14px;text-align:right;font-size:0.78rem;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border-color);width:160px">Montant / Quantité</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Salaire de base --}}
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:10px 14px">
                            <div style="font-weight:600">Salaire de base</div>
                            <div style="font-size:0.78rem;color:var(--text-muted)">Rémunération mensuelle contractuelle</div>
                        </td>
                        <td style="padding:10px 14px">
                            <input type="number" name="base_salary" class="form-control"
                                   value="{{ old('base_salary', $existing?->base_salary ?? $employee->base_salary) }}"
                                   step="0.01" min="0" style="text-align:right">
                            <div style="font-size:0.72rem;color:var(--text-muted);text-align:right;margin-top:2px">MAD</div>
                        </td>
                    </tr>

                    {{-- Prime ancienneté (auto) --}}
                    <tr style="border-bottom:1px solid var(--border-color);background:#f9fafb">
                        <td style="padding:10px 14px">
                            <div style="font-weight:600">Prime d'ancienneté <span class="badge badge-info" style="font-size:0.68rem">Auto</span></div>
                            <div style="font-size:0.78rem;color:var(--text-muted)">
                                {{ $employee->seniority_years }} ans → taux {{ $employee->seniority_rate * 100 }}%
                                (Code du travail marocain)
                            </div>
                        </td>
                        <td style="padding:10px 14px;text-align:right">
                            <div class="font-semibold bonus">
                                {{ number_format($employee->base_salary * $employee->seniority_rate, 2, ',', ' ') }} MAD
                            </div>
                            <div style="font-size:0.72rem;color:var(--text-muted)">Calculé automatiquement</div>
                        </td>
                    </tr>

                    {{-- Heures supplémentaires --}}
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td colspan="2" style="padding:8px 14px;background:#fffbeb">
                            <div style="font-weight:600;font-size:0.85rem;color:#78350f;margin-bottom:8px">
                                Heures supplémentaires
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
                                <div>
                                    <label style="font-size:0.78rem;color:var(--text-muted)">Jour (25%)</label>
                                    <input type="number" name="overtime_day_hours" class="form-control"
                                           value="{{ old('overtime_day_hours', $existing?->overtime_hours ?? 0) }}"
                                           step="0.5" min="0" placeholder="0h">
                                </div>
                                <div>
                                    <label style="font-size:0.78rem;color:var(--text-muted)">Nuit (50%)</label>
                                    <input type="number" name="overtime_night_hours" class="form-control"
                                           value="{{ old('overtime_night_hours', 0) }}"
                                           step="0.5" min="0" placeholder="0h">
                                </div>
                                <div>
                                    <label style="font-size:0.78rem;color:var(--text-muted)">Weekend/Férié (100%)</label>
                                    <input type="number" name="overtime_weekend_hours" class="form-control"
                                           value="{{ old('overtime_weekend_hours', 0) }}"
                                           step="0.5" min="0" placeholder="0h">
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- Prime de rendement --}}
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:10px 14px">
                            <div style="font-weight:600">Prime de rendement</div>
                            <div style="font-size:0.78rem;color:var(--text-muted)">Prime mensuelle liée à la performance</div>
                        </td>
                        <td style="padding:10px 14px">
                            <input type="number" name="performance_bonus" class="form-control"
                                   value="{{ old('performance_bonus', $existing?->performance_bonus ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right">
                        </td>
                    </tr>

                    {{-- Indemnité de transport --}}
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:10px 14px">
                            <div style="font-weight:600">Indemnité de transport</div>
                            <div style="font-size:0.78rem;color:var(--text-muted)">Remboursement frais de déplacement</div>
                        </td>
                        <td style="padding:10px 14px">
                            <input type="number" name="transport_allowance" class="form-control"
                                   value="{{ old('transport_allowance', $existing?->transport_allowance ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right">
                        </td>
                    </tr>

                    {{-- Prime de panier --}}
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:10px 14px">
                            <div style="font-weight:600">Prime de panier</div>
                            <div style="font-size:0.78rem;color:var(--text-muted)">Indemnité repas journalière</div>
                        </td>
                        <td style="padding:10px 14px">
                            <input type="number" name="meal_allowance" class="form-control"
                                   value="{{ old('meal_allowance', $existing?->meal_allowance ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right">
                        </td>
                    </tr>

                    {{-- Indemnité logement --}}
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:10px 14px">
                            <div style="font-weight:600">Indemnité logement</div>
                            <div style="font-size:0.78rem;color:var(--text-muted)">Avantage contractuel logement</div>
                        </td>
                        <td style="padding:10px 14px">
                            <input type="number" name="housing_allowance" class="form-control"
                                   value="{{ old('housing_allowance', $existing?->housing_allowance ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right">
                        </td>
                    </tr>

                    {{-- Indemnité de responsabilité --}}
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:10px 14px">
                            <div style="font-weight:600">Indemnité de responsabilité</div>
                            <div style="font-size:0.78rem;color:var(--text-muted)">Liée à la fonction encadrante</div>
                        </td>
                        <td style="padding:10px 14px">
                            <input type="number" name="responsibility_allowance" class="form-control"
                                   value="{{ old('responsibility_allowance', $existing?->responsibility_allowance ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right">
                        </td>
                    </tr>

                    {{-- Éléments variables gains du mois --}}
                    @if($variableElements->where('category','gain')->count())
                    <tr style="background:#f0fff4">
                        <td colspan="2" style="padding:10px 14px">
                            <div style="font-weight:600;font-size:0.82rem;color:#065f46;margin-bottom:6px">
                                Autres gains (éléments variables saisis)
                            </div>
                            @foreach($variableElements->where('category','gain') as $ve)
                            <div style="display:flex;justify-content:space-between;font-size:0.82rem;padding:3px 0">
                                <span>{{ $ve->label }}</span>
                                <span class="bonus font-semibold">+{{ number_format($ve->amount,2,',',' ') }} MAD</span>
                            </div>
                            @endforeach
                        </td>
                    </tr>
                    @endif

                    {{-- Ligne total gains --}}
                    <tr style="background:#d1fae5">
                        <td style="padding:12px 14px;font-weight:700;color:#065f46">TOTAL GAINS (Salaire Brut)</td>
                        <td style="padding:12px 14px;text-align:right;font-weight:700;color:#065f46;font-size:1.05rem">
                            — Calculé automatiquement
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ════════════════════════════════════════════════════════════
     COLONNE DROITE — RETENUES
════════════════════════════════════════════════════════════ --}}
<div>

    {{-- ── TABLEAU RETENUES ──────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header" style="background:#fff0f0;border-bottom:2px solid #fecaca">
            <div class="card-title" style="color:#991b1b">RETENUES</div>
            <div style="font-size:0.8rem;color:#ef4444">Déductions sur le salaire brut</div>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f9fafb">
                        <th style="padding:10px 14px;text-align:left;font-size:0.78rem;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border-color)">Rubrique</th>
                        <th style="padding:10px 14px;text-align:right;font-size:0.78rem;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border-color);width:160px">Montant</th>
                    </tr>
                </thead>
                <tbody>

                    {{-- Absences --}}
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:10px 14px">
                            <div style="font-weight:600">Absences non payées</div>
                            <div style="font-size:0.78rem;color:var(--text-muted)">
                                Déduction = (Base / 26) × jours absents
                            </div>
                        </td>
                        <td style="padding:10px 14px">
                            <input type="number" name="absence_days" class="form-control"
                                   value="{{ old('absence_days', $existing?->absence_days ?? 0) }}"
                                   step="0.5" min="0" max="31" style="text-align:right">
                            <div style="font-size:0.72rem;color:var(--text-muted);text-align:right;margin-top:2px">Jours</div>
                        </td>
                    </tr>

                    {{-- Avance sur salaire --}}
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:10px 14px">
                            <div style="font-weight:600">Avance sur salaire</div>
                            <div style="font-size:0.78rem;color:var(--text-muted)">Avance accordée le mois courant</div>
                        </td>
                        <td style="padding:10px 14px">
                            <input type="number" name="advance_deduction" class="form-control"
                                   value="{{ old('advance_deduction', $existing?->advance_deduction ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right">
                        </td>
                    </tr>

                    {{-- Remboursement prêt --}}
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:10px 14px">
                            <div style="font-weight:600">Remboursement de prêt salarié</div>
                            <div style="font-size:0.78rem;color:var(--text-muted)">Mensualité prêt entreprise</div>
                        </td>
                        <td style="padding:10px 14px">
                            <input type="number" name="loan_deduction" class="form-control"
                                   value="{{ old('loan_deduction', $existing?->loan_deduction ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right">
                        </td>
                    </tr>

                    {{-- Saisie sur salaire --}}
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:10px 14px">
                            <div style="font-weight:600">Saisie sur salaire</div>
                            <div style="font-size:0.78rem;color:var(--text-muted)">Ordonnance judiciaire</div>
                        </td>
                        <td style="padding:10px 14px">
                            <input type="number" name="garnishment_deduction" class="form-control"
                                   value="{{ old('garnishment_deduction', $existing?->garnishment_deduction ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right">
                        </td>
                    </tr>

                    {{-- Éléments variables retenues --}}
                    @if($variableElements->where('category','retenue')->count())
                    <tr style="background:#fff0f0">
                        <td colspan="2" style="padding:10px 14px">
                            <div style="font-weight:600;font-size:0.82rem;color:#991b1b;margin-bottom:6px">
                                Autres retenues (éléments variables saisis)
                            </div>
                            @foreach($variableElements->where('category','retenue') as $ve)
                            <div style="display:flex;justify-content:space-between;font-size:0.82rem;padding:3px 0">
                                <span>{{ $ve->label }}</span>
                                <span class="deduction font-semibold">-{{ number_format($ve->amount,2,',',' ') }} MAD</span>
                            </div>
                            @endforeach
                        </td>
                    </tr>
                    @endif

                    {{-- Total retenues salariales --}}
                    <tr style="background:#fecaca;border-top:2px solid #f87171">
                        <td style="padding:12px 14px;font-weight:700;color:#991b1b">TOTAL RETENUES SALARIALES</td>
                        <td style="padding:12px 14px;text-align:right;font-weight:700;color:#991b1b">— Calculé</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── COTISATIONS SOCIALES (info) ───────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header" style="background:#eff6ff;border-bottom:2px solid #bfdbfe">
            <div class="card-title" style="color:#1e3a5f">COTISATIONS SOCIALES</div>
            <div style="font-size:0.8rem;color:#2563eb">Calculées automatiquement sur le brut</div>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse;font-size:0.85rem">
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:10px 14px">
                        <div style="font-weight:600">CNSS salariale</div>
                        <div style="font-size:0.78rem;color:var(--text-muted)">Plafonné à 6 000 MAD/mois</div>
                    </td>
                    <td style="padding:10px 14px;text-align:right;color:var(--text-muted)">4,48% du brut plafonné</td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:10px 14px">
                        <div style="font-weight:600">AMO salariale</div>
                        <div style="font-size:0.78rem;color:var(--text-muted)">Assurance maladie obligatoire</div>
                    </td>
                    <td style="padding:10px 14px;text-align:right;color:var(--text-muted)">2,26% du brut</td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:10px 14px">
                        <div style="font-weight:600">Frais professionnels <span class="badge badge-success" style="font-size:0.65rem">Déduction fiscale</span></div>
                        <div style="font-size:0.78rem;color:var(--text-muted)">20% du brut, plafonné à 2 500 MAD/mois</div>
                    </td>
                    <td style="padding:10px 14px;text-align:right;color:var(--text-muted)">-20% (max 2 500)</td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color);background:#f0f9ff">
                    <td style="padding:10px 14px;font-weight:600">Net imposable</td>
                    <td style="padding:10px 14px;text-align:right;color:var(--text-muted)">Brut - CNSS - AMO - FP</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px">
                        <div style="font-weight:600">IR — Impôt sur le revenu</div>
                        <div style="font-size:0.78rem;color:var(--text-muted)">Barème progressif + déductions familiales</div>
                    </td>
                    <td style="padding:10px 14px;text-align:right;color:var(--text-muted)">0% → 38% annuel ÷ 12</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ── CHARGES PATRONALES (info) ─────────────────────────── --}}
    <div class="card mb-4" style="border-left:3px solid #BA7517">
        <div class="card-header">
            <div class="card-title">Charges patronales <span style="font-size:0.8rem;font-weight:400;color:var(--text-muted)">(non déduites du net)</span></div>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse;font-size:0.85rem">
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:8px 14px">CNSS patronale</td>
                    <td style="padding:8px 14px;text-align:right;color:var(--text-muted)">10,29% (plafonné 6 000)</td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:8px 14px">AMO patronale</td>
                    <td style="padding:8px 14px;text-align:right;color:var(--text-muted)">2,26% du brut</td>
                </tr>
                <tr>
                    <td style="padding:8px 14px">TFP (Formation prof.)</td>
                    <td style="padding:8px 14px;text-align:right;color:var(--text-muted)">1,60% du brut</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Boutons --}}
    <div style="display:flex;gap:12px">
        <button type="submit" class="btn btn-primary" style="flex:1;font-size:1rem;padding:12px">
            Calculer & Enregistrer la paie
        </button>
        <a href="{{ route('variables.index', ['month'=>$month,'year'=>$year]) }}"
           class="btn btn-ghost">
            Éléments variables
        </a>
    </div>

</div>
</div>
</form>

@endsection
