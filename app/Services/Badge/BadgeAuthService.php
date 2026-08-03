<?php

namespace App\Services\Badge;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BadgeAuthService
{
    // ─── Vérification PIN ──────────────────────────────────────────────

    public function verifyPin(string $pin): ?Employee
    {
        // ✅ FIX: Utiliser plain_pin au lieu de pin
        $employees = Employee::where('status', 'active')->get();

        return $employees->first(function ($emp) use ($pin) {
            if (empty($emp->plain_pin)) return false;
            return $emp->plain_pin === $pin;
        });
    }

    // ─── Auto-création du user lié à l'employé ─────────────────────────

    public function ensureUserForEmployee(Employee $employee): User
    {
        $user = $employee->user;
        if ($user) {
            return $user;
        }

        $email = $employee->email
            ?: ('badge.emp' . $employee->id . '@hospitalrh.local');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''))
                                ?: 'Employee ' . $employee->id,
                'password' => Hash::make(Str::random(16)),
                'role'     => User::ROLE_EMPLOYEE,
            ]
        );

        $employee->user_id = $user->id;
        $employee->save();
        $user->employee_id = $employee->id;
        $user->save();

        return $user;
    }

    public function saveSignature(Employee $employee, string $signature): void
    {
        $employee->update(['signature' => substr($signature, 0, 255)]);
    }

    // ─── Résolution des inputs ──────────────────────────────────────────

    public function resolveShiftType(?string $input): string
    {
        return in_array($input, ['normal', 'garde'], true) ? $input : 'normal';
    }

    public function resolveActionType(string $action, string $subaction): string
    {
        return match ($subaction) {
            'debut'        => 'entree',
            'retour_pause' => 'retour_pause',
            'sortie_pause' => 'pause',
            'fin_shift'    => 'sortie',
            default        => $action === 'entree' ? 'entree' : 'sortie',
        };
    }

    // ─── Géolocalisation ─────────────────────────────────────────────────

    public function buildGeoData(Request $request): array
    {
        $rawLat    = trim((string) $request->input('geo_latitude',  ''));
        $rawLng    = trim((string) $request->input('geo_longitude', ''));
        $rawAcc    = trim((string) $request->input('geo_accuracy',  ''));
        $rawDenied = trim((string) $request->input('geo_denied',    '1'));
        $rawAddr   = trim((string) $request->input('geo_address',   ''));

        Log::info('Badge geo_data reçu (raw)', [
            'geo_denied'    => $rawDenied,
            'geo_latitude'  => $rawLat,
            'geo_longitude' => $rawLng,
            'geo_accuracy'  => $rawAcc,
            'geo_address'   => $rawAddr,
        ]);

        if ($rawLat === '' || $rawLng === '') {
            Log::warning('Badge geo : coordonnées vides → denied forcé');
            $geoData = ['latitude' => null, 'longitude' => null, 'accuracy' => null, 'address' => null, 'denied' => true];
        } else {
            $lat = (float) $rawLat;
            $lng = (float) $rawLng;
            $acc = ($rawAcc !== '') ? (float) $rawAcc : null;

            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                Log::warning('Badge geo : coordonnées hors plage', ['lat' => $lat, 'lng' => $lng]);
                $geoData = ['latitude' => null, 'longitude' => null, 'accuracy' => null, 'address' => null, 'denied' => true];
            } else {
                $geoData = [
                    'latitude'  => $lat,
                    'longitude' => $lng,
                    'accuracy'  => ($acc !== null && $acc > 0) ? (int) round($acc) : null,
                    'address'   => ($rawAddr !== '') ? $rawAddr : null,
                    'denied'    => false,
                ];

                Log::info('Badge geo : données construites', $geoData);
            }
        }

        // ── Reverse geocoding côté serveur (fallback si le client n'a pas fourni d'adresse) ──
        if (! $geoData['denied']
            && $geoData['latitude'] !== null
            && $geoData['longitude'] !== null
            && empty($geoData['address'])) {
            $geoData['address'] = $this->reverseGeocode($geoData['latitude'], $geoData['longitude']);
        }

        Log::info('Badge geoData final avant session', [
            'denied'    => $geoData['denied'],
            'latitude'  => $geoData['latitude'],
            'longitude' => $geoData['longitude'],
            'accuracy'  => $geoData['accuracy'],
            'address'   => $geoData['address'],
        ]);

        return $geoData;
    }

    private function reverseGeocode(float $lat, float $lng): ?string
    {
        try {
            $response = Http::timeout(6)
                ->withHeaders([
                    'User-Agent'      => 'HospitalRH-Badge/1.0 contact@hospitalrh.ma',
                    'Accept-Language' => 'fr',
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat'             => $lat,
                    'lon'             => $lng,
                    'format'          => 'json',
                    'zoom'            => 18,
                    'accept-language' => 'fr',
                ]);

            if ($response->successful()) {
                $data    = $response->json();
                $a       = $data['address'] ?? [];
                $road    = $a['road'] ?? $a['pedestrian'] ?? $a['footway'] ?? null;
                $num     = $a['house_number'] ?? null;
                $quarter = $a['quarter'] ?? $a['neighbourhood'] ?? $a['suburb'] ?? null;
                $city    = $a['city'] ?? $a['town'] ?? $a['village'] ?? null;
                $state   = $a['state'] ?? $a['region'] ?? null;
                $country = $a['country'] ?? null;
                $street  = $road ? ($num ? $num . ' ' . $road : $road) : null;
                $parts   = array_filter([$street, $quarter, $city, $state, $country]);
                return implode(', ', $parts) ?: ($data['display_name'] ?? null);
            }
        } catch (\Exception $e) {
            Log::warning('Reverse geocoding failed', ['error' => $e->getMessage()]);
        }
        return null;
    }

    // ─── Photo faciale ───────────────────────────────────────────────────

    /**
     * Traite le data-URL base64 de la photo faciale.
     * Sauvegarde le fichier sur disque ET conserve le base64 brut.
     */
    public function buildPhotoData(?string $dataUrl, int $employeeId): array
    {
        if (empty($dataUrl)) {
            return $this->emptyPhotoData();
        }

        try {
            if (! preg_match('/^data:([a-z\/]+);base64,(.+)$/s', $dataUrl, $m)) {
                Log::warning('Badge photo : format data-URL invalide');
                return $this->emptyPhotoData();
            }

            $mime   = strtolower($m[1]);
            $base64 = $m[2];

            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (! in_array($mime, $allowed, true)) {
                Log::warning('Badge photo : MIME non autorisé', ['mime' => $mime]);
                return $this->emptyPhotoData();
            }

            $binary = base64_decode($base64, strict: true);
            if ($binary === false) {
                Log::warning('Badge photo : base64 invalide');
                return $this->emptyPhotoData();
            }

            $size = strlen($binary);
            if ($size > 5 * 1024 * 1024) {
                Log::warning('Badge photo : trop volumineuse', ['size' => $size]);
                return $this->emptyPhotoData();
            }

            $ext      = match ($mime) { 'image/png' => 'png', 'image/webp' => 'webp', default => 'jpg' };
            $filename = 'emp_' . $employeeId . '_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.' . $ext;
            $path     = 'pointages/faces/' . $filename;

            Storage::disk('public')->put($path, $binary);

            Log::info('Badge photo : sauvegardée', ['path' => $path, 'size' => $size, 'mime' => $mime]);

            return [
                'face_photo_path'   => $path,
                'face_photo_disk'   => 'public',
                'face_photo_base64' => $base64,
                'face_photo_size'   => $size,
                'face_photo_mime'   => $mime,
            ];

        } catch (\Exception $e) {
            Log::error('Badge photo : erreur inattendue', ['error' => $e->getMessage()]);
            return $this->emptyPhotoData();
        }
    }

    private function emptyPhotoData(): array
    {
        return [
            'face_photo_path'   => null,
            'face_photo_disk'   => 'public',
            'face_photo_base64' => null,
            'face_photo_size'   => 0,
            'face_photo_mime'   => null,
        ];
    }
}
