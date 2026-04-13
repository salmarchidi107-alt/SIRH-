<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayService
{
    public function getCurrentYearHolidays(): array
    {
        return $this->getHolidaysForYear(now()->year);
    }

    public function getHolidaysForYear(int $year): array
    {
        return Cache::remember("holidays_{$year}", now()->addDay(), function () use ($year) {
            return $this->fetchHolidaysFromApi($year) ?? $this->getDefaultHolidays($year);
        });
    }

    public function refreshHolidaysCache(int $year = null): array
    {
        $year = $year ?: now()->year;
        $holidays = $this->fetchHolidaysFromApi($year) ?? $this->getDefaultHolidays($year);

        Cache::put("holidays_{$year}", $holidays, now()->addDay());

        return $holidays;
    }

    protected function fetchHolidaysFromApi(int $year): ?array
    {
        try {
            $response = Http::timeout(10)
                ->get('https://calendar-api.ma/api/holidays', ['year' => $year]);

            if (! $response->successful()) {
                return null;
            }

            $payload = $response->json();
            if (isset($payload['holidays']) && is_array($payload['holidays'])) {
                return $payload['holidays'];
            }

            if (is_array($payload)) {
                return $payload;
            }
        } catch (\Throwable $exception) {
            Log::error('HolidayService API error: ' . $exception->getMessage());
        }

        return null;
    }

    protected function getDefaultHolidays(int $year): array
    {
        return [
            ['name' => 'Nouvel An', 'date' => "{$year}-01-01"],
            ['name' => 'Manifeste de l\'Indépendance', 'date' => "{$year}-01-11"],
            ['name' => 'Fête du Travail', 'date' => "{$year}-05-01"],
            ['name' => 'Fête de la Throne', 'date' => "{$year}-07-30"],
            ['name' => 'Fête de la Révolution', 'date' => "{$year}-08-14"],
            ['name' => 'Fête de la Jeunesse', 'date' => "{$year}-08-21"],
            ['name' => 'Mort du Roi Hassan II', 'date' => "{$year}-07-30"],
            ['name' => 'Anniversaire du Roi', 'date' => "{$year}-08-21"],
            ['name' => 'Aïd al-Fitr', 'date' => ''],
            ['name' => 'Aïd al-Adha', 'date' => ''],
            ['name' => 'Nouvel An Hégirien', 'date' => ''],
            ['name' => 'Fête de l’Indépendance', 'date' => "{$year}-11-18"],
        ];
    }
}
