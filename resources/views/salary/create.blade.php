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

{{-- ════════════════════════════════════════════════════════════
     MODAL PLANNING DE GARDE
════════════════════════════════════════════════════════════ --}}
<div id="gardeModal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.55);overflow-y:auto">
    <div style="background:white;margin:4% auto 40px;padding:0;border-radius:16px;width:90%;max-width:700px;box-shadow:0 24px 80px rgba(0,0,0,0.3);overflow:hidden">
        <div style="background:linear-gradient(135deg,#0f766e,#2dd4bf);padding:22px 28px;display:flex;justify-content:space-between;align-items:center">
            <div>
                <div style="color:white;font-size:1.15rem;font-weight:700;display:flex;align-items:center;gap:8px">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Planning de Garde
                </div>
                <div style="color:rgba(255,255,255,0.8);font-size:0.82rem;margin-top:3px">
                    {{ $employee->full_name }} — {{ \Carbon\Carbon::create($year,$month)->locale('fr')->isoFormat('MMMM YYYY') }}
                </div>
            </div>
            <button onclick="closeGardeModal()" style="background:rgba(255,255,255,0.2);border:none;color:white;width:34px;height:34px;border-radius:50%;font-size:1.3rem;cursor:pointer;display:flex;align-items:center;justify-content:center;" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">×</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1px;background:#e5e7eb">
            <div style="background:#f0fdfa;padding:16px 20px;text-align:center">
                <div style="font-size:0.7rem;color:#0f766e;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px">Jours de garde</div>
                <div style="font-size:2.2rem;font-weight:900;color:#0f766e" id="garde-count">0</div>
                <div style="font-size:0.72rem;color:#14b8a6;margin-top:2px">ce mois</div>
            </div>
            <div style="background:#f0fdfa;padding:16px 20px;text-align:center">
                <div style="font-size:0.7rem;color:#0f766e;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px">Total heures</div>
                <div style="font-size:2.2rem;font-weight:900;color:#0f766e" id="garde-total-h">0 h</div>
                <div style="font-size:0.72rem;color:#14b8a6;margin-top:2px">heures de garde</div>
            </div>
            <div style="background:#f0fdfa;padding:16px 20px;text-align:center">
                <div style="font-size:0.7rem;color:#0f766e;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px">Indemnité estimée</div>
                <div style="font-size:1.6rem;font-weight:900;color:#0f766e" id="garde-total-amt">0,00 MAD</div>
                <div style="font-size:0.72rem;color:#14b8a6;margin-top:2px">taux horaire × heures</div>
            </div>
        </div>
        <div style="padding:22px 28px">
            <div style="display:grid;grid-template-columns:130px 1fr 90px 90px 110px;gap:8px;padding:8px 14px;background:#ccfbf1;border-radius:8px;font-size:0.71rem;font-weight:700;color:#0f766e;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px">
                <div>Date</div><div>Horaires</div><div>Durée</div><div>Salle</div><div style="text-align:right">Indemnité</div>
            </div>
            <div id="garde-list" style="display:flex;flex-direction:column;gap:6px">
                <div style="text-align:center;padding:32px;color:#94a3b8">Chargement...</div>
            </div>
            <div id="garde-total-row" style="display:none;margin-top:10px">
                <div style="display:grid;grid-template-columns:130px 1fr 90px 90px 110px;gap:8px;padding:12px 14px;background:linear-gradient(135deg,#0f766e,#2dd4bf);border-radius:8px;align-items:center">
                    <div style="color:white;font-weight:700;font-size:0.85rem;grid-column:1/5">TOTAL DU MOIS</div>
                    <div style="text-align:right;color:white;font-weight:900;font-size:1.05rem" id="garde-total-final">0,00 MAD</div>
                </div>
            </div>
        </div>
        <div style="padding:14px 28px 20px;border-top:1px solid #ccfbf1;background:#f0fdfa;display:flex;justify-content:space-between;align-items:center">
            <div style="font-size:0.78rem;color:#0f766e;font-style:italic">* Montant calculé sur la base du taux horaire du bulletin en cours</div>
            <button onclick="closeGardeModal()" class="btn btn-ghost">Fermer</button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     MODAL DÉTAILS — Heures travaillées / HS / Absences / Retards
════════════════════════════════════════════════════════════ --}}
<div id="detailModal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.55);overflow-y:auto">
    <div style="background:white;margin:4% auto 40px;padding:0;border-radius:16px;width:90%;max-width:680px;box-shadow:0 24px 80px rgba(0,0,0,0.3);overflow:hidden">
        <div id="detailModalHeader" style="padding:22px 28px;display:flex;justify-content:space-between;align-items:center">
            <div>
                <div id="detailModalTitle" style="color:white;font-size:1.15rem;font-weight:700"></div>
                <div style="color:rgba(255,255,255,0.8);font-size:0.82rem;margin-top:3px">
                    {{ $employee->full_name }} — {{ \Carbon\Carbon::create($year,$month)->locale('fr')->isoFormat('MMMM YYYY') }}
                </div>
            </div>
            <button onclick="closeDetailModal()" style="background:rgba(255,255,255,0.2);border:none;color:white;width:34px;height:34px;border-radius:50%;font-size:1.3rem;cursor:pointer;display:flex;align-items:center;justify-content:center">×</button>
        </div>
        <div id="detailModalStats" style="display:grid;gap:1px;background:#e5e7eb"></div>
        <div style="padding:20px 28px">
            <div id="detailModalTableHeader"></div>
            <div id="detailModalList" style="display:flex;flex-direction:column;gap:6px">
                <div style="text-align:center;padding:32px;color:#94a3b8">Chargement...</div>
            </div>
        </div>
        <div style="padding:14px 28px 20px;border-top:1px solid #e5e7eb;background:#f9fafb;display:flex;justify-content:flex-end">
            <button onclick="closeDetailModal()" class="btn btn-ghost">Fermer</button>
        </div>
    </div>
</div>

<script>
const EMPLOYEE_DATA = {
    base_salary:     {{ (float) $employee->base_salary }},
    seniority_years: {{ (int) ($employee->seniority_years ?? 0) }},
    seniority_rate:  {{ (float) ($employee->seniority_rate ?? 0) }},
    family_status:   '{{ $employee->family_status ?? 'celibataire' }}',
    children_count:  {{ (int) ($employee->children_count ?? 0) }},
    working_hours:   {{ (float) ($workingData['working_hours'] ?? 191.25) }},
    ot_day:          {{ (float) ($workingData['overtime_day'] ?? 0) }},
    ot_night:        {{ (float) ($workingData['overtime_night'] ?? 0) }},
    ot_weekend:      {{ (float) ($workingData['overtime_weekend'] ?? 0) }},
    absence_hours:   {{ (float) ($workingData['absence_hours'] ?? 0) }},
    delay_hours:     {{ (float) ($workingData['delay_hours'] ?? 0) }},
    garde_hours:     {{ (float) ($workingData['garde_hours'] ?? 0) }},
    garde_days:      {{ (int)   ($workingData['garde_days']  ?? 0) }},
};
const EXISTING = {
    salary_type:              '{{ $existing?->salary_type ?? 'monthly' }}',
    hourly_rate:              {{ (float) ($existing?->hourly_rate ?? 0) }},
    base_salary:              {{ (float) ($existing?->base_salary ?? $employee->base_salary) }},
    performance_bonus:        {{ (float) ($existing?->performance_bonus ?? 0) }},
    transport_allowance:      {{ (float) ($existing?->transport_allowance ?? 0) }},
    meal_allowance:           {{ (float) ($existing?->meal_allowance ?? 0) }},
    housing_allowance:        {{ (float) ($existing?->housing_allowance ?? 0) }},
    responsibility_allowance: {{ (float) ($existing?->responsibility_allowance ?? 0) }},
    other_gains:              {{ (float) ($existing?->other_gains ?? 0) }},
    advance_deduction:        {{ (float) ($existing?->advance_deduction ?? 0) }},
    loan_deduction:           {{ (float) ($existing?->loan_deduction ?? 0) }},
    garnishment_deduction:    {{ (float) ($existing?->garnishment_deduction ?? 0) }},
    other_deductions:         {{ (float) ($existing?->other_deductions ?? 0) }},
    mode_cotisation:          '{{ $existing?->mode_cotisation ?? 'auto' }}',
    cnss_deduction_manual:    {{ (float) ($existing?->cnss_deduction_manual ?? 0) }},
    amo_deduction_manual:     {{ (float) ($existing?->amo_deduction_manual ?? 0) }},
    fp_deduction_manual:      {{ (float) ($existing?->fp_deduction_manual ?? 0) }},
    currency:                 '{{ $existing?->currency ?? 'MAD' }}',
    garde_indemnite:          {{ (float) ($existing?->garde_indemnite ?? 0) }},
    garde_override:           {{ (int)   ($existing?->garde_override   ?? 0) }},
};
const GARDE_SHIFTS    = @json($workingData['garde_shifts']    ?? []);
const WORKING_SHIFTS  = @json($workingData['pointage_shifts'] ?? []);
const OVERTIME_SHIFTS = @json($workingData['overtime_shifts'] ?? []);
const ABSENCE_SHIFTS  = @json($workingData['absence_shifts']  ?? []);
const DELAY_SHIFTS    = @json($workingData['delay_shifts']    ?? []);
</script>

<form action="{{ route('salary.update', $employee) }}" method="POST" id="salaryForm">
@csrf
<input type="hidden" name="month"  value="{{ $month }}">
<input type="hidden" name="year"   value="{{ $year }}">
@if($existing)
<input type="hidden" name="salary_id" value="{{ $existing->id }}">
@endif
<input type="hidden" name="gross_salary"            id="h_gross_salary">
<input type="hidden" name="seniority_bonus"         id="h_seniority_bonus">
<input type="hidden" name="overtime_day_amount"     id="h_ot_day_amount">
<input type="hidden" name="overtime_night_amount"   id="h_ot_night_amount">
<input type="hidden" name="overtime_weekend_amount" id="h_ot_wknd_amount">
<input type="hidden" name="overtime_hours"          id="h_overtime_hours">
<input type="hidden" name="absence_deduction"       id="h_absence_deduction">
<input type="hidden" name="absence_days"            id="h_absence_days">
<input type="hidden" name="cnss_base"               id="h_cnss_base">
<input type="hidden" name="cnss_deduction"          id="h_cnss_deduction">
<input type="hidden" name="amo_deduction"           id="h_amo_deduction">
<input type="hidden" name="fp_deduction"            id="h_fp_deduction">
<input type="hidden" name="taxable_income"          id="h_taxable_income">
<input type="hidden" name="ir_annual"               id="h_ir_annual">
<input type="hidden" name="ir_family_deduction"     id="h_ir_family_deduction">
<input type="hidden" name="ir_deduction"            id="h_ir_deduction">
<input type="hidden" name="net_salary"              id="h_net_salary">
<input type="hidden" name="employer_cnss"           id="h_employer_cnss">
<input type="hidden" name="employer_amo"            id="h_employer_amo">
<input type="hidden" name="employer_tfp"            id="h_employer_tfp">
<input type="hidden" name="employer_total_cost"     id="h_employer_total_cost">
<input type="hidden" name="overtime_hours_day"      id="h_ot_day_h">
<input type="hidden" name="overtime_hours_night"    id="h_ot_night_h">
<input type="hidden" name="overtime_hours_weekend"  id="h_ot_wknd_h">
<input type="hidden" name="working_hours"           id="h_working_hours">
<input type="hidden" name="absence_hours"           id="h_abs_hours">
<input type="hidden" name="delay_hours"             id="h_delay_hours">
<input type="hidden" name="garde_hours"             id="h_garde_hours">
<input type="hidden" name="hourly_rate"             id="h_hourly_rate">
<input type="hidden" name="currency"                id="h_currency" value="MAD">
<input type="hidden" name="garde_indemnite"         id="h_garde_indemnite" value="{{ old('garde_indemnite', $existing?->garde_indemnite ?? 0) }}">
<input type="hidden" name="garde_override"          id="h_garde_override"  value="{{ old('garde_override',  $existing?->garde_override  ?? 0) }}">

{{-- ════════════════════════════════════════════════════════════
     SECTION 1 — TEMPS DE TRAVAIL
════════════════════════════════════════════════════════════ --}}
<div class="card mb-4" style="border-left:4px solid var(--primary)">
    <div class="card-header" style="border:none;padding:16px 20px">
        <div class="card-title" style="font-size:1.05rem;color:#0066cc">TEMPS DE TRAVAIL — {{ \Carbon\Carbon::create($year,$month)->locale('fr')->translatedFormat('F Y') }}</div>
        <div style="font-size:0.8rem;color:var(--text-muted)">Données extraites automatiquement du pointage — cliquer sur une carte pour les détails</div>
    </div>
    <div class="card-body" style="padding:0">
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:1px;background:var(--border-color)">

            {{-- Heures travaillées --}}
            <div onclick="openDetailModal('working')"
                 style="background:var(--surface,white);padding:14px 16px;cursor:pointer;transition:background .2s"
                 onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='var(--surface,white)'">
                <div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;margin-bottom:4px;display:flex;align-items:center;gap:4px">
                    Heures travaillées
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div style="font-size:1.6rem;font-weight:700;color:#065f46" id="disp-working">{{ $workingData['working_hours'] ?? 0 }} h</div>
                <div style="font-size:0.7rem;color:#10b981;margin-top:2px">Voir pointages →</div>
            </div>

            {{-- H. supp jour --}}
            <div onclick="openDetailModal('overtime')"
                 style="background:var(--surface,white);padding:14px 16px;cursor:pointer;transition:background .2s"
                 onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background='var(--surface,white)'">
                <div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;margin-bottom:4px;display:flex;align-items:center;gap:4px">
                    H. supp jour (25%)
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div style="font-size:1.6rem;font-weight:700;color:#d97706" id="disp-ot-day">{{ $workingData['overtime_day'] ?? 0 }} h</div>
                <div style="font-size:0.7rem;color:#f59e0b;margin-top:2px">Voir détails →</div>
            </div>

            {{-- Heures absence --}}
            <div onclick="openDetailModal('absence')"
                 style="background:var(--surface,white);padding:14px 16px;cursor:pointer;transition:background .2s"
                 onmouseover="this.style.background='#fff1f2'" onmouseout="this.style.background='var(--surface,white)'">
                <div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;margin-bottom:4px;display:flex;align-items:center;gap:4px">
                    Heures absence
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div style="font-size:1.6rem;font-weight:700;color:#ef4444" id="disp-abs">{{ $workingData['absence_hours'] ?? 0 }} h</div>
                <div style="font-size:0.7rem;color:#f87171;margin-top:2px">Voir absences →</div>
            </div>

            {{-- Heures retard --}}
            <div onclick="openDetailModal('delay')"
                 style="background:var(--surface,white);padding:14px 16px;cursor:pointer;transition:background .2s"
                 onmouseover="this.style.background='#fdf4ff'" onmouseout="this.style.background='var(--surface,white)'">
                <div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;margin-bottom:4px;display:flex;align-items:center;gap:4px">
                    Heures retard
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div style="font-size:1.6rem;font-weight:700;color:#ec4899" id="disp-delay">{{ $workingData['delay_hours'] ?? 0 }} h</div>
                <div style="font-size:0.7rem;color:#f472b6;margin-top:2px">Voir retards →</div>
            </div>

            {{-- Jours de garde --}}
            <div style="background:#f0fdfa;padding:14px 16px;cursor:pointer;transition:background 0.2s;border-left:3px solid #0f766e;position:relative"
                 onclick="openGardeModal()"
                 onmouseover="this.style.background='#f3e8ff'"
                 onmouseout="this.style.background='#f0fdfa'">
                <div style="font-size:0.75rem;color:#0f766e;font-weight:600;margin-bottom:4px;display:flex;align-items:center;gap:4px">
                    Jours de garde
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#0f766e" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div style="font-size:1.6rem;font-weight:700;color:#0f766e" id="disp-garde-days">{{ $workingData['garde_days'] ?? 0 }}<span style="font-size:1rem;font-weight:500"> j</span></div>
                <div style="font-size:0.72rem;color:#2dd4bf;margin-top:3px" id="disp-garde-sub">{{ $workingData['garde_hours'] ?? 0 }} h au total</div>
                <div style="position:absolute;bottom:6px;right:8px;font-size:0.65rem;color:#c084fc;font-weight:600">Voir détails →</div>
            </div>

        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     SECTION 2 — SYSTÈME DE PAIE
════════════════════════════════════════════════════════════ --}}
<div class="card mb-4" style="border-left:4px solid #0f766e">
    <div class="card-header" style="border:none;padding:14px 20px">
        <div class="card-title" style="font-size:1.0rem;color:#0f766e">SYSTÈME DE PAIE &amp; TYPE DE SALAIRE</div>
    </div>
    <div class="card-body" style="padding:12px 20px">
        <div style="display:flex;gap:24px;align-items:center;flex-wrap:wrap">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="radio" name="salary_type" value="monthly" id="type_monthly" style="cursor:pointer;width:15px;height:15px" onchange="onTypeChange()">
                <strong>Salaire mensuel</strong>
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="radio" name="salary_type" value="hourly" id="type_hourly" style="cursor:pointer;width:15px;height:15px" onchange="onTypeChange()">
                <strong>Salaire horaire</strong>
            </label>
            <div style="display:flex;align-items:center;gap:8px">
                <label style="font-size:0.85rem;color:var(--text-muted);white-space:nowrap">Taux horaire</label>
                <input type="number" name="hourly_rate_display" id="hourly_rate" class="form-control"
                       value="{{ $existing?->hourly_rate ?? $employee->hourly_rate ?? 0 }}"
                       step="0.01" min="0" style="width:130px;text-align:right" disabled oninput="calculate()">
            </div>
            <div style="width:1px;height:32px;background:var(--border-color);margin:0 4px"></div>
            <div style="display:flex;align-items:center;gap:10px">
                <label style="font-size:0.85rem;color:var(--text-muted);white-space:nowrap;font-weight:600">Système</label>
                <div style="display:flex;border:2px solid #e5e7eb;border-radius:8px;overflow:hidden">
                    <button type="button" id="btn-mad" onclick="setSystem('MAD')" style="padding:9px 22px;font-weight:700;font-size:0.9rem;border:none;cursor:pointer;background:#1d4ed8;color:white;transition:all 0.2s;border-right:1px solid #e5e7eb">🇲🇦 MAD — Maroc</button>
                    <button type="button" id="btn-mru" onclick="setSystem('MRU')" style="padding:9px 22px;font-weight:700;font-size:0.9rem;border:none;cursor:pointer;background:#f9fafb;color:#6b7280;transition:all 0.2s">🇲🇷 MRU — Mauritanie</button>
                </div>
                <span id="system-badge" style="font-size:0.78rem;padding:4px 12px;border-radius:20px;background:#dbeafe;color:#1e40af;border:1px solid #bfdbfe;font-weight:700;white-space:nowrap">Système marocain actif</span>
            </div>
        </div>
        <div id="mad-info-banner" style="margin-top:10px;padding:10px 14px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;font-size:0.82rem;color:#1e3a5f">
            <strong>🇲🇦 Système marocain</strong> — CNSS sal. <strong>4,48%</strong> (plafond 6 000 MAD) + AMO <strong>2,26%</strong> + FP <strong>20%</strong> max 2 500 MAD | IR DGI barème annuel ÷ 12 | Patronal : CNSS <strong>10,29%</strong> + AMO <strong>2,26%</strong> + TFP <strong>1,60%</strong>
        </div>
        <div id="mru-info-banner" style="display:none;margin-top:10px;padding:10px 14px;background:#f0fdf4;border:1px solid #86efac;border-radius:6px;font-size:0.82rem;color:#14532d">
            <strong>🇲🇷 Système mauritanien</strong> — CNSS sal. <strong>1%</strong> (plafond 15 000 MRU) + CNAM <strong>4%</strong> | ITS mensuel progressif | Patronal : CNSS <strong>13%</strong> + CNAM <strong>2%</strong>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

{{-- ════════════════════════════════════════════════════════════
     COLONNE GAUCHE — GAINS
════════════════════════════════════════════════════════════ --}}
<div>
    <div class="card mb-4" style="border-left:3px solid var(--primary)">
        <div class="card-body" style="padding:10px 16px">
            <div style="display:flex;gap:20px;font-size:0.83rem;flex-wrap:wrap">
                <div><span style="color:var(--text-muted)">Base contrat</span> <strong style="margin-left:6px">{{ number_format($employee->base_salary,0,',',' ') }} <span class="cur-label">MAD</span></strong></div>
                <div><span style="color:var(--text-muted)">Ancienneté</span> <strong style="margin-left:6px">{{ $employee->seniority_label }}</strong></div>
                <div><span style="color:var(--text-muted)">Situation</span> <strong style="margin-left:6px">{{ ucfirst($employee->family_status ?? 'Célibataire') }} — {{ $employee->children_count ?? 0 }} enfant(s)</strong></div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header" style="background:#f0fff4;border-bottom:2px solid #d1fae5">
            <div class="card-title" style="color:#065f46">GAINS</div>
            <div style="font-size:0.78rem;color:#059669">Éléments constitutifs du salaire brut</div>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f9fafb">
                        <th style="padding:9px 14px;text-align:left;font-size:0.75rem;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border-color)">Rubrique</th>
                        <th style="padding:9px 14px;text-align:right;font-size:0.75rem;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border-color);width:155px">Montant (<span class="cur-label">MAD</span>)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px">
                            <div style="font-weight:600">Salaire de base</div>
                            <div style="font-size:0.75rem;color:var(--text-muted)" id="base-sub">Rémunération mensuelle contractuelle</div>
                        </td>
                        <td style="padding:9px 14px">
                            <input type="number" name="base_salary" id="base_salary" class="form-control"
                                   value="{{ old('base_salary', $existing?->base_salary ?? $employee->base_salary) }}"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color);background:#f9fafb">
                        <td style="padding:9px 14px">
                            <div style="font-weight:600">Prime d'ancienneté</div>
                        </td>
                        <td style="padding:9px 14px">
                            <input type="number" name="seniority_bonus" id="seniority_bonus" class="form-control"
                                   value="{{ old('seniority_bonus', $existing?->seniority_bonus ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                            <div style="font-size:0.72rem;color:var(--text-muted);margin-top:4px">{{ $employee->seniority_years }} an(s) → {{ ($employee->seniority_rate * 100) }}%</div>
                        </td>
                    </tr>

                    {{-- Heures supplémentaires --}}
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td colspan="2" style="padding:0">
                            <div style="padding:8px 14px;font-weight:600;font-size:0.82rem;color:#92400e;background:#fffbeb;border-bottom:1px solid #fde68a">
                                Heures supplémentaires <span style="font-weight:400;font-size:0.75rem;color:var(--text-muted)">— taux horaire × majoration</span>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;background:#fffbeb">
                                <div style="padding:10px 14px;border-right:1px solid #fde68a">
                                    <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px">Jour +25%</div>
                                    <div style="font-size:1.1rem;font-weight:700;color:#d97706" id="ot-day-h-disp">{{ $workingData['overtime_day'] ?? 0 }} h</div>
                                    <div style="font-size:0.72rem;color:var(--text-muted)" id="ot-day-amt-disp">= 0,00 <span class="cur-label">MAD</span></div>
                                </div>
                                <div style="padding:10px 14px;border-right:1px solid #fde68a">
                                    <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px">Nuit +50%</div>
                                    <div style="font-size:0.9rem;font-weight:700;color:#d97706" id="ot-night-h-disp">{{ $workingData['overtime_night'] ?? 0 }} h</div>
                                    <input type="number" id="ot_night_h" class="form-control" value="{{ $workingData['overtime_night'] ?? 0 }}" step="0.5" min="0" style="margin-top:4px;font-size:0.8rem" oninput="calculate()">
                                    <div style="font-size:0.72rem;color:var(--text-muted)" id="ot-night-amt-disp">= 0,00 <span class="cur-label">MAD</span></div>
                                </div>
                                <div style="padding:10px 14px">
                                    <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px">Weekend +100%</div>
                                    <div style="font-size:0.9rem;font-weight:700;color:#d97706" id="ot-wknd-h-disp">{{ $workingData['overtime_weekend'] ?? 0 }} h</div>
                                    <input type="number" id="ot_wknd_h" class="form-control" value="{{ $workingData['overtime_weekend'] ?? 0 }}" step="0.5" min="0" style="margin-top:4px;font-size:0.8rem" oninput="calculate()">
                                    <div style="font-size:0.72rem;color:var(--text-muted)" id="ot-wknd-amt-disp">= 0,00 <span class="cur-label">MAD</span></div>
                                </div>
                            </div>
                            <div style="padding:6px 14px;background:#fffbeb;border-top:1px solid #fde68a;display:flex;justify-content:space-between;font-size:0.82rem">
                                <span style="color:var(--text-muted)">Total HS</span>
                                <span style="font-weight:700;color:#d97706" id="ot-total-disp">0,00 <span class="cur-label">MAD</span></span>
                            </div>
                        </td>
                    </tr>

                    {{-- Indemnité de garde --}}
                    <tr style="border-bottom:1px solid var(--border-color);background:#f0fdfa">
                        <td style="padding:9px 14px">
                            <div style="font-weight:600;color:#0f766e;display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                                Indemnité de garde
                                <button type="button" onclick="openGardeModal()"
                                        style="background:#ccfbf1;border:none;color:#0f766e;padding:2px 10px;border-radius:20px;font-size:0.68rem;cursor:pointer;font-weight:700;"
                                        onmouseover="this.style.background='#99f6e4'"
                                        onmouseout="this.style.background='#ccfbf1'">
                                     {{ $workingData['garde_days'] ?? 0 }} j — Voir planning
                                </button>
                                <span id="garde-override-badge"
                                      style="display:none;background:#fef3c7;border:1px solid #fcd34d;color:#92400e;
                                             padding:1px 8px;border-radius:20px;font-size:0.65rem;font-weight:700;">
                                    ✎ modifié
                                </span>
                            </div>
                            <div style="font-size:0.72rem;color:#14b8a6;margin-top:2px">
                                {{ $workingData['garde_hours'] ?? 0 }} h × taux horaire
                            </div>
                        </td>
                        <td style="padding:9px 14px">
                            <div id="garde-gain-display"
                                 onclick="toggleGardeEdit(true)"
                                 title="Cliquer pour modifier manuellement"
                                 style="font-weight:700;color:#0f766e;font-size:1rem;cursor:pointer;
                                        padding:6px 10px;border-radius:8px;text-align:right;
                                        border:1px dashed rgba(13,118,110,0.35);transition:all 0.2s;"
                                 onmouseover="this.style.background='#ccfbf1'"
                                 onmouseout="if(!gardeEditMode)this.style.background='transparent'">
                                0,00 <span class="cur-label">MAD</span>
                            </div>
                            <div id="garde-input-wrap" style="display:none">
                                <input type="number" id="garde_indemnite_input" class="form-control"
                                       step="0.01" min="0"
                                       style="text-align:right;border-color:#0f766e;border-width:2px"
                                       oninput="onGardeManualChange()"
                                       placeholder="0.00">
                                <div style="display:flex;gap:6px;margin-top:5px">
                                    <button type="button" onclick="resetGardeAuto()"
                                            style="flex:1;font-size:0.72rem;padding:4px 8px;border:1px solid #2dd4bf;background:white;color:#0f766e;border-radius:6px;cursor:pointer;font-weight:600">
                                        ↺ Auto
                                    </button>
                                    <button type="button" onclick="toggleGardeEdit(false)"
                                            style="flex:1;font-size:0.72rem;padding:4px 8px;border:1px solid #0f766e;background:#0f766e;color:white;border-radius:6px;cursor:pointer;font-weight:600">
                                        ✓ OK
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Prime de rendement</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="performance_bonus" id="performance_bonus" class="form-control"
                                   value="{{ old('performance_bonus', $existing?->performance_bonus ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Indemnité de transport</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="transport_allowance" id="transport_allowance" class="form-control"
                                   value="{{ old('transport_allowance', $existing?->transport_allowance ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Prime de panier</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="meal_allowance" id="meal_allowance" class="form-control"
                                   value="{{ old('meal_allowance', $existing?->meal_allowance ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Indemnité logement</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="housing_allowance" id="housing_allowance" class="form-control"
                                   value="{{ old('housing_allowance', $existing?->housing_allowance ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Indemnité de responsabilité</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="responsibility_allowance" id="responsibility_allowance" class="form-control"
                                   value="{{ old('responsibility_allowance', $existing?->responsibility_allowance ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Autres gains</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="other_gains" id="other_gains" class="form-control"
                                   value="{{ old('other_gains', $existing?->other_gains ?? 0) }}"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>

                    @if($variableElements->where('category','gain')->count())
                    <tr style="background:#f0fff4">
                        <td colspan="2" style="padding:9px 14px">
                            <div style="font-weight:600;font-size:0.78rem;color:#065f46;margin-bottom:5px">Éléments variables (gains)</div>
                            @foreach($variableElements->where('category','gain') as $ve)
                            <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;padding:3px 0;gap:8px">
                                <span>{{ $ve->label }}</span>
                                @if(str_contains(strtolower($ve->label), 'garde'))
                                <div style="display:flex;align-items:center;gap:5px">
                                    <input type="number"
                                           id="ve_garde_input"
                                           name="ve_garde_amount"
                                           class="form-control"
                                           value="{{ number_format($ve->amount, 2, '.', '') }}"
                                           step="0.01" min="0"
                                           data-ve-id="{{ $ve->id }}"
                                           style="width:110px;text-align:right;font-size:0.8rem;border-color:#0f766e;background:#f0fdfa"
                                           oninput="onVeGardeChange()">
                                    <span class="cur-label" style="color:#059669;font-weight:600;white-space:nowrap">MAD</span>
                                </div>
                                @else
                                <span class="bonus font-semibold" style="white-space:nowrap">
                                    +{{ number_format($ve->amount,2,',',' ') }} <span class="cur-label">MAD</span>
                                </span>
                                @endif
                            </div>
                            @endforeach
                        </td>
                    </tr>
                    @endif

                    <tr style="background:#d1fae5">
                        <td style="padding:11px 14px;font-weight:700;color:#065f46">SALAIRE BRUT</td>
                        <td style="padding:11px 14px;text-align:right;font-weight:700;color:#065f46;font-size:1.05rem">
                            <span id="gross-display">0,00</span> <span class="cur-label">MAD</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     COLONNE DROITE
════════════════════════════════════════════════════════════ --}}
<div>
    <div class="card mb-4">
        <div class="card-header" style="background:#eff6ff;border-bottom:2px solid #bfdbfe">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px">
                <div>
                    <div class="card-title" style="color:#1e3a5f">COTISATIONS SOCIALES</div>
                    <div style="font-size:0.75rem;color:#2563eb" id="cot-subtitle">Mode automatique = taux légaux marocains</div>
                </div>
                <div style="display:flex;gap:4px;background:white;padding:3px;border-radius:4px;border:1px solid #dbeafe">
                    <label style="padding:5px 11px;cursor:pointer;font-weight:600;border-radius:3px;font-size:0.82rem" id="autoLabel">
                        <input type="radio" name="mode_cotisation" value="auto" style="cursor:pointer;margin-right:4px" onchange="toggleCotisationMode()"> Automatique
                    </label>
                    <label style="padding:5px 11px;cursor:pointer;font-weight:600;border-radius:3px;font-size:0.82rem" id="manuelLabel">
                        <input type="radio" name="mode_cotisation" value="manual" style="cursor:pointer;margin-right:4px" onchange="toggleCotisationMode()"> Manuel
                    </label>
                </div>
            </div>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse;font-size:0.85rem">
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:9px 14px"><div style="font-weight:600" id="cnss-label">CNSS salariale</div><div style="font-size:0.75rem;color:var(--text-muted)" id="cnss-sub">4,48% × brut plafonné à 6 000 MAD/mois</div></td>
                    <td style="padding:9px 14px;width:155px;text-align:right">
                        <div id="cnss-auto" style="font-weight:600;padding:6px 0">0,00 <span class="cur-label">MAD</span></div>
                        <input type="number" name="cnss_deduction_manual" id="cnss-manual" class="form-control" value="{{ $existing?->cnss_deduction_manual ?? 0 }}" step="0.01" min="0" style="display:none;text-align:right" oninput="calculate()">
                    </td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:9px 14px"><div style="font-weight:600" id="amo-label">AMO salariale</div><div style="font-size:0.75rem;color:var(--text-muted)" id="amo-sub">2,26% du salaire brut</div></td>
                    <td style="padding:9px 14px;text-align:right">
                        <div id="amo-auto" style="font-weight:600;padding:6px 0">0,00 <span class="cur-label">MAD</span></div>
                        <input type="number" name="amo_deduction_manual" id="amo-manual" class="form-control" value="{{ $existing?->amo_deduction_manual ?? 0 }}" step="0.01" min="0" style="display:none;text-align:right" oninput="calculate()">
                    </td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color);background:#f0f9ff" id="row-fp">
                    <td style="padding:9px 14px"><div style="font-weight:600">Frais professionnels <span class="badge badge-success" style="font-size:0.62rem">Déduction fiscale</span></div><div style="font-size:0.75rem;color:var(--text-muted)">20% du brut, plafonné à 2 500 MAD/mois</div></td>
                    <td style="padding:9px 14px;text-align:right">
                        <div id="fp-auto" style="font-weight:600;color:#059669;padding:6px 0">0,00 <span class="cur-label">MAD</span></div>
                        <input type="number" name="fp_deduction_manual" id="fp-manual" class="form-control" value="{{ $existing?->fp_deduction_manual ?? 0 }}" step="0.01" min="0" style="display:none;text-align:right" oninput="calculate()">
                    </td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color);background:#f0f9ff">
                    <td style="padding:9px 14px;font-weight:600;color:#1e3a5f" id="taxable-label">Net imposable (NI)</td>
                    <td style="padding:9px 14px;text-align:right;font-weight:700;color:#1e3a5f" id="taxable-display">0,00 <span class="cur-label">MAD</span></td>
                </tr>
                <tr style="background:#dbeafe;border-top:2px solid #0284c7">
                    <td style="padding:11px 14px;font-weight:700;color:#1e3a5f" id="cot-total-label">TOTAL COTISATIONS (CNSS+AMO)</td>
                    <td style="padding:11px 14px;text-align:right;font-weight:700;color:#1e3a5f;font-size:1rem" id="cot-total-display">0,00 <span class="cur-label">MAD</span></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header" style="background:#fef3c7;border-bottom:2px solid #fcd34d">
            <div class="card-title" style="color:#78350f" id="ir-title">IR — Impôt sur le Revenu</div>
            <div style="font-size:0.75rem;color:#92400e" id="ir-subtitle">Barème progressif annuel DGI ÷ 12</div>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse;font-size:0.83rem">
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:8px 14px;color:var(--text-muted)" id="ir-annual-label">IR annuel brut (barème)</td>
                    <td style="padding:8px 14px;text-align:right;font-weight:600" id="ir-annual">0,00 <span class="cur-label">MAD</span></td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)" id="row-ir-family">
                    <td style="padding:8px 14px;color:var(--text-muted)" id="ir-family-label">Déductions familiales (360 MAD/pers.)</td>
                    <td style="padding:8px 14px;text-align:right;font-weight:600;color:#059669" id="ir-family">−0,00 <span class="cur-label">MAD</span></td>
                </tr>
                <tr style="background:#fef3c7">
                    <td style="padding:9px 14px;font-weight:700;color:#78350f" id="ir-monthly-label">IR mensuel retenu</td>
                    <td style="padding:9px 14px;text-align:right;font-weight:700;color:#991b1b" id="ir-monthly">0,00 <span class="cur-label">MAD</span></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header" style="background:#fff0f0;border-bottom:2px solid #fecaca">
            <div class="card-title" style="color:#991b1b">RETENUES</div>
            <div style="font-size:0.75rem;color:#ef4444">Déductions diverses sur salaire</div>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f9fafb">
                        <th style="padding:9px 14px;text-align:left;font-size:0.75rem;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border-color)">Rubrique</th>
                        <th style="padding:9px 14px;text-align:right;font-size:0.75rem;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border-color);width:155px">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px">
                            <div style="font-weight:600">Absences non payées</div>
                            <div style="font-size:0.75rem;color:var(--text-muted)" id="absence-sub">(Brut / <span id="heures-ref-label">191,25</span> h) × {{ $workingData['absence_hours'] ?? 0 }} h = calculé auto</div>
                        </td>
                        <td style="padding:9px 14px;text-align:right"><div style="font-weight:600;color:#991b1b" id="absence-auto">0,00 <span class="cur-label">MAD</span></div></td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Avance sur salaire</div></td>
                        <td style="padding:9px 14px"><input type="number" name="advance_deduction" id="advance_deduction" class="form-control" value="{{ old('advance_deduction', $existing?->advance_deduction ?? 0) }}" step="0.01" min="0" style="text-align:right" oninput="calculate()"></td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Remboursement de prêt</div></td>
                        <td style="padding:9px 14px"><input type="number" name="loan_deduction" id="loan_deduction" class="form-control" value="{{ old('loan_deduction', $existing?->loan_deduction ?? 0) }}" step="0.01" min="0" style="text-align:right" oninput="calculate()"></td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Saisie sur salaire</div></td>
                        <td style="padding:9px 14px"><input type="number" name="garnishment_deduction" id="garnishment_deduction" class="form-control" value="{{ old('garnishment_deduction', $existing?->garnishment_deduction ?? 0) }}" step="0.01" min="0" style="text-align:right" oninput="calculate()"></td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Autres retenues</div></td>
                        <td style="padding:9px 14px"><input type="number" name="other_deductions" id="other_deductions" class="form-control" value="{{ old('other_deductions', $existing?->other_deductions ?? 0) }}" step="0.01" min="0" style="text-align:right" oninput="calculate()"></td>
                    </tr>
                    @if($variableElements->where('category','retenue')->count())
                    <tr style="background:#fff0f0">
                        <td colspan="2" style="padding:9px 14px">
                            <div style="font-weight:600;font-size:0.78rem;color:#991b1b;margin-bottom:5px">Éléments variables (retenues)</div>
                            @foreach($variableElements->where('category','retenue') as $ve)
                            <div style="display:flex;justify-content:space-between;font-size:0.8rem;padding:2px 0">
                                <span>{{ $ve->label }}</span>
                                <span class="deduction font-semibold">−{{ number_format($ve->amount,2,',',' ') }} <span class="cur-label">MAD</span></span>
                            </div>
                            @endforeach
                        </td>
                    </tr>
                    @endif
                    <tr style="background:#fecaca;border-top:2px solid #f87171">
                        <td style="padding:11px 14px;font-weight:700;color:#991b1b">TOTAL RETENUES</td>
                        <td style="padding:11px 14px;text-align:right;font-weight:700;color:#991b1b;font-size:1rem" id="ret-total-display">0,00 <span class="cur-label">MAD</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4" style="border:2px solid var(--success);background:linear-gradient(135deg,#f0fdf4,#ffffff)">
        <div class="card-body" style="padding:20px;text-align:center">
            <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:6px;letter-spacing:0.08em;text-transform:uppercase;font-weight:600">Net à payer</div>
            <div style="font-size:2.8rem;font-weight:900;color:var(--success);letter-spacing:-1px">
                <span id="net-display">0,00</span> <span style="font-size:1.4rem" class="cur-label">MAD</span>
            </div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:8px" id="net-detail">Brut 0,00 − Cotis. 0,00 − FP 0,00 − IR 0,00 − Retenues 0,00</div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header" style="background:#fef3c7;border-bottom:2px solid #fcd34d">
            <div class="card-title" style="color:#78350f">Charges patronales <span style="font-size:0.72rem;font-weight:400">(informatives)</span></div>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse;font-size:0.83rem">
                <tr style="border-bottom:1px solid var(--border-color)"><td style="padding:7px 14px;color:var(--text-muted)" id="emp-cnss-label">CNSS patronale (10,29%)</td><td style="padding:7px 14px;text-align:right;color:#d97706;font-weight:600" id="emp-cnss">0,00</td></tr>
                <tr style="border-bottom:1px solid var(--border-color)"><td style="padding:7px 14px;color:var(--text-muted)" id="emp-amo-label">AMO patronale (2,26%)</td><td style="padding:7px 14px;text-align:right;color:#d97706;font-weight:600" id="emp-amo">0,00</td></tr>
                <tr style="border-bottom:2px solid var(--border-color)" id="row-tfp"><td style="padding:7px 14px;color:var(--text-muted)" id="emp-tfp-label">TFP (1,60%)</td><td style="padding:7px 14px;text-align:right;color:#d97706;font-weight:600" id="emp-tfp">0,00</td></tr>
                <tr style="background:#fef3c7"><td style="padding:10px 14px;font-weight:700;color:#78350f">Coût total employeur</td><td style="padding:10px 14px;text-align:right;font-weight:700;color:#78350f" id="emp-total">0,00 <span class="cur-label">MAD</span></td></tr>
            </table>
        </div>
    </div>

    <div style="display:flex;gap:12px">
        <button type="submit" class="btn btn-primary" style="flex:1;font-size:1rem;padding:12px">✓ Calculer &amp; Enregistrer</button>
        <a href="{{ route('variables.index', ['month'=>$month,'year'=>$year]) }}" class="btn btn-ghost">Éléments variables</a>
    </div>
</div>
</div>
</form>

<script>
/* ══════════════════════════════════════════════════════════════════
   VARIABLES GLOBALES
══════════════════════════════════════════════════════════════════ */
var currentSystem  = 'MAD';
var gardeEditMode  = false;
var gardeAutoValue = 0;

// Lire la valeur persistée dès le chargement — ne jamais l'écraser si override
var isGardeOverride = EXISTING.garde_override === 1;
var gardeManual     = isGardeOverride ? EXISTING.garde_indemnite : 0;

var SYS = {
    MAD:{CNSS_SAL:0.0448,CNSS_PLAFOND:6000,AMO_SAL:0.0226,FP_RATE:0.20,FP_MAX:2500,HAS_FP:true,CNSS_PAT:0.1029,AMO_PAT:0.0226,TFP:0.016,HAS_TFP:true,HEURES_REF:191.25,
        cot_sub:'Mode automatique = taux legaux marocains',cnss_lbl:'CNSS salariale',amo_lbl:'AMO salariale',amo_sub:'2,26% du salaire brut',
        taxable_lbl:'Net imposable (NI)',cot_tot_lbl:'TOTAL COTISATIONS (CNSS+AMO)',ir_title:'IR - Impot sur le Revenu',
        ir_sub:'Bareme progressif annuel DGI / 12',ir_ann_lbl:'IR annuel brut (bareme)',ir_fam_lbl:'Deductions familiales (360 MAD/pers.)',
        ir_mon_lbl:'IR mensuel retenu',emp_cnss_lbl:'CNSS patronale (10,29%)',emp_amo_lbl:'AMO patronale (2,26%)',emp_tfp_lbl:'TFP (1,60%)',hr_lbl:'191,25'},
    MRU:{CNSS_SAL:0.01,CNSS_PLAFOND:15000,AMO_SAL:0.04,FP_RATE:0,FP_MAX:0,HAS_FP:false,CNSS_PAT:0.13,AMO_PAT:0.02,TFP:0,HAS_TFP:false,HEURES_REF:173.33,
        cot_sub:'Mode automatique = taux legaux mauritaniens',cnss_lbl:'CNSS salariale (Mauritanie)',amo_lbl:'CNAM salariale (Mauritanie)',
        amo_sub:'4% du salaire brut - assurance maladie',taxable_lbl:'Revenu imposable ITS',cot_tot_lbl:'TOTAL COTISATIONS (CNSS+CNAM)',
        ir_title:'ITS - Impot sur Traitements et Salaires',ir_sub:'Bareme progressif mensuel - Mauritanie',ir_ann_lbl:'ITS calcule (bareme mensuel)',
        ir_fam_lbl:'Abattement',ir_mon_lbl:'ITS mensuel retenu',emp_cnss_lbl:'CNSS patronale (13%)',emp_amo_lbl:'CNAM patronale (2%)',emp_tfp_lbl:'',hr_lbl:'173,33'}
};

/* ══════════════════════════════════════════════════════════════════
   UTILITAIRES
══════════════════════════════════════════════════════════════════ */
function calcIR_MAD(a){if(a<=30000)return 0;if(a<=50000)return(a-30000)*.10;if(a<=60000)return 2000+(a-50000)*.20;if(a<=80000)return 4000+(a-60000)*.30;if(a<=180000)return 10000+(a-80000)*.34;return 44000+(a-180000)*.38;}
function calcITS_MRU(m){if(m<=6000)return 0;if(m<=9000)return(m-6000)*.15;if(m<=21000)return 450+(m-9000)*.25;return 3450+(m-21000)*.40;}
function calcDeductFam_MAD(s,c){var d=0;if(s==='marie'||s==='veuf'||s==='divorce')d+=360;d+=Math.min(c,6)*360;return d;}
function seniorityRate(y){if(y<2)return 0;if(y<5)return .05;if(y<12)return .10;if(y<20)return .15;if(y<25)return .20;return .25;}
function fmt(n){return parseFloat(n.toFixed(2)).toLocaleString('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2});}
function setHTML(id,val){var el=document.getElementById(id);if(el)el.innerHTML=val+' <span class="cur-label">'+currentSystem+'</span>';}
function setText(id,val){var el=document.getElementById(id);if(el)el.textContent=val;}
function getVal(id){return parseFloat(document.getElementById(id).value)||0;}

/* ══════════════════════════════════════════════════════════════════
   MODAL DÉTAILS — Heures / HS / Absences / Retards
══════════════════════════════════════════════════════════════════ */
function openDetailModal(type) {
    var configs = {
        working: {
            title: 'Détail des Heures Travaillées',
            gradient: 'linear-gradient(135deg,#065f46,#10b981)',
            stats: [
                {label:'Jours pointés', value: WORKING_SHIFTS.length+' j', color:'#065f46'},
                {label:'Total heures',  value: fmt(EMPLOYEE_DATA.working_hours)+' h', color:'#065f46'}
            ],
            header: ['Date','Entrée','Sortie','Durée'],
            cols: '130px 90px 90px 90px',
            border: '#d1fae5',
            rowBg: ['white','#f0fdf4'],
            render: function(s,i){
                var d=new Date(s.date+'T00:00:00');
                var J=['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],M=['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
                var lbl=J[d.getDay()]+' '+d.getDate()+' '+M[d.getMonth()];
                var debut=s.heure_entree?s.heure_entree.substring(0,5):'--:--';
                var fin=s.heure_sortie?s.heure_sortie.substring(0,5):'--:--';
                var dur=parseFloat(s.duree_heures||0);
                return '<div style="display:grid;grid-template-columns:130px 90px 90px 90px;gap:8px;padding:11px 14px;background:'+(i%2===0?'white':'#f0fdf4')+';border:1px solid #d1fae5;border-radius:8px;align-items:center;font-size:0.83rem">'
                    +'<div style="font-weight:700;color:#065f46">'+lbl+'</div>'
                    +'<div style="text-align:center;background:#d1fae5;color:#065f46;padding:3px 8px;border-radius:20px;font-size:0.75rem;font-weight:600">'+debut+'</div>'
                    +'<div style="text-align:center;background:#d1fae5;color:#065f46;padding:3px 8px;border-radius:20px;font-size:0.75rem;font-weight:600">'+fin+'</div>'
                    +'<div style="text-align:center;font-weight:700;color:#065f46">'+fmt(dur)+' h</div></div>';
            },
            shifts: WORKING_SHIFTS
        },
        overtime: {
            title: 'Détail des Heures Supplémentaires',
            gradient: 'linear-gradient(135deg,#92400e,#f59e0b)',
            stats: [
                {label:'HS jour (25%)',     value: fmt(EMPLOYEE_DATA.ot_day)+' h',     color:'#92400e'},
                {label:'HS nuit (50%)',     value: fmt(EMPLOYEE_DATA.ot_night)+' h',   color:'#92400e'},
                {label:'HS weekend (100%)', value: fmt(EMPLOYEE_DATA.ot_weekend)+' h', color:'#92400e'},
                {label:'Total HS',          value: fmt(EMPLOYEE_DATA.ot_day+EMPLOYEE_DATA.ot_night+EMPLOYEE_DATA.ot_weekend)+' h', color:'#92400e'}
            ],
            header: ['Date','Type','Heures','Majoration'],
            cols: '130px 90px 90px 90px',
            render: function(s,i){
                var d=new Date(s.date+'T00:00:00');
                var J=['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],M=['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
                var lbl=J[d.getDay()]+' '+d.getDate()+' '+M[d.getMonth()];
                var type=s.type==='night'?'Nuit':s.type==='weekend'?'Weekend':'Jour';
                var maj=s.type==='night'?'+50%':s.type==='weekend'?'+100%':'+25%';
                var dur=parseFloat(s.duree_heures||0);
                return '<div style="display:grid;grid-template-columns:130px 90px 90px 90px;gap:8px;padding:11px 14px;background:'+(i%2===0?'white':'#fffbeb')+';border:1px solid #fde68a;border-radius:8px;align-items:center;font-size:0.83rem">'
                    +'<div style="font-weight:700;color:#92400e">'+lbl+'</div>'
                    +'<div style="text-align:center;background:#fef3c7;color:#92400e;padding:3px 8px;border-radius:20px;font-size:0.75rem;font-weight:600">'+type+'</div>'
                    +'<div style="text-align:center;font-weight:700;color:#d97706">'+fmt(dur)+' h</div>'
                    +'<div style="text-align:center;background:#fcd34d;color:#78350f;padding:3px 8px;border-radius:20px;font-size:0.75rem;font-weight:700">'+maj+'</div></div>';
            },
            shifts: OVERTIME_SHIFTS
        },
        absence: {
            title: "Détail des Heures d'Absence",
            gradient: 'linear-gradient(135deg,#991b1b,#ef4444)',
            stats: [
                {label:"Absences",    value: ABSENCE_SHIFTS.length+' j',                    color:'#991b1b'},
                {label:'Total heures',value: fmt(EMPLOYEE_DATA.absence_hours)+' h', color:'#991b1b'}
            ],
            header: ['Date','Type','Heures','Statut'],
            cols: '130px 110px 80px 100px',
            render: function(s,i){
                var d=new Date(s.date+'T00:00:00');
                var J=['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],M=['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
                var lbl=J[d.getDay()]+' '+d.getDate()+' '+M[d.getMonth()];
                var dur=parseFloat(s.heures||0);
                return '<div style="display:grid;grid-template-columns:130px 110px 80px 100px;gap:8px;padding:11px 14px;background:'+(i%2===0?'white':'#fff1f2')+';border:1px solid #fecaca;border-radius:8px;align-items:center;font-size:0.83rem">'
                    +'<div style="font-weight:700;color:#991b1b">'+lbl+'</div>'
                    +'<div style="color:#64748b;font-size:0.78rem">'+(s.type||'Absence')+'</div>'
                    +'<div style="text-align:center;font-weight:700;color:#ef4444">'+fmt(dur)+' h</div>'
                    +'<div style="text-align:center;background:#fecaca;color:#991b1b;padding:3px 8px;border-radius:20px;font-size:0.73rem;font-weight:600">'+(s.statut||'—')+'</div></div>';
            },
            shifts: ABSENCE_SHIFTS
        },
        delay: {
            title: 'Détail des Retards',
            gradient: 'linear-gradient(135deg,#701a75,#ec4899)',
            stats: [
                {label:'Jours avec retard', value: DELAY_SHIFTS.length+' j',                    color:'#701a75'},
                {label:'Total retard',      value: fmt(EMPLOYEE_DATA.delay_hours)+' h', color:'#701a75'}
            ],
            header: ['Date','Heure arrivée','Retard','Heure prévue'],
            cols: '130px 110px 90px 110px',
            render: function(s,i){
                var d=new Date(s.date+'T00:00:00');
                var J=['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],M=['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
                var lbl=J[d.getDay()]+' '+d.getDate()+' '+M[d.getMonth()];
                var arrivee=s.heure_entree?s.heure_entree.substring(0,5):'--:--';
                var prevue=s.heure_prevue?s.heure_prevue.substring(0,5):'--:--';
                var retard=parseFloat(s.retard_minutes||0);
                return '<div style="display:grid;grid-template-columns:130px 110px 90px 110px;gap:8px;padding:11px 14px;background:'+(i%2===0?'white':'#fdf4ff')+';border:1px solid #f5d0fe;border-radius:8px;align-items:center;font-size:0.83rem">'
                    +'<div style="font-weight:700;color:#701a75">'+lbl+'</div>'
                    +'<div style="text-align:center;background:#fdf4ff;color:#701a75;padding:3px 8px;border-radius:20px;font-size:0.75rem;font-weight:600">'+arrivee+'</div>'
                    +'<div style="text-align:center;font-weight:700;color:#ec4899">'+retard+' min</div>'
                    +'<div style="text-align:center;background:#f5d0fe;color:#701a75;padding:3px 8px;border-radius:20px;font-size:0.73rem;font-weight:600">'+prevue+'</div></div>';
            },
            shifts: DELAY_SHIFTS
        }
    };

    var cfg = configs[type];
    if (!cfg) return;

    // Header
    document.getElementById('detailModalHeader').style.background = cfg.gradient;
    document.getElementById('detailModalTitle').textContent = cfg.title;

    // Stats
    var statsHtml = '';
    cfg.stats.forEach(function(st) {
        statsHtml += '<div style="background:white;padding:14px 20px;text-align:center">'
            +'<div style="font-size:0.7rem;color:'+st.color+';font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px">'+st.label+'</div>'
            +'<div style="font-size:2rem;font-weight:900;color:'+st.color+'">'+st.value+'</div></div>';
    });
    var statsEl = document.getElementById('detailModalStats');
    statsEl.style.gridTemplateColumns = 'repeat('+cfg.stats.length+',1fr)';
    statsEl.innerHTML = statsHtml;

    // Table header
    var hdrHtml = '<div style="display:grid;grid-template-columns:'+cfg.cols+';gap:8px;padding:8px 14px;background:#f1f5f9;border-radius:8px;font-size:0.71rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px">';
    cfg.header.forEach(function(c){ hdrHtml += '<div>'+c+'</div>'; });
    hdrHtml += '</div>';
    document.getElementById('detailModalTableHeader').innerHTML = hdrHtml;

    // Rows
    var rowsHtml = '';
    if (!cfg.shifts.length) {
        rowsHtml = '<div style="text-align:center;padding:40px;color:#94a3b8">Aucune donnée pour ce mois</div>';
    } else {
        cfg.shifts.forEach(function(s,i){ rowsHtml += cfg.render(s,i); });
    }
    document.getElementById('detailModalList').innerHTML = rowsHtml;

    document.getElementById('detailModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeDetailModal() {
    document.getElementById('detailModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

/* ══════════════════════════════════════════════════════════════════
   GARDE — édition et synchronisation
══════════════════════════════════════════════════════════════════ */
function toggleGardeEdit(open) {
    gardeEditMode = open;
    document.getElementById('garde-gain-display').style.display = open ? 'none' : 'block';
    document.getElementById('garde-input-wrap').style.display   = open ? 'block' : 'none';
    if (open) {
        var inp = document.getElementById('garde_indemnite_input');
        inp.value = (isGardeOverride ? gardeManual : gardeAutoValue).toFixed(2);
        setTimeout(function(){ inp.focus(); inp.select(); }, 30);
    }
}

function onGardeManualChange() {
    var v = parseFloat(document.getElementById('garde_indemnite_input').value) || 0;
    gardeManual     = v;
    isGardeOverride = true;
    syncGardeToVE(v);
    updateGardeDisplay(v);
    calculate();
}

function onVeGardeChange() {
    var inp = document.getElementById('ve_garde_input');
    if (!inp) return;
    var v = parseFloat(inp.value) || 0;
    gardeManual     = v;
    isGardeOverride = true;
    var mainInp = document.getElementById('garde_indemnite_input');
    if (mainInp) mainInp.value = v.toFixed(2);
    updateGardeDisplay(v);
    calculate();
}

function resetGardeAuto() {
    isGardeOverride = false;
    gardeManual     = gardeAutoValue;
    var inp = document.getElementById('garde_indemnite_input');
    if (inp) inp.value = gardeAutoValue.toFixed(2);
    syncGardeToVE(gardeAutoValue);
    updateGardeDisplay(gardeAutoValue);
    toggleGardeEdit(false);
    calculate();
}

function syncGardeToVE(amount) {
    var veInp = document.getElementById('ve_garde_input');
    if (veInp) veInp.value = amount.toFixed(2);
}

function updateGardeDisplay(amount) {
    var disp = document.getElementById('garde-gain-display');
    if (disp) {
        disp.innerHTML    = fmt(amount) + ' <span class="cur-label">' + currentSystem + '</span>';
        disp.style.background  = isGardeOverride ? '#fffbeb' : 'transparent';
        disp.style.borderColor = isGardeOverride ? '#fcd34d' : 'rgba(13,118,110,0.35)';
        disp.style.borderStyle = isGardeOverride ? 'solid'   : 'dashed';
    }
    document.getElementById('h_garde_indemnite').value = amount.toFixed(2);
    document.getElementById('h_garde_override').value  = isGardeOverride ? '1' : '0';
    var badge = document.getElementById('garde-override-badge');
    if (badge) badge.style.display = isGardeOverride ? 'inline-flex' : 'none';
}

/* ══════════════════════════════════════════════════════════════════
   MODAL PLANNING DE GARDE
══════════════════════════════════════════════════════════════════ */
function openGardeModal() {
    var baseSalary = getVal('base_salary') || EMPLOYEE_DATA.base_salary;
    var S     = SYS[currentSystem];
    var tauxH = baseSalary / S.HEURES_REF;
    var gardeH= EMPLOYEE_DATA.garde_hours;
    var gardeD= EMPLOYEE_DATA.garde_days;
    var shifts= GARDE_SHIFTS;
    var totalAmt = tauxH * gardeH;
    document.getElementById('garde-count').textContent     = gardeD+(gardeD>1?' jours':' jour');
    document.getElementById('garde-total-h').textContent   = fmt(gardeH)+' h';
    document.getElementById('garde-total-amt').textContent = fmt(totalAmt)+' '+currentSystem;
    var J=['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],M=['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
    var html='';
    if(!shifts.length){
        html='<div style="text-align:center;padding:40px;color:#94a3b8">Aucune garde planifiée ce mois-ci</div>';
    } else {
        shifts.forEach(function(s,i){
            var d=new Date(s.date+'T00:00:00');
            var label=J[d.getDay()]+' '+d.getDate()+' '+M[d.getMonth()];
            var debut=s.shift_start?s.shift_start.substring(0,5):'--:--';
            var fin  =s.shift_end  ?s.shift_end.substring(0,5)  :'--:--';
            var duree=parseFloat(s.duree_heures||0);
            var amt=tauxH*duree;
            var salle=s.room||'—';
            var bg=i%2===0?'white':'#f0fdfa';
            html+='<div style="display:grid;grid-template-columns:130px 1fr 90px 90px 110px;gap:8px;padding:11px 14px;background:'+bg+';border:1px solid #ccfbf1;border-radius:8px;align-items:center;font-size:0.83rem">';
            html+='<div style="font-weight:700;color:#0f766e">'+label+'</div>';
            html+='<div style="display:flex;align-items:center;gap:5px"><span style="background:#0f766e;color:white;padding:3px 9px;border-radius:20px;font-size:0.73rem;font-weight:600">'+debut+'</span><span style="color:#2dd4bf">→</span><span style="background:#2dd4bf;color:white;padding:3px 9px;border-radius:20px;font-size:0.73rem;font-weight:600">'+fin+'</span></div>';
            html+='<div style="font-weight:700;color:#0f766e;text-align:center">'+fmt(duree)+' h</div>';
            html+='<div style="color:#64748b;font-size:0.78rem;text-align:center">'+salle+'</div>';
            html+='<div style="text-align:right;font-weight:700;color:#0f766e">'+fmt(amt)+' '+currentSystem+'</div></div>';
        });
    }
    document.getElementById('garde-list').innerHTML=html;
    if(shifts.length){
        document.getElementById('garde-total-row').style.display='block';
        document.getElementById('garde-total-final').textContent=fmt(totalAmt)+' '+currentSystem;
    } else {
        document.getElementById('garde-total-row').style.display='none';
    }
    document.getElementById('gardeModal').style.display='block';
    document.body.style.overflow='hidden';
}
function closeGardeModal(){document.getElementById('gardeModal').style.display='none';document.body.style.overflow='auto';}

window.addEventListener('click',function(e){
    if(e.target===document.getElementById('gardeModal'))closeGardeModal();
    if(e.target===document.getElementById('detailModal'))closeDetailModal();
});

/* ══════════════════════════════════════════════════════════════════
   SYSTÈME MAD / MRU
══════════════════════════════════════════════════════════════════ */
function setSystem(sys) {
    currentSystem=sys;
    document.getElementById('h_currency').value=sys;
    var S=SYS[sys];
    document.getElementById('btn-mad').style.background=sys==='MAD'?'#1d4ed8':'#f9fafb';
    document.getElementById('btn-mad').style.color=sys==='MAD'?'white':'#6b7280';
    document.getElementById('btn-mru').style.background=sys==='MRU'?'#065f46':'#f9fafb';
    document.getElementById('btn-mru').style.color=sys==='MRU'?'white':'#6b7280';
    var badge=document.getElementById('system-badge');
    badge.textContent=sys==='MAD'?'Systeme marocain actif':'Systeme mauritanien actif';
    badge.style.background=sys==='MAD'?'#dbeafe':'#dcfce7';
    badge.style.color=sys==='MAD'?'#1e40af':'#14532d';
    badge.style.borderColor=sys==='MAD'?'#bfdbfe':'#86efac';
    document.getElementById('mad-info-banner').style.display=sys==='MAD'?'block':'none';
    document.getElementById('mru-info-banner').style.display=sys==='MRU'?'block':'none';
    document.querySelectorAll('.cur-label').forEach(function(el){el.textContent=sys;});
    setText('cot-subtitle',S.cot_sub);setText('cnss-label',S.cnss_lbl);setText('amo-label',S.amo_lbl);
    setText('amo-sub',S.amo_sub);setText('taxable-label',S.taxable_lbl);setText('cot-total-label',S.cot_tot_lbl);
    setText('ir-title',S.ir_title);setText('ir-subtitle',S.ir_sub);setText('ir-annual-label',S.ir_ann_lbl);
    setText('ir-family-label',S.ir_fam_lbl);setText('ir-monthly-label',S.ir_mon_lbl);
    setText('emp-cnss-label',S.emp_cnss_lbl);setText('emp-amo-label',S.emp_amo_lbl);
    if(S.HAS_TFP)setText('emp-tfp-label',S.emp_tfp_lbl);
    document.getElementById('row-fp').style.display=S.HAS_FP?'':'none';
    document.getElementById('row-tfp').style.display=S.HAS_TFP?'':'none';
    document.getElementById('row-ir-family').style.display=sys==='MAD'?'':'none';
    setText('heures-ref-label',S.hr_lbl);
    calculate();
}

function toggleCotisationMode() {
    var checked=document.querySelector('input[name="mode_cotisation"]:checked');
    var isManual=checked&&checked.value==='manual';
    ['cnss','amo','fp'].forEach(function(k){
        var a=document.getElementById(k+'-auto');var m=document.getElementById(k+'-manual');
        if(a)a.style.display=isManual?'none':'block';if(m)m.style.display=isManual?'block':'none';
    });
    var aL=document.getElementById('autoLabel');var mL=document.getElementById('manuelLabel');
    if(aL){aL.style.background=isManual?'white':'#e0f2fe';aL.style.color=isManual?'':'#0369a1';}
    if(mL){mL.style.background=isManual?'#fef08a':'white';mL.style.color=isManual?'#78350f':'';}
    calculate();
}

function onTypeChange() {
    var isHourly=document.getElementById('type_hourly').checked;
    document.getElementById('hourly_rate').disabled=!isHourly;
    document.getElementById('base_salary').readOnly=isHourly;
    setText('base-sub',isHourly?'Calcule : taux horaire x heures travaillees':'Remuneration mensuelle contractuelle');
    calculate();
}

/* ══════════════════════════════════════════════════════════════════
   CALCULATE
   Modification clé : si isGardeOverride=true, gardeManual n'est
   JAMAIS réinitialisé par calculate(), même après soumission.
══════════════════════════════════════════════════════════════════ */
function calculate() {
    var S      = SYS[currentSystem];
    var workH  = EMPLOYEE_DATA.working_hours;
    var otDayH = EMPLOYEE_DATA.ot_day;
    var absH   = EMPLOYEE_DATA.absence_hours;
    var delayH = EMPLOYEE_DATA.delay_hours;
    var gardeH = EMPLOYEE_DATA.garde_hours;
    var gardeD = EMPLOYEE_DATA.garde_days;
    var otNightH = getVal('ot_night_h');
    var otWkndH  = getVal('ot_wknd_h');

    setText('disp-working', workH+' h');
    setText('disp-ot-day',  otDayH+' h');
    setText('disp-abs',     absH+' h');
    setText('disp-delay',   delayH+' h');
    document.getElementById('disp-garde-days').innerHTML = gardeD+'<span style="font-size:1rem;font-weight:500"> j</span>';
    setText('disp-garde-sub', fmt(gardeH)+' h au total');
    setText('ot-day-h-disp',   otDayH+' h');
    setText('ot-night-h-disp', otNightH+' h');
    setText('ot-wknd-h-disp',  otWkndH+' h');

    var isHourly   = document.getElementById('type_hourly').checked;
    var hourlyRate = getVal('hourly_rate');
    var baseSalary;
    if (isHourly) {
        baseSalary = hourlyRate * workH;
        document.getElementById('base_salary').value = baseSalary.toFixed(2);
    } else {
        baseSalary = getVal('base_salary');
    }
    var tauxH = isHourly ? hourlyRate : (baseSalary / S.HEURES_REF);

    var seniInp = document.getElementById('seniority_bonus');
    var autoSeniority = baseSalary * seniorityRate(EMPLOYEE_DATA.seniority_years);
    if (seniInp && parseFloat(seniInp.value) === 0 && autoSeniority > 0) {
        seniInp.value = autoSeniority.toFixed(2);
    }
    var seniority = getVal('seniority_bonus');

    var otDayAmt   = tauxH * otDayH  * 1.25;
    var otNightAmt = tauxH * otNightH * 1.50;
    var otWkndAmt  = tauxH * otWkndH  * 2.00;
    var totalOT    = otDayAmt + otNightAmt + otWkndAmt;
    setHTML('ot-day-amt-disp',  '= '+fmt(otDayAmt));
    setHTML('ot-night-amt-disp','= '+fmt(otNightAmt));
    setHTML('ot-wknd-amt-disp', '= '+fmt(otWkndAmt));
    setHTML('ot-total-disp',    fmt(totalOT));

    // ── GARDE : calcule l'auto mais PROTÈGE la valeur manuelle ──────
    gardeAutoValue = tauxH * gardeH;
    var gardeAmt;
    if (isGardeOverride) {
        // Valeur saisie manuellement — intouchable
        gardeAmt = gardeManual;
    } else {
        gardeManual = gardeAutoValue;
        gardeAmt    = gardeAutoValue;
        syncGardeToVE(gardeAutoValue);
    }
    updateGardeDisplay(gardeAmt);

    var perfBonus      = getVal('performance_bonus');
    var transport      = getVal('transport_allowance');
    var meal           = getVal('meal_allowance');
    var housing        = getVal('housing_allowance');
    var responsibility = getVal('responsibility_allowance');
    var otherGains     = getVal('other_gains');
    var absDeduction   = tauxH * absH;

    setHTML('absence-auto', fmt(absDeduction));
    document.getElementById('absence-sub').innerHTML =
        '('+fmt(baseSalary)+' / <span id="heures-ref-label">'+S.hr_lbl+'</span> h) x '+absH+' h = '+fmt(absDeduction)+' '+currentSystem;

    var grossSalary = Math.max(0,
        baseSalary + seniority + totalOT + gardeAmt
        + perfBonus + transport + meal + housing + responsibility + otherGains
        - absDeduction
    );
    setText('gross-display', fmt(grossSalary));

    if (currentSystem==='MAD') setText('cnss-sub','4,48% x min('+fmt(grossSalary)+', 6 000) = '+fmt(Math.min(grossSalary,6000)*0.0448)+' MAD');
    else setText('cnss-sub','1% x min('+fmt(grossSalary)+', 15 000) = '+fmt(Math.min(grossSalary,15000)*0.01)+' MRU');

    var checked2=document.querySelector('input[name="mode_cotisation"]:checked');
    var isManual=checked2&&checked2.value==='manual';
    var cnss,amo,fp;
    if(isManual){
        cnss=getVal('cnss-manual');amo=getVal('amo-manual');fp=S.HAS_FP?getVal('fp-manual'):0;
    } else {
        cnss=Math.min(grossSalary,S.CNSS_PLAFOND)*S.CNSS_SAL;
        amo=grossSalary*S.AMO_SAL;
        fp=S.HAS_FP?Math.min(grossSalary*S.FP_RATE,S.FP_MAX):0;
        setHTML('cnss-auto',fmt(cnss));setHTML('amo-auto',fmt(amo));setHTML('fp-auto',fmt(fp));
    }

    var totalCot      = cnss+amo;
    var taxableIncome = Math.max(0,grossSalary-cnss-amo-fp);
    setHTML('taxable-display',   fmt(taxableIncome));
    setHTML('cot-total-display', fmt(totalCot));

    var irAnnuelBrut,deductFam,irMensuel;
    if(currentSystem==='MAD'){
        irAnnuelBrut=calcIR_MAD(taxableIncome*12);
        deductFam=calcDeductFam_MAD(EMPLOYEE_DATA.family_status,EMPLOYEE_DATA.children_count);
        irMensuel=Math.max(0,irAnnuelBrut-deductFam)/12;
        setHTML('ir-annual',fmt(irAnnuelBrut));
        document.getElementById('ir-family').innerHTML='-'+fmt(deductFam)+' <span class="cur-label">MAD</span>';
    } else {
        irAnnuelBrut=calcITS_MRU(taxableIncome);
        deductFam=0;irMensuel=irAnnuelBrut;
        setHTML('ir-annual',fmt(irAnnuelBrut));
        document.getElementById('ir-family').innerHTML='-0,00 <span class="cur-label">MRU</span>';
    }
    setHTML('ir-monthly',fmt(irMensuel));

    var totalRet=getVal('advance_deduction')+getVal('loan_deduction')+getVal('garnishment_deduction')+getVal('other_deductions');
    setHTML('ret-total-display',fmt(totalRet));

    var netSalary=Math.max(0,grossSalary-totalCot-fp-irMensuel-totalRet);
    setText('net-display',fmt(netSalary));
    setText('net-detail','Brut '+fmt(grossSalary)+' - Cotis. '+fmt(totalCot)+(fp>0?' - FP '+fmt(fp):'')
        +' - '+(currentSystem==='MAD'?'IR':'ITS')+' '+fmt(irMensuel)+' - Retenues '+fmt(totalRet)+' ('+currentSystem+')');

    var empBase=Math.min(grossSalary,S.CNSS_PLAFOND);
    var empCnss=empBase*S.CNSS_PAT,empAmo=grossSalary*S.AMO_PAT,empTfp=S.HAS_TFP?grossSalary*S.TFP:0;
    var empTotal=netSalary+totalCot+fp+irMensuel+empCnss+empAmo+empTfp;
    setText('emp-cnss',fmt(empCnss));setText('emp-amo',fmt(empAmo));setText('emp-tfp',fmt(empTfp));
    setHTML('emp-total',fmt(empTotal));

    document.getElementById('h_gross_salary').value        = grossSalary.toFixed(2);
    document.getElementById('h_seniority_bonus').value     = seniority.toFixed(2);
    document.getElementById('h_ot_day_amount').value       = otDayAmt.toFixed(2);
    document.getElementById('h_ot_night_amount').value     = otNightAmt.toFixed(2);
    document.getElementById('h_ot_wknd_amount').value      = otWkndAmt.toFixed(2);
    document.getElementById('h_overtime_hours').value      = (otDayH+otNightH+otWkndH).toFixed(2);
    document.getElementById('h_absence_deduction').value   = absDeduction.toFixed(2);
    document.getElementById('h_absence_days').value        = (absH/8).toFixed(2);
    document.getElementById('h_cnss_base').value           = Math.min(grossSalary,S.CNSS_PLAFOND).toFixed(2);
    document.getElementById('h_cnss_deduction').value      = cnss.toFixed(2);
    document.getElementById('h_amo_deduction').value       = amo.toFixed(2);
    document.getElementById('h_fp_deduction').value        = fp.toFixed(2);
    document.getElementById('h_taxable_income').value      = taxableIncome.toFixed(2);
    document.getElementById('h_ir_annual').value           = irAnnuelBrut.toFixed(2);
    document.getElementById('h_ir_family_deduction').value = deductFam.toFixed(2);
    document.getElementById('h_ir_deduction').value        = irMensuel.toFixed(2);
    document.getElementById('h_net_salary').value          = netSalary.toFixed(2);
    document.getElementById('h_employer_cnss').value       = empCnss.toFixed(2);
    document.getElementById('h_employer_amo').value        = empAmo.toFixed(2);
    document.getElementById('h_employer_tfp').value        = empTfp.toFixed(2);
    document.getElementById('h_employer_total_cost').value = empTotal.toFixed(2);
    document.getElementById('h_ot_day_h').value            = otDayH.toFixed(2);
    document.getElementById('h_ot_night_h').value          = otNightH.toFixed(2);
    document.getElementById('h_ot_wknd_h').value           = otWkndH.toFixed(2);
    document.getElementById('h_working_hours').value       = workH.toFixed(2);
    document.getElementById('h_abs_hours').value           = absH.toFixed(2);
    document.getElementById('h_delay_hours').value         = delayH.toFixed(2);
    document.getElementById('h_garde_hours').value         = gardeH.toFixed(2);
    document.getElementById('h_hourly_rate').value         = (isHourly?hourlyRate:0).toFixed(2);
}

/* ══════════════════════════════════════════════════════════════════
   INIT
══════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function() {
    if (EXISTING.salary_type === 'hourly') {
        document.getElementById('type_hourly').checked  = true;
        document.getElementById('hourly_rate').disabled = false;
        document.getElementById('hourly_rate').value    = EXISTING.hourly_rate;
        document.getElementById('base_salary').readOnly = true;
    } else {
        document.getElementById('type_monthly').checked = true;
    }

    var modeInput=document.querySelector('input[name="mode_cotisation"][value="'+EXISTING.mode_cotisation+'"]');
    if(modeInput)modeInput.checked=true;

    setSystem(EXISTING.currency||'MAD');
    toggleCotisationMode();

    setTimeout(function(){
        calculate();
        // Restaurer la garde manuelle sauvegardée APRÈS le premier calculate()
        // calculate() ne peut pas l'écraser car isGardeOverride=true,
        // mais on force l'affichage ici pour être sûr
        if(isGardeOverride && gardeManual > 0){
            updateGardeDisplay(gardeManual);
            syncGardeToVE(gardeManual);
        }
    }, 0);
});
</script>

@endsection
