<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Pointage;
use App\Models\Tablette;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PointageController extends Controller
{
    /**
     * Affiche la page principale de pointage.
     */
    public function index(Request $request): View
    {
        $date        = $request->get('date', today()->toDateString());
        $currentDate = Carbon::parse($date);

        // Semaine courante (lundi → dimanche)
        $startOfWeek = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $currentDate->copy()->endOfWeek(Carbon::SUNDAY);

        // Jours de la semaine avec statut
        $weekDays = collect();
        for ($d = $startOfWeek->copy(); $d->lte($endOfWeek); $d->addDay()) {
            $weekDays->push([
                'date'       => $d->copy(),
                'label'      => ucfirst($d->translatedFormat('l')),
                'short'      => $d->translatedFormat('d M.'),
                'isToday'    => $d->isToday(),
                'isSelected' => $d->toDateString() === $currentDate->toDateString(),
                'valide'     => Pointage::forDate($d->toDateString())->where('valide', true)->exists(),
            ]);
        }

        // Employés avec pointages du jour
        $employees = Employee::with(['pointages' => function ($q) use ($currentDate) {
            $q->forDate($currentDate->toDateString());
        }])
        ->orderBy('last_name')   // ← adapté à tes colonnes : last_name / first_name
        ->get()
        ->map(function ($emp) {
            $pointage = $emp->pointages->first();

            // Initiales pour l'avatar
            $initiales = strtoupper(
                substr($emp->first_name ?? '', 0, 1) .
                substr($emp->last_name  ?? '', 0, 1)
            );

            return [
                'id'       => $emp->id,
                'nom'      => trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')),
                'avatar'   => $initiales ?: '?',
                'pointage' => $pointage,
            ];
        });

        // Statistiques du jour
        $stats = [
            'valides'    => $employees->filter(fn($e) => $e['pointage']?->valide)->count(),
            'presents'   => $employees->filter(fn($e) => $e['pointage']?->statut === 'present')->count(),
            'absents'    => $employees->filter(fn($e) => in_array($e['pointage']?->statut, ['absent', 'absence_injustifiee']))->count(),
            'en_attente' => $employees->filter(fn($e) => !$e['pointage'] || $e['pointage']?->statut === 'pas_de_badge')->count(),
            'total'      => $employees->count(),
        ];

        // Dernière tablette connectée
        $dernierSync = null;
        try {
            $dernierSync = Tablette::where('active', true)
                ->latest('derniere_connexion')
                ->first();
        } catch (\Exception $e) {}

        return view('pointage.index', compact(
            'employees', 'weekDays', 'currentDate',
            'startOfWeek', 'endOfWeek', 'stats', 'dernierSync'
        ));
    }

    /**
     * Valide tous les pointages "present" du jour.
     */
    public function validerJournee(Request $request): JsonResponse
    {
        $date = $request->input('date', today()->toDateString());

        $count = Pointage::forDate($date)
            ->where('statut', 'present')
            ->update(['valide' => true]);

        return response()->json([
            'success' => true,
            'message' => "{$count} pointage(s) validé(s)",
            'count'   => $count,
        ]);
    }

    /**
     * Toggle validation d'un pointage individuel.
     */
    public function toggleValider(Pointage $pointage): JsonResponse
    {
        $pointage->update(['valide' => !$pointage->valide]);

        return response()->json([
            'success' => true,
            'valide'  => $pointage->fresh()->valide,
        ]);
    }

    /**
     * Toggle ignorer/garder un badge.
     */
    public function toggleIgnore(Pointage $pointage): JsonResponse
    {
        $pointage->update(['ignore_badge' => !$pointage->ignore_badge]);

        return response()->json([
            'success'      => true,
            'ignore_badge' => $pointage->fresh()->ignore_badge,
        ]);
    }

    /**
     * Mise à jour manuelle d'un pointage.
     */
    public function update(Request $request, Pointage $pointage): JsonResponse
    {
        $data = $request->validate([
            'heure_entree'  => 'nullable|date_format:H:i',
            'heure_sortie'  => 'nullable|date_format:H:i',
            'pause_minutes' => 'nullable|integer|min:0|max:480',
            'statut'        => 'nullable|in:present,absent,absence_injustifiee,pas_de_badge',
        ]);

        $pointage->update($data);
        $pointage->calculerTotalHeures();

        return response()->json([
            'success'  => true,
            'pointage' => $pointage->fresh(),
        ]);
    }
}
