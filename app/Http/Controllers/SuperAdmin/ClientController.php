<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant as Tenant;
use App\Models\User;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Tenant::with('admin')->withCount('users')->latest()->paginate(20);

        return view('superadmin.clients.index', compact('clients'));
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['users', 'admin']);

        return view('superadmin.clients.show', compact('tenant'));
    }
}
