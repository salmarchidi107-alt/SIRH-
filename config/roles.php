<?php

return [
    'roles' => [
        'admin' => ['Administrateur', 'super admin'],
        'rh' => ['Responsable RH'],
        'employee' => ['Employé'],
    ],

    'permissions' => [
        'manage_employees' => ['admin', 'rh'],
        'manage_absences' => ['admin', 'rh'],
        'approve_absences' => ['admin'],
        'view_dashboard_stats' => ['admin', 'rh'],
        'manage_salary' => ['admin', 'rh'],
        'manage_planning' => ['admin', 'rh'],
    ],
];

