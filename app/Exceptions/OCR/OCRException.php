<?php

declare(strict_types=1);

namespace App\Exceptions\OCR;

use Exception;

/**
 * Exception de base pour toutes les erreurs liées à l'OCR.
 * Permet d'attraper "toutes les erreurs OCR" avec un seul catch(OCRException)
 * tout en gardant des exceptions spécifiques pour un traitement fin.
 */
abstract class OCRException extends Exception
{
    /**
     * Message destiné à être affiché tel quel à l'utilisateur final (FR, sans jargon technique).
     */
    abstract public function userMessage(): string;

    /**
     * Code HTTP à renvoyer dans la réponse JSON.
     */
    public function httpStatusCode(): int
    {
        return 422;
    }
}
