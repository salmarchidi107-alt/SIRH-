<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PointageScanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Toutes ces routes sont préfixées par /api (via RouteServiceProvider)
| et utilisent le middleware "api" (stateless, pas de session).
|
| La tablette s'authentifie via : Authorization: Bearer {token_tablette}
| (token stocké dans la table "tablettes", généré une seule fois)
|--------------------------------------------------------------------------
*/

// ─── Pointage tablette (authentification par token Bearer) ───────────────────
Route::prefix('pointage')->group(function () {

    // Envoyer un scan de badge (entrée ou sortie)
    // POST /api/pointage/scan
    Route::post('/scan', [PointageScanController::class, 'scan']);

    // Consulter l'état des pointages du jour
    // GET /api/pointage/status?date=2025-02-24
    Route::get('/status', [PointageScanController::class, 'status']);

    // Signal de vie (heartbeat toutes les 5 min depuis la tablette)
    // POST /api/pointage/heartbeat
    Route::post('/heartbeat', [PointageScanController::class, 'heartbeat']);
});

// ─── Route auth Sanctum (optionnel, si tu utilises Sanctum plus tard) ────────
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
