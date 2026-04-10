<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Domain Redirect URL
    |--------------------------------------------------------------------------
    |
    | This is the URL (without the domain) that will be used when redirecting
    | to other domains. E.g.: if the request is made to tenant1.example.com,
    | the user is redirected to example.com/redirect?tenant=tenant1...
    |
    */

    'cross_domain_redirect_url' => env('CROSS_DOMAIN_REDIRECT_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Central Domains
    |--------------------------------------------------------------------------
    |
    | Central domains are domains that belong to the central app. These
    | domains will not be identified as tenants and will use the
    | central database connection.
    |
    */

    'central_domains' => [
        'localhost',
        '127.0.0.1',
        'hospitalrh.test',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Manager
    |--------------------------------------------------------------------------
    |
    | This class is responsible for creating tenant databases. By default
    | we use the single database strategy, but you may want to switch
    | to separate databases per tenant.
    |
    */

'database_manager' => null, // Single DB - no tenant DBs

    /*
    |--------------------------------------------------------------------------
    | Tenant Identifier
    |--------------------------------------------------------------------------
    |
    | The class that identifies tenants from requests.
    |
    */

    'tenant_identifier' => \Stancl\Tenancy\Identification\Drivers\DomainTenantIdentifier::class,

    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    |
    | The model used to store tenants.
    |
    */

'tenant_model' => \App\Models\Tenant::class,

    /*
    |--------------------------------------------------------------------------
    | Tenant Connection Name
    |--------------------------------------------------------------------------
    |
    | The connection name that is used when switching to a tenant.
    |
    */

'tenant_connection_name' => null, // No DB switching

    /*
    |--------------------------------------------------------------------------
    | Central Connection Name
    |--------------------------------------------------------------------------
    |
    | The connection name that is used when on a central domain.
    |
    */

'central_connection_name' => null, // Single DB

    // Migrations des bases tenants
// Removed tenant-specific migrations - all in database/migrations/
    // Single database setup
];

