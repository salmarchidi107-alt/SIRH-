<?php
// app/Http/Controllers/ReferentielController.php

namespace App\Http\Controllers;

use App\Models\Formateur;
use App\Models\CatalogueFormation;
use App\Models\OrganismeFormation;
use App\Services\ReferentielService;
use Illuminate\Http\Request;

class ReferentielController extends Controller
{
    public function __construct(private ReferentielService $referentielService) {}


    public function index(Request $request)
    {
        return view('referentiel.index', $this->referentielService->getIndexData($request));
    }


    public function storeFormateur(Request $request)
    {
        $data = $request->validate([
            'nom'        => 'required|string|max:100',
            'prenom'     => 'required|string|max:100',
            'email'      => 'nullable|email|max:150',
            'telephone'  => 'nullable|string|max:20',
            'specialite' => 'nullable|string|max:150',
            'type'       => 'required|in:interne,externe',
        ]);

        $this->referentielService->createFormateur($data);

        return redirect()->route('referentiel.index', ['onglet' => 'formateurs'])
            ->with('success', 'Formateur ajouté avec succès.');
    }

    public function updateFormateur(Request $request, Formateur $formateur)
    {
        $data = $request->validate([
            'nom'        => 'required|string|max:100',
            'prenom'     => 'required|string|max:100',
            'email'      => 'nullable|email|max:150',
            'telephone'  => 'nullable|string|max:20',
            'specialite' => 'nullable|string|max:150',
            'type'       => 'required|in:interne,externe',
            'actif'      => 'boolean',
        ]);

        $this->referentielService->updateFormateur($formateur, $data);

        return redirect()->route('referentiel.index', ['onglet' => 'formateurs'])
            ->with('success', 'Formateur mis à jour.');
    }

    public function destroyFormateur(Formateur $formateur)
    {
        $this->referentielService->deleteFormateur($formateur);

        return redirect()->route('referentiel.index', ['onglet' => 'formateurs'])
            ->with('success', 'Formateur supprimé.');
    }

    public function storeFormation(Request $request)
    {
        $data = $request->validate([
            'titre'         => 'required|string|max:200',
            'description'   => 'nullable|string|max:1000',
            'categorie'     => 'nullable|string|max:100',
            'duree_heures'  => 'required|integer|min:1|max:9999',
            'type'          => 'required|in:presentiel,distanciel,mixte',
            'date_creation' => 'nullable|date',
        ]);

        $this->referentielService->createFormation($data);

        return redirect()->route('referentiel.index', ['onglet' => 'formations'])
            ->with('success', 'Formation ajoutée au catalogue.');
    }

    public function updateFormation(Request $request, CatalogueFormation $formation)
    {
        $data = $request->validate([
            'titre'         => 'required|string|max:200',
            'description'   => 'nullable|string|max:1000',
            'categorie'     => 'nullable|string|max:100',
            'duree_heures'  => 'required|integer|min:1|max:9999',
            'type'          => 'required|in:presentiel,distanciel,mixte',
            'date_creation' => 'nullable|date',
            'actif'         => 'boolean',
        ]);

        $this->referentielService->updateFormation($formation, $data);

        return redirect()->route('referentiel.index', ['onglet' => 'formations'])
            ->with('success', 'Formation mise à jour.');
    }

    public function destroyFormation(CatalogueFormation $formation)
    {
        $this->referentielService->deleteFormation($formation);

        return redirect()->route('referentiel.index', ['onglet' => 'formations'])
            ->with('success', 'Formation supprimée du catalogue.');
    }


    public function storeOrganisme(Request $request)
    {
        $data = $request->validate([
            'nom'           => 'required|string|max:200',
            'adresse'       => 'nullable|string|max:300',
            'telephone'     => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:150',
            'site_web'      => 'nullable|url|max:200',
            'agree'         => 'boolean',
            'date_creation' => 'nullable|date',
        ]);

        $this->referentielService->createOrganisme($data);

        return redirect()->route('referentiel.index', ['onglet' => 'organismes'])
            ->with('success', 'Organisme ajouté avec succès.');
    }

    public function updateOrganisme(Request $request, OrganismeFormation $organisme)
    {
        $data = $request->validate([
            'nom'           => 'required|string|max:200',
            'adresse'       => 'nullable|string|max:300',
            'telephone'     => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:150',
            'site_web'      => 'nullable|url|max:200',
            'agree'         => 'boolean',
            'actif'         => 'boolean',
            'date_creation' => 'nullable|date',
        ]);

        $this->referentielService->updateOrganisme($organisme, $data);

        return redirect()->route('referentiel.index', ['onglet' => 'organismes'])
            ->with('success', 'Organisme mis à jour.');
    }

    public function destroyOrganisme(OrganismeFormation $organisme)
    {
        $this->referentielService->deleteOrganisme($organisme);

        return redirect()->route('referentiel.index', ['onglet' => 'organismes'])
            ->with('success', 'Organisme supprimé.');
    }

    /** Formateurs actifs → label = "Prénom Nom" */
    public function formateursActifs()
    {
        return response()->json($this->referentielService->getFormateursActifs());
    }

    /** Catalogue des formations actives */
    public function catalogueActif()
    {
        return response()->json($this->referentielService->getCatalogueActif());
    }

    /** Organismes actifs */
    public function organismesActifs()
    {
        return response()->json($this->referentielService->getOrganismesActifs());
    }
}
