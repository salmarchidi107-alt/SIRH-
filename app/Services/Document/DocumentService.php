<?php

namespace App\Services\Document;

use App\Models\Document;
use App\Models\DocumentEntete;
use App\Models\DocumentModele;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DocumentService
{
    public function getIndexData(Request $request): array
    {
        $query = Document::with(['employe', 'createdBy', 'modele'])->latest();

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        $documents = $query->paginate(15)->withQueryString();
        $employes  = Employee::orderBy('last_name')->orderBy('first_name')->get();
        $modeles   = DocumentModele::orderBy('nom')->get();

        return compact('documents', 'employes', 'modeles');
    }

    public function create(array $data): Document
    {
        $modele = DocumentModele::find($data['modele_id']);

        return Document::create([
            'nom'           => $data['nom'],
            'employe_id'    => $data['employe_id'],
            'modele_id'     => $data['modele_id'],
            'date_document' => $data['date_document'],
            'contenu'       => $modele?->contenu,
            'created_by'    => Auth::id(),
            'type'          => 'Autre',
        ]);
    }

    public function getEditData(Document $document): array
    {
        $document->load(['employe', 'modele']);

        $employes = Employee::orderBy('last_name')->orderBy('first_name')->get();
        $modeles  = DocumentModele::orderBy('nom')->get();
        $tenant   = auth()->user()?->tenant;
        $entete   = DocumentEntete::getActive();

        $contenuInitial = base64_encode($document->contenu ?? $document->modele?->contenu ?? '');

        $modelesContenu = $modeles->mapWithKeys(
            fn ($mod) => [$mod->id => base64_encode($mod->contenu ?? '')]
        );

        $employesJson = $employes->mapWithKeys(
            fn ($emp) => [$emp->id => $this->buildEmployeeJson($emp)]
        );

        $tenantJson = [
            'societe'       => $entete?->nom_societe ?: ($tenant?->name          ?? ''),
            'adresse'       => $entete?->adresse     ?: ($tenant?->address       ?? ''),
            'telephone'     => $entete?->telephone   ?: ($tenant?->phone         ?? ''),
            'email_societe' => $entete?->email       ?: ($tenant?->email_societe ?? ''),
            'site_web'      => $entete?->site_web    ?: ($tenant?->website       ?? ''),
            'ice'           => $entete?->ice         ?: ($tenant?->ice           ?? ''),
            'rc'            => $entete?->rc          ?? '',
        ];

        return compact(
            'document', 'employes', 'modeles',
            'contenuInitial', 'modelesContenu', 'employesJson', 'tenantJson'
        );
    }


    private function buildEmployeeJson(Employee $emp): array
    {
        return [
            'id'                => $emp->id,
            'nom'               => $emp->last_name     ?? '',
            'prenom'            => $emp->first_name    ?? '',
            'matricule'         => $emp->matricule     ?? '',
            'poste'             => $emp->position      ?? '',
            'departement'       => $emp->department    ?? '',
            'contrat'           => $emp->contract_type ?? '',
            'date_embauche'     => $emp->hire_date
                                    ? Carbon::parse($emp->hire_date)->format('d/m/Y')
                                    : '',
            'salaire'           => $emp->salary
                                    ? number_format($emp->salary, 2, ',', ' ') . ' MAD'
                                    : '',
            // ── Nouvelles variables ─────────────────────────────
            'adresse_employe'   => $emp->address ?? '',
            'cin'               => $emp->cin     ?? '',
            'telephone_employe' => $emp->phone   ?? '',
            'date_fin_contrat'  => $emp->contract_end_date
                                    ? Carbon::parse($emp->contract_end_date)->format('d/m/Y')
                                    : '',
            'date_naissance'    => $emp->birth_date
                                    ? Carbon::parse($emp->birth_date)->format('d/m/Y')
                                    : '',
        ];
    }

    public function update(Document $document, array $data): Document
    {
        $document->update([
            'nom'           => $data['nom'],
            'employe_id'    => $data['employe_id'],
            'modele_id'     => $data['modele_id'],
            'date_document' => $data['date_document'],
            'contenu'       => $data['contenu'] ?? null,
        ]);

        return $document;
    }

    public function delete(Document $document): void
    {
        $document->delete();
    }
}
