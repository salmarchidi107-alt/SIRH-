<?php

namespace App\Ai\Tools;

use App\Models\Salary;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SalaryTool
{
    public function name(): string
    {
        return 'salary_query';
    }

    public function description(): string
    {
        return 'Analyse des salaires, masse salariale, CNSS, employés payés + génération PDF.';
    }

    public function execute(array $arguments): string
    {
        $query = strtolower(trim($arguments['query'] ?? ''));
$month = $arguments['month'] ?? null;
        $year  = $arguments['year'] ?? now()->year;
// 🔥 PRIORITÉ AUX QUESTIONS GLOBALES (CNSS, MASSE, PDF...)
if (
    str_contains($query, 'masse') ||
    str_contains($query, 'cnss') ||
    str_contains($query, 'total') ||
    str_contains($query, 'combien') ||
    str_contains($query, 'stat') ||
    str_contains($query, 'pdf')
) {
    return $this->analytics($month, $year, $query, $arguments);
}
        // 🔥 ================= ANALYTICS =================
        if (preg_match('/masse|cnss|total|stat|combien/', $query)) {
            return $this->analytics($month, $year, $query, $arguments);
        }

        // 🔥 ================= LISTE SALAIRES =================
        $salaries = Salary::with('employee')
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($year, fn($q) => $q->where('year', $year))
            ->when($query, function ($q) use ($query) {
                $q->where('matricule', 'like', "%{$query}%")
                  ->orWhereHas('employee', function ($e) use ($query) {
                      $e->whereRaw(
                          'LOWER(CONCAT(first_name, " ", last_name)) LIKE ?',
                          ["%{$query}%"]
                      );
                  });
            })
            ->limit(20)
            ->get();

        if ($salaries->isEmpty()) {
            return "Aucune donnée salariale trouvée.";
        }

        return $this->formatList($salaries);
    }
    

    // ================= ANALYTICS =================
    private function analytics($month, $year, $query, $arguments): string
    {
        $salaries = Salary::query()
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($year, fn($q) => $q->where('year', $year))
            ->get();

        if ($salaries->isEmpty()) {
            return "Aucune donnée salariale.";
        }

        $totalBrut = (float) $salaries->sum('gross_salary');
        $totalNet  = (float) $salaries->sum('net_salary');
        $totalCNSS = (float) $salaries->sum('cnss_deduction');

        $payes = $salaries->where('status', 'paid')->count();
        $cash  = $salaries->where('payment_method', 'cash')->count();
        $bank  = $salaries->where('payment_method', 'bank')->count();

        $response = " Rapport Salarial {$year}\n";
        if ($month) {
            $response .= "Mois: {$month}\n";
        }

        $response .= "\n Masse brute: " . number_format($totalBrut, 0, ',', ' ') . " DH\n";
        $response .= " Masse nette: " . number_format($totalNet, 0, ',', ' ') . " DH\n";
        $response .= " CNSS: " . number_format($totalCNSS, 0, ',', ' ') . " DH\n\n";

        $response .= " Employés payés: {$payes}\n";
        $response .= " Paiement espèces: {$cash}\n";
        $response .= " Paiement virement: {$bank}\n";

        // 🔥 PDF
        if (str_contains($query, 'pdf') || isset($arguments['pdf'])) {
            return $this->generateSalaryPdf($salaries, $month, $year, $response);
        }

        return $response;
    }

    // ================= LIST =================
    private function formatList($salaries): string
    {
        $output = " Bulletins de salaire :\n\n";

        foreach ($salaries as $s) {
            $name = $s->employee
                ? $s->employee->first_name . ' ' . $s->employee->last_name
                : 'N/A';

            $output .= "• {$name} ({$s->matricule})\n";
            $output .= "  Mois: {$s->month}/{$s->year}\n";
            $output .= "  Net: " . number_format($s->net_salary, 0, ',', ' ') . " DH\n\n";
        }

        return $output;
    }

    // ================= PDF =================
    private function generateSalaryPdf($salaries, $month, $year, $summary): string
    {
        $pdf = Pdf::loadView('pdf.salaries', [
            'salaries' => $salaries,
            'month'    => $month,
            'year'     => $year,
            'summary'  => $summary
        ])->setPaper('A4', 'portrait');

        $filename = 'salaires_' . ($month ? $month . '_' : '') . $year . '_' . uniqid() . '.pdf';

        Storage::disk('public')->put('pdfs/' . $filename, $pdf->output());

        $url = asset('storage/pdfs/' . $filename);

        return $summary . "\n\nPDF généré\n\nPDF_DOWNLOAD::{$url}::{$filename}::Rapport salaires";
    }
}