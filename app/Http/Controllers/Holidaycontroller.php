<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class HolidayController extends Controller
{
    /**
     * GET /holidays/{year}/{month}
     *
     * Proxy vers calendar-api.ma — la clé API reste côté serveur.
     * Résultats mis en cache 24h pour éviter les appels répétés.
     */
    /**
     * GET /holidays/debug  →  affiche la réponse brute de l'API (à supprimer après debug)
     */
    public function debug(): JsonResponse
    {
        $response = Http::withoutVerifying()->withHeaders([
            'accept'    => 'application/json',
            'x-api-key' => config('services.calendarapi.key'),
        ])->get("https://calendar-api.ma/api/v1/holidays/2026", [
            'holiday_type' => 'ND',
        ]);

        return response()->json([
            'status'  => $response->status(),
            'headers' => $response->headers(),
            'body'    => $response->json() ?? $response->body(),
        ]);
    }

    /**
     * GET /holidays/{year}/{month}
     * Proxy vers calendar-api.ma — résultats mis en cache 24h.
     */
    public function index(int $year, int $month): JsonResponse
    {
        $cacheKey = "holidays_{$year}_{$month}";

        $holidays = Cache::remember($cacheKey, now()->addHours(24), function () use ($year, $month) {

            // Endpoint réel : GET /api/v1/holidays/{year}?holiday_type=ND
            $response = Http::withoutVerifying()->withHeaders([
                'accept'    => 'application/json',
                'x-api-key' => config('services.calendarapi.key'),
            ])->get("https://calendar-api.ma/api/v1/holidays/{$year}", [
                'holiday_type' => 'ND',
            ]);

            if ($response->failed()) {
                return null;
            }

            $json = $response->json();
            $all  = is_array($json) ? $json : ($json['data'] ?? $json['holidays'] ?? []);

            // Filtrer uniquement le mois demandé (champ "month" disponible directement)
            return array_values(array_filter($all, function ($h) use ($month) {
                return isset($h['month']) && (int) $h['month'] === $month;
            }));
        });

        if ($holidays === null) {
            return response()->json(['error' => 'Impossible de charger les jours fériés.'], 502);
        }

        return response()->json($holidays);
    }
}