<?php

namespace App\Http\Controllers;

use App\Models\DocumentModele;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentModeleController extends Controller
{
    public function index(Request $request)
    {
        $query = DocumentModele::latest();

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        $modeles = $query->paginate(15)->withQueryString();

        return view('ged.modeles', compact('modeles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'categorie' => 'required|string',
            'contenu'   => 'nullable|string',
        ]);

        DocumentModele::create([
            'nom'        => $request->nom,
            'categorie'  => $request->categorie,
            'contenu'    => $request->contenu,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('ged.modeles.index')
            ->with('success', 'Modèle créé avec succès.');
    }

    public function edit(DocumentModele $modele)
{
    $contenuModele = base64_encode($modele->contenu ?? '');

    return view('ged.modeles-edit', compact('modele', 'contenuModele'));
}

    public function update(Request $request, DocumentModele $modele)
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'categorie' => 'required|string',
            'contenu'   => 'nullable|string',
        ]);

        $modele->update([
            'nom'       => $request->nom,
            'categorie' => $request->categorie,
            'contenu'   => $request->contenu,
        ]);

        return redirect()->route('ged.modeles.index')
            ->with('success', 'Modèle modifié avec succès.');
    }

    public function destroy(DocumentModele $modele)
    {
        $modele->delete();

        return redirect()->route('ged.modeles.index')
            ->with('success', 'Modèle supprimé.');
    }
}