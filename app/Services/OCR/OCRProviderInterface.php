<?php

declare(strict_types=1);

namespace App\Services\OCR;

use App\Services\OCR\DTO\OCRResult;
use Illuminate\Http\UploadedFile;

/**
 * Contrat commun à tout fournisseur OCR utilisé pour l'analyse des
 * justificatifs de dépense (module "Notes de frais").
 *
 * Toute nouvelle implémentation (Mindee, AWS Textract...) doit respecter
 * cette interface pour être utilisable par OCRManager sans modification
 * du reste de l'application.
 */
interface OCRProviderInterface
{
    /**
     * Nom court et unique du fournisseur (ex: "ocrspace"), utilisé comme
     * clé de résolution dans OCRManager et pour le logging.
     */
    public function getName(): string;

    /**
     * Analyse le justificatif fourni et retourne un résultat OCR normalisé.
     *
     * @throws \App\Exceptions\OCR\OcrException
     */
    public function analyze(UploadedFile $file): OCRResult;
}
