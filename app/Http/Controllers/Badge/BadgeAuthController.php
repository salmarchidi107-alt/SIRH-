<?php

namespace App\Http\Controllers\Badge;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BadgeAuthController extends Controller
{
    // ── Afficher la page d'authentification ─────────────────────────────
    public function showAuth(Request $request)
    {
        return view('badge.login', [
            'action' => $request->action ?? 'entree',
            'intent' => $request->intent ?? $request->action ?? 'entree',
        ]);
    }

    // ── Auth + pointage + géolocalisation ───────────────────────────────
    public function authAction(Request $request)
    {
        $action = $request->input('action', 'entree');

        $request->validate([
            'pin'       => 'required|string|size:6|regex:/^[0-9]{4}[A-Z]{2}$/',
            'signature' => 'required|string',
        ]);

        // ── 1. Vérifier le PIN ──────────────────────────────────────────
        $employees = Employee::where('status', 'active')->get();
        $employee  = $employees->first(function ($emp) use ($request) {
            if (empty($emp->pin)) return false;
            try {
                if (Hash::check($request->pin, $emp->pin)) return true;
            } catch (\Exception $e) {}
            return $emp->pin === $request->pin;
        });

        if (! $employee) {
            return back()->withErrors(['pin' => 'PIN incorrect.'])->withInput();
        }

        // ── 2. Auto-créer le user si besoin ────────────────────────────
        $user = $employee->user;
        if (! $user) {
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
        }

        // ── 3. Sauvegarder la signature ─────────────────────────────────
        $employee->update(['signature' => substr($request->signature, 0, 255)]);

        // ── 4. Session badge ────────────────────────────────────────────
        $request->session()->put('badge_user_id', $user->id);

        // ── 5. Résoudre le type d'action ────────────────────────────────
        $subaction  = $request->input('action_sub', $action);
        $recordType = match ($subaction) {
            'debut'        => 'entree',
            'retour_pause' => 'retour_pause',
            'sortie_pause' => 'pause',
            'fin_shift'    => 'sortie',
            default        => $action === 'entree' ? 'entree' : 'sortie',
        };

        // ── 6. Construire les données géo ───────────────────────────────
        $geoData = $this->buildGeoData($request);

        // ── 7. Reverse geocoding côté serveur (fallback si le client n'a pas pu) ──
        if (! $geoData['denied']
            && $geoData['latitude'] !== null
            && $geoData['longitude'] !== null
            && empty($geoData['address'])) {
            $geoData['address'] = $this->reverseGeocode(
                $geoData['latitude'],
                $geoData['longitude']
            );
        }

        // ── 8. Log complet pour diagnostic ──────────────────────────────
        Log::info('Badge geoData final avant session', [
            'denied'    => $geoData['denied'],
            'latitude'  => $geoData['latitude'],
            'longitude' => $geoData['longitude'],
            'accuracy'  => $geoData['accuracy'],
            'address'   => $geoData['address'],
        ]);

        // ── 9. Enregistrer le pointage ──────────────────────────────────
        try {
            app(BadgePointageController::class)->recordAction(
                $recordType,
                $employee,
                $geoData
            );
        } catch (\Exception $e) {
            Log::error('Badge pointage error', [
                'error'    => $e->getMessage(),
                'employee' => $employee->id,
            ]);
        }

        // ── 10. Stocker en session et rediriger ─────────────────────────
        // On stocke APRÈS le pointage pour éviter tout écrasement de session
        $request->session()->put('last_type', $recordType);
        $request->session()->put('last_geo',  $geoData);
        $request->session()->save();

        return redirect()->route('badge.result');
    }

    // ── Déconnexion ──────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        $request->session()->forget(['badge_user_id', 'last_type', 'last_geo']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('badge.pointage');
    }

    // ── Helpers privés ────────────────────────────────────────────────────

    /**
     * Extrait, valide et nettoie les données GPS envoyées depuis le formulaire.
     *
     * geo_denied vaut :
     *   "0"  → géoloc accordée  (on doit avoir lat/lng)
     *   "1"  → géoloc refusée   (pas de coords)
     *   ""   → non envoyé       (traité comme refusé)
     */
    private function buildGeoData(Request $request): array
    {
        $rawLat    = trim((string) $request->input('geo_latitude',  ''));
        $rawLng    = trim((string) $request->input('geo_longitude', ''));
        $rawAcc    = trim((string) $request->input('geo_accuracy',  ''));
        $rawDenied = trim((string) $request->input('geo_denied',    '1'));
        $rawAddr   = trim((string) $request->input('geo_address',   ''));

        // Log complet pour diagnostiquer
        Log::info('Badge geo_data reçu (raw)', [
            'geo_denied'    => $rawDenied,
            'geo_latitude'  => $rawLat,
            'geo_longitude' => $rawLng,
            'geo_accuracy'  => $rawAcc,
            'geo_address'   => $rawAddr,
        ]);

        // ── geo_denied : "0" = autorisé, tout le reste = refusé ─────────
        // On compare explicitement à la string "0" pour éviter tout
        // problème de cast PHP (filter_var, intval, boolval, etc.)
        $denied = ($rawDenied !== '0');

        // Si pas de coordonnées → denied quoi qu'il arrive
        if ($rawLat === '' || $rawLng === '') {
            Log::warning('Badge geo : coordonnées vides → denied forcé', [
                'geo_denied_raw' => $rawDenied,
                'denied_forced'  => true,
            ]);
            return [
                'latitude'  => null,
                'longitude' => null,
                'accuracy'  => null,
                'address'   => null,
                'denied'    => true,
            ];
        }

        $lat = (float) $rawLat;
        $lng = (float) $rawLng;
        $acc = ($rawAcc !== '') ? (float) $rawAcc : null;

        // Validation plages géographiques
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            Log::warning('Badge geo : coordonnées hors plage', [
                'lat' => $lat,
                'lng' => $lng,
            ]);
            return [
                'latitude'  => null,
                'longitude' => null,
                'accuracy'  => null,
                'address'   => null,
                'denied'    => true,
            ];
        }

        // Adresse reçue depuis le client (reverse geocoding JS)
        $address = ($rawAddr !== '') ? $rawAddr : null;

        $result = [
            'latitude'  => $lat,
            'longitude' => $lng,
            'accuracy'  => ($acc !== null && $acc > 0) ? (int) round($acc) : null,
            'address'   => $address,
            'denied'    => false,   // on a des coords valides → pas denied
        ];

        Log::info('Badge geo : données construites', $result);

        return $result;
    }

    /**
     * Reverse geocoding via Nominatim (OpenStreetMap, gratuit, sans clé API).
     * Utilisé en fallback si le JS n'a pas pu envoyer l'adresse.
     */
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
                $data = $response->json();
                $a    = $data['address'] ?? [];

                // Construire une adresse lisible
                $road    = $a['road'] ?? $a['pedestrian'] ?? $a['footway'] ?? $a['path'] ?? null;
                $num     = $a['house_number'] ?? null;
                $quarter = $a['quarter'] ?? $a['neighbourhood'] ?? $a['suburb'] ?? null;
                $city    = $a['city'] ?? $a['town'] ?? $a['village'] ?? $a['municipality'] ?? null;
                $state   = $a['state'] ?? $a['region'] ?? null;
                $country = $a['country'] ?? null;

                $street = null;
                if ($road && $num)  $street = $num . ' ' . $road;
                elseif ($road)      $street = $road;

                $parts = array_filter([$street, $quarter, $city, $state, $country]);

                return implode(', ', $parts) ?: ($data['display_name'] ?? null);
            }

            Log::warning('Reverse geocoding : réponse non-ok', [
                'status' => $response->status(),
            ]);

        } catch (\Exception $e) {
            Log::warning('Reverse geocoding failed', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
