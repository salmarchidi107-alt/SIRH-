<?php

declare(strict_types=1);

namespace App\Exceptions\OCR;

class OCRProviderUnavailableException extends OCRException
{
    public function userMessage(): string
    {
        return "Le service de reconnaissance automatique est momentanément indisponible. Vous pouvez réessayer dans quelques instants ou saisir les informations manuellement.";
    }

    public function httpStatusCode(): int
    {
        return 503;
    }
}
