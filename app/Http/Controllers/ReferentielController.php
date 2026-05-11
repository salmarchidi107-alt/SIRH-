<?php
// app/Http/Controllers/ReferentielController.php

namespace App\Http\Controllers;

use App\Models\Formateur;
use App\Models\CatalogueFormation;
use App\Models\OrganismeFormation;
use App\Models\Formation;
use Illuminate\Http\Request;

class ReferentielController extends Controller
{
    /* ══════════════════════════════════════════
     |  PAGE PRINCIPALE (3 onglets)
     ══════════════════════════════════════════ */

    public function index(Request $request)
    {
        $onglet     = $request->get('onglet', 'formateurs');
        $formateurs = Formateur::orderBy('nom')->get();
        $formations = CatalogueFormation::orderBy('titre')->get();
        $organismes = OrganismeFormation::orderBy('nom')->get();

        $stats = [
            'formateurs' => Formateur::count(),
            'formations' => CatalogueFormation::count(),
            'organismes' => OrganismeFormation::count(),
        ];

        return view('referentiel.index', compact(
            'formateurs', 'formations', 'organismes', 'stats', 'onglet'
        ));
    }

    /* ══════════════════════════════════════════
     |  FORMATEURS
     ══════════════════════════════════════════ */

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

        Formateur::create($data);
        Formation::clearCacheFormateurs(); // invalide le cache LMS

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

        $formateur->update($data);
        Formation::clearCacheFormateurs();

        return redirect()->route('referentiel.index', ['onglet' => 'formateurs'])
            ->with('success', 'Formateur mis à jour.');
    }

    public function destroyFormateur(Formateur $formateur)
    {
        $formateur->delete();
        Formation::clearCacheFormateurs();

        return redirect()->route('referentiel.index', ['onglet' => 'formateurs'])
            ->with('success', 'Formateur supprimé.');
    }

    /* ══════════════════════════════════════════
     |  CATALOGUE FORMATIONS
     ══════════════════════════════════════════ */

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

        CatalogueFormation::create($data);
        Formation::clearCacheTitres();

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

        $formation->update($data);
        Formation::clearCacheTitres();

        return redirect()->route('referentiel.index', ['onglet' => 'formations'])
            ->with('success', 'Formation mise à jour.');
    }

    public function destroyFormation(CatalogueFormation $formation)
    {
        $formation->delete();
        Formation::clearCacheTitres();

        return redirect()->route('referentiel.index', ['onglet' => 'formations'])
            ->with('success', 'Formation supprimée du catalogue.');
    }

    /* ══════════════════════════════════════════
     |  ORGANISMES
     ══════════════════════════════════════════ */

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

        OrganismeFormation::create($data);
        Formation::clearCacheOrganismes();

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

        $organisme->update($data);
        Formation::clearCacheOrganismes();

        return redirect()->route('referentiel.index', ['onglet' => 'organismes'])
            ->with('success', 'Organisme mis à jour.');
    }

    public function destroyOrganisme(OrganismeFormation $organisme)
    {
        $organisme->delete();
        Formation::clearCacheOrganismes();

        return redirect()->route('referentiel.index', ['onglet' => 'organismes'])
            ->with('success', 'Organisme supprimé.');
    }

    /* ══════════════════════════════════════════
     |  AJAX — utilisé par le LMS modal
     ══════════════════════════════════════════ */

    /** Formateurs actifs → label = "Prénom Nom" */
    public function formateursActifs()
    {
        return response()->json(
            Formateur::where('actif', true)->orderBy('nom')
                ->get(['id', 'nom', 'prenom', 'specialite', 'type'])
                ->map(fn($f) => [
                    'id'        => $f->id,
                    'label'     => trim("{$f->prenom} {$f->nom}"),
                    'specialite'=> $f->specialite,
                    'type'      => $f->type,
                ])
        );
    }

    /** Catalogue des formations actives */
    public function catalogueActif()
    {
        return response()->json(
            CatalogueFormation::where('actif', true)->orderBy('titre')
                ->get(['id', 'titre', 'duree_heures', 'type'])
        );
    }

    /** Organismes actifs */
    public function organismesActifs()
    {
        return response()->json(
            OrganismeFormation::where('actif', true)->orderBy('nom')
                ->get(['id', 'nom', 'agree'])
        );
    }
}
