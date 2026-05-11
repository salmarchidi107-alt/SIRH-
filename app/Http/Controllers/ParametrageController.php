<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Department;
use Illuminate\Http\Request;

class ParametrageController extends Controller
{
    /**
     * Force le tenant_id dans la config si absent (route hors middleware identify-tenant).
     */
    private function ensureTenant(): void
    {
        if (blank(config('app.current_tenant_id')) && auth()->check()) {
            $tenantId = auth()->user()->tenant_id;
            if ($tenantId) {
                config(['app.current_tenant_id' => $tenantId]);
            }
        }
    }

    public function index()
    {
        $this->ensureTenant(); // 

        $rooms       = Room::with('department')->orderBy('name')->get();
        $departments = Department::withCount('rooms')->orderBy('name')->get();

        return view('parametrage.index', compact('rooms', 'departments'));
    }
}