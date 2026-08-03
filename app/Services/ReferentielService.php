<?php

namespace App\Services;

use App\Models\CatalogueFormation;
use App\Models\Formateur;
use App\Models\Formation;
use App\Models\OrganismeFormation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReferentielService
{

    public function getIndexData(Request $request): array
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

        return compact('formateurs', 'formations', 'organismes', 'stats', 'onglet');
    }


    public function createFormateur(array $data): Formateur
    {
        $formateur = Formateur::create($data);
        Formation::clearCacheFormateurs();

        return $formateur;
    }

    public function updateFormateur(Formateur $formateur, array $data): Formateur
    {
        $formateur->update($data);
        Formation::clearCacheFormateurs();

        return $formateur;
    }

    public function deleteFormateur(Formateur $formateur): void
    {
        $formateur->delete();
        Formation::clearCacheFormateurs();
    }


    public function createFormation(array $data): CatalogueFormation
    {
        $formation = CatalogueFormation::create($data);
        Formation::clearCacheTitres();

        return $formation;
    }

    public function updateFormation(CatalogueFormation $formation, array $data): CatalogueFormation
    {
        $formation->update($data);
        Formation::clearCacheTitres();

        return $formation;
    }

    public function deleteFormation(CatalogueFormation $formation): void
    {
        $formation->delete();
        Formation::clearCacheTitres();
    }


    public function createOrganisme(array $data): OrganismeFormation
    {
        $organisme = OrganismeFormation::create($data);
        Formation::clearCacheOrganismes();

        return $organisme;
    }

    public function updateOrganisme(OrganismeFormation $organisme, array $data): OrganismeFormation
    {
        $organisme->update($data);
        Formation::clearCacheOrganismes();

        return $organisme;
    }

    public function deleteOrganisme(OrganismeFormation $organisme): void
    {
        $organisme->delete();
        Formation::clearCacheOrganismes();
    }

    /** Formateurs actifs → label = "Prénom Nom" */
    public function getFormateursActifs(): Collection
    {
        return Formateur::where('actif', true)->orderBy('nom')
            ->get(['id', 'nom', 'prenom', 'specialite', 'type'])
            ->map(fn($f) => [
                'id'         => $f->id,
                'label'      => trim("{$f->prenom} {$f->nom}"),
                'specialite' => $f->specialite,
                'type'       => $f->type,
            ]);
    }

    /** Catalogue des formations actives */
    public function getCatalogueActif(): Collection
    {
        return CatalogueFormation::where('actif', true)->orderBy('titre')
            ->get(['id', 'titre', 'duree_heures', 'type']);
    }

    /** Organismes actifs */
    public function getOrganismesActifs(): Collection
    {
        return OrganismeFormation::where('actif', true)->orderBy('nom')
            ->get(['id', 'nom', 'agree']);
    }
}
