<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class HolidayController extends Controller
{
< HEAD

=======

    public function debug(): JsonResponse
    {
        $response = Http::withoutVerifying()->withHeaders([
            'accept'    => 'application/json',
            'X-API-KEY' => 'ff0292833c26ea7056ebc413bfbcaaa48f75fbd235c85615',
        ])->get("https://calendar-api.ma/api/v1/holidays/2026", [
            'holiday_type' => 'ND',
        ]);



        return response()->json([
            'status'  => $response->status(),
            'headers' => $response->headers(),
            'body'    => $response->json() ?? $response->body(),
        ]);
    }
>>>>>>> 6b5799881c0e6344d7e3c861606c54fdeaa2dc06





    public function index(int $year, int $month): JsonResponse
    {
        $cacheKey = "holidays_{$year}_{$month}";

        $holidays = Cache::remember($cacheKey, now()->addHours(24), function () use ($year, $month) {


            $response = Http::withoutVerifying()->withHeaders([
                'accept'    => 'application/json',
            'X-API-KEY' => 'ff0292833c26ea7056ebc413bfbcaaa48f75fbd235c85615',
            ])->get("https://calendar-api.ma/api/v1/holidays/{$year}", [
                'holiday_type' => 'ND',
            ]);



            if ($response->failed()) {
                return null;
            }

            $json = $response->json();
            $all  = is_array($json) ? $json : ($json['data'] ?? $json['holidays'] ?? []);


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
