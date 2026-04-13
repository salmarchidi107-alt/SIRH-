<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Pointage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\TrombinoscopeExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class TrombinoscopeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();

        $query->when($request->department, fn($q) => $q->where('department', $request->department))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('position', 'like', "%{$request->search}%");
            }));

        $employees = $query->active()->get();
        $departments = Department::names();

        $today = Carbon::today()->toDateString();

        $pointages = Pointage::where('date', $today)
            ->get()
            ->keyBy('employee_id');

        return view('trombinoscope.index', compact('employees', 'departments', 'pointages'));
    }

    public function export()
    {
        return Excel::download(new TrombinoscopeExport, 'trombinoscope.xlsx');
    }
}