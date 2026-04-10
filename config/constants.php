<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Employee Constants
    |--------------------------------------------------------------------------
    */
    'employee' => [
        'default_avatar' => 'images/default-avatar.png',
        'matricule_prefix' => 'EMP',
        'matricule_padding' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Constants
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'recent_limit' => 5,
        'recent_news_days' => 7,
        'cache_minutes' => 10,
        'months_back' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Planning Constants
    |--------------------------------------------------------------------------
    */
    'planning' => [
        'weekend_days' => [6, 0], // Carbon::SATURDAY, Carbon::SUNDAY
    ],

    /*
    |--------------------------------------------------------------------------
    | Hospital Constants
    |--------------------------------------------------------------------------
    */
    'hospital' => [
        'name' => env('HOSPITAL_NAME', 'HospitalRH'),
        'logo' => env('HOSPITAL_LOGO', '/images/default-avatar.png'),
    ],
];


