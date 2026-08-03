<?php


namespace App\Http\Controllers\Badge;

use App\Http\Controllers\Controller;
use App\Services\Badge\BadgeAuthService;
use App\Services\Badge\BadgePointageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BadgeAuthController extends Controller
{
    public function __construct(
        private BadgeAuthService     $badgeAuthService,
        private BadgePointageService $badgePointageService
    ) {}

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
            'pin'        => 'required|string|size:6|regex:/^[0-9]{4}[A-Z]{2}$/',
            'signature'  => 'required|string',
            'face_photo' => 'nullable|string',
            'shift_type' => 'nullable|string|in:normal,garde',
        ]);

        // ── 1. Vérifier le PIN ──────────────────────────────────────────
        $employee = $this->badgeAuthService->verifyPin($request->pin);

        if (! $employee) {
            return back()->withErrors(['pin' => 'PIN incorrect.'])->withInput();
        }

        // ── 2. Auto-créer le user si besoin ────────────────────────────
        $user = $this->badgeAuthService->ensureUserForEmployee($employee);

        // ── 3. Sauvegarder la signature ─────────────────────────────────
        $this->badgeAuthService->saveSignature($employee, $request->signature);

        // ── 4. Session badge ────────────────────────────────────────────
        $request->session()->put('badge_user_id', $user->id);

        // ── 5. Résoudre le type de shift (normal / garde) ───────────────
        $shiftType = $this->badgeAuthService->resolveShiftType($request->input('shift_type'));

        // ── 6. Résoudre le type d'action ────────────────────────────────
        $recordType = $this->badgeAuthService->resolveActionType(
            $action,
            $request->input('action_sub', $action)
        );

        // ── 7. Construire les données géo (+ reverse geocoding fallback) ─
        $geoData = $this->badgeAuthService->buildGeoData($request);

        // ── 8. Traiter la photo faciale ─────────────────────────────────
        $photoData = $this->badgeAuthService->buildPhotoData(
            $request->input('face_photo'),
            $employee->id
        );

        // ── 9. Enregistrer le pointage ───────────────────────────────────
        try {
            $this->badgePointageService->recordAction(
                $recordType,
                $employee,
                $geoData,
                $photoData,
                $shiftType
            );
        } catch (\Exception $e) {
            Log::error('Badge pointage error', [
                'error'    => $e->getMessage(),
                'employee' => $employee->id,
            ]);
        }

        // ── 10. Stocker en session et rediriger ─────────────────────────
        $request->session()->put('last_type',       $recordType);
        $request->session()->put('last_geo',        $geoData);
        $request->session()->put('last_shift_type', $shiftType);
        $request->session()->save();

        return redirect()->route('badge.result');
    }

    // ── Déconnexion ──────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        $request->session()->forget(['badge_user_id', 'last_type', 'last_geo', 'last_shift_type']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('badge.pointage');
    }
}
