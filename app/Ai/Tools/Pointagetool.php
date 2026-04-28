<?php

namespace App\Ai\Tools;

use App\Models\Attendance; // Adaptez selon votre modèle réel
use App\Models\Employee;
use Carbon\Carbon;

/**
 * PointageTool
 * Recherche les pointages (entrées/sorties) d'un ou plusieurs employés.
 * Adaptez le nom du modèle (Attendance / Pointage) et les colonnes
 * selon votre migration réelle.
 */
class PointageTool
{
    public function name(): string
    {
        return 'pointage_search';
    }

    public function description(): string
    {
        return 'Recherche les pointages (entrées/sorties) d\'un employé ou de tous les employés. '
             . 'Arguments: matricule (string, optionnel), date (YYYY-MM-DD, optionnel), '
             . 'date_debut (YYYY-MM-DD, optionnel), date_fin (YYYY-MM-DD, optionnel).';
    }

    public function execute(array $arguments): string
    {
        $matricule  = trim($arguments['matricule'] ?? '');
        $dateStr    = trim($arguments['date']       ?? '');
        $dateDebut  = trim($arguments['date_debut'] ?? '');
        $dateFin    = trim($arguments['date_fin']   ?? '');

        // ── Résolution de la plage de dates ──────────────────────────────────
        if ($dateStr !== '') {
            // Journée exacte
            $from = Carbon::parse($dateStr)->startOfDay();
            $to   = Carbon::parse($dateStr)->endOfDay();
        } elseif ($dateDebut !== '' && $dateFin !== '') {
            // Plage explicite
            $from = Carbon::parse($dateDebut)->startOfDay();
            $to   = Carbon::parse($dateFin)->endOfDay();
        } else {
            // Par défaut : aujourd'hui
            $from = Carbon::today()->startOfDay();
            $to   = Carbon::today()->endOfDay();
        }

        // ── Construction de la requête ────────────────────────────────────────
        // ⚠️  Adaptez "Attendance" → votre modèle réel (ex: Pointage)
        // ⚠️  Adaptez les colonnes : check_in / check_out / status …
        $query = Attendance::with('employee')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date', 'desc')
            ->orderBy('check_in');

        // Filtrage par matricule si fourni
        if ($matricule !== '') {
            $employee = Employee::where('matricule', $matricule)->first();

            if (!$employee) {
                return "Aucun employé trouvé avec le matricule '{$matricule}'.";
            }

            $query->where('employee_id', $employee->id);
        }

        $pointages = $query->limit(30)->get();

        if ($pointages->isEmpty()) {
            $periode = $from->isSameDay($to)
                ? $from->format('d/m/Y')
                : $from->format('d/m/Y') . ' → ' . $to->format('d/m/Y');

            $who = $matricule !== '' ? " pour le matricule {$matricule}" : '';
            return "Aucun pointage trouvé{$who} ({$periode}).";
        }

        // ── Formatage tableau Markdown ────────────────────────────────────────
        $periode = $from->isSameDay($to)
            ? $from->format('d/m/Y')
            : $from->format('d/m/Y') . ' → ' . $to->format('d/m/Y');

        $result  = "Pointages ({$periode}) — {$pointages->count()} enregistrement(s)\n\n";
        $result .= "| Matricule | Employé | Date | Entrée | Sortie | Durée | Statut |\n";
        $result .= "|-----------|---------|------|--------|--------|-------|--------|\n";

        foreach ($pointages as $p) {
            $emp  = $p->employee;
            $name = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : 'Inconnu';
            $mat  = $emp->matricule ?? 'N/A';

            // Calcul durée travaillée si check_in et check_out sont renseignés
            $duree = '';
            if (!empty($p->check_in) && !empty($p->check_out)) {
                $in  = Carbon::parse($p->check_in);
                $out = Carbon::parse($p->check_out);
                $minutes = $in->diffInMinutes($out);
                $duree   = sprintf('%dh%02d', intdiv($minutes, 60), $minutes % 60);
            }

            $result .= sprintf(
                "| %s | %s | %s | %s | %s | %s | %s |\n",
                $mat,
                $name,
                Carbon::parse($p->date)->format('d/m/Y'),
                $p->check_in  ?? '-',
                $p->check_out ?? '-',
                $duree !== '' ? $duree : '-',
                $p->status    ?? '-',
            );
        }

        return $result;
    }
}