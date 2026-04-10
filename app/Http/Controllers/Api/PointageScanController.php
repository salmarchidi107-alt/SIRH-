<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Pointage;
use App\Models\PointageEvent;
use App\Models\Tablette;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointageScanController extends Controller
{
    /**
     * Reçoit un scan de badge depuis la tablette.
     *
     * POST /api/pointage/scan
     * Headers: Authorization: Bearer {token_tablette}
     * Body: {
     *   "employee_id": 12,
     *   "type": "entree" | "sortie",
     *   "scanne_le": "2025-02-24T11:15:00",   // ISO 8601 (optionnel, défaut: now)
     *   "tablette_id": "TAB-001",
     *   "geolat": 33.9716,
     *   "geolng": -6.8498
     * }
     */
    public function scan(Request $request): JsonResponse
    {
        // 1. Authentifier la tablette via token Bearer
        $token = $request->bearerToken();
        $tablette = Tablette::where('token', $token)->where('active', true)->first();

        if (!$tablette) {
            return response()->json(['error' => 'Tablette non autorisée'], 401);
        }

        $tablette->marquerConnexion();

        // 2. Valider les données reçues
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|in:entree,sortie',
            'scanne_le'   => 'nullable|date',
            'geolat'      => 'nullable|numeric',
            'geolng'      => 'nullable|numeric',
        ]);

        $scanTime = isset($data['scanne_le'])
            ? Carbon::parse($data['scanne_le'])
            : now();

        $dateJour = $scanTime->toDateString();

        DB::beginTransaction();

        try {
            // 3. Trouver ou créer le pointage du jour
            $pointage = Pointage::firstOrCreate(
                ['employee_id' => $data['employee_id'], 'date' => $dateJour],
                [
                    'statut'      => 'pas_de_badge',
                    'tablette_id' => $tablette->tablette_id,
                    'source'      => 'tablette',
                    'geolat'      => $data['geolat'] ?? null,
                    'geolng'      => $data['geolng'] ?? null,
                ]
            );

            // 4. Enregistrer l'événement brut
            PointageEvent::create([
                'employee_id'    => $data['employee_id'],
                'pointage_id'    => $pointage->id,
                'type'           => $data['type'],
                'scanne_le'      => $scanTime,
                'tablette_id'    => $tablette->tablette_id,
                'token_tablette' => $token,
            ]);

            // 5. Mettre à jour le pointage selon le type
            if ($data['type'] === 'entree') {
                // Ne pas écraser une entrée existante (garder la première)
                if (!$pointage->heure_entree) {
                    $pointage->heure_entree = $scanTime->format('H:i:s');
                    $pointage->statut       = 'present';
                    $pointage->derniere_sync = now();
                    $pointage->save();
                }
            } else {
                // Pour la sortie, toujours prendre le dernier scan
                $pointage->heure_sortie  = $scanTime->format('H:i:s');
                $pointage->statut        = 'present';
                $pointage->derniere_sync = now();
                $pointage->save();
                $pointage->calculerTotalHeures();
            }

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => "Badge {$data['type']} enregistré pour l'employé #{$data['employee_id']}",
                'pointage'   => [
                    'id'           => $pointage->id,
                    'date'         => $pointage->date->toDateString(),
                    'heure_entree' => $pointage->heure_entree,
                    'heure_sortie' => $pointage->heure_sortie,
                    'total_heures' => $pointage->total_heures,
                    'statut'       => $pointage->statut,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Retourne l'état de pointage du jour pour la tablette.
     * GET /api/pointage/status?date=2025-02-24
     */
    public function status(Request $request): JsonResponse
    {
        $token    = $request->bearerToken();
        $tablette = Tablette::where('token', $token)->where('active', true)->first();

        if (!$tablette) {
            return response()->json(['error' => 'Non autorisé'], 401);
        }

        $date = $request->get('date', today()->toDateString());

        $pointages = Pointage::forDate($date)
            ->with('employee:id,nom,prenom')
            ->get()
            ->map(fn($p) => [
                'employee_id'  => $p->employee_id,
                'nom'          => $p->employee->nom . ' ' . $p->employee->prenom,
                'statut'       => $p->statut,
                'heure_entree' => $p->heure_entree,
                'heure_sortie' => $p->heure_sortie,
            ]);

        return response()->json([
            'date'      => $date,
            'tablette'  => $tablette->nom,
            'pointages' => $pointages,
        ]);
    }

    /**
     * Heartbeat — la tablette signale qu'elle est en ligne.
     * POST /api/pointage/heartbeat
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $token    = $request->bearerToken();
        $tablette = Tablette::where('token', $token)->where('active', true)->first();

        if (!$tablette) {
            return response()->json(['error' => 'Non autorisé'], 401);
        }

        $tablette->marquerConnexion();

        return response()->json(['ok' => true, 'server_time' => now()->toIso8601String()]);
    }
}
