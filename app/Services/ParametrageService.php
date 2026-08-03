<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ParametrageService
{

    private function ensureTenant(): void
    {
        if (blank(config('app.current_tenant_id')) && auth()->check()) {
            $tenantId = auth()->user()->tenant_id;
            if ($tenantId) {
                config(['app.current_tenant_id' => $tenantId]);
            }
        }
    }

    public function getIndexData(Request $request): array
    {
        $this->ensureTenant();

        $tenantId    = auth()->user()->tenant_id;
        $rooms       = Room::with('department')->orderBy('name')->get();
        $departments = Department::withCount('rooms')->orderBy('name')->get();

        $employeesQuery = Employee::where('tenant_id', $tenantId)
            ->select([
                'id', 'first_name', 'last_name', 'department',
                'doc_casier_path', 'doc_rib_path', 'doc_diplomes_path',
                'doc_cin_path', 'doc_contrat_path',
            ])
            ->orderBy('last_name');

        if ($request->filled('dept_filter')) {
            $employeesQuery->where('department', $request->dept_filter);
        }

        $employeesWithDocs = $employeesQuery->get()->map(function ($emp) {
            $customCount = EmployeeDocument::where('employee_id', $emp->id)->count();
            $fixedCount  = collect([
                $emp->doc_casier_path,
                $emp->doc_rib_path,
                $emp->doc_diplomes_path,
                $emp->doc_cin_path,
                $emp->doc_contrat_path,
            ])->filter()->count();
            $emp->doc_count = $customCount + $fixedCount;
            return $emp;
        });

        $departmentNames = Employee::where('tenant_id', $tenantId)
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        $allEmployees = Employee::where('tenant_id', $tenantId)
            ->select(['id', 'first_name', 'last_name', 'department'])
            ->orderBy('last_name')
            ->get();

        $allEmployeesJs = $allEmployees->map(function ($e) {
            $fn = $e->first_name ?? '';
            $ln = $e->last_name  ?? '';
            return [
                'id'         => $e->id,
                'name'       => trim($fn . ' ' . $ln),
                'department' => $e->department ?? '',
                'initials'   => strtoupper(substr($fn, 0, 1) . substr($ln, 0, 1)),
            ];
        })->values()->toArray();

        return compact(
            'rooms', 'departments', 'employeesWithDocs', 'departmentNames',
            'allEmployees', 'allEmployeesJs'
        );
    }

    /**
     * @param array<int|string, UploadedFile> $files Fichiers indexés par employee_id
     */
    public function uploadDocuments(array $files, string $docName): int
    {
        $this->ensureTenant();
        $tenantId = auth()->user()->tenant_id;

        $savedCount = 0;

        foreach ($files as $employeeId => $file) {
            $emp = Employee::where('id', $employeeId)
                           ->where('tenant_id', $tenantId)
                           ->first();

            if (!$emp) continue;

            $path         = $file->store('documents/custom', 'public');
            $originalName = $file->getClientOriginalName();

            EmployeeDocument::create([
                'employee_id'   => $emp->id,
                'name'          => $docName,
                'path'          => $path,
                'original_name' => $originalName,
            ]);

            $savedCount++;
        }

        return $savedCount;
    }


    public function getEmployeeDocumentsData(int|string $employeeId): array
    {
        $this->ensureTenant();
        $tenantId = auth()->user()->tenant_id;

        $emp = Employee::where('id', $employeeId)
                       ->where('tenant_id', $tenantId)
                       ->firstOrFail();

        $docs = collect();

        // 1. Documents fixes (colonnes employees)
        $fixedDocs = [
            'doc_casier_path'   => 'Casier judiciaire',
            'doc_rib_path'      => 'Releve bancaire (RIB)',
            'doc_diplomes_path' => 'Diplomes',
            'doc_cin_path'      => 'CIN',
            'doc_contrat_path'  => 'Contrat de travail',
        ];
        foreach ($fixedDocs as $col => $label) {
            if (!empty($emp->$col)) {
                $docs->push([
                    'id'            => 'fixed_' . $col,
                    'name'          => $label,
                    'original_name' => basename($emp->$col),
                    'url'           => asset('storage/' . $emp->$col),
                    'date'          => '—',
                    'type'          => 'fixed',
                ]);
            }
        }

        // 2. Documents custom (table employee_documents)
        $customDocs = EmployeeDocument::where('employee_id', $employeeId)
                                      ->orderBy('created_at', 'desc')
                                      ->get()
                                      ->map(function ($doc) {
                                          return [
                                              'id'            => $doc->id,
                                              'name'          => $doc->name,
                                              'original_name' => $doc->original_name ?? basename($doc->path),
                                              'url'           => asset('storage/' . $doc->path),
                                              'date'          => $doc->created_at->format('d/m/Y'),
                                              'type'          => 'custom',
                                          ];
                                      });

        $docs = $docs->concat($customDocs);

        return [
            'employee' => $emp->first_name . ' ' . $emp->last_name,
            'docs'     => $docs->values(),
        ];
    }

    public function findDocumentForTenant(int|string $id): ?EmployeeDocument
    {
        $this->ensureTenant();
        $tenantId = auth()->user()->tenant_id;

        $doc = EmployeeDocument::findOrFail($id);

        $emp = Employee::where('id', $doc->employee_id)
                       ->where('tenant_id', $tenantId)
                       ->first();

        return $emp ? $doc : null;
    }

    public function removeDocument(EmployeeDocument $doc): void
    {
        // Supprime le fichier physique seulement si aucun autre enregistrement
        // ne le référence encore.
        $others = EmployeeDocument::where('path', $doc->path)
                                  ->where('id', '!=', $doc->id)
                                  ->count();
        if ($others === 0) {
            Storage::disk('public')->delete($doc->path);
        }

        $doc->delete();
    }
}
