<?php

namespace App\Http\Controllers;

use App\Services\ParametrageService;
use Illuminate\Http\Request;

class ParametrageController extends Controller
{
    public function __construct(private ParametrageService $parametrageService) {}

    public function index(Request $request)
    {
        return view('parametrage.index', $this->parametrageService->getIndexData($request));
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'doc_name'    => 'required|string|max:255',
            'target_type' => 'required|in:employee,department,all',
            'files'       => 'required|array|min:1',
            'files.*'     => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'doc_name.required' => 'Le nom du document est obligatoire.',
            'files.required'    => 'Veuillez ajouter au moins un fichier.',
            'files.*.mimes'     => 'Formats acceptes : PDF, JPG, PNG.',
            'files.*.max'       => 'Chaque fichier ne doit pas depasser 10 Mo.',
        ]);

        $savedCount = $this->parametrageService->uploadDocuments(
            $request->file('files'),
            $request->doc_name
        );

        if ($savedCount === 0) {
            return back()->with('error', 'Aucun fichier n\'a pu etre enregistre.');
        }

        return redirect()
            ->route('parametrage.index', ['tab' => 'documents'])
            ->with('success', "Document \"{$request->doc_name}\" ajoute pour {$savedCount} employe(s).");
    }

    public function getEmployeeDocuments($employeeId)
    {
        return response()->json($this->parametrageService->getEmployeeDocumentsData($employeeId));
    }

    public function deleteDocument($id)
    {
        $doc = $this->parametrageService->findDocumentForTenant($id);

        if (! $doc) {
            abort(403);
        }

        $this->parametrageService->removeDocument($doc);

        return response()->json(['success' => true]);
    }
}
