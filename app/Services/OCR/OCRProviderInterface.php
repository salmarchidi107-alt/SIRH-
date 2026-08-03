<?php

declare(strict_types=1);

namespace App\Services\OCR;

use App\Services\OCR\DTO\OCRResult;
use Illuminate\Http\UploadedFile;


interface OCRProviderInterface
{
    public function getName(): string;

    /**
     * Analyse le justificatif fourni et retourne un résultat OCR normalisé.
     *
     * @throws \App\Exceptions\OCR\OcrException
     */
    public function analyze(UploadedFile $file): OCRResult;
}
