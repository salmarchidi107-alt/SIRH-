<?php

namespace App\Http\Controllers;

use App\Models\DocumentEntete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentEnteteController extends Controller
{
    public function index()
    {
        // getActive() filtre déjà par tenant via GlobalScope
        $entete = DocumentEntete::getActive();
        $contenuLibre      = base64_encode($entete->contenu_libre        ?? '');
        $contenuPiedDePage = base64_encode($entete->contenu_pied_de_page ?? '');
        return view('ged.entete', compact('entete', 'contenuLibre', 'contenuPiedDePage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'contenu_libre'        => 'nullable|string',
            'contenu_pied_de_page' => 'nullable|string',
        ]);

        $ancienneEntete = DocumentEntete::getActive();
        $logoPath       = $ancienneEntete?->logo_path;

        // Désactiver uniquement les entêtes du tenant courant
        DocumentEntete::where('actif', true)->update(['actif' => false]);

        // tenant_id est injecté automatiquement par BelongsToTenant::creating()
        DocumentEntete::create([
            'nom'                  => 'Entête principale',
            'type'                 => 'entete',
            'logo_path'            => $logoPath,
            'nom_societe'          => $request->nom_societe,
            'adresse'              => $request->adresse,
            'telephone'            => $request->telephone,
            'email'                => $request->email,
            'site_web'             => $request->site_web,
            'rc'                   => $request->rc,
            'ice'                  => $request->ice,
            'contenu_libre'        => $request->contenu_libre,
            'contenu_pied_de_page' => $request->contenu_pied_de_page,
            'actif'                => true,
            'created_by'           => Auth::id(),
        ]);

        return redirect()->route('ged.entete.index')->with('success', 'Entête enregistrée.');
    }
}