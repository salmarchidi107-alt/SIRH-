<?php

declare(strict_types=1);

namespace App\Exceptions\OCR;

class UnsupportedFileFormatException extends OCRException
{
    public function userMessage(): string
    {
        return "Ce format de fichier n'est pas pris en charge. Formats acceptés : JPG, PNG, PDF.";
    }
}
