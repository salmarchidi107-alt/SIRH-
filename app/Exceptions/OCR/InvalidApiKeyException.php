<?php

declare(strict_types=1);

namespace App\Exceptions\OCR;

class InvalidApiKeyException extends OCRException
{
    public function userMessage(): string
    {
        // Volontairement générique : ne jamais exposer de détail technique/clé à l'utilisateur final.
        return "Le service de reconnaissance automatique est mal configuré. L'équipe technique a été notifiée.";
    }

    public function httpStatusCode(): int
    {
        return 500;
    }
}
