<?php

namespace App\Http\Controllers\Badge;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\Badge\BadgePointageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BadgePointageController extends Controller
{
    public function __construct(private BadgePointageService $badgeService)
    {
        Auth::shouldUse('badge');
    }

    // ─── Pages ───────────────────────────────────────────────────────────

    public function pointage()
    {
        return view('badge.pointage');
    }

    public function dashboard(Request $request)
    {
        $employee = $this->getAuthEmployee();

        return view('badge.dashboard', $this->badgeService->buildDashboardData($employee));
    }

    public function result(Request $request)
    {
        $employee  = $this->getAuthEmployee();
        $type      = $request->session()->get('last_type', 'entree');
        $geoData   = $request->session()->get('last_geo', []);
        $shiftType = $request->session()->get('last_shift_type', 'normal');

        $data = $this->badgeService->buildResultData($employee, $type, $geoData, $shiftType);

        return view('badge.result', $data);
    }

    // ─── Actions AJAX (dashboard blade) ──────────────────────────────────

    public function handleAction(Request $request)
    {
        $request->validate([
            'action'       => 'required|string',
            'shift_type'   => 'nullable|string|in:normal,garde',
            'photo_base64' => 'nullable|string', // data URI: "data:image/jpeg;base64,...."
        ]);

        $employee  = $this->getAuthEmployee();
        $realType  = $this->badgeService->resolveType($request->action);
        $shiftType = $this->badgeService->resolveShiftType($request->input('shift_type'));
        $geoData   = $this->badgeService->buildGeoDataFromRequest($request);

        $photoData = $this->badgeService->storeFacePhoto(
            $employee,
            $realType,
            $request->input('photo_base64')
        );

        $this->badgeService->recordAction($realType, $employee, $geoData, $photoData, $shiftType);

        $request->session()->put('last_type',       $realType);
        $request->session()->put('last_geo',        $geoData);
        $request->session()->put('last_shift_type', $shiftType);

        return response()->json([
            'success'  => true,
            'redirect' => route('badge.result'),
        ]);
    }

    // ─── Helpers privés ───────────────────────────────────────────────────

    private function getAuthEmployee(): Employee
    {
        $employee = $this->badgeService->resolveEmployee();

        if (! $employee) {
            abort(403, 'Aucun employé associé à ce compte badge. Contactez l\'administrateur.');
        }

        return $employee;
    }
}
