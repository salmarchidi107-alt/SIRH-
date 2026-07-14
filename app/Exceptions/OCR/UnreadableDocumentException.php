<?php

declare(strict_types=1);

namespace App\Exceptions\OCR;

class UnreadableDocumentException extends OCRException
{
    public function userMessage(): string
    {
        return "Le document n'a pas pu être lu. Vérifiez que l'image est nette, bien cadrée et suffisamment éclairée, puis réessayez.";
    }
}
