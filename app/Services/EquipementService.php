<?php

namespace App\Services;

use App\Models\AffectationEquipement;
use App\Models\Employee;
use App\Models\Equipement;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipementService
{
    // tenant_id est un UUID (string) dans ce projet
    private function tenantId(): string
    {
        return (string) Auth::user()->tenant_id;
    }

    // =========================================================================
    // TABLEAU DE BORD
    // =========================================================================

    public function getDashboardData(Request $request): array
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

        // ── Décharges & Retours (fusionné) : toutes les affectations actives ──
        $toutes_affectations_actives = AffectationEquipement::forTenant($tenantId)
            ->with(['employee', 'equipement'])
            ->actives()
            ->orderByDesc('date_affectation')
            ->get();

        $liste_categories = [
            'Ordinateur portable', 'Téléphone', 'Tablette', 'Véhicule',
            'Badge', 'EPI', 'Uniforme', 'Mobilier', 'Autre',
        ];

        return compact(
            'tab', 'metrics', 'categories', 'alertes_depart',
            'dernieres_affectations', 'equipements', 'employees_actifs',
            'equipements_disponibles', 'toutes_affectations_actives',
            'liste_categories'
        );
    }

    // =========================================================================
    // EXPORTS (CSV)
    // =========================================================================

    public function exportDataCatalogue(Request $request): array
    {
        $tenantId    = $this->tenantId();
        $equipements = Equipement::forTenant($tenantId)
            ->when($request->categorie, fn($q, $c) => $q->where('categorie', $c))
            ->when($request->statut,    fn($q, $s) => $q->where('statut', $s))
            ->when($request->search,    fn($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('reference',   'like', "%$s%")
                   ->orWhere('designation', 'like', "%$s%")
                   ->orWhere('marque',      'like', "%$s%");
            }))
            ->orderBy('reference')
            ->get();

        $headers = ['Référence', 'Désignation', 'Catégorie', 'Marque', 'Modèle', 'N° série', 'État', 'Statut', 'Valeur (MAD)'];
        $rows    = $equipements->map(fn($eq) => [
            $eq->reference,
            $eq->designation,
            $eq->categorie,
            $eq->marque,
            $eq->modele,
            $eq->numero_serie,
            $eq->etat,
            $eq->statut,
            $eq->valeur_acquisition,
        ]);

        return [$headers, $rows];
    }

    public function exportDataAffectations(): array
    {
        $affectations = AffectationEquipement::forTenant($this->tenantId())
            ->with(['employee', 'equipement'])
            ->actives()
            ->orderByDesc('date_affectation')
            ->get();

        $headers = ['Salarié', 'Matricule', 'Matériel', 'Référence', 'Date affectation', 'État remise', 'Statut'];
        $rows    = $affectations->map(fn($aff) => [
            trim(($aff->employee->first_name ?? '') . ' ' . ($aff->employee->last_name ?? '')),
            $aff->employee->employee_number ?? $aff->employee->matricule ?? '',
            $aff->equipement->designation ?? '',
            $aff->equipement->reference ?? '',
            optional($aff->date_affectation)->format('d/m/Y'),
            $aff->etat_remise,
            $aff->statut,
        ]);

        return [$headers, $rows];
    }

    public function exportDataSalaries(): array
    {
        $groups = AffectationEquipement::forTenant($this->tenantId())
            ->where('statut', 'Actif')
            ->with(['employee', 'equipement'])
            ->get()
            ->groupBy('employee_id');

        $headers = ['Salarié', 'Matricule', 'Nb équipements', 'Valeur confiée (MAD)'];
        $rows    = $groups->map(function ($affs) {
            $emp = $affs->first()->employee;
            return [
                trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')),
                $emp->employee_number ?? $emp->matricule ?? '',
                $affs->count(),
                $affs->sum(fn($a) => $a->equipement->valeur_acquisition ?? 0),
            ];
        })->values();

        return [$headers, $rows];
    }

    public function exportDataDecharges(): array
    {
        $decharges = AffectationEquipement::forTenant($this->tenantId())
            ->with(['employee', 'equipement'])
            ->where('decharge_signee', false)
            ->actives()
            ->orderByDesc('created_at')
            ->get();

        $headers = ['N° Décharge', 'Salarié', 'Équipement', 'Référence', 'État remise', 'Statut'];
        $rows    = $decharges->map(fn($dch) => [
            $dch->numero_decharge,
            trim(($dch->employee->first_name ?? '') . ' ' . ($dch->employee->last_name ?? '')),
            $dch->equipement->designation ?? '',
            $dch->equipement->reference ?? '',
            $dch->etat_remise,
            'En attente',
        ]);

        return [$headers, $rows];
    }

    public function exportDataRetours(): array
    {
        // Même logique que getDashboardData() pour les "employés en départ" :
        // affectations actives dont le salarié a le statut "inactive".
        $affectations = AffectationEquipement::forTenant($this->tenantId())
            ->with(['employee', 'equipement'])
            ->actives()
            ->whereHas('employee', fn($q) => $q->where('status', 'inactive'))
            ->orderBy('employee_id')
            ->get();

        $headers = ['Salarié', 'Matricule', 'Équipement', 'Référence', 'Date affectation', 'Valeur (MAD)'];
        $rows    = $affectations->map(fn($aff) => [
            trim(($aff->employee->first_name ?? '') . ' ' . ($aff->employee->last_name ?? '')),
            $aff->employee->employee_number ?? $aff->employee->matricule ?? '',
            $aff->equipement->designation ?? '',
            $aff->equipement->reference ?? '',
            optional($aff->date_affectation)->format('d/m/Y'),
            $aff->equipement->valeur_acquisition ?? 0,
        ]);

        return [$headers, $rows];
    }

    // =========================================================================
    // CATALOGUE : AJOUTER
    // =========================================================================

    /**
     * Crée un équipement avec génération de référence unique.
     * La génération de référence + la création sont dans la même transaction,
     * avec verrouillage (lockForUpdate) dans genererReference(), pour éviter
     * toute collision même en cas de double soumission concurrente.
     * Le retry supplémentaire couvre le cas résiduel où deux transactions
     * concurrentes liraient le même "dernier numéro" avant que l'une des
     * deux ait pu committer (edge case selon l'isolation level de MySQL).
     *
     * @return string La référence générée pour l'équipement créé.
     */
    public function createEquipement(array $data): string
    {
        $tenantId      = $this->tenantId();
        $maxTentatives = 3;
        $reference     = null;

        for ($tentative = 1; $tentative <= $maxTentatives; $tentative++) {
            try {
                $reference = DB::transaction(function () use ($data, $tenantId) {
                    $reference = Equipement::genererReference($data['categorie'], $tenantId);

                    Equipement::create([
                        'tenant_id'          => $tenantId,
                        'reference'          => $reference,
                        'designation'        => $data['designation'],
                        'categorie'          => $data['categorie'],
                        'marque'             => $data['marque'] ?? null,
                        'modele'             => $data['modele'] ?? null,
                        'numero_serie'       => $data['numero_serie'] ?? null,
                        'date_acquisition'   => $data['date_acquisition'] ?? null,
                        'fournisseur'        => $data['fournisseur'] ?? null,
                        'valeur_acquisition' => $data['valeur_acquisition'] ?? 0,
                        'etat'               => $data['etat'],
                        'localisation'       => $data['localisation'] ?? null,
                        'statut'             => $data['statut'],
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

        return $reference;
    }

    // =========================================================================
    // CATALOGUE : MODIFIER
    // =========================================================================

    public function updateEquipement(Equipement $equipement, array $data): Equipement
    {
        $equipement->update($data);

        return $equipement;
    }

    // =========================================================================
    // CATALOGUE : SUPPRIMER
    // =========================================================================

    /**
     * Vérifie si l'équipement peut être supprimé.
     * Retourne un message d'erreur si non, null si la suppression est possible.
     */
    public function canDeleteEquipement(Equipement $equipement): ?string
    {
        if ($equipement->statut === 'Affecté') {
            return "Impossible de supprimer « {$equipement->designation} » : il est actuellement affecté. Restituez-le d'abord.";
        }

        // Sécurité supplémentaire : vérifie qu'aucune affectation active
        // (même orpheline) n'existe pour cet équipement avant suppression.
        $affectationActive = AffectationEquipement::forTenant($this->tenantId())
            ->where('equipement_id', $equipement->id)
            ->actives()
            ->exists();

        if ($affectationActive) {
            return "Impossible de supprimer « {$equipement->designation} » : une affectation active y est encore liée.";
        }

        return null;
    }

    public function deleteEquipement(Equipement $equipement): void
    {
        $equipement->delete();
    }

    // =========================================================================
    // AFFECTATION
    // =========================================================================

    public function getEquipementForAffectation(int $equipementId): Equipement
    {
        return Equipement::forTenant($this->tenantId())->findOrFail($equipementId);
    }

    public function createAffectation(Equipement $equipement, array $data): void
    {
        $tenantId = $this->tenantId();

        DB::transaction(function () use ($data, $tenantId, $equipement) {
            $numero = AffectationEquipement::genererNumeroDecharge($tenantId);

            AffectationEquipement::create([
                'tenant_id'          => $tenantId,
                'equipement_id'      => $equipement->id,
                'employee_id'        => $data['employee_id'],
                'date_affectation'   => $data['date_affectation'],
                'date_retour_prevue' => $data['date_retour_prevue'] ?? null,
                'etat_remise'        => $data['etat_remise'],
                'observations'       => $data['observations'] ?? null,
                'statut'             => 'Actif',
                'numero_decharge'    => $numero,
                'decharge_signee'    => false,
            ]);

            $equipement->update(['statut' => 'Affecté']);
        });
    }

    // =========================================================================
    // RESTITUTION
    // =========================================================================

    public function restituerAffectation(AffectationEquipement $affectation, array $data): void
    {
        DB::transaction(function () use ($data, $affectation) {
            $affectation->update([
                'date_retour_effectif' => $data['date_retour_effectif'],
                'etat_retour'          => $data['etat_retour'],
                'observations_retour'  => $data['observations_retour'] ?? null,
                'statut'               => 'Restitué',
            ]);

            $nouveauStatut = match ($data['etat_retour']) {
                'Perdu', 'Endommagé' => 'Maintenance',
                default              => 'Disponible',
            };

            $affectation->equipement->update(['statut' => $nouveauStatut]);
        });
    }

    // =========================================================================
    // VALIDER SORTIE
    // =========================================================================

    public function countPendingAffectations(int $employeeId): int
    {
        return AffectationEquipement::forTenant($this->tenantId())
            ->where('employee_id', $employeeId)
            ->actives()
            ->count();
    }

    // =========================================================================
    // SIGNER DECHARGE
    // =========================================================================

    public function signerDecharge(AffectationEquipement $affectation): void
    {
        $affectation->update(['decharge_signee' => true]);
    }

    // =========================================================================
    // DECLARER PERTE
    // =========================================================================

    public function declarerPerte(AffectationEquipement $affectation): void
    {
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
    }

    // =========================================================================
    // FICHE SALARIE
    // =========================================================================

    public function getFicheSalarieData(int $employeeId): array
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

        return compact('employee', 'affectations_actives', 'historique', 'metrics_salarie');
    }
}
