<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PointageExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    // PointageExport reçoit des données déjà filtrées par le controller
    // (PointageController filtre par tenant via le global scope + la date).
    // Le tenantId n'est pas nécessaire ici — les données sont pré-isolées.

    public function __construct(
        private \Illuminate\Support\Collection $data,
        private Carbon $date
    ) {}

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->data->map(fn($row) => [
            $row['nom'],
            $row['department'],
            $row['shift_type'] === 'garde' ? 'Garde' : 'Shift normal',
            $row['heure_entree'] ? substr($row['heure_entree'], 0, 5) : '—',
            $row['heure_sortie'] ? substr($row['heure_sortie'], 0, 5) : '—',
            $row['total_heures'] ?? '—',
            ucfirst(str_replace('_', ' ', $row['statut'] ?? 'pas_de_badge')),
            $row['valide'] ? 'Oui' : 'Non',
        ]);
    }

    public function headings(): array
    {
        return [
            'Employé', 'Département', 'Type de shift',
            'Heure entrée', 'Heure sortie', 'Total travaillé', 'Statut', 'Validé',
        ];
    }

    public function title(): string
    {
        return 'Pointage ' . $this->date->format('d-m-Y');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, 'B' => 20, 'C' => 16,
            'D' => 14, 'E' => 14, 'F' => 16, 'G' => 16, 'H' => 10,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0D9488']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('C1:H' . ($this->data->count() + 1))
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
