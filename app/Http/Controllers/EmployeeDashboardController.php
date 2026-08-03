<?php

namespace App\Http\Controllers;

use App\Services\EmployeeDashboardService;

class EmployeeDashboardController extends Controller
{
    public function __construct(private EmployeeDashboardService $employeeDashboardService) {}

    public function index()
    {
        return view('employe.dashboard', $this->employeeDashboardService->getDashboardData());
    }
}
