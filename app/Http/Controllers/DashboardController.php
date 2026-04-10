<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Absence;
use App\Models\Planning;
use App\Models\News;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index()
    {
        try {
            $user = Auth::user();

            if ($user && $user->role === 'employee') {
                return redirect()->route('employee.dashboard');
            }

            $data = $this->dashboardService->getDashboardData($user);
            return view('dashboard.index', $data);
        } catch (ModelNotFoundException | NotFoundHttpException $e) {
            Log::warning('Dashboard data not found: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Données dashboard indisponibles.');
        } catch (\Exception $e) {
            Log::error('Dashboard index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return view('dashboard.index', ['error' => 'Erreur chargement dashboard.']);
        }
    }

    public function stats()
    {
        try {
            return response()->json([
                'total_employees' => Employee::count(),
                'active' => Employee::active()->count(),
                'on_leave' => Absence::where('status', 'approved')
                    ->whereDate('start_date', '<=', today())
                    ->whereDate('end_date', '>=', today())
                    ->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard stats error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
            Log::error('Dashboard data error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Erreur chargement données'], 500);
        }
    }
}

