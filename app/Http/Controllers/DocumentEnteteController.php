<?php

namespace App\Http\Controllers;

use App\Models\DocumentEntete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentEnteteController extends Controller
{
    public function index()
    {
        $entete = DocumentEntete::getActive();
        $contenuLibre = base64_encode($entete->contenu_libre ?? '');
        return view('ged.entete', compact('entete', 'contenuLibre'));
    }

   public function store(Request $request)
{
    $request->validate([
        'contenu_libre' => 'nullable|string',
    ]);

    // récupérer ancienne entête AVANT désactivation
    $ancienneEntete = DocumentEntete::getActive();

    $logoPath = $ancienneEntete?->logo_path;

    DocumentEntete::where('actif', true)
        ->update(['actif'=>false]);

    DocumentEntete::create([
        'nom'           => 'Entête principale',
        'logo_path'     => $logoPath,
        'nom_societe'   => $request->nom_societe,
        'adresse'       => $request->adresse,
        'telephone'     => $request->telephone,
        'email'         => $request->email,
        'site_web'      => $request->site_web,
        'rc'            => $request->rc,
        'ice'           => $request->ice,
        'contenu_libre' => $request->contenu_libre,
        'actif'         => true,
        'created_by'    => Auth::id(),
    ]);

    return redirect()
        ->route('ged.entete.index')
        ->with('success','Entête enregistrée.');
}
}