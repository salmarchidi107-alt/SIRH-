<?php

declare(strict_types=1);

namespace App\Exceptions\OCR;

class OCRTimeoutException extends OCRException
{
    public function userMessage(): string
    {
        return "L'analyse du justificatif a pris trop de temps. Réessayez, ou saisissez les informations manuellement si le problème persiste.";
    }

    public function httpStatusCode(): int
    {
        return 504;
    }
}
