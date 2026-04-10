<?php

namespace App\Ai\Tools;

use App\Models\Absence;
use App\Models\Employee;
use App\Models\Planning;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfTool
{


    public function name(): string
    {
        return 'generate_pdf';
    }

    public function description(): string
    {
        return 'Génère un fichier PDF téléchargeable et retourne une URL de téléchargement. '
             . 'Types disponibles : '
             . '"absences" → absences approuvées du jour, '
             . '"employees" → liste complète des employés actifs, '
             . '"planning" → planning hebdomadaire d\'un employé (matricule requis).';
    }

    public function execute(array $arguments): string
    {
        $type = $arguments['type'] ?? '';

        Storage::disk('public')->makeDirectory('pdfs');
        // Symlink removed - Windows permission issue, controller handles path

        return match ($type) {
            'absences'  => $this->generateAbsencesPdf($arguments),
            'employees' => $this->generateEmployeesPdf(),
'planning'  => $this->generatePlanningPdf($arguments),
            'salaries'  => "Génération bulletin de salaire nécessite config supplémentaire. Contactez admin.",
            default     => "Type de PDF inconnu : '{$type}'. Types disponibles : absences, employees, planning, salaries.",
        };
    }

    // ─────────────────────────────────────────────
    // PDF : Absences du jour
    // ─────────────────────────────────────────────

    private function generateAbsencesPdf(array $args): string
    {
        $today = Carbon::today();

        $absences = Absence::with('employee')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderBy('start_date')
            ->get();

        $pdf = Pdf::loadView('pdf.absences', [
            'absences'    => $absences,
            'today'       => $today,
            'generatedAt' => now()->format('d/m/Y à H:i'),
        ])->setPaper('A4', 'portrait');

        $filename = 'absences_' . $today->format('Y-m-d') . '_' . uniqid() . '.pdf';
        Storage::disk('public')->put('pdfs/' . $filename, $pdf->output());
        $url = url("pdf/{$filename}");

        return "PDF_DOWNLOAD::{$url}::{$filename}::Absences du "
             . $today->format('d/m/Y') . " (" . $absences->count() . " absence(s))";
    }

    // ─────────────────────────────────────────────
    // PDF : Liste des employés
    // ─────────────────────────────────────────────

    private function generateEmployeesPdf(): string
    {
        $employees = Employee::active()
            ->orderBy('department')
            ->orderBy('last_name')
            ->get();

        $pdf = Pdf::loadView('pdf.employees', [
            'employees'   => $employees,
            'total'       => $employees->count(),
            'generatedAt' => now()->format('d/m/Y à H:i'),
        ])->setPaper('A4', 'landscape');

        $filename = 'employes_' . now()->format('Y-m-d') . '_' . uniqid() . '.pdf';
        Storage::disk('public')->put('pdfs/' . $filename, $pdf->output());
        $url = url("pdf/{$filename}");

        return "PDF_DOWNLOAD::{$url}::{$filename}::Liste des employés actifs ({$employees->count()})";
    }

    // ─────────────────────────────────────────────
    // PDF : Planning employé
    // ─────────────────────────────────────────────

    private function generatePlanningPdf(array $args): string
    {
        $matricule = trim($args['matricule'] ?? '');

        if ($matricule === '') {
            return "Le matricule est requis pour générer un PDF de planning. Exemple : EMP0001.";
        }

        $employee = Employee::where('matricule', $matricule)->first();

        if (!$employee) {
            return "Aucun employé trouvé avec le matricule '{$matricule}'.";
        }

        $date      = Carbon::parse($args['date'] ?? now()->format('Y-m-d'));
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd   = $date->copy()->endOfWeek();

        $plannings = Planning::where('employee_id', $employee->id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->orderBy('date')
            ->get();

        $pdf = Pdf::loadView('pdf.planning', [
            'employee'    => $employee,
            'plannings'   => $plannings,
            'weekStart'   => $weekStart,
            'weekEnd'     => $weekEnd,
            'generatedAt' => now()->format('d/m/Y à H:i'),
        ])->setPaper('A4', 'landscape');

        $filename = 'planning_' . $matricule . '_' . $weekStart->format('Y-m-d') . '_' . uniqid() . '.pdf';
        Storage::disk('public')->put('pdfs/' . $filename, $pdf->output());
        $url = url("pdf/{$filename}");

        $label = "Planning {$employee->first_name} {$employee->last_name} — sem. " . $weekStart->format('d/m');

        return "PDF_DOWNLOAD::{$url}::{$filename}::{$label}";
    }
}
