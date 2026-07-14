<?php

declare(strict_types=1);

namespace App\Exceptions\OCR;

class AmountNotFoundException extends OCRException
{
    public function userMessage(): string
    {
        return "Le montant n'a pas pu être détecté automatiquement. Merci de le renseigner manuellement.";
    }
}
