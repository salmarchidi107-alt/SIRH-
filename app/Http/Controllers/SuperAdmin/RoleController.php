<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    public function index()
    {
        $roles = [
            ['name' => 'superadmin', 'label' => 'Super Admin',   'description' => 'Accès total à la plateforme'],
            ['name' => 'admin',      'label' => 'Admin Tenant',  'description' => 'Gère son espace tenant'],
            ['name' => 'user',       'label' => 'Utilisateur',   'description' => 'Accès à son espace seulement'],
        ];

        return view('superadmin.roles.index', compact('roles'));
    }
}
