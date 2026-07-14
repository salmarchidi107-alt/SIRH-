<?php

declare(strict_types=1);

namespace App\Exceptions\OCR;

class DateNotFoundException extends OCRException
{
    public function userMessage(): string
    {
        return "La date de la dépense n'a pas pu être détectée automatiquement. Merci de la renseigner manuellement.";
    }
}
