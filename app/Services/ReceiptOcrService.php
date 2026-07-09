<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Str;
use thiagoalessio\TesseractOCR\TesseractOCR;

class ReceiptOcrService
{
    /**
     * Extrait le texte brut d'un fichier reçu (image ou PDF) via Tesseract,
     * puis tente d'en déduire titre / montant / date / catégorie / employé.
     *
     * @param string $absolutePath Chemin absolu vers le fichier.
     * @param string|null $extension Extension du fichier (jpg, png, pdf...).
     * @param string|null $tenantId Tenant courant, pour restreindre la recherche d'employé.
     * @return array{title:?string, amount:?string, date:?string, category:?string, employee_id:?int, raw_text:string}
     */
    public function scan(string $absolutePath, ?string $extension = null, ?string $tenantId = null): array
    {
        $pathForOcr = $absolutePath;
        $tempFile = null;

        if ($extension && strtolower($extension) === 'pdf') {
            $tempFile = $this->convertPdfFirstPageToImage($absolutePath);
            $pathForOcr = $tempFile;
        }

        try {
            $ocr = new TesseractOCR($pathForOcr);
            $ocr->executable(config('services.tesseract.binary'));
            $ocr->lang('fra', 'eng');
            $text = trim($ocr->run());
        } finally {
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }

        return [
            'title'       => $this->guessTitle($text),
            'amount'      => $this->guessAmount($text),
            'date'        => $this->guessDate($text),
            'category'    => $this->guessCategory($text),
            'employee_id' => $tenantId ? $this->guessEmployeeId($text, $tenantId) : null,
            'raw_text'    => $text,
        ];
    }

    /**
     * Convertit la première page d'un PDF en image PNG haute résolution,
     * pour permettre à Tesseract de la lire (Tesseract ne lit pas les PDF
     * directement).
     */
    private function convertPdfFirstPageToImage(string $pdfPath): string
    {
        $imagick = new \Imagick();
        $imagick->setResolution(300, 300);
        $imagick->readImage($pdfPath . '[0]'); // première page uniquement
        $imagick->setImageFormat('png');
        $imagick->setImageBackgroundColor('white');
        $imagick = $imagick->flattenImages();

        $tempPath = tempnam(sys_get_temp_dir(), 'ocr_') . '.png';
        $imagick->writeImage($tempPath);
        $imagick->clear();

        return $tempPath;
    }

    private function guessTitle(string $text): ?string
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        foreach ($lines as $line) {
            if (mb_strlen($line) >= 4 && !preg_match('/^[\d\s.,€$MADmad]+$/u', $line)) {
                return mb_substr($line, 0, 255);
            }
        }
        return null;
    }

    private function guessAmount(string $text): ?string
    {
        if (preg_match('/(?:total|montant|net\s*à\s*payer)\D{0,10}(\d{1,6}[.,]\d{2})/iu', $text, $m)) {
            return str_replace(',', '.', $m[1]);
        }

        if (preg_match_all('/\d{1,6}[.,]\d{2}/', $text, $matches) && !empty($matches[0])) {
            $amounts = array_map(fn ($v) => (float) str_replace(',', '.', $v), $matches[0]);
            return number_format(max($amounts), 2, '.', '');
        }

        return null;
    }

    /**
     * Détection de date élargie : ISO, dd/mm/yyyy, dd-mm-yyyy, dd.mm.yyyy,
     * années sur 2 chiffres, et dates en toutes lettres en français
     * (ex: "3 juillet 2026").
     */
    private function guessDate(string $text): ?string
    {
        // ISO : 2026-07-03 ou 2026/07/03
        if (preg_match('/(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})/', $text, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        // dd/mm/yyyy, dd-mm-yyyy, dd.mm.yyyy
        if (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/', $text, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // dd/mm/yy (année sur 2 chiffres)
        if (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2})(?!\d)/', $text, $m)) {
            $year = (int) $m[3] >= 70 ? 1900 + (int) $m[3] : 2000 + (int) $m[3];
            return sprintf('%04d-%02d-%02d', $year, $m[2], $m[1]);
        }

        // Date en toutes lettres en français : "3 juillet 2026"
        $months = [
            'janvier' => 1, 'février' => 2, 'fevrier' => 2, 'mars' => 3, 'avril' => 4,
            'mai' => 5, 'juin' => 6, 'juillet' => 7, 'août' => 8, 'aout' => 8,
            'septembre' => 9, 'octobre' => 10, 'novembre' => 11, 'décembre' => 12, 'decembre' => 12,
        ];
        $pattern = '/(\d{1,2})\s+(' . implode('|', array_keys($months)) . ')\s+(\d{4})/iu';
        if (preg_match($pattern, mb_strtolower($text), $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $months[$m[2]], $m[1]);
        }

        return null;
    }

    private function guessCategory(string $text): ?string
    {
        $text = mb_strtolower($text);

        $map = [
            'deplacement' => ['taxi', 'uber', 'essence', 'péage', 'parking', 'train', 'avion', 'billet'],
            'repas'       => ['restaurant', 'café', 'brasserie', 'menu', 'déjeuner', 'diner'],
            'hebergement' => ['hôtel', 'hotel', 'nuitée', 'chambre'],
            'medical'     => ['pharmacie', 'médecin', 'clinique', 'ordonnance'],
            'fournitures' => ['fourniture', 'papeterie', 'bureau'],
        ];

        foreach ($map as $category => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    return $category;
                }
            }
        }

        return null;
    }

    /**
     * Recherche un employé du tenant dont le nom complet apparaît dans le
     * texte OCR (comparaison insensible à la casse et aux accents).
     */
    private function guessEmployeeId(string $text, string $tenantId): ?int
    {
        $normalizedText = Str::ascii(mb_strtolower($text));

        $employees = Employee::where('tenant_id', $tenantId)->get(['id', 'first_name', 'last_name']);

        foreach ($employees as $employee) {
            $fullName = Str::ascii(mb_strtolower("{$employee->first_name} {$employee->last_name}"));
            $reversed = Str::ascii(mb_strtolower("{$employee->last_name} {$employee->first_name}"));

            if (str_contains($normalizedText, $fullName) || str_contains($normalizedText, $reversed)) {
                return $employee->id;
            }
        }

        return null;
    }
}
