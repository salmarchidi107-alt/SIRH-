<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Pointage;
use App\Models\BadgeRecord;
use App\Models\Tablette;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Collection;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Exception;

class PointageController extends Controller
{
    public function marquerAbsent($employee_id)
    {
        $today = today()->toDateString();

        Pointage::updateOrCreate(
            [
                'employee_id' => $employee_id,
                'date' => $today
            ],
            [
                'statut' => 'absent',
                'heure_entree' => null,
                'heure_sortie' => null,
                'valide' => false,
            ]
        );

        return back()->with('success', 'Employé marqué absent');
    }

    public function index(Request $request): View
    {
        $date        = $request->get('date', today()->toDateString());
        $currentDate = Carbon::parse($date);

Carbon::setLocale('fr');     $startOfWeek = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $currentDate->copy()->endOfWeek(Carbon::SUNDAY);

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

$departments = \App\Models\Department::names();

        $vue = $request->get('vue', 'tous');
        

        
        $employeesQuery = Employee::active()
            ->with(['pointages' => function ($q) use ($currentDate) {
                $q->forDate($currentDate->toDateString());
            }])
            ->when($request->filled('search'), function ($q) use ($request) {
                return $q->search($request->search);
            })
            ->when($request->filled('department'), function ($q) use ($request) {
                return $q->department($request->department);
            })
            ->defaultOrder();
            
        $employees = $employeesQuery->get()
            ->map(function ($emp) use ($currentDate) {

            $pointage = $emp->pointages->first();

            //  NE PAS écraser une absence
            if ($pointage && $pointage->statut === 'absent') {
                return [
                    'id'       => $emp->id,
                    'nom'      => $emp->first_name . ' ' . $emp->last_name,
                    'avatar'   => strtoupper(substr($emp->first_name,0,1).substr($emp->last_name,0,1)),
                    'pointage' => $pointage,
                ];
            }

            if (!$pointage || !$pointage->ignore_badge) {
                $shift = BadgeRecord::where('employee_id', $emp->id)
                    ->whereDate('created_at', $currentDate->toDateString())
                    ->get();

                if ($shift->isNotEmpty()) {
                    $pointage = $this->syncPointageFromBadgeRecords($emp->id, $currentDate, $shift);
                }
            }

            return [
                'id'       => $emp->id,
                'nom'      => $emp->first_name . ' ' . $emp->last_name,
                'avatar'   => strtoupper(substr($emp->first_name,0,1).substr($emp->last_name,0,1)),
                'pointage' => $pointage,
            ];
        });

        // Filter by vue
        if ($vue === 'pointe') {
            $employees = $employees->filter(function ($e) {
                return $e['pointage']?->heure_entree && !in_array($e['pointage']->statut ?? '', ['absent']);
            });
        } elseif ($vue === 'non_pointe') {
            $employees = $employees->filter(function ($e) {
                return !$e['pointage']?->heure_entree || in_array($e['pointage']?->statut ?? '', ['absent', 'pas_de_badge']);
            });
        }

        $stats = [
            'valides'    => $employees->filter(fn($e) => $e['pointage']?->valide)->count(),
            'presents'   => $employees->filter(fn($e) => $e['pointage']?->statut === 'present')->count(),
            'absents'    => $employees->filter(fn($e) => in_array($e['pointage']?->statut, ['absent', 'absence_injustifiee']))->count(),
            'en_attente' => $employees->filter(fn($e) => !$e['pointage'] || $e['pointage']?->statut === 'pas_de_badge')->count(),
            'total'      => $employees->count(),
        ];

        $dernierSync = null;
        try {
            $dernierSync = Tablette::where('active', true)
                ->latest('derniere_connexion')
                ->first();
        } catch (\Exception $e) {}

        return view('pointage.index', compact(
            'employees', 'departments', 'weekDays', 'currentDate',
            'startOfWeek', 'endOfWeek', 'stats', 'dernierSync', 'vue'
        ));
    }


    public function validerJournee(Request $request): JsonResponse
    {
        $date = $request->input('date', today()->toDateString());

        $count = Pointage::forDate($date)
            ->where('statut', 'present')
            ->update(['valide' => true]);

        return response()->json([
            'success' => true,
            'count'   => $count,
        ]);
    }

    public function toggleValider(Pointage $pointage): JsonResponse
    {
        $pointage->update(['valide' => !$pointage->valide]);

        return response()->json([
            'success' => true,
            'valide'  => $pointage->fresh()->valide,
        ]);
    }

    public function toggleIgnore(Pointage $pointage): JsonResponse
    {
        $pointage->update(['ignore_badge' => !$pointage->ignore_badge]);

        return response()->json([
            'success'      => true,
            'ignore_badge' => $pointage->fresh()->ignore_badge,
        ]);
    }

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

    private function syncPointageFromBadgeRecords(int $employeeId, Carbon $date, Collection $shift): Pointage
    {
        $pointage = Pointage::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $date->toDateString(),
            ],
            [
                'statut' => 'present',
            ]
        );

        // ne pas écraser absence
        if ($pointage->statut === 'absent') {
            return $pointage;
        }

        $firstEntree = $shift->where('type', 'entree')->first()?->created_at;
        $lastSortie = $shift->where('type', 'sortie')->last()?->created_at;

        $pointage->heure_entree = $firstEntree?->format('H:i');
        $pointage->heure_sortie = $lastSortie?->format('H:i');

        $pointage->pause_minutes = $this->calcPauseMinutes($shift);

        $workedMinutes = $this->calcWorkedMinutes($shift);
        $pointage->total_heures = round(($workedMinutes - $pointage->pause_minutes) / 60, 2);

        $pointage->statut = 'present';

        $pointage->save();

        return $pointage->fresh();
    }

    private function calcWorkedMinutes(Collection $shift): float
    {
        $entrees = $shift->where('type', 'entree')->pluck('created_at')->toArray();
        $sorties = $shift->where('type', 'sortie')->pluck('created_at')->toArray();

        if (empty($entrees) || empty($sorties)) return 0;

        $total = 0;
        $count = min(count($entrees), count($sorties));

        for ($i = 0; $i < $count; $i++) {
            $total += strtotime($sorties[$i]) - strtotime($entrees[$i]);
        }

        return $total / 60;
    }

    private function calcPauseMinutes(Collection $shift): int
    {
        $pauses = $shift->where('type', 'pause')->pluck('created_at')->toArray();
        $retours = $shift->where('type', 'entree')->slice(1)->pluck('created_at')->toArray();

        if (empty($pauses) || empty($retours)) return 0;

        $total = 0;
        $count = min(count($pauses), count($retours));

        for ($i = 0; $i < $count; $i++) {
            $total += strtotime($retours[$i]) - strtotime($pauses[$i]);
        }

        return floor($total / 60);
    }

    public function toggleAbsence(Request $request)
    {
        $pointage = Pointage::updateOrCreate(
            [
                'employee_id' => $request->employee_id,
                'date' => $request->date ?? today()->toDateString()
            ],
            [
                'statut' => $request->absent ? 'absent' : 'present',
                'heure_entree' => null,
                'heure_sortie' => null,
            ]
        );

        return response()->json([
            'success' => true,
            'statut' => $pointage->statut
        ]);
    }

public function exportPdf(Request $request)
    {
        try {
            $date = $request->get('date', today()->toDateString());
            $currentDate = Carbon::parse($date);
            $departments = \App\Models\Department::names();
            $vue = $request->get('vue', 'tous');

            $employeesQuery = Employee::active()
                ->with(['pointages' => function ($q) use ($currentDate) {
                    $q->forDate($currentDate->toDateString());
                }])
                ->when($request->filled('search'), function ($q) use ($request) {
                    return $q->search($request->search);
                })
                ->when($request->filled('department'), function ($q) use ($request) {
                    return $q->department($request->department);
                })
                ->defaultOrder();

            $employees = $employeesQuery->get()
                ->map(function ($emp) use ($currentDate) {
                    $pointage = $emp->pointages->first();

                    if ($pointage && $pointage->statut === 'absent') {
                        return [
                            'id' => $emp->id,
                            'nom' => $emp->first_name . ' ' . $emp->last_name,
                            'department' => $emp->department,
                            'pointage' => $pointage,
                        ];
                    }

                    if (!$pointage || !$pointage->ignore_badge) {
                        $shift = BadgeRecord::where('employee_id', $emp->id)
                            ->whereDate('created_at', $currentDate->toDateString())
                            ->get();

                        if ($shift->isNotEmpty()) {
                            $pointage = $this->syncPointageFromBadgeRecords($emp->id, $currentDate, $shift);
                        }
                    }

                    return [
                        'id' => $emp->id,
                        'nom' => $emp->first_name . ' ' . $emp->last_name,
                        'department' => $emp->department,
                        'pointage' => $pointage,
                    ];
                });

            // Filter by vue
            if ($vue === 'pointe') {
                $employees = $employees->filter(function ($e) {
                    return $e['pointage']?->heure_entree && !in_array($e['pointage']->statut ?? '', ['absent']);
                });
            } elseif ($vue === 'non_pointe') {
                $employees = $employees->filter(function ($e) {
                    return !$e['pointage']?->heure_entree || in_array($e['pointage']?->statut ?? '', ['absent', 'pas_de_badge']);
                });
            }

            $stats = [
                'valides' => $employees->filter(fn($e) => $e['pointage']?->valide)->count(),
                'total' => $employees->count(),
            ];

            if ($employees->isEmpty()) {
                return back()->with('error', 'Aucun résultat avec ces filtres.');
            }

            $dept = $request->get('department', 'Tous');
            $dateStr = $currentDate->format('d/m/Y');
            $filterInfo = 'Département: ' . $dept . ' | Vue: ' . ucfirst($vue);
            $generatedAt = now()->format('d/m/Y H:i');
            $filename = 'pointage_' . $currentDate->format('Y-m-d') . '_' . \Illuminate\Support\Str::slug($dept) . '_' . $vue . '.pdf';

            $pdf = Pdf::loadView('pdf.pointage', compact('employees', 'stats', 'dateStr', 'filterInfo', 'generatedAt'))
                ->setPaper('a4', 'portrait')
                ->setOptions(['isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);

            return $pdf->download($filename);

        } catch (Exception $e) {
            Log::error('Pointage PDF export error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur génération PDF: ' . $e->getMessage());
        }
    }
    public function exportPdfByDept(Request $request, string $department)
    {
        try {
            $employees = Employee::where('department', $department)->get();
            $total = $employees->count();

            if ($total === 0) {
                return back()->with('error', 'Aucun employé dans ce département.');
            }

            $generatedAt = now()->format('d/m/Y à H:i');
            $filename = 'employes-' . \Str::slug($department) . '_' . now()->format('Y-m-d') . '.pdf';

            $pdf = Pdf::loadView('pdf.employees', compact('employees', 'total', 'generatedAt'));
            return $pdf->download($filename);

        } catch (Exception $e) {
            Log::error('PDF dept export error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur génération PDF.');
        }
    }
}

