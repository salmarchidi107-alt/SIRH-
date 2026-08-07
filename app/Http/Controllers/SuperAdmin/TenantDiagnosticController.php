<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\TenantIntegrityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantDiagnosticController extends Controller
{
    public function __construct(private readonly TenantIntegrityService $tenantIntegrityService)
    {
    }

    /**
     * Affiche le diagnostic d'intégrité multi-tenant.
     * ?refresh=1 force un recalcul immédiat au lieu du rapport en cache.
     *
     * Aucun changement requis ici : le service renvoie désormais un check
     * supplémentaire ("runtime_leak_alerts") au même format que les autres,
     * et la vue (tenant-diagnostic/index.blade.php) boucle déjà de façon
     * générique sur $report['checks'] — le nouveau check s'affiche donc
     * automatiquement sans aucune modification de la vue.
     */
    public function index(Request $request): View
    {
        $report = $this->tenantIntegrityService->run(fresh: $request->boolean('refresh'));

        return view('superadmin.tenant-diagnostic.index', compact('report'));
    }
}
