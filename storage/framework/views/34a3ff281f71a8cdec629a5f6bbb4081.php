<?php $__env->startSection('title', 'Saisie Paie — '.$employee->full_name); ?>
<?php $__env->startSection('page-title', 'Saisie de la Paie'); ?>

<?php $__env->startSection('content'); ?>

<div class="page-header">
    <div class="page-header-left">
        <h1><?php echo e($employee->full_name); ?></h1>
        <p><?php echo e($employee->department); ?> — <?php echo e($employee->position); ?> — Paie <?php echo e(\Carbon\Carbon::create($year,$month)->locale('fr')->isoFormat('MMMM YYYY')); ?></p>
    </div>
    <a href="<?php echo e(route('salary.index', ['month'=>$month,'year'=>$year])); ?>" class="btn btn-ghost">← Retour</a>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>


<script>
const EMPLOYEE_DATA = {
    base_salary:      <?php echo e((float) $employee->base_salary); ?>,
    seniority_years:  <?php echo e((int) ($employee->seniority_years ?? 0)); ?>,
    seniority_rate:   <?php echo e((float) ($employee->seniority_rate ?? 0)); ?>,
    family_status:    '<?php echo e($employee->family_status ?? 'celibataire'); ?>',
    children_count:   <?php echo e((int) ($employee->children_count ?? 0)); ?>,
    working_hours:    <?php echo e((float) ($workingData['working_hours'] ?? 191.25)); ?>,
    ot_day:           <?php echo e((float) ($workingData['overtime_day'] ?? 0)); ?>,
    ot_night:         <?php echo e((float) ($workingData['overtime_night'] ?? 0)); ?>,
    ot_weekend:       <?php echo e((float) ($workingData['overtime_weekend'] ?? 0)); ?>,
    absence_hours:    <?php echo e((float) ($workingData['absence_hours'] ?? 0)); ?>,
    delay_hours:      <?php echo e((float) ($workingData['delay_hours'] ?? 0)); ?>,
};
const EXISTING = {
    salary_type:           '<?php echo e($existing?->salary_type ?? 'monthly'); ?>',
    hourly_rate:           <?php echo e((float) ($existing?->hourly_rate ?? 0)); ?>,
    base_salary:           <?php echo e((float) ($existing?->base_salary ?? $employee->base_salary)); ?>,
    performance_bonus:     <?php echo e((float) ($existing?->performance_bonus ?? 0)); ?>,
    transport_allowance:   <?php echo e((float) ($existing?->transport_allowance ?? 0)); ?>,
    meal_allowance:        <?php echo e((float) ($existing?->meal_allowance ?? 0)); ?>,
    housing_allowance:     <?php echo e((float) ($existing?->housing_allowance ?? 0)); ?>,
    responsibility_allowance: <?php echo e((float) ($existing?->responsibility_allowance ?? 0)); ?>,
    other_gains:           <?php echo e((float) ($existing?->other_gains ?? 0)); ?>,
    advance_deduction:     <?php echo e((float) ($existing?->advance_deduction ?? 0)); ?>,
    loan_deduction:        <?php echo e((float) ($existing?->loan_deduction ?? 0)); ?>,
    garnishment_deduction: <?php echo e((float) ($existing?->garnishment_deduction ?? 0)); ?>,
    other_deductions:      <?php echo e((float) ($existing?->other_deductions ?? 0)); ?>,
    mode_cotisation:       '<?php echo e($existing?->mode_cotisation ?? 'auto'); ?>',
    cnss_deduction_manual: <?php echo e((float) ($existing?->cnss_deduction_manual ?? 0)); ?>,
    amo_deduction_manual:  <?php echo e((float) ($existing?->amo_deduction_manual ?? 0)); ?>,
    fp_deduction_manual:   <?php echo e((float) ($existing?->fp_deduction_manual ?? 0)); ?>,
};
</script>

<form action="<?php echo e(route('salary.update', $employee)); ?>" method="POST" id="salaryForm">
<?php echo csrf_field(); ?>
<input type="hidden" name="month" value="<?php echo e($month); ?>">
<input type="hidden" name="year"  value="<?php echo e($year); ?>">
<?php if($existing): ?>
<input type="hidden" name="salary_id" value="<?php echo e($existing->id); ?>">
<?php endif; ?>


<input type="hidden" name="gross_salary"       id="h_gross_salary">
<input type="hidden" name="seniority_bonus"    id="h_seniority_bonus">
<input type="hidden" name="overtime_day_amount"    id="h_ot_day_amount">
<input type="hidden" name="overtime_night_amount"  id="h_ot_night_amount">
<input type="hidden" name="overtime_weekend_amount" id="h_ot_wknd_amount">
<input type="hidden" name="overtime_hours"     id="h_overtime_hours">
<input type="hidden" name="absence_deduction"  id="h_absence_deduction">
<input type="hidden" name="absence_days"       id="h_absence_days">
<input type="hidden" name="cnss_base"          id="h_cnss_base">
<input type="hidden" name="cnss_deduction"     id="h_cnss_deduction">
<input type="hidden" name="amo_deduction"      id="h_amo_deduction">
<input type="hidden" name="fp_deduction"       id="h_fp_deduction">
<input type="hidden" name="taxable_income"     id="h_taxable_income">
<input type="hidden" name="ir_annual"          id="h_ir_annual">
<input type="hidden" name="ir_family_deduction" id="h_ir_family_deduction">
<input type="hidden" name="ir_deduction"       id="h_ir_deduction">
<input type="hidden" name="net_salary"         id="h_net_salary">
<input type="hidden" name="employer_cnss"      id="h_employer_cnss">
<input type="hidden" name="employer_amo"       id="h_employer_amo">
<input type="hidden" name="employer_tfp"       id="h_employer_tfp">
<input type="hidden" name="employer_total_cost" id="h_employer_total_cost">
<input type="hidden" name="overtime_hours_day"    id="h_ot_day_h">
<input type="hidden" name="overtime_hours_night"  id="h_ot_night_h">
<input type="hidden" name="overtime_hours_weekend" id="h_ot_wknd_h">
<input type="hidden" name="working_hours"      id="h_working_hours">
<input type="hidden" name="absence_hours"      id="h_abs_hours">
<input type="hidden" name="delay_hours"        id="h_delay_hours">
<input type="hidden" name="hourly_rate"        id="h_hourly_rate">


<div class="card mb-4" style="border-left:4px solid var(--primary)">
    <div class="card-header" style="border:none;padding:16px 20px">
        <div class="card-title" style="font-size:1.05rem;color:#0066cc">
            ⏱ TEMPS DE TRAVAIL — <?php echo e(\Carbon\Carbon::create($year,$month)->locale('fr')->format('F Y')); ?>

        </div>
        <div style="font-size:0.8rem;color:var(--text-muted)">Données extraites automatiquement du pointage</div>
    </div>
    <div class="card-body" style="padding:0">
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--border-color)">
            <div style="background:var(--surface,white);padding:14px 16px">
                <div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;margin-bottom:4px">Heures travaillées</div>
                <div style="font-size:1.6rem;font-weight:700;color:#065f46" id="disp-working"><?php echo e($workingData['working_hours'] ?? 0); ?> h</div>
            </div>
            <div style="background:var(--surface,white);padding:14px 16px">
                <div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;margin-bottom:4px">H. supp jour (25%)</div>
                <div style="font-size:1.6rem;font-weight:700;color:#d97706" id="disp-ot-day"><?php echo e($workingData['overtime_day'] ?? 0); ?> h</div>
            </div>
            <div style="background:var(--surface,white);padding:14px 16px">
                <div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;margin-bottom:4px">Heures absence</div>
                <div style="font-size:1.6rem;font-weight:700;color:#ef4444" id="disp-abs"><?php echo e($workingData['absence_hours'] ?? 0); ?> h</div>
            </div>
            <div style="background:var(--surface,white);padding:14px 16px">
                <div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;margin-bottom:4px">Heures retard</div>
                <div style="font-size:1.6rem;font-weight:700;color:#ec4899" id="disp-delay"><?php echo e($workingData['delay_hours'] ?? 0); ?> h</div>
            </div>
        </div>
    </div>
</div>


<div class="card mb-4" style="border-left:4px solid #7c3aed">
    <div class="card-header" style="border:none;padding:14px 20px">
        <div class="card-title" style="font-size:1.0rem;color:#7c3aed">TYPE DE SALAIRE</div>
    </div>
    <div class="card-body" style="padding:12px 20px">

        
        <div style="display:flex;gap:24px;align-items:center;flex-wrap:wrap">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="radio" name="salary_type" value="monthly" id="type_monthly"
                       style="cursor:pointer;width:15px;height:15px" onchange="onTypeChange()">
                <strong>Salaire mensuel</strong>
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="radio" name="salary_type" value="hourly" id="type_hourly"
                       style="cursor:pointer;width:15px;height:15px" onchange="onTypeChange()">
                <strong>Salaire horaire</strong>
            </label>
            <div style="display:flex;align-items:center;gap:8px">
                <label style="font-size:0.85rem;color:var(--text-muted);white-space:nowrap">Taux horaire (MAD)</label>
                <input type="number" name="hourly_rate_display" id="hourly_rate" class="form-control"
                       value="<?php echo e($existing?->hourly_rate ?? $employee->hourly_rate ?? 0); ?>"
                       step="0.01" min="0" style="width:130px;text-align:right" disabled oninput="calculate()">
            </div>

            
            <div style="width:1px;height:32px;background:var(--border-color);margin:0 4px"></div>

            
            <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:240px">
                <label style="font-size:0.85rem;color:var(--text-muted);white-space:nowrap">
                    💱 Devise d'affichage
                </label>
                <select id="currency_select" class="form-control"
                        style="flex:1;font-size:0.85rem;min-width:210px;max-width:280px"
                        onchange="onCurrencyChange()">
                    <optgroup label="🌍 Maghreb & Mauritanie">
                        <option value="MAD" data-symbol="MAD" data-rate="1"       selected>🇲🇦 MAD — Dirham marocain</option>
                        <option value="MRU" data-symbol="MRU" data-rate="2.77"           >🇲🇷 MRU — Ouguiya mauritanien</option>
                        <option value="DZD" data-symbol="DZD" data-rate="0.076"          >🇩🇿 DZD — Dinar algérien</option>
                        <option value="TND" data-symbol="TND" data-rate="0.29"           >🇹🇳 TND — Dinar tunisien</option>
                        <option value="LYD" data-symbol="LYD" data-rate="0.20"           >🇱🇾 LYD — Dinar libyen</option>
                        <option value="EGP" data-symbol="EGP" data-rate="0.056"          >🇪🇬 EGP — Livre égyptienne</option>
                    </optgroup>
                    <optgroup label="🌍 Afrique de l'Ouest">
                        <option value="XOF" data-symbol="XOF" data-rate="0.152"          >🌐 XOF — Franc CFA BCEAO</option>
                        <option value="NGN" data-symbol="NGN" data-rate="0.0066"         >🇳🇬 NGN — Naira nigérian</option>
                        <option value="GHS" data-symbol="GHS" data-rate="0.60"           >🇬🇭 GHS — Cedi ghanéen</option>
                        <option value="GMD" data-symbol="GMD" data-rate="1.33"           >🇬🇲 GMD — Dalasi gambien</option>
                        <option value="SLL" data-symbol="SLL" data-rate="0.0044"         >🇸🇱 SLL — Leone sierra-léonais</option>
                        <option value="GNF" data-symbol="GNF" data-rate="0.86"           >🇬🇳 GNF — Franc guinéen</option>
                        <option value="MWK" data-symbol="MWK" data-rate="0.058"          >🇲🇼 MWK — Kwacha malawien</option>
                    </optgroup>
                    <optgroup label="🌍 Afrique de l'Est & Centrale">
                        <option value="KES" data-symbol="KES" data-rate="0.070"          >🇰🇪 KES — Shilling kenyan</option>
                        <option value="ETB" data-symbol="ETB" data-rate="0.074"          >🇪🇹 ETB — Birr éthiopien</option>
                        <option value="TZS" data-symbol="TZS" data-rate="0.035"          >🇹🇿 TZS — Shilling tanzanien</option>
                        <option value="XAF" data-symbol="XAF" data-rate="0.152"          >🌐 XAF — Franc CFA BEAC</option>
                        <option value="RWF" data-symbol="RWF" data-rate="0.073"          >🇷🇼 RWF — Franc rwandais</option>
                        <option value="UGX" data-symbol="UGX" data-rate="0.27"           >🇺🇬 UGX — Shilling ougandais</option>
                        <option value="SDG" data-symbol="SDG" data-rate="0.17"           >🇸🇩 SDG — Livre soudanaise</option>
                    </optgroup>
                    <optgroup label="🌍 Afrique du Sud & Australe">
                        <option value="ZAR" data-symbol="ZAR" data-rate="0.053"          >🇿🇦 ZAR — Rand sud-africain</option>
                        <option value="ZMW" data-symbol="ZMW" data-rate="0.047"          >🇿🇲 ZMW — Kwacha zambien</option>
                        <option value="MZN" data-symbol="MZN" data-rate="0.016"          >🇲🇿 MZN — Metical mozambicain</option>
                        <option value="BWP" data-symbol="BWP" data-rate="0.073"          >🇧🇼 BWP — Pula botswanais</option>
                        <option value="MGA" data-symbol="MGA" data-rate="0.22"           >🇲🇬 MGA — Ariary malgache</option>
                    </optgroup>
                    <optgroup label="💶 Devises internationales">
                        <option value="EUR" data-symbol="€"   data-rate="0.092"          >🇪🇺 EUR — Euro</option>
                        <option value="USD" data-symbol="$"   data-rate="0.100"          >🇺🇸 USD — Dollar américain</option>
                        <option value="GBP" data-symbol="£"   data-rate="0.079"          >🇬🇧 GBP — Livre sterling</option>
                        <option value="SAR" data-symbol="SAR" data-rate="0.375"          >🇸🇦 SAR — Riyal saoudien</option>
                        <option value="AED" data-symbol="AED" data-rate="0.367"          >🇦🇪 AED — Dirham émirati</option>
                        <option value="CAD" data-symbol="CAD" data-rate="0.138"          >🇨🇦 CAD — Dollar canadien</option>
                        <option value="CNY" data-symbol="CNY" data-rate="0.72"           >🇨🇳 CNY — Yuan chinois</option>
                    </optgroup>
                </select>

                <span id="currency_rate_badge"
                      style="font-size:0.72rem;color:#7c3aed;background:#f5f3ff;
                             padding:3px 8px;border-radius:20px;white-space:nowrap;
                             border:1px solid #ddd6fe;font-weight:600">
                    = 1 MAD
                </span>
            </div>
        </div>

        
        <div id="currency_banner" style="display:none;margin-top:10px;padding:9px 14px;
             background:#fffbeb;border:1px solid #fcd34d;border-radius:6px;
             font-size:0.82rem;color:#78350f;display:none;align-items:center;gap:8px">
            <span style="font-size:1rem">⚠️</span>
            <span>
                Affichage converti en <strong id="currency_banner_code">USD</strong> à titre indicatif
                (taux : <strong id="currency_banner_rate">1 MAD = 0,1000 USD</strong>).
                La paie est <u>toujours enregistrée en MAD</u>.
                Les taux sont approximatifs — mettez-les à jour selon le cours du jour.
            </span>
        </div>

    </div>
</div>


<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">


<div>

    
    <div class="card mb-4" style="border-left:3px solid var(--primary)">
        <div class="card-body" style="padding:10px 16px">
            <div style="display:flex;gap:20px;font-size:0.83rem;flex-wrap:wrap">
                <div><span style="color:var(--text-muted)">Base contrat</span>
                    <strong style="margin-left:6px"><?php echo e(number_format($employee->base_salary,0,',',' ')); ?> MAD</strong></div>
                <div><span style="color:var(--text-muted)">Ancienneté</span>
                    <strong style="margin-left:6px"><?php echo e($employee->seniority_label); ?></strong></div>
                <div><span style="color:var(--text-muted)">Situation</span>
                    <strong style="margin-left:6px"><?php echo e(ucfirst($employee->family_status ?? 'Célibataire')); ?> — <?php echo e($employee->children_count ?? 0); ?> enfant(s)</strong></div>
            </div>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header" style="background:#f0fff4;border-bottom:2px solid #d1fae5">
            <div class="card-title" style="color:#065f46">GAINS</div>
            <div style="font-size:0.78rem;color:#059669">
                Éléments constitutifs du salaire brut
                <span id="gains_currency_note" style="display:none;margin-left:8px;color:#7c3aed;font-weight:600"></span>
            </div>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f9fafb">
                        <th style="padding:9px 14px;text-align:left;font-size:0.75rem;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border-color)">Rubrique</th>
                        <th style="padding:9px 14px;text-align:right;font-size:0.75rem;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border-color);width:155px">
                            Montant (<span class="cur-label">MAD</span>)
                        </th>
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
                                   value="<?php echo e(old('base_salary', $existing?->base_salary ?? $employee->base_salary)); ?>"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>

                    
                    <tr style="border-bottom:1px solid var(--border-color);background:#f9fafb">
                        <td style="padding:9px 14px">
                            <div style="font-weight:600">Prime d'ancienneté
                                <span class="badge badge-info" style="font-size:0.65rem">Auto</span>
                            </div>
                            <div style="font-size:0.75rem;color:var(--text-muted)" id="seniority-sub">
                                <?php echo e($employee->seniority_years); ?> an(s) → <?php echo e(($employee->seniority_rate * 100)); ?>%
                            </div>
                        </td>
                        <td style="padding:9px 14px;text-align:right">
                            <div class="font-semibold bonus" id="seniority-val">0,00 MAD</div>
                            <div style="font-size:0.72rem;color:var(--text-muted)">Calculé auto.</div>
                        </td>
                    </tr>

                    
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td colspan="2" style="padding:0">
                            <div style="padding:8px 14px;font-weight:600;font-size:0.82rem;color:#92400e;background:#fffbeb;border-bottom:1px solid #fde68a">
                                Heures supplémentaires
                                <span style="font-weight:400;font-size:0.75rem;color:var(--text-muted)"> — taux horaire × majoration légale</span>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;background:#fffbeb">
                                <div style="padding:10px 14px;border-right:1px solid #fde68a">
                                    <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px">Jour +25%</div>
                                    <div style="font-size:1.1rem;font-weight:700;color:#d97706" id="ot-day-h-disp"><?php echo e($workingData['overtime_day'] ?? 0); ?> h</div>
                                    <div style="font-size:0.72rem;color:var(--text-muted)" id="ot-day-amt-disp">= 0,00 MAD</div>
                                </div>
                                <div style="padding:10px 14px;border-right:1px solid #fde68a">
                                    <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px">Nuit +50%</div>
                                    <div style="font-size:0.9rem;font-weight:700;color:#d97706" id="ot-night-h-disp"><?php echo e($workingData['overtime_night'] ?? 0); ?> h</div>
                                    <input type="number" id="ot_night_h" class="form-control"
                                           value="<?php echo e($workingData['overtime_night'] ?? 0); ?>"
                                           step="0.5" min="0" style="margin-top:4px;font-size:0.8rem" oninput="calculate()">
                                    <div style="font-size:0.72rem;color:var(--text-muted)" id="ot-night-amt-disp">= 0,00 MAD</div>
                                </div>
                                <div style="padding:10px 14px">
                                    <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px">Weekend +100%</div>
                                    <div style="font-size:0.9rem;font-weight:700;color:#d97706" id="ot-wknd-h-disp"><?php echo e($workingData['overtime_weekend'] ?? 0); ?> h</div>
                                    <input type="number" id="ot_wknd_h" class="form-control"
                                           value="<?php echo e($workingData['overtime_weekend'] ?? 0); ?>"
                                           step="0.5" min="0" style="margin-top:4px;font-size:0.8rem" oninput="calculate()">
                                    <div style="font-size:0.72rem;color:var(--text-muted)" id="ot-wknd-amt-disp">= 0,00 MAD</div>
                                </div>
                            </div>
                            <div style="padding:6px 14px;background:#fffbeb;border-top:1px solid #fde68a;display:flex;justify-content:space-between;font-size:0.82rem">
                                <span style="color:var(--text-muted)">Total HS</span>
                                <span style="font-weight:700;color:#d97706" id="ot-total-disp">0,00 MAD</span>
                            </div>
                        </td>
                    </tr>

                    
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Prime de rendement</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="performance_bonus" id="performance_bonus" class="form-control"
                                   value="<?php echo e(old('performance_bonus', $existing?->performance_bonus ?? 0)); ?>"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>

                    
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Indemnité de transport</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="transport_allowance" id="transport_allowance" class="form-control"
                                   value="<?php echo e(old('transport_allowance', $existing?->transport_allowance ?? 0)); ?>"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>

                    
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Prime de panier</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="meal_allowance" id="meal_allowance" class="form-control"
                                   value="<?php echo e(old('meal_allowance', $existing?->meal_allowance ?? 0)); ?>"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>

                    
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Indemnité logement</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="housing_allowance" id="housing_allowance" class="form-control"
                                   value="<?php echo e(old('housing_allowance', $existing?->housing_allowance ?? 0)); ?>"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>

                    
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Indemnité de responsabilité</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="responsibility_allowance" id="responsibility_allowance" class="form-control"
                                   value="<?php echo e(old('responsibility_allowance', $existing?->responsibility_allowance ?? 0)); ?>"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>

                    
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Autres gains</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="other_gains" id="other_gains" class="form-control"
                                   value="<?php echo e(old('other_gains', $existing?->other_gains ?? 0)); ?>"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>

                    
                    <?php if($variableElements->where('category','gain')->count()): ?>
                    <tr style="background:#f0fff4">
                        <td colspan="2" style="padding:9px 14px">
                            <div style="font-weight:600;font-size:0.78rem;color:#065f46;margin-bottom:5px">Éléments variables (gains)</div>
                            <?php $__currentLoopData = $variableElements->where('category','gain'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ve): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div style="display:flex;justify-content:space-between;font-size:0.8rem;padding:2px 0">
                                <span><?php echo e($ve->label); ?></span>
                                <span class="bonus font-semibold">+<?php echo e(number_format($ve->amount,2,',',' ')); ?> MAD</span>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </td>
                    </tr>
                    <?php endif; ?>

                    
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


<div>

    
    <div class="card mb-4">
        <div class="card-header" style="background:#eff6ff;border-bottom:2px solid #bfdbfe">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px">
                <div>
                    <div class="card-title" style="color:#1e3a5f">COTISATIONS SOCIALES</div>
                    <div style="font-size:0.75rem;color:#2563eb">Mode automatique = taux légaux marocains</div>
                </div>
                <div style="display:flex;gap:4px;background:white;padding:3px;border-radius:4px;border:1px solid #dbeafe">
                    <label style="padding:5px 11px;cursor:pointer;font-weight:600;border-radius:3px;font-size:0.82rem" id="autoLabel">
                        <input type="radio" name="mode_cotisation" value="auto"
                               style="cursor:pointer;margin-right:4px" onchange="toggleCotisationMode()">
                        Automatique
                    </label>
                    <label style="padding:5px 11px;cursor:pointer;font-weight:600;border-radius:3px;font-size:0.82rem" id="manuelLabel">
                        <input type="radio" name="mode_cotisation" value="manual"
                               style="cursor:pointer;margin-right:4px" onchange="toggleCotisationMode()">
                        Manuel
                    </label>
                </div>
            </div>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse;font-size:0.85rem">
                
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:9px 14px">
                        <div style="font-weight:600">CNSS salariale</div>
                        <div style="font-size:0.75rem;color:var(--text-muted)" id="cnss-sub">4,48% × brut plafonné à 6 000 MAD/mois</div>
                    </td>
                    <td style="padding:9px 14px;width:155px;text-align:right">
                        <div id="cnss-auto" style="font-weight:600;padding:6px 0">0,00 MAD</div>
                        <input type="number" name="cnss_deduction_manual" id="cnss-manual" class="form-control"
                               value="<?php echo e($existing?->cnss_deduction_manual ?? 0); ?>"
                               step="0.01" min="0" style="display:none;text-align:right" oninput="calculate()">
                    </td>
                </tr>
                
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:9px 14px">
                        <div style="font-weight:600">AMO salariale</div>
                        <div style="font-size:0.75rem;color:var(--text-muted)">2,26% du salaire brut</div>
                    </td>
                    <td style="padding:9px 14px;text-align:right">
                        <div id="amo-auto" style="font-weight:600;padding:6px 0">0,00 MAD</div>
                        <input type="number" name="amo_deduction_manual" id="amo-manual" class="form-control"
                               value="<?php echo e($existing?->amo_deduction_manual ?? 0); ?>"
                               step="0.01" min="0" style="display:none;text-align:right" oninput="calculate()">
                    </td>
                </tr>
                
                <tr style="border-bottom:1px solid var(--border-color);background:#f0f9ff">
                    <td style="padding:9px 14px">
                        <div style="font-weight:600">Frais professionnels
                            <span class="badge badge-success" style="font-size:0.62rem">Déduction fiscale</span>
                        </div>
                        <div style="font-size:0.75rem;color:var(--text-muted)">20% du brut, plafonné à 2 500 MAD/mois</div>
                    </td>
                    <td style="padding:9px 14px;text-align:right">
                        <div id="fp-auto" style="font-weight:600;color:#059669;padding:6px 0">0,00 MAD</div>
                        <input type="number" name="fp_deduction_manual" id="fp-manual" class="form-control"
                               value="<?php echo e($existing?->fp_deduction_manual ?? 0); ?>"
                               step="0.01" min="0" style="display:none;text-align:right" oninput="calculate()">
                    </td>
                </tr>
                
                <tr style="border-bottom:1px solid var(--border-color);background:#f0f9ff">
                    <td style="padding:9px 14px;font-weight:600;color:#1e3a5f">Net imposable (NI)</td>
                    <td style="padding:9px 14px;text-align:right;font-weight:700;color:#1e3a5f" id="taxable-display">0,00 MAD</td>
                </tr>
                
                <tr style="background:#dbeafe;border-top:2px solid #0284c7">
                    <td style="padding:11px 14px;font-weight:700;color:#1e3a5f">TOTAL COTISATIONS (CNSS+AMO)</td>
                    <td style="padding:11px 14px;text-align:right;font-weight:700;color:#1e3a5f;font-size:1rem" id="cot-total-display">0,00 MAD</td>
                </tr>
            </table>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header" style="background:#fef3c7;border-bottom:2px solid #fcd34d">
            <div class="card-title" style="color:#78350f">IR — Impôt sur le Revenu</div>
            <div style="font-size:0.75rem;color:#92400e">Barème progressif annuel DGI ÷ 12</div>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse;font-size:0.83rem">
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:8px 14px;color:var(--text-muted)">IR annuel brut (barème)</td>
                    <td style="padding:8px 14px;text-align:right;font-weight:600" id="ir-annual">0,00 MAD</td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:8px 14px;color:var(--text-muted)">Déductions familiales (360 MAD/pers.)</td>
                    <td style="padding:8px 14px;text-align:right;font-weight:600;color:#059669" id="ir-family">−0,00 MAD</td>
                </tr>
                <tr style="background:#fef3c7">
                    <td style="padding:9px 14px;font-weight:700;color:#78350f">IR mensuel retenu</td>
                    <td style="padding:9px 14px;text-align:right;font-weight:700;color:#991b1b" id="ir-monthly">0,00 MAD</td>
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
                            <div style="font-size:0.75rem;color:var(--text-muted)" id="absence-sub">
                                (Brut / 191,25 h) × <?php echo e($workingData['absence_hours'] ?? 0); ?> h = calculé auto
                            </div>
                        </td>
                        <td style="padding:9px 14px;text-align:right">
                            <div style="font-weight:600;color:#991b1b" id="absence-auto">0,00 MAD</div>
                        </td>
                    </tr>
                    
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Avance sur salaire</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="advance_deduction" id="advance_deduction" class="form-control"
                                   value="<?php echo e(old('advance_deduction', $existing?->advance_deduction ?? 0)); ?>"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>
                    
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Remboursement de prêt</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="loan_deduction" id="loan_deduction" class="form-control"
                                   value="<?php echo e(old('loan_deduction', $existing?->loan_deduction ?? 0)); ?>"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>
                    
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Saisie sur salaire</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="garnishment_deduction" id="garnishment_deduction" class="form-control"
                                   value="<?php echo e(old('garnishment_deduction', $existing?->garnishment_deduction ?? 0)); ?>"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>
                    
                    <tr style="border-bottom:1px solid var(--border-color)">
                        <td style="padding:9px 14px"><div style="font-weight:600">Autres retenues</div></td>
                        <td style="padding:9px 14px">
                            <input type="number" name="other_deductions" id="other_deductions" class="form-control"
                                   value="<?php echo e(old('other_deductions', $existing?->other_deductions ?? 0)); ?>"
                                   step="0.01" min="0" style="text-align:right" oninput="calculate()">
                        </td>
                    </tr>
                    
                    <?php if($variableElements->where('category','retenue')->count()): ?>
                    <tr style="background:#fff0f0">
                        <td colspan="2" style="padding:9px 14px">
                            <div style="font-weight:600;font-size:0.78rem;color:#991b1b;margin-bottom:5px">Éléments variables (retenues)</div>
                            <?php $__currentLoopData = $variableElements->where('category','retenue'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ve): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div style="display:flex;justify-content:space-between;font-size:0.8rem;padding:2px 0">
                                <span><?php echo e($ve->label); ?></span>
                                <span class="deduction font-semibold">−<?php echo e(number_format($ve->amount,2,',',' ')); ?> MAD</span>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    
                    <tr style="background:#fecaca;border-top:2px solid #f87171">
                        <td style="padding:11px 14px;font-weight:700;color:#991b1b">TOTAL RETENUES</td>
                        <td style="padding:11px 14px;text-align:right;font-weight:700;color:#991b1b;font-size:1rem" id="ret-total-display">0,00 MAD</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="card mb-4" style="border:2px solid var(--success);background:linear-gradient(135deg,#f0fdf4,#ffffff)">
        <div class="card-body" style="padding:20px;text-align:center">
            <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:6px;letter-spacing:0.08em;text-transform:uppercase;font-weight:600">
                Net à payer
            </div>
            <div style="font-size:2.8rem;font-weight:900;color:var(--success);letter-spacing:-1px">
                <span id="net-display">0,00</span> <span style="font-size:1.4rem" class="cur-label">MAD</span>
            </div>
            
            <div id="net-converted-line" style="display:none;margin-top:6px;font-size:0.9rem;color:#7c3aed;font-weight:600">
                ≈ <span id="net-converted-display">0,00</span> <span id="net-converted-code">USD</span>
                <span style="font-size:0.72rem;font-weight:400;color:var(--text-muted)">(indicatif)</span>
            </div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:8px" id="net-detail">
                Brut 0,00 − Cotis. 0,00 − FP 0,00 − IR 0,00 − Retenues 0,00
            </div>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header" style="background:#fef3c7;border-bottom:2px solid #fcd34d">
            <div class="card-title" style="color:#78350f">Charges patronales <span style="font-size:0.72rem;font-weight:400">(informatives)</span></div>
        </div>
        <div class="card-body" style="padding:0">
            <table style="width:100%;border-collapse:collapse;font-size:0.83rem">
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 14px;color:var(--text-muted)">CNSS patronale (10,29%)</td>
                    <td style="padding:7px 14px;text-align:right;color:#d97706;font-weight:600" id="emp-cnss">0,00</td>
                </tr>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:7px 14px;color:var(--text-muted)">AMO patronale (2,26%)</td>
                    <td style="padding:7px 14px;text-align:right;color:#d97706;font-weight:600" id="emp-amo">0,00</td>
                </tr>
                <tr style="border-bottom:2px solid var(--border-color)">
                    <td style="padding:7px 14px;color:var(--text-muted)">TFP (1,60%)</td>
                    <td style="padding:7px 14px;text-align:right;color:#d97706;font-weight:600" id="emp-tfp">0,00</td>
                </tr>
                <tr style="background:#fef3c7">
                    <td style="padding:10px 14px;font-weight:700;color:#78350f">Coût total employeur</td>
                    <td style="padding:10px 14px;text-align:right;font-weight:700;color:#78350f" id="emp-total">0,00 MAD</td>
                </tr>
            </table>
        </div>
    </div>

    
    <div style="display:flex;gap:12px">
        <button type="submit" class="btn btn-primary" style="flex:1;font-size:1rem;padding:12px">
            ✓ Calculer &amp; Enregistrer
        </button>
        <a href="<?php echo e(route('variables.index', ['month'=>$month,'year'=>$year])); ?>" class="btn btn-ghost">
            Éléments variables
        </a>
    </div>

</div>
</div>

</form>

<script>
// ══════════════════════════════════════════════════════════════
//  CONSTANTES LÉGALES MAROCAINES
// ══════════════════════════════════════════════════════════════
const CNSS_RATE_SAL   = 0.0448;
const CNSS_PLAFOND    = 6000;
const AMO_RATE_SAL    = 0.0226;
const FP_RATE         = 0.20;
const FP_MAX          = 2500;
const HEURES_REF      = 191.25;

const CNSS_RATE_PAT   = 0.1029;
const AMO_RATE_PAT    = 0.0226;
const TFP_RATE        = 0.016;

// ══════════════════════════════════════════════════════════════
//  GESTION DE LA DEVISE
// ══════════════════════════════════════════════════════════════
let currentRate   = 1;
let currentSymbol = 'MAD';
let currentCode   = 'MAD';

function onCurrencyChange() {
    const sel = document.getElementById('currency_select');
    const opt = sel.options[sel.selectedIndex];
    currentRate   = parseFloat(opt.dataset.rate)   || 1;
    currentSymbol = opt.dataset.symbol             || 'MAD';
    currentCode   = opt.value;

    // Badge taux
    const badge = document.getElementById('currency_rate_badge');
    badge.textContent = currentCode === 'MAD'
        ? '= 1 MAD'
        : '1 MAD = ' + currentRate.toFixed(4) + ' ' + currentCode;

    // Bandeau avertissement
    const banner = document.getElementById('currency_banner');
    if (currentCode !== 'MAD') {
        banner.style.display = 'flex';
        document.getElementById('currency_banner_code').textContent = currentCode;
        document.getElementById('currency_banner_rate').textContent =
            '1 MAD = ' + currentRate.toFixed(4) + ' ' + currentCode;
    } else {
        banner.style.display = 'none';
    }

    // Note dans l'en-tête gains
    const note = document.getElementById('gains_currency_note');
    if (currentCode !== 'MAD') {
        note.style.display = 'inline';
        note.textContent   = '— Affichage en ' + currentCode;
    } else {
        note.style.display = 'none';
    }

    // Mettre à jour tous les libellés de devise
    document.querySelectorAll('.cur-label').forEach(el => el.textContent = currentCode);

    calculate();
}

// Formate un montant MAD en devise affichée (pour l'affichage uniquement)
function fmtMAD(n) {
    return parseFloat(n.toFixed(2))
        .toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2})
        + ' MAD';
}

// Formate pour affichage dans la devise courante
function fmtC(madAmount) {
    const converted = madAmount * currentRate;
    return parseFloat(converted.toFixed(2))
        .toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2})
        + ' ' + currentCode;
}

// Formate sans suffixe (pour les spans où le suffixe est séparé)
function fmtCVal(madAmount) {
    const converted = madAmount * currentRate;
    return parseFloat(converted.toFixed(2))
        .toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Formate toujours en MAD (pour les éléments qui restent en MAD)
function fmt(n) {
    return parseFloat(n.toFixed(2))
        .toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// ── Prime d'ancienneté (Art. 350 Code du travail) ─────────────
function seniorityRate(years) {
    if (years < 2)  return 0;
    if (years < 5)  return 0.05;
    if (years < 12) return 0.10;
    if (years < 20) return 0.15;
    if (years < 25) return 0.20;
    return 0.25;
}

// ── Barème IR annuel DGI (Art. 73 CGI — tranches 2024) ────────
function calcIRAnnuel(revenu) {
    if (revenu <= 30000)  return 0;
    if (revenu <= 50000)  return (revenu - 30000) * 0.10;
    if (revenu <= 60000)  return 2000  + (revenu - 50000) * 0.20;
    if (revenu <= 80000)  return 4000  + (revenu - 60000) * 0.30;
    if (revenu <= 180000) return 10000 + (revenu - 80000)  * 0.34;
    return 44000 + (revenu - 180000) * 0.38;
}

// ── Déductions familiales IR (Art. 74 CGI) ────────────────────
function calcDeductFam(status, children) {
    let d = 0;
    if (['marie','veuf','divorce'].includes(status)) d += 360;
    d += Math.min(children, 6) * 360;
    return d;
}

// ══════════════════════════════════════════════════════════════
//  MODE COTISATION
// ══════════════════════════════════════════════════════════════
function toggleCotisationMode() {
    const isManual = document.querySelector('input[name="mode_cotisation"]:checked').value === 'manual';

    ['cnss','amo','fp'].forEach(k => {
        document.getElementById(k+'-auto').style.display   = isManual ? 'none'  : 'block';
        document.getElementById(k+'-manual').style.display = isManual ? 'block' : 'none';
    });

    const aL = document.getElementById('autoLabel');
    const mL = document.getElementById('manuelLabel');
    aL.style.background = isManual ? 'white'   : '#e0f2fe';
    aL.style.color      = isManual ? 'var(--text-muted)' : '#0369a1';
    mL.style.background = isManual ? '#fef08a' : 'white';
    mL.style.color      = isManual ? '#78350f' : 'var(--text-muted)';

    calculate();
}

// ══════════════════════════════════════════════════════════════
//  TYPE DE SALAIRE
// ══════════════════════════════════════════════════════════════
function onTypeChange() {
    const isHourly = document.getElementById('type_hourly').checked;
    document.getElementById('hourly_rate').disabled = !isHourly;
    document.getElementById('base_salary').readOnly  = isHourly;
    document.getElementById('base-sub').textContent  = isHourly
        ? 'Calculé : taux horaire × heures travaillées'
        : 'Rémunération mensuelle contractuelle';
    calculate();
}

// ══════════════════════════════════════════════════════════════
//  CALCUL PRINCIPAL
// ══════════════════════════════════════════════════════════════
function calculate() {

    // ─── 1. Données pointage ──────────────────────────────────
    const workH    = EMPLOYEE_DATA.working_hours;
    const otDayH   = EMPLOYEE_DATA.ot_day;
    const absH     = EMPLOYEE_DATA.absence_hours;
    const delayH   = EMPLOYEE_DATA.delay_hours;
    const otNightH = parseFloat(document.getElementById('ot_night_h').value) || 0;
    const otWkndH  = parseFloat(document.getElementById('ot_wknd_h').value)  || 0;

    document.getElementById('disp-working').textContent  = workH  + ' h';
    document.getElementById('disp-ot-day').textContent   = otDayH + ' h';
    document.getElementById('disp-abs').textContent      = absH   + ' h';
    document.getElementById('disp-delay').textContent    = delayH + ' h';
    document.getElementById('ot-day-h-disp').textContent   = otDayH   + ' h';
    document.getElementById('ot-night-h-disp').textContent = otNightH + ' h';
    document.getElementById('ot-wknd-h-disp').textContent  = otWkndH  + ' h';

    // ─── 2. Salaire de base ───────────────────────────────────
    const isHourly  = document.getElementById('type_hourly').checked;
    const hourlyRate = parseFloat(document.getElementById('hourly_rate').value) || 0;
    let baseSalary;

    if (isHourly) {
        baseSalary = hourlyRate * workH;
        document.getElementById('base_salary').value = baseSalary.toFixed(2);
    } else {
        baseSalary = parseFloat(document.getElementById('base_salary').value) || 0;
    }

    const tauxH = isHourly ? hourlyRate : (baseSalary / HEURES_REF);

    // ─── 3. Prime ancienneté ──────────────────────────────────
    const sRate     = seniorityRate(EMPLOYEE_DATA.seniority_years);
    const seniority = baseSalary * sRate;
    document.getElementById('seniority-val').textContent = fmtC(seniority);

    // ─── 4. Heures supplémentaires ────────────────────────────
    const otDayAmt   = tauxH * otDayH  * 1.25;
    const otNightAmt = tauxH * otNightH * 1.50;
    const otWkndAmt  = tauxH * otWkndH  * 2.00;
    const totalOT    = otDayAmt + otNightAmt + otWkndAmt;

    document.getElementById('ot-day-amt-disp').textContent   = '= ' + fmtC(otDayAmt);
    document.getElementById('ot-night-amt-disp').textContent = '= ' + fmtC(otNightAmt);
    document.getElementById('ot-wknd-amt-disp').textContent  = '= ' + fmtC(otWkndAmt);
    document.getElementById('ot-total-disp').textContent     = fmtC(totalOT);

    // ─── 5. Autres gains ──────────────────────────────────────
    const perfBonus      = parseFloat(document.getElementById('performance_bonus').value)      || 0;
    const transport      = parseFloat(document.getElementById('transport_allowance').value)    || 0;
    const meal           = parseFloat(document.getElementById('meal_allowance').value)         || 0;
    const housing        = parseFloat(document.getElementById('housing_allowance').value)      || 0;
    const responsibility = parseFloat(document.getElementById('responsibility_allowance').value)|| 0;
    const otherGains     = parseFloat(document.getElementById('other_gains').value)            || 0;

    // ─── 6. Déduction absences ────────────────────────────────
    const absDeduction = tauxH * absH;
    document.getElementById('absence-auto').textContent = fmtC(absDeduction);
    document.getElementById('absence-sub').textContent  =
        '(' + fmt(baseSalary) + ' / ' + HEURES_REF + ' h) × ' + absH + ' h = ' + fmt(absDeduction) + ' MAD';

    // ─── 7. SALAIRE BRUT ──────────────────────────────────────
    const grossSalary = Math.max(0,
        baseSalary + seniority + totalOT
        + perfBonus + transport + meal + housing + responsibility + otherGains
        - absDeduction
    );
    document.getElementById('gross-display').textContent = fmtCVal(grossSalary);

    // ─── 8. Cotisations salariales ────────────────────────────
    const isManual = document.querySelector('input[name="mode_cotisation"]:checked')?.value === 'manual';
    let cnss, amo, fp;

    if (isManual) {
        cnss = parseFloat(document.getElementById('cnss-manual').value) || 0;
        amo  = parseFloat(document.getElementById('amo-manual').value)  || 0;
        fp   = parseFloat(document.getElementById('fp-manual').value)   || 0;
    } else {
        const cnssBase = Math.min(grossSalary, CNSS_PLAFOND);
        cnss = cnssBase * CNSS_RATE_SAL;
        amo  = grossSalary * AMO_RATE_SAL;
        fp   = Math.min(grossSalary * FP_RATE, FP_MAX);
        // Cotisations toujours affichées en MAD (réglementaire)
        document.getElementById('cnss-auto').textContent = fmtMAD(cnss);
        document.getElementById('amo-auto').textContent  = fmtMAD(amo);
        document.getElementById('fp-auto').textContent   = fmtMAD(fp);
        document.getElementById('cnss-sub').textContent  =
            '4,48% × min(' + fmt(grossSalary) + ', 6 000) = ' + fmt(cnss) + ' MAD';
    }

    const totalCot = cnss + amo;

    // ─── 9. Net imposable ─────────────────────────────────────
    const taxableIncome = Math.max(0, grossSalary - cnss - amo - fp);
    document.getElementById('taxable-display').textContent   = fmtMAD(taxableIncome);
    document.getElementById('cot-total-display').textContent = fmtMAD(totalCot);

    // ─── 10. IR ───────────────────────────────────────────────
    const revAnnuel    = taxableIncome * 12;
    const irAnnuelBrut = calcIRAnnuel(revAnnuel);
    const deductFam    = calcDeductFam(EMPLOYEE_DATA.family_status, EMPLOYEE_DATA.children_count);
    const irAnnuelNet  = Math.max(0, irAnnuelBrut - deductFam);
    const irMensuel    = irAnnuelNet / 12;

    document.getElementById('ir-annual').textContent  = fmtMAD(irAnnuelBrut);
    document.getElementById('ir-family').textContent  = '−' + fmt(deductFam) + ' MAD';
    document.getElementById('ir-monthly').textContent = fmtMAD(irMensuel);

    // ─── 11. Retenues diverses ────────────────────────────────
    const advance     = parseFloat(document.getElementById('advance_deduction').value)     || 0;
    const loan        = parseFloat(document.getElementById('loan_deduction').value)        || 0;
    const garnishment = parseFloat(document.getElementById('garnishment_deduction').value) || 0;
    const otherDed    = parseFloat(document.getElementById('other_deductions').value)      || 0;
    const totalRet    = advance + loan + garnishment + otherDed;
    document.getElementById('ret-total-display').textContent = fmtMAD(totalRet);

    // ─── 12. NET À PAYER ──────────────────────────────────────
    const netSalary = Math.max(0,
        grossSalary - totalCot - fp - irMensuel - totalRet
    );

    // Valeur nette dans la devise choisie
    document.getElementById('net-display').textContent = fmtCVal(netSalary);

    // Ligne de conversion (si devise ≠ MAD, afficher équivalent MAD en dessous)
    const netConvLine = document.getElementById('net-converted-line');
    if (currentCode !== 'MAD') {
        netConvLine.style.display = 'block';
        document.getElementById('net-converted-display').textContent = fmt(netSalary);
        document.getElementById('net-converted-code').textContent    = 'MAD';
    } else {
        netConvLine.style.display = 'none';
    }

    document.getElementById('net-detail').textContent =
        'Brut ' + fmt(grossSalary) +
        ' − Cotis. ' + fmt(totalCot) +
        ' − FP ' + fmt(fp) +
        ' − IR ' + fmt(irMensuel) +
        ' − Retenues ' + fmt(totalRet) + ' (MAD)';

    // ─── 13. Charges patronales ───────────────────────────────
    const cnssPatBase = Math.min(grossSalary, CNSS_PLAFOND);
    const empCnss  = cnssPatBase * CNSS_RATE_PAT;
    const empAmo   = grossSalary * AMO_RATE_PAT;
    const empTfp   = grossSalary * TFP_RATE;
    const empTotal = netSalary + totalCot + fp + irMensuel + empCnss + empAmo + empTfp;
    document.getElementById('emp-cnss').textContent  = fmt(empCnss);
    document.getElementById('emp-amo').textContent   = fmt(empAmo);
    document.getElementById('emp-tfp').textContent   = fmt(empTfp);
    document.getElementById('emp-total').textContent = fmt(empTotal) + ' MAD';

    // ─── 14. Champs cachés (toujours en MAD) ─────────────────
    document.getElementById('h_gross_salary').value        = grossSalary.toFixed(2);
    document.getElementById('h_seniority_bonus').value     = seniority.toFixed(2);
    document.getElementById('h_ot_day_amount').value       = otDayAmt.toFixed(2);
    document.getElementById('h_ot_night_amount').value     = otNightAmt.toFixed(2);
    document.getElementById('h_ot_wknd_amount').value      = otWkndAmt.toFixed(2);
    document.getElementById('h_overtime_hours').value      = (otDayH + otNightH + otWkndH).toFixed(2);
    document.getElementById('h_absence_deduction').value   = absDeduction.toFixed(2);
    document.getElementById('h_absence_days').value        = (absH / 8).toFixed(2);
    document.getElementById('h_cnss_base').value           = Math.min(grossSalary, CNSS_PLAFOND).toFixed(2);
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
    document.getElementById('h_hourly_rate').value         = (isHourly ? hourlyRate : 0).toFixed(2);
}

// ══════════════════════════════════════════════════════════════
//  INITIALISATION
// ══════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function () {

    // Type de salaire
    const savedType = EXISTING.salary_type;
    if (savedType === 'hourly') {
        document.getElementById('type_hourly').checked  = true;
        document.getElementById('hourly_rate').disabled = false;
        document.getElementById('hourly_rate').value    = EXISTING.hourly_rate;
        document.getElementById('base_salary').readOnly = true;
    } else {
        document.getElementById('type_monthly').checked = true;
    }

    // Mode cotisation
    const savedMode = EXISTING.mode_cotisation;
    document.querySelector(`input[name="mode_cotisation"][value="${savedMode}"]`).checked = true;

    // Lancer le calcul initial
    toggleCotisationMode();
    calculate();
});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\SIRH-\resources\views/salary/create.blade.php ENDPATH**/ ?>