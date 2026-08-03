<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\DashboardOverviewService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private DashboardOverviewService $dashboardOverviewService
    ) {}

    public function index()
    {
        $data = $this->dashboardOverviewService->getIndexData(Auth::user());

        return view('dashboard.index', $data);
    }

    public function stats()
    {
        try {
            $stats = $this->dashboardOverviewService->getStats(Auth::user());

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur stats'], 500);
        }
    }

    public function data()
    {
        try {
            return response()->json($this->dashboardService->getDashboardData(Auth::user()));
        } catch (ModelNotFoundException | NotFoundHttpException $e) {
            Log::warning('Dashboard data not found: ' . $e->getMessage());
            return response()->json(['error' => 'Données non trouvées'], 404);
        } catch (\Exception $e) {
            Log::error('Dashboard data error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur chargement données'], 500);
        }
    }
}
