<?php

namespace App\Http\Controllers;

use App\Models\AffectationEquipement;
use App\Models\Equipement;
use App\Services\EquipementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EquipementController extends Controller
{
    public function __construct(private EquipementService $equipementService) {}

    // tenant_id est un UUID (string) dans ce projet — nécessaire ici pour
    // les vérifications d'autorisation (abort_if) qui doivent rester au
    // niveau du contrôleur.
    private function tenantId(): string
    {
        return (string) Auth::user()->tenant_id;
    }

    // ─── Tableau de bord ──────────────────────────────────────────────────────

    public function index(Request $request)
    {
        return view('equipements.index', $this->equipementService->getDashboardData($request));
    }

    // ─── Export (CSV natif — pas de dépendance externe) ───────────────────────

    public function export(Request $request)
    {
        $type = $request->query('type');

        [$headers, $rows] = match ($type) {
            'catalogue'    => $this->equipementService->exportDataCatalogue($request),
            'affectations' => $this->equipementService->exportDataAffectations(),
            'salaries'     => $this->equipementService->exportDataSalaries(),
            'decharges'    => $this->equipementService->exportDataDecharges(),
            'retours'      => $this->equipementService->exportDataRetours(),
            default        => abort(404, "Type d'export inconnu."),
        };

        $filename = ($type ?: 'export') . '_' . now()->format('Y-m-d') . '.csv';

        return $this->streamCsv($filename, $headers, $rows);
    }

    private function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 pour qu'Excel affiche correctement les accents
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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

        $reference = $this->equipementService->createEquipement($request->only([
            'designation', 'categorie', 'marque', 'modele', 'numero_serie',
            'date_acquisition', 'fournisseur', 'valeur_acquisition',
            'etat', 'localisation', 'statut',
        ]));

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

        $this->equipementService->updateEquipement($equipement, $request->only([
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

        $error = $this->equipementService->canDeleteEquipement($equipement);
        if ($error) {
            return back()->with('error', $error);
        }

        $designation = $equipement->designation;
        $reference   = $equipement->reference;

        $this->equipementService->deleteEquipement($equipement);

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

        $equipement = $this->equipementService->getEquipementForAffectation($request->equipement_id);

        abort_if($equipement->statut !== 'Disponible', 422, "Cet équipement n'est pas disponible.");

        $this->equipementService->createAffectation($equipement, $request->only([
            'employee_id', 'date_affectation', 'date_retour_prevue', 'etat_remise', 'observations',
        ]));

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

        $this->equipementService->restituerAffectation($affectation, $request->only([
            'date_retour_effectif', 'etat_retour', 'observations_retour',
        ]));

        return redirect()->route('equipements.index', ['tab' => 'retour'])
                         ->with('success', 'Restitution enregistrée.');
    }

    // ─── Valider sortie ───────────────────────────────────────────────────────

    public function validerSortie(Request $request, int $employeeId)
    {
        $pending = $this->equipementService->countPendingAffectations($employeeId);

        abort_if($pending > 0, 422, 'Des équipements ne sont pas encore restitués.');

        return redirect()->route('equipements.index', ['tab' => 'retour'])
                         ->with('success', 'Sortie validée. Processus RH finalisé.');
    }

    // ─── Signer décharge ──────────────────────────────────────────────────────

    public function signerDecharge(AffectationEquipement $affectation)
    {
        abort_if($affectation->tenant_id !== $this->tenantId(), 403);

        $this->equipementService->signerDecharge($affectation);

        return back()->with('success', 'Décharge marquée comme signée.');
    }

    // ─── Déclarer perte ───────────────────────────────────────────────────────

    public function declarerPerte(AffectationEquipement $affectation)
    {
        abort_if($affectation->tenant_id !== $this->tenantId(), 403);

        $this->equipementService->declarerPerte($affectation);

        return back()->with('success', 'Perte déclarée.');
    }

    // ─── Fiche salarié ────────────────────────────────────────────────────────

    public function ficheSalarie(int $employeeId)
    {
        return view('equipements.fiche_salarie', $this->equipementService->getFicheSalarieData($employeeId));
    }

    public function ficheSalariePdf(int $employeeId)
    {
        $data     = $this->equipementService->getFicheSalarieData($employeeId);
        $employee = $data['employee'];

        $pdf = \PDF::loadView('equipements.fiche_salarie_pdf', $data)
            ->setPaper('a4', 'portrait');

        $nom = str_replace(' ', '_', trim($employee->first_name . '_' . $employee->last_name));

        return $pdf->download("fiche_patrimoine_{$nom}_" . now()->format('Y-m-d') . '.pdf');
    }
}
