<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page {
        margin: 130px 34px 70px 34px;
    }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10.5px;
        color: #1e293b;
        line-height: 1.5;
    }

    /* ══ EN-TÊTE FIXE (répété sur chaque page) ══ */
    header {
        position: fixed;
        top: -110px;
        left: 0;
        right: 0;
        height: 100px;
    }
    .letterhead-bar { height: 5px; background: #14b8a6; margin-bottom: 14px; }
    .letterhead table { width: 100%; }
    .company-name { font-size: 14px; font-weight: bold; color: #0f172a; letter-spacing: .3px; }
    .company-sub { font-size: 8.5px; color: #64748b; margin-top: 1px; }
    .doc-title { font-size: 13px; font-weight: bold; color: #0f766e; text-align: right; }
    .doc-ref { font-size: 8.5px; color: #64748b; text-align: right; margin-top: 2px; }
    .header-divider { border-bottom: 1px solid #e2e8f0; margin-top: 10px; }

    /* ══ PIED DE PAGE FIXE ══ */
    footer {
        position: fixed;
        bottom: -55px;
        left: 0;
        right: 0;
        height: 45px;
        font-size: 8px;
        color: #94a3b8;
        border-top: 1px solid #e2e8f0;
        padding-top: 7px;
    }
    footer table { width: 100%; }
    .page-number:after { content: "Page " counter(page) " / " counter(pages); }

    /* ══ CONTENU ══ */
    .info-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 12px 16px;
        margin-bottom: 16px;
    }
    .info-box table { width: 100%; font-size: 10px; }
    .info-box td { padding: 4px 6px; }
    .info-label { color: #64748b; width: 100px; }
    .info-value { font-weight: bold; color: #0f172a; }

    .section-title {
        font-size: 11.5px;
        font-weight: bold;
        color: #fff;
        background: #0f766e;
        padding: 6px 10px;
        margin: 18px 0 10px;
        border-radius: 3px;
    }

    table.data { width: 100%; border-collapse: collapse; font-size: 9.5px; margin-bottom: 6px; }
    table.data th {
        background: #f0fdfa; color: #0f766e; text-transform: uppercase;
        font-size: 8px; font-weight: bold; padding: 7px 8px; text-align: left;
        border-bottom: 1.5px solid #14b8a6;
    }
    table.data td { padding: 7px 8px; border-bottom: 1px solid #f1f5f9; }
    table.data tr:nth-child(even) td { background: #fafcfc; }

    .stats table { width: 100%; margin-bottom: 6px; border-collapse: separate; border-spacing: 6px 0; }
    .stat-cell { width: 25%; text-align: center; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px 4px; background: #fff; }
    .stat-val { font-size: 15px; font-weight: bold; color: #0f172a; }
    .stat-lbl { font-size: 7.5px; color: #64748b; margin-top: 3px; text-transform: uppercase; letter-spacing: .3px; }

    .badge { padding: 2px 7px; border-radius: 8px; font-size: 8px; font-weight: bold; }
    .b-green { background: #f0fdf4; color: #166534; }
    .b-amber { background: #fffbeb; color: #92400e; }
    .b-blue  { background: #eff6ff; color: #1d4ed8; }
    .b-red   { background: #fef2f2; color: #991b1b; }
    .b-gray  { background: #f8fafc; color: #475569; }

    .empty-row { text-align: center; color: #94a3b8; padding: 18px; font-style: italic; }

    /* ══ SIGNATURES ══ */
    .signatures { margin-top: 26px; }
    .signatures table { width: 100%; }
    .sig-cell { width: 50%; text-align: center; padding: 0 12px; }
    .sig-label { font-size: 9px; color: #64748b; font-weight: bold; margin-bottom: 6px; text-transform: uppercase; letter-spacing: .3px; }
    .sig-box { height: 55px; border: 1px dashed #cbd5e1; border-radius: 4px; }

    .confidential {
        margin-top: 18px;
        font-size: 8px;
        color: #94a3b8;
        font-style: italic;
        border-top: 1px solid #f1f5f9;
        padding-top: 8px;
    }
</style>
</head>
<body>

    <header>
        <div class="letterhead-bar"></div>
        <div class="letterhead">
            <table>
                <tr>
                    <td>
                        <div class="company-name">MEDSTAFF — HR SOLUTIONS</div>
                        <div class="company-sub">Plénitude Groupe · Direction des Ressources Humaines</div>
                    </td>
                    <td>
                        <div class="doc-title">FICHE PATRIMOINE</div>
                        <div class="doc-ref">
                            Réf. FP-{{ str_pad($employee->id, 5, '0', STR_PAD_LEFT) }}-{{ now()->format('Y') }}
                            &nbsp;·&nbsp; Édité le {{ now()->format('d/m/Y à H:i') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="header-divider"></div>
    </header>

    <footer>
        <table>
            <tr>
                <td style="text-align:left">MedStaff HR Solutions — Document interne confidentiel</td>
                <td style="text-align:right" class="page-number"></td>
            </tr>
        </table>
    </footer>

    <div class="info-box">
        <table>
            <tr>
                <td class="info-label">Nom &amp; prénom</td>
                <td class="info-value">{{ $employee->first_name }} {{ $employee->last_name }}</td>
                <td class="info-label">Matricule</td>
                <td class="info-value">{{ $employee->employee_number ?? $employee->matricule ?? '—' }}</td>
            </tr>
            <tr>
                <td class="info-label">Fonction</td>
                <td class="info-value">{{ $employee->position ?? $employee->poste ?? '—' }}</td>
                <td class="info-label">Service</td>
                <td class="info-value">{{ $employee->department ?? $employee->departement ?? '—' }}</td>
            </tr>
        </table>
    </div>

    <div class="stats">
        <table>
            <tr>
                <td class="stat-cell">
                    <div class="stat-val">{{ $metrics_salarie['equipements_actuels'] }}</div>
                    <div class="stat-lbl">Équipements actuels</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-val">{{ number_format($metrics_salarie['valeur_confiee'], 0, ',', ' ') }}</div>
                    <div class="stat-lbl">Valeur confiée (MAD)</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-val">{{ $metrics_salarie['derniere_affectation'] ? \Carbon\Carbon::parse($metrics_salarie['derniere_affectation'])->format('d/m/Y') : '—' }}</div>
                    <div class="stat-lbl">Dernière affectation</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-val">{{ $metrics_salarie['decharges_signees'] }} / {{ $metrics_salarie['equipements_actuels'] }}</div>
                    <div class="stat-lbl">Décharges signées</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Équipements actuellement en sa possession</div>
    <table class="data">
        <thead>
            <tr>
                <th>Équipement</th>
                <th>Référence</th>
                <th>Pris le</th>
                <th>Retour prévu</th>
                <th>État remise</th>
                <th>Décharge</th>
            </tr>
        </thead>
        <tbody>
            @forelse($affectations_actives as $aff)
            <tr>
                <td>{{ $aff->equipement->designation ?? '—' }}</td>
                <td>{{ $aff->equipement->reference ?? '—' }}</td>
                <td>{{ optional($aff->date_affectation)->format('d/m/Y') }}</td>
                <td>{{ $aff->date_retour_prevue ? optional($aff->date_retour_prevue)->format('d/m/Y') : 'Non défini' }}</td>
                <td>{{ $aff->etat_remise }}</td>
                <td>
                    @if($aff->decharge_signee)
                        <span class="badge b-green">Signée</span>
                    @else
                        <span class="badge b-amber">En attente</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="empty-row">Aucun équipement actuellement affecté</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Historique complet</div>
    <table class="data">
        <thead>
            <tr>
                <th>Équipement</th>
                <th>Référence</th>
                <th>Pris le</th>
                <th>Rendu le</th>
                <th>État remise</th>
                <th>État retour</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historique as $h)
            <tr>
                <td>{{ $h->equipement->designation ?? '—' }}</td>
                <td>{{ $h->equipement->reference ?? '—' }}</td>
                <td>{{ optional($h->date_affectation)->format('d/m/Y') }}</td>
                <td>{{ $h->date_retour_effectif ? optional($h->date_retour_effectif)->format('d/m/Y') : '—' }}</td>
                <td>{{ $h->etat_remise }}</td>
                <td>{{ $h->etat_retour ?? '—' }}</td>
                <td>
                    @php
                        $badgeClass = match($h->statut) {
                            'Actif'     => 'b-blue',
                            'Restitué'  => 'b-green',
                            'Perdu'     => 'b-red',
                            default     => 'b-gray',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $h->statut }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="empty-row">Aucun historique pour ce salarié</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="signatures">
        <table>
            <tr>
                <td class="sig-cell">
                    <div class="sig-label">Signature du salarié</div>
                    <div class="sig-box"></div>
                </td>
                <td class="sig-cell">
                    <div class="sig-label">Cachet &amp; signature RH</div>
                    <div class="sig-box"></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="confidential">
        Ce document récapitule l'ensemble des équipements affectés au salarié mentionné ci-dessus à la date d'édition.
        Il ne remplace pas les décharges individuelles signées lors de chaque remise de matériel et est strictement confidentiel.
    </div>

</body>
</html>
