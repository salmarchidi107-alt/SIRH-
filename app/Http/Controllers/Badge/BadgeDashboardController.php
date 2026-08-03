<?php

namespace App\Http\Controllers\Badge;

use App\Http\Controllers\Controller;
use App\Services\Badge\BadgeDashboardService;
use Illuminate\Support\Facades\Auth;

class BadgeDashboardController extends Controller
{
    public function __construct(private BadgeDashboardService $badgeDashboardService) {}

    public function index()
    {
        $user = Auth::guard('badge')->user();

        $data = $this->badgeDashboardService->buildIndexData($user);

        return view('badge.dashboard', array_merge(['user' => $user], $data));
    }

    public static function getTodayShift(int $employeeId): array
    {
        return app(BadgeDashboardService::class)->getTodayShift($employeeId);
    }
}
