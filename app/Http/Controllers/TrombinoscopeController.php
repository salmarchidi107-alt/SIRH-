<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\TrombinoscopeExport;
use Maatwebsite\Excel\Facades\Excel;

class TrombinoscopeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->department) {
            $query->where('department', $request->department);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('position', 'like', "%{$request->search}%");
            });
        }

$employees = $query->where('status', 'active')->get();
$departments = Employee::distinct()->pluck('department');


        return view('trombinoscope.index', compact('employees', 'departments'));


    }

    public function export()
    {
        return Excel::download(new TrombinoscopeExport, 'trombinoscope.xlsx');
    }
}
