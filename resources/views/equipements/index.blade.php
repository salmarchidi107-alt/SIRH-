@extends('layouts.app')

@section('title', 'Gestion des équipements')
@section('page-title', 'Équipements')

@section('content')

{{-- ══ EN-TÊTE + ONGLETS ══ --}}
<div style="background:#fff;border-bottom:1px solid #e2e8f0;margin:-16px -16px 24px;padding:0">

    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px 0">
        <div>
            <h1 style="font-size:18px;font-weight:700;color:#0f172a;margin:0">Gestion des équipements</h1>
            <p style="font-size:12px;color:#64748b;margin:3px 0 0">Catalogue · Affectations · Décharges · Retours</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            @if($alertes_depart->isNotEmpty())
            <span style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:99px;padding:4px 12px;font-size:12px;font-weight:600">
                {{ $alertes_depart->count() }} alerte(s) départ
            </span>
            @endif
        </div>
    </div>

    <div class="eq-nav" style="margin:0;border-bottom:none">
        <a href="{{ route('equipements.index', ['tab' => 'dash']) }}"
           class="eq-tab {{ $tab === 'dash' ? 'active' : '' }}">Tableau de bord</a>
        <a href="{{ route('equipements.index', ['tab' => 'catalogue']) }}"
           class="eq-tab {{ $tab === 'catalogue' ? 'active' : '' }}">Catalogue</a>
        <a href="{{ route('equipements.index', ['tab' => 'affecter']) }}"
           class="eq-tab {{ $tab === 'affecter' ? 'active' : '' }}">Affectation</a>
        <a href="{{ route('equipements.index', ['tab' => 'salarie']) }}"
           class="eq-tab {{ $tab === 'salarie' ? 'active' : '' }}">Fiche salarié</a>
        <a href="{{ route('equipements.index', ['tab' => 'decharge']) }}"
           class="eq-tab {{ in_array($tab, ['decharge', 'retour']) ? 'active' : '' }}">Décharges &amp; Retours</a>
    </div>
</div>


{{-- ═══════════════════════════════
     TABLEAU DE BORD (simple)
════════════════════════════════ --}}
<div class="eq-page {{ $tab === 'dash' ? 'active' : '' }}">

    @if($alertes_depart->isNotEmpty())
    <div class="eq-alert error" style="margin-bottom:16px">
        <div>
            <strong>{{ $alertes_depart->count() }} alerte(s) départ</strong>
            — équipements toujours affectés non restitués.
            <a href="{{ route('equipements.index', ['tab' => 'decharge']) }}" style="color:#991b1b;font-weight:600;text-decoration:underline;margin-left:6px">Voir</a>
        </div>
    </div>
    @endif

    <div class="eq-card">
        <div class="eq-card-title">
            Dernières affectations
            <a href="{{ route('equipements.export', ['type' => 'affectations']) }}"
               style="margin-left:auto;display:inline-flex;align-items:center;gap:5px;font-size:12px;color:#14b8a6;text-decoration:none;font-weight:500;border:1px solid #ccfbf1;background:#f0fdfa;border-radius:7px;padding:5px 10px">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                Exporter
            </a>
        </div>
        <table class="eq-table">
            <thead>
                <tr>
                    <th>Salarié</th>
                    <th>Matériel</th>
                    <th>Date</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dernieres_affectations->take(8) as $aff)
                <tr>
                    <td style="font-weight:500">{{ $aff->employee->first_name ?? '' }} {{ $aff->employee->last_name ?? '—' }}</td>
                    <td style="color:#64748b">{{ $aff->equipement->designation ?? '—' }}</td>
                    <td style="color:#64748b;font-size:12px">{{ optional($aff->date_affectation)->format('d/m/Y') }}</td>
                    <td><span class="eq-badge b-teal">Affecté</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:#64748b;padding:20px">Aucune affectation récente</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


{{-- ═══════════════════════════════
     CATALOGUE
════════════════════════════════ --}}
<div class="eq-page {{ $tab === 'catalogue' ? 'active' : '' }}">

    <div class="eq-card" style="padding:14px 18px;margin-bottom:16px">
        <form method="GET" action="{{ route('equipements.index') }}"
              style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <input type="hidden" name="tab" value="catalogue">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder=" Rechercher"
                   style="height:36px;font-size:13px;border:1px solid #e2e8f0;border-radius:8px;padding:0 12px;width:220px;flex-shrink:0">
            <select name="categorie"
                    style="height:36px;font-size:13px;border:1px solid #e2e8f0;border-radius:8px;padding:0 10px;background:#fff;min-width:160px">
                <option value="">Toutes catégories</option>
                @foreach($liste_categories as $cat)
                <option value="{{ $cat }}" {{ request('categorie') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <select name="statut"
                    style="height:36px;font-size:13px;border:1px solid #e2e8f0;border-radius:8px;padding:0 10px;background:#fff;min-width:140px">
                <option value="">Tous statuts</option>
                <option value="Disponible"  {{ request('statut') === 'Disponible'  ? 'selected' : '' }}>Disponible</option>
                <option value="Affecté"     {{ request('statut') === 'Affecté'     ? 'selected' : '' }}>Affecté</option>
                <option value="Maintenance" {{ request('statut') === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="Perdu"       {{ request('statut') === 'Perdu'       ? 'selected' : '' }}>Perdu</option>
            </select>
            <button type="submit"
                    style="height:36px;padding:0 16px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer">
                Filtrer
            </button>
            @if(request('search') || request('categorie') || request('statut'))
            <a href="{{ route('equipements.index', ['tab' => 'catalogue']) }}"
               style="height:36px;padding:0 12px;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;text-decoration:none;display:inline-flex;align-items:center">
                ✕ Réinitialiser
            </a>
            @endif
            <div style="margin-left:auto;display:flex;gap:8px">
                <a href="{{ route('equipements.export', array_merge(['type' => 'catalogue'], request()->only(['search', 'categorie', 'statut']))) }}"
                   style="height:36px;padding:0 14px;background:#f0fdfa;color:#14b8a6;border:1px solid #ccfbf1;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                    Exporter
                </a>
                <button type="button" onclick="toggleForm('add-eq-form')"
                        style="height:36px;padding:0 16px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                    + Ajouter un équipement
                </button>
            </div>
        </form>
    </div>

    <div id="add-eq-form" style="display:none;margin-bottom:16px">
        <div class="eq-card">
            <div class="eq-card-title">Nouvel équipement</div>
            <form method="POST" action="{{ route('equipements.store') }}">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
                    <div class="fgroup" style="grid-column:1/-1">
                        <label>Désignation *</label>
                        <input type="text" name="designation" placeholder="Ex : Dell XPS 15 – Laptop Core i7" required>
                    </div>
                    <div class="fgroup">
                        <label>Catégorie *</label>
                        <select name="categorie" id="cat-select-add" onchange="toggleAutreAdd(this)" required>
                            @foreach($liste_categories as $cat)
                                @if($cat !== 'Autre')
                                <option value="{{ $cat }}">{{ $cat }}</option>
                                @endif
                            @endforeach
                            <option value="Autre">Autre (préciser…)</option>
                        </select>
                        <input type="text" name="categorie_autre" id="cat-autre-add"
                               placeholder="Nom de la nouvelle catégorie"
                               style="display:none;margin-top:6px;height:36px;border:1px solid #e2e8f0;border-radius:8px;padding:0 12px;font-size:13px;width:100%">
                    </div>
                    <div class="fgroup">
                        <label>Marque</label>
                        <input type="text" name="marque" placeholder="Dell, HP, Samsung…">
                    </div>
                    <div class="fgroup">
                        <label>Modèle</label>
                        <input type="text" name="modele" placeholder="XPS 15 9530">
                    </div>
                    <div class="fgroup">
                        <label>N° de série</label>
                        <input type="text" name="numero_serie" placeholder="SN…">
                    </div>
                    <div class="fgroup">
                        <label>Date d'acquisition</label>
                        <input type="date" name="date_acquisition">
                    </div>
                    <div class="fgroup">
                        <label>Fournisseur</label>
                        <input type="text" name="fournisseur">
                    </div>
                    <div class="fgroup">
                        <label>Valeur d'acquisition (MAD)</label>
                        <input type="number" name="valeur_acquisition" min="0" step="0.01" placeholder="0">
                    </div>
                    <div class="fgroup">
                        <label>État *</label>
                        <select name="etat" required>
                            <option>Neuf</option>
                            <option>Bon état</option>
                            <option>À réparer</option>
                            <option>Hors service</option>
                        </select>
                    </div>
                    <div class="fgroup">
                        <label>Localisation</label>
                        <input type="text" name="localisation" placeholder="Bâtiment A / Bureau 201">
                    </div>
                    <div class="fgroup">
                        <label>Statut initial *</label>
                        <select name="statut" required>
                            <option>Disponible</option>
                            <option>Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="btn-row">
                    <button type="submit"
                            style="height:36px;padding:0 18px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                        Enregistrer
                    </button>
                    <button type="button" onclick="toggleForm('add-eq-form')"
                            style="height:36px;padding:0 14px;background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;cursor:pointer">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="eq-card" style="padding:0;overflow:hidden">
        <div style="overflow-x:auto">
            <table class="eq-table" style="min-width:850px">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Désignation</th>
                        <th>Catégorie</th>
                        <th>Marque</th>
                        <th>N° série</th>
                        <th>État</th>
                        <th>Statut</th>
                        <th style="text-align:right">Valeur (MAD)</th>
                        <th style="text-align:center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipements as $eq)
                    <tr>
                        <td class="mono" style="font-weight:600;color:#0f172a">{{ $eq->reference }}</td>
                        <td style="font-weight:500">{{ $eq->designation }}</td>
                        <td><span class="{{ $eq->categorie_color }}">{{ $eq->categorie }}</span></td>
                        <td style="color:#64748b">{{ $eq->marque ?? '—' }}</td>
                        <td class="mono" style="color:#64748b">{{ $eq->numero_serie ?? '—' }}</td>
                        <td><span class="{{ $eq->etat_color }}">{{ $eq->etat }}</span></td>
                        <td><span class="{{ $eq->statut_color }}">{{ $eq->statut }}</span></td>
                        <td style="text-align:right;font-weight:600">{{ number_format($eq->valeur_acquisition, 0, ',', ' ') }}</td>
                        <td style="text-align:center">
                            <div style="display:inline-flex;gap:6px;align-items:center">
                                @if($eq->statut === 'Disponible')
                                <a href="{{ route('equipements.index', ['tab' => 'affecter', 'equipement_id' => $eq->id]) }}"
                                   title="Affecter cet équipement"
                                   style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:#f0fdfa;color:#14b8a6;text-decoration:none;border:1px solid #ccfbf1">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                                </a>
                                @elseif($eq->affectationActive && $eq->affectationActive->employee_id)
                                <a href="{{ route('equipements.fiche_salarie', $eq->affectationActive->employee_id) }}"
                                   title="Voir fiche salarié"
                                   style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:#f8fafc;color:#64748b;text-decoration:none;border:1px solid #e2e8f0">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                @endif

                                @if($eq->statut !== 'Affecté')
                                <form method="POST" action="{{ route('equipements.destroy', $eq->id) }}"
                                      onsubmit="return confirm('Supprimer définitivement « {{ addslashes($eq->designation) }} » ({{ $eq->reference }}) ? Cette action est irréversible.');"
                                      style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Supprimer cet équipement"
                                            style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;cursor:pointer">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            <line x1="10" y1="11" x2="10" y2="17"/>
                                            <line x1="14" y1="11" x2="14" y2="17"/>
                                        </svg>
                                    </button>
                                </form>
                                @else
                                <span title="Impossible de supprimer un équipement affecté — restituez-le d'abord"
                                      style="color:#cbd5e1;cursor:not-allowed;display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;color:#64748b;padding:32px;font-size:13px">
                            Aucun équipement trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-top:12px">{{ $equipements->appends(request()->query())->links() }}</div>
</div>


{{-- ═══════════════════════════════
     AFFECTATION
════════════════════════════════ --}}
<div class="eq-page {{ $tab === 'affecter' ? 'active' : '' }}">
    <div class="eq-card" style="max-width:1200px;margin:0 auto">
        <div class="eq-card-title">Nouvelle affectation</div>
        <form method="POST" action="{{ route('equipements.affecter') }}">
            @csrf
            <div class="aff-grid">
                <div class="fgroup full">
                    <label>Salarié *</label>
                    <select name="employee_id" id="sel-salarie" onchange="updateSalarieInfo(this)" required>
                        <option value="">— Sélectionner un salarié —</option>
                        @foreach($employees_actifs as $emp)
                        <option value="{{ $emp->id }}"
                            data-nom="{{ $emp->first_name }} {{ $emp->last_name }}"
                            data-fonction="{{ $emp->position ?? $emp->poste ?? '' }}"
                            data-service="{{ $emp->department ?? $emp->departement ?? '' }}"
                            data-mat="{{ $emp->employee_number ?? $emp->matricule ?? '' }}"
                            {{ (request('employee_id') == $emp->id) ? 'selected' : '' }}>
                            {{ $emp->first_name }} {{ $emp->last_name }}
                            @if($emp->employee_number ?? $emp->matricule ?? '')
                                — {{ $emp->employee_number ?? $emp->matricule }}
                            @endif
                        </option>
                        @endforeach
                    </select>
                </div>

                <div id="sal-info" class="full" style="display:none;background:#f0fdfa;border-radius:10px;padding:12px;gap:12px;align-items:center;border:1px solid #ccfbf1">
                    <div class="eq-avatar" id="sal-avatar" style="width:40px;height:40px;font-size:14px">—</div>
                    <div>
                        <div style="font-weight:600;font-size:13px;color:#0f172a" id="sal-nom">—</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px" id="sal-detail">—</div>
                    </div>
                </div>

                <div class="fgroup full">
                    <label>Équipement à affecter *</label>
                    <select name="equipement_id" id="sel-equipement" onchange="updateEquipementInfo(this)" required>
                        <option value="">— Sélectionner un équipement disponible —</option>
                        @foreach($equipements_disponibles as $eq)
                        <option value="{{ $eq->id }}"
                            data-desig="{{ $eq->designation }}"
                            data-ref="{{ $eq->reference }}"
                            data-etat="{{ $eq->etat }}"
                            {{ (request('equipement_id') == $eq->id) ? 'selected' : '' }}>
                            {{ $eq->reference }} — {{ $eq->designation }} ({{ $eq->etat }})
                        </option>
                        @endforeach
                    </select>
                    @if($equipements_disponibles->isEmpty())
                    <div style="font-size:12px;color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 10px;margin-top:4px">
                        Aucun équipement disponible actuellement.
                    </div>
                    @endif
                </div>

                <div class="fgroup">
                    <label>Date d'affectation *</label>
                    <input type="date" name="date_affectation" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="fgroup">
                    <label>Date de retour prévue</label>
                    <input type="date" name="date_retour_prevue">
                </div>

                <div class="fgroup">
                    <label>État au moment de la remise *</label>
                    <select name="etat_remise" required>
                        <option>Neuf</option>
                        <option>Bon état</option>
                        <option>État moyen</option>
                    </select>
                </div>

                <div class="fgroup full">
                    <label>Observations</label>
                    <textarea name="observations" placeholder="Remarques sur l'état, accessoires remis…"></textarea>
                </div>

                <div id="aff-recap" class="full" style="display:none;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:12px 14px;font-size:12.5px;color:#334155;line-height:1.7">
                    <strong id="recap-salarie">—</strong> recevra <strong id="recap-equipement">—</strong>. Une décharge sera générée automatiquement après validation.
                </div>
            </div>
            <div class="btn-row">
                <button type="submit"
                        style="height:36px;padding:0 18px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                    Valider et générer décharge
                </button>
                <a href="{{ route('equipements.index', ['tab' => 'catalogue']) }}"
                   style="height:36px;padding:0 14px;background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;text-decoration:none">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>


{{-- ═══════════════════════════════
     FICHE SALARIÉ
════════════════════════════════ --}}
<div class="eq-page {{ $tab === 'salarie' ? 'active' : '' }}">

    <div class="eq-card">
        <div class="eq-card-title">Consulter la fiche patrimoine d'un salarié</div>
        <div style="display:flex;gap:10px;align-items:flex-end">
            <div class="fgroup" style="flex:1">
                <label>Sélectionner un salarié</label>
                <select id="sel-salarie-view" style="height:36px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;padding:0 12px;background:#fff">
                    <option value="">— Choisir un salarié —</option>
                    @foreach($employees_actifs as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }} — {{ $emp->employee_number ?? $emp->matricule ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button"
                    style="height:36px;padding:0 18px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px"
                    onclick="var v=document.getElementById('sel-salarie-view').value; if(v) window.location='{{ url('equipements/salarie') }}/'+v;">
                Consulter la fiche
            </button>
        </div>
    </div>

    <div class="eq-card">
        <div class="eq-card-title">
            Salariés avec équipements affectés
            <a href="{{ route('equipements.export', ['type' => 'salaries']) }}"
               style="margin-left:auto;display:inline-flex;align-items:center;gap:5px;font-size:12px;color:#14b8a6;text-decoration:none;font-weight:500;border:1px solid #ccfbf1;background:#f0fdfa;border-radius:7px;padding:5px 10px">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                Exporter
            </a>
        </div>
        <table class="eq-table">
            <thead>
                <tr>
                    <th>Salarié</th>
                    <th>Matricule</th>
                    <th>Nb équipements</th>
                    <th>Valeur confiée</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $salaries_eq = \App\Models\AffectationEquipement::where('tenant_id', auth()->user()->tenant_id)
                        ->where('statut', 'Actif')
                        ->with(['employee', 'equipement'])
                        ->get()
                        ->groupBy('employee_id');
                @endphp
                @forelse($salaries_eq as $empId => $affs)
                @php
                    $emp    = $affs->first()->employee;
                    $valeur = $affs->sum(fn($a) => $a->equipement->valeur_acquisition ?? 0);
                @endphp
                @if($emp)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            @php $ini = mb_strtoupper(mb_substr($emp->first_name ?? 'X', 0, 1) . mb_substr($emp->last_name ?? 'X', 0, 1)); @endphp
                            <div class="eq-avatar" style="width:32px;height:32px;font-size:12px">{{ $ini }}</div>
                            <span style="font-weight:500">{{ $emp->first_name }} {{ $emp->last_name }}</span>
                        </div>
                    </td>
                    <td class="mono">{{ $emp->employee_number ?? $emp->matricule ?? '—' }}</td>
                    <td><span class="eq-badge b-teal">{{ $affs->count() }}</span></td>
                    <td style="font-weight:600">{{ number_format($valeur, 0, ',', ' ') }} MAD</td>
                    <td style="text-align:center">
                        <div style="display:inline-flex;gap:6px">
                            <a href="{{ route('equipements.fiche_salarie', $empId) }}"
                               style="display:inline-flex;align-items:center;gap:5px;padding:0 12px;height:30px;background:#f0fdfa;color:#14b8a6;border:1px solid #ccfbf1;border-radius:7px;font-size:12px;font-weight:500;text-decoration:none">
                                Voir fiche
                            </a>
                            <a href="{{ route('equipements.fiche_salarie.pdf', $empId) }}"
                               title="Exporter en PDF"
                               style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;background:#f8fafc;color:#374151;border:1px solid #e2e8f0;border-radius:7px;text-decoration:none">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#64748b;padding:28px;font-size:13px">
                        Aucun équipement affecté actuellement
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


{{-- ═══════════════════════════════
     DÉCHARGES & RETOURS (fusionné)
════════════════════════════════ --}}
<div class="eq-page {{ in_array($tab, ['decharge', 'retour']) ? 'active' : '' }}">

    @if($alertes_depart->isNotEmpty())
    <div class="eq-alert error" style="margin-bottom:16px">
        <div>
            <strong>Alerte départ :</strong>
            {{ $alertes_depart->count() }} salarié(s) avec contrat terminé ont des équipements non restitués.
        </div>
    </div>
    @endif

    <div class="eq-card">
        <div class="eq-card-title">
            Affectations en cours — décharges &amp; retours
            <span style="font-size:11px;color:#94a3b8;font-weight:400">{{ $toutes_affectations_actives->count() }} au total</span>
            <a href="{{ route('equipements.export', ['type' => 'decharges']) }}"
               style="margin-left:auto;display:inline-flex;align-items:center;gap:5px;font-size:12px;color:#14b8a6;text-decoration:none;font-weight:500;border:1px solid #ccfbf1;background:#f0fdfa;border-radius:7px;padding:5px 10px">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                Exporter
            </a>
        </div>
        <div style="overflow-x:auto">
            <table class="eq-table" style="min-width:900px">
                <thead>
                    <tr>
                        <th>Salarié</th>
                        <th>Équipement</th>
                        <th>Pris le</th>
                        <th>Retour prévu</th>
                        <th style="text-align:center">Décharge</th>
                        <th style="text-align:center">Statut</th>
                        <th style="text-align:center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($toutes_affectations_actives as $aff)
                    @php $enDepart = ($aff->employee->status ?? 'active') === 'inactive'; @endphp
                    <tr>
                        <td style="font-weight:500">{{ $aff->employee->first_name ?? '' }} {{ $aff->employee->last_name ?? '—' }}</td>
                        <td style="color:#64748b">
                            {{ $aff->equipement->designation ?? '—' }}
                            <span class="mono" style="color:#94a3b8">({{ $aff->equipement->reference ?? '' }})</span>
                        </td>
                        <td style="color:#64748b;font-size:12px">{{ optional($aff->date_affectation)->format('d/m/Y') }}</td>
                        <td style="color:#64748b;font-size:12px">
                            {{ $aff->date_retour_prevue ? optional($aff->date_retour_prevue)->format('d/m/Y') : 'Non défini' }}
                        </td>
                        <td style="text-align:center">
                            @if($aff->decharge_signee)
                                <span class="eq-badge b-green">Signée</span>
                            @else
                                <form method="POST" action="{{ route('equipements.signer_decharge', $aff->id) }}" style="display:inline">
                                    @csrf
                                    <button type="submit"
                                            style="height:26px;padding:0 10px;background:#fffbeb;color:#92400e;border:1px solid #fde68a;border-radius:7px;font-size:11px;font-weight:600;cursor:pointer">
                                        En attente — signer
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td style="text-align:center">
                            <span class="eq-badge {{ $enDepart ? 'b-red' : 'b-blue' }}">{{ $enDepart ? 'Départ' : 'En cours' }}</span>
                        </td>
                        <td style="text-align:center">
                            <div style="display:inline-flex;gap:6px">
                                <a href="{{ route('equipements.index', ['tab' => 'decharge', 'affectation_id' => $aff->id]) }}#restitution"
                                   style="height:26px;padding:0 10px;background:#f0fdfa;color:#14b8a6;border:1px solid #ccfbf1;border-radius:7px;font-size:11px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center">
                                    Restituer
                                </a>
                                @if($enDepart)
                                <form method="POST" action="{{ route('equipements.declarer_perte', $aff->id) }}" style="display:inline">
                                    @csrf
                                    <button type="submit"
                                            style="height:26px;padding:0 10px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:7px;font-size:11px;font-weight:600;cursor:pointer">
                                        Perte
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;color:#64748b;padding:28px;font-size:13px">
                            Aucune affectation en cours
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="restitution" class="eq-card">
        <div class="eq-card-title">Enregistrer une restitution</div>
        @if($toutes_affectations_actives->isNotEmpty())
        @php
            $preselect = $toutes_affectations_actives->firstWhere('id', (int) request('affectation_id'));
            $defaultAffId = $preselect->id ?? $toutes_affectations_actives->first()->id;
        @endphp
        <form method="POST" action="{{ route('equipements.restituer', $defaultAffId) }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:10px">
                <div class="fgroup">
                    <label>Équipement restitué</label>
                    <select name="affectation_id_display" disabled style="background:#f8fafc">
                        @foreach($toutes_affectations_actives as $aff)
                        <option {{ (int) request('affectation_id') === $aff->id ? 'selected' : '' }}>
                            {{ $aff->equipement->reference ?? '' }} — {{ $aff->equipement->designation ?? '' }}
                            ({{ $aff->employee->first_name ?? '' }} {{ $aff->employee->last_name ?? '' }})
                        </option>
                        @endforeach
                    </select>
                    <div style="font-size:11px;color:#94a3b8;margin-top:2px">Cliquez sur « Restituer » dans la table ci-dessus pour sélectionner l'équipement.</div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="fgroup">
                        <label>Date de restitution *</label>
                        <input type="date" name="date_retour_effectif" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="fgroup">
                        <label>État à la restitution *</label>
                        <select name="etat_retour" required>
                            <option>Bon état</option>
                            <option>Usure normale</option>
                            <option>Endommagé</option>
                            <option>Perdu</option>
                        </select>
                    </div>
                </div>
                <div class="fgroup">
                    <label>Observations</label>
                    <textarea name="observations_retour" placeholder="Accessoires restitués, dommages constatés…"></textarea>
                </div>
            </div>
            <div class="btn-row">
                <button type="submit"
                        style="height:36px;padding:0 18px;background:#14b8a6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                    Enregistrer restitution
                </button>
            </div>
        </form>
        @else
        <p style="font-size:13px;color:#64748b;margin:0">Aucun équipement actuellement affecté.</p>
        @endif
    </div>
</div>


<script>
/* ── Utilitaires généraux ── */
function toggleForm(id) {
    var el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function toggleAutreAdd(sel) {
    var inp = document.getElementById('cat-autre-add');
    if (!inp) return;
    if (sel.value === 'Autre') {
        inp.style.display = 'block';
        inp.required = true;
        sel.name = '_categorie_orig';
        inp.name = 'categorie';
    } else {
        inp.style.display = 'none';
        inp.required = false;
        sel.name = 'categorie';
        inp.name = 'categorie_autre';
    }
}

function updateSalarieInfo(sel) {
    var opt  = sel.options[sel.selectedIndex];
    var info = document.getElementById('sal-info');
    if (!sel.value) { info.style.display = 'none'; updateAffRecap(); return; }
    info.style.display = 'flex';
    var nom = opt.getAttribute('data-nom') || '';
    var ini = nom.split(' ').map(function(p) { return p.charAt(0); }).slice(0, 2).join('').toUpperCase();
    document.getElementById('sal-avatar').textContent  = ini;
    document.getElementById('sal-nom').textContent     = nom;
    document.getElementById('sal-detail').textContent  =
        (opt.getAttribute('data-fonction') || '') + ' — ' +
        (opt.getAttribute('data-service')  || '') + ' — ' +
        (opt.getAttribute('data-mat')      || '');
    updateAffRecap();
}

function updateEquipementInfo(sel) {
    updateAffRecap();
}

function updateAffRecap() {
    var selSal = document.getElementById('sel-salarie');
    var selEq  = document.getElementById('sel-equipement');
    var recap  = document.getElementById('aff-recap');
    if (!selSal || !selEq || !recap) return;
    if (!selSal.value || !selEq.value) { recap.style.display = 'none'; return; }
    var optSal = selSal.options[selSal.selectedIndex];
    var optEq  = selEq.options[selEq.selectedIndex];
    document.getElementById('recap-salarie').textContent    = optSal.getAttribute('data-nom') || '';
    document.getElementById('recap-equipement').textContent = (optEq.getAttribute('data-desig') || '') + ' (' + (optEq.getAttribute('data-ref') || '') + ')';
    recap.style.display = 'block';
}

/* ── Init ── */
(function() {
    var sel = document.getElementById('sel-salarie');
    if (sel && sel.value) updateSalarieInfo(sel);
    updateAffRecap();
})();
</script>

@endsection
