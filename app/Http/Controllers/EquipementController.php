<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Models\AffectationEquipement;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\UniqueConstraintViolationException;

class EquipementController extends Controller
{
    // tenant_id est un UUID (string) dans ce projet
    private function tenantId(): string
    {
        return (string) Auth::user()->tenant_id;
    }

    // ─── Tableau de bord ──────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();
        $tab      = $request->get('tab', 'dash');

        // ── Filtres dashboard (catégorie + plage date acquisition) ──
        $dashCat  = $request->get('dash_cat');
        $dashFrom = $request->get('dash_from');
        $dashTo   = $request->get('dash_to');

        // Closure qui retourne une query de base avec les filtres dashboard appliqués
        $baseQuery = fn() => Equipement::forTenant($tenantId)
            ->when($dashCat,  fn($q) => $q->where('categorie', $dashCat))
            ->when($dashFrom, fn($q) => $q->whereDate('date_acquisition', '>=', $dashFrom))
            ->when($dashTo,   fn($q) => $q->whereDate('date_acquisition', '<=', $dashTo));

        // ── Métriques filtrées ──
        $metrics = [
            'total'       => $baseQuery()->count(),
            'affectes'    => $baseQuery()->affectes()->count(),
            'disponibles' => $baseQuery()->disponibles()->count(),
            'maintenance' => $baseQuery()->where('statut', 'Maintenance')->count(),
            'perdus'      => $baseQuery()->where('statut', 'Perdu')->count(),
            'valeur_parc' => $baseQuery()->sum('valeur_acquisition'),
        ];

        // ── Répartition par catégorie (filtrée) ──
        $categories = $baseQuery()
            ->select('categorie', DB::raw('count(*) as total'))
            ->groupBy('categorie')
            ->orderByDesc('total')
            ->get();

        // ── Alertes : employés inactifs avec équipements non restitués ──
        $alertes_depart = AffectationEquipement::forTenant($tenantId)
            ->with(['employee', 'equipement'])
            ->actives()
            ->whereHas('employee', fn($q) => $q->where('status', 'inactive'))
            ->get()
            ->groupBy('employee_id');

        // ── Dernières affectations ──
        $dernieres_affectations = AffectationEquipement::forTenant($tenantId)
            ->with(['employee', 'equipement'])
            ->actives()
            ->orderByDesc('date_affectation')
            ->limit(10)
            ->get();

        // ── Catalogue (onglet) ──
        $equipements = Equipement::forTenant($tenantId)
            ->with('affectationActive.employee')
            ->when($request->categorie, fn($q, $c) => $q->where('categorie', $c))
            ->when($request->statut,    fn($q, $s) => $q->where('statut', $s))
            ->when($request->search,    fn($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('reference',   'like', "%$s%")
                   ->orWhere('designation', 'like', "%$s%")
                   ->orWhere('marque',      'like', "%$s%");
            }))
            ->orderBy('reference')
            ->paginate(20);

        // ── Employés actifs du tenant (onglet affectation) ──
        $employees_actifs = Employee::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('last_name')
            ->get();

        $equipements_disponibles = Equipement::forTenant($tenantId)
            ->disponibles()
            ->orderBy('designation')
            ->get();

        // ── Retours : employés inactifs avec affectations actives ──
        $employee_ids_depart = AffectationEquipement::forTenant($tenantId)
            ->actives()
            ->whereHas('employee', fn($q) => $q->where('status', 'inactive'))
            ->pluck('employee_id')
            ->unique();

        $employes_depart = collect();
        if ($employee_ids_depart->isNotEmpty()) {
            $employes_depart = Employee::whereIn('id', $employee_ids_depart)->get();

            $affectations_par_emp = AffectationEquipement::forTenant($tenantId)
                ->actives()
                ->with('equipement')
                ->whereIn('employee_id', $employee_ids_depart)
                ->get()
                ->groupBy('employee_id');

            $employes_depart->each(function ($emp) use ($affectations_par_emp) {
                $emp->setRelation(
                    'affectationsEquipements',
                    $affectations_par_emp->get($emp->id, collect())
                );
            });
        }

        // ── Décharges en attente ──
        $decharges_en_attente = AffectationEquipement::forTenant($tenantId)
            ->with(['employee', 'equipement'])
            ->where('decharge_signee', false)
            ->actives()
            ->orderByDesc('created_at')
            ->get();

        $liste_categories = [
            'Ordinateur portable', 'Téléphone', 'Tablette', 'Véhicule',
            'Badge', 'EPI', 'Uniforme', 'Mobilier', 'Autre',
        ];

        return view('equipements.index', compact(
            'tab', 'metrics', 'categories', 'alertes_depart',
            'dernieres_affectations', 'equipements', 'employees_actifs',
            'equipements_disponibles', 'employes_depart', 'decharges_en_attente',
            'liste_categories'
        ));
    }

    // ─── Catalogue : Ajouter ──────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'designation'        => 'required|string|max:255',
            'categorie'          => 'required|string',
            'marque'             => 'nullable|string|max:100',
            'modele'             => 'nullable|string|max:100',
            'numero_serie'       => 'nullable|string|max:100',
            'date_acquisition'   => 'nullable|date',
            'fournisseur'        => 'nullable|string|max:150',
            'valeur_acquisition' => 'nullable|numeric|min:0',
            'etat'               => 'required|in:Neuf,Bon état,À réparer,Hors service',
            'localisation'       => 'nullable|string|max:150',
            'statut'             => 'required|in:Disponible,Affecté,Maintenance,Perdu',
        ]);

        $tenantId    = $this->tenantId();
        $maxTentatives = 3;
        $reference   = null;

        // La génération de référence + la création sont dans la même transaction,
        // avec verrouillage (lockForUpdate) dans genererReference(), pour éviter
        // toute collision même en cas de double soumission concurrente.
        // Le retry supplémentaire couvre le cas résiduel où deux transactions
        // concurrentes liraient le même "dernier numéro" avant que l'une des
        // deux ait pu committer (edge case selon l'isolation level de MySQL).
        for ($tentative = 1; $tentative <= $maxTentatives; $tentative++) {
            try {
                $reference = DB::transaction(function () use ($request, $tenantId) {
                    $reference = Equipement::genererReference($request->categorie, $tenantId);

                    Equipement::create([
                        'tenant_id'          => $tenantId,
                        'reference'          => $reference,
                        'designation'        => $request->designation,
                        'categorie'          => $request->categorie,
                        'marque'             => $request->marque,
                        'modele'             => $request->modele,
                        'numero_serie'       => $request->numero_serie,
                        'date_acquisition'   => $request->date_acquisition,
                        'fournisseur'        => $request->fournisseur,
                        'valeur_acquisition' => $request->valeur_acquisition ?? 0,
                        'etat'               => $request->etat,
                        'localisation'       => $request->localisation,
                        'statut'             => $request->statut,
                    ]);

                    return $reference;
                });

                break; // succès, on sort de la boucle
            } catch (UniqueConstraintViolationException $e) {
                if ($tentative >= $maxTentatives) {
                    throw $e;
                }
                // On retente : une autre requête a probablement pris la référence
                // entre-temps, la prochaine itération de genererReference() en
                // recalculera une nouvelle en tenant compte de cet insert.
                continue;
            }
        }

        return redirect()->route('equipements.index', ['tab' => 'catalogue'])
                         ->with('success', "Équipement $reference ajouté avec succès.");
    }

    // ─── Catalogue : Modifier ─────────────────────────────────────────────────

    public function update(Request $request, Equipement $equipement)
    {
        abort_if($equipement->tenant_id !== $this->tenantId(), 403);

        $request->validate([
            'designation'        => 'required|string|max:255',
            'categorie'          => 'required|string',
            'etat'               => 'required|in:Neuf,Bon état,À réparer,Hors service',
            'statut'             => 'required|in:Disponible,Affecté,Maintenance,Perdu',
            'valeur_acquisition' => 'nullable|numeric|min:0',
        ]);

        $equipement->update($request->only([
            'designation', 'categorie', 'marque', 'modele', 'numero_serie',
            'date_acquisition', 'fournisseur', 'valeur_acquisition',
            'etat', 'localisation', 'statut', 'observations',
        ]));

        return redirect()->route('equipements.index', ['tab' => 'catalogue'])
                         ->with('success', 'Équipement mis à jour.');
    }

    // ─── Catalogue : Supprimer ────────────────────────────────────────────────

    public function destroy(Equipement $equipement)
    {
        abort_if($equipement->tenant_id !== $this->tenantId(), 403);

        if ($equipement->statut === 'Affecté') {
            return back()->with('error', "Impossible de supprimer « {$equipement->designation} » : il est actuellement affecté. Restituez-le d'abord.");
        }

        // Sécurité supplémentaire : vérifie qu'aucune affectation active
        // (même orpheline) n'existe pour cet équipement avant suppression.
        $affectationActive = AffectationEquipement::forTenant($this->tenantId())
            ->where('equipement_id', $equipement->id)
            ->actives()
            ->exists();

        if ($affectationActive) {
            return back()->with('error', "Impossible de supprimer « {$equipement->designation} » : une affectation active y est encore liée.");
        }

        $designation = $equipement->designation;
        $reference   = $equipement->reference;

        $equipement->delete();

        return redirect()->route('equipements.index', ['tab' => 'catalogue'])
                         ->with('success', "Équipement {$reference} — {$designation} supprimé avec succès.");
    }

    // ─── Affectation ──────────────────────────────────────────────────────────

    public function affecter(Request $request)
    {
        $request->validate([
            'employee_id'        => 'required|exists:employees,id',
            'equipement_id'      => 'required|exists:equipements,id',
            'date_affectation'   => 'required|date',
            'etat_remise'        => 'required|in:Neuf,Bon état,État moyen',
            'observations'       => 'nullable|string|max:500',
            'date_retour_prevue' => 'nullable|date|after:date_affectation',
        ]);

        $tenantId   = $this->tenantId();
        $equipement = Equipement::forTenant($tenantId)->findOrFail($request->equipement_id);

        abort_if($equipement->statut !== 'Disponible', 422, "Cet équipement n'est pas disponible.");

        DB::transaction(function () use ($request, $tenantId, $equipement) {
            $numero = AffectationEquipement::genererNumeroDecharge($tenantId);

            AffectationEquipement::create([
                'tenant_id'          => $tenantId,
                'equipement_id'      => $equipement->id,
                'employee_id'        => $request->employee_id,
                'date_affectation'   => $request->date_affectation,
                'date_retour_prevue' => $request->date_retour_prevue,
                'etat_remise'        => $request->etat_remise,
                'observations'       => $request->observations,
                'statut'             => 'Actif',
                'numero_decharge'    => $numero,
                'decharge_signee'    => false,
            ]);

            $equipement->update(['statut' => 'Affecté']);
        });

        return redirect()->route('equipements.index', ['tab' => 'decharge'])
                         ->with('success', 'Affectation enregistrée. Décharge générée.');
    }

    // ─── Restitution ──────────────────────────────────────────────────────────

    public function restituer(Request $request, AffectationEquipement $affectation)
    {
        abort_if($affectation->tenant_id !== $this->tenantId(), 403);

        $request->validate([
            'date_retour_effectif' => 'required|date',
            'etat_retour'          => 'required|in:Bon état,Usure normale,Endommagé,Perdu',
            'observations_retour'  => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $affectation) {
            $affectation->update([
                'date_retour_effectif' => $request->date_retour_effectif,
                'etat_retour'          => $request->etat_retour,
                'observations_retour'  => $request->observations_retour,
                'statut'               => 'Restitué',
            ]);

            $nouveauStatut = match ($request->etat_retour) {
                'Perdu', 'Endommagé' => 'Maintenance',
                default              => 'Disponible',
            };

            $affectation->equipement->update(['statut' => $nouveauStatut]);
        });

        return redirect()->route('equipements.index', ['tab' => 'retour'])
                         ->with('success', 'Restitution enregistrée.');
    }

    // ─── Valider sortie ───────────────────────────────────────────────────────

    public function validerSortie(Request $request, int $employeeId)
    {
        $tenantId = $this->tenantId();

        $pending = AffectationEquipement::forTenant($tenantId)
            ->where('employee_id', $employeeId)
            ->actives()
            ->count();

        abort_if($pending > 0, 422, 'Des équipements ne sont pas encore restitués.');

        return redirect()->route('equipements.index', ['tab' => 'retour'])
                         ->with('success', 'Sortie validée. Processus RH finalisé.');
    }

    // ─── Signer décharge ──────────────────────────────────────────────────────

    public function signerDecharge(AffectationEquipement $affectation)
    {
        abort_if($affectation->tenant_id !== $this->tenantId(), 403);
        $affectation->update(['decharge_signee' => true]);
        return back()->with('success', 'Décharge marquée comme signée.');
    }

    // ─── Déclarer perte ───────────────────────────────────────────────────────

    public function declarerPerte(AffectationEquipement $affectation)
    {
        abort_if($affectation->tenant_id !== $this->tenantId(), 403);

        DB::transaction(function () use ($affectation) {
            $affectation->update([
                'statut'               => 'Perdu',
                'etat_retour'          => 'Perdu',
                'date_retour_effectif' => now(),
            ]);
            $affectation->equipement->update([
                'statut' => 'Perdu',
                'etat'   => 'Hors service',
            ]);
        });

        return back()->with('success', 'Perte déclarée.');
    }

    // ─── Fiche salarié ────────────────────────────────────────────────────────

    public function ficheSalarie(int $employeeId)
    {
        $tenantId = $this->tenantId();
        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($employeeId);

        $affectations_actives = AffectationEquipement::forTenant($tenantId)
            ->where('employee_id', $employeeId)
            ->actives()
            ->with('equipement')
            ->orderByDesc('date_affectation')
            ->get();

        $historique = AffectationEquipement::forTenant($tenantId)
            ->where('employee_id', $employeeId)
            ->with('equipement')
            ->orderByDesc('created_at')
            ->get();

        $metrics_salarie = [
            'equipements_actuels'  => $affectations_actives->count(),
            'valeur_confiee'       => $affectations_actives->sum(fn($a) => $a->equipement->valeur_acquisition ?? 0),
            'derniere_affectation' => $affectations_actives->max('date_affectation'),
            'decharges_signees'    => $affectations_actives->where('decharge_signee', true)->count(),
        ];

        return view('equipements.fiche_salarie', compact(
            'employee', 'affectations_actives', 'historique', 'metrics_salarie'
        ));
    }
}
