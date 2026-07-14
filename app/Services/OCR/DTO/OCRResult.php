<?php

declare(strict_types=1);

namespace App\Services\OCR\DTO;

/**
 * DTO représentant le résultat normalisé d'une extraction OCR.
 * Contrat de sortie commun à tous les fournisseurs OCR.
 */
final readonly class OCRResult
{
    public function __construct(
        public ?string $title = null,
        public ?string $category = null,
        public ?string $expenseDate = null,   // format Y-m-d, null si non détecté
        public ?float $amount = null,
        public ?float $amountExcludingTax = null,
        public ?float $vatAmount = null,
        public ?string $currency = null,      // ISO 4217 : EUR, USD, MAD...
        public ?string $description = null,
        public ?string $merchantName = null,
        public float $confidence = 0.0,
        public array $rawResponse = [],
    ) {
    }

    public function isUsable(): bool
    {
        return $this->amount !== null || $this->expenseDate !== null;
    }

    /**
     * Champs critiques non détectés, pour affichage d'un avertissement
     * côté formulaire ("champs non détectés : montant, date") sans pour
     * autant bloquer l'utilisateur si au moins un champ exploitable existe.
     *
     * @return array<int, string>
     */
    public function warnings(): array
    {
        $warnings = [];

        if ($this->amount === null) {
            $warnings[] = 'amount_not_detected';
        }

        if ($this->expenseDate === null) {
            $warnings[] = 'date_not_detected';
        }

        return $warnings;
    }

    /**
     * Alias de warnings(), utilisé par ExpenseOCRService pour savoir si
     * l'analyse est partielle (certains champs critiques non détectés)
     * avant de décider de bloquer ou non l'utilisateur.
     *
     * @return array<int, string>
     */
    public function missingCriticalFields(): array
    {
        return $this->warnings();
    }

    /**
     * Convertit le DTO en tableau prêt à être renvoyé en JSON au front-end.
     *
     * NOTE : la clé 'date' est celle lue par resources/views/expenses/create.blade.php
     * et edit.blade.php (`data.date`). 'expense_date' est conservée en parallèle
     * pour ne pas casser un éventuel autre appelant qui lirait encore cette clé.
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'category' => $this->category,
            'date' => $this->expenseDate,
            'expense_date' => $this->expenseDate,
            'amount' => $this->amount,
            'amount_excluding_tax' => $this->amountExcludingTax,
            'vat_amount' => $this->vatAmount,
            'currency' => $this->currency,
            'description' => $this->description,
            'merchant_name' => $this->merchantName,
            'confidence' => $this->confidence,
        ];
    }
}
