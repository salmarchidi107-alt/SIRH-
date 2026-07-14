<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\OCR\Contracts\FieldExtractorInterface;
use App\Services\OCR\DTO\OcrTextResult;
use App\Services\OCR\Extractors\AddressExtractor;
use App\Services\OCR\Extractors\BirthDateExtractor;
use App\Services\OCR\Extractors\CinExtractor;
use App\Services\OCR\Extractors\DiplomaExtractor;
use App\Services\OCR\Extractors\DocumentNumberExtractor;
use App\Services\OCR\Extractors\EmailExtractor;
use App\Services\OCR\Extractors\ExperienceExtractor;
use App\Services\OCR\Extractors\ExpiryDateExtractor;
use App\Services\OCR\Extractors\FirstNameExtractor;
use App\Services\OCR\Extractors\GenderExtractor;
use App\Services\OCR\Extractors\IssueDateExtractor;
use App\Services\OCR\Extractors\NameExtractor;
use App\Services\OCR\Extractors\NationalityExtractor;
use App\Services\OCR\Extractors\PhoneExtractor;
use App\Services\OCR\Extractors\SkillsExtractor;
use App\Services\OCR\Extractors\UniversityExtractor;
use Illuminate\Support\Facades\Log;

/**
 * Analyse le texte brut renvoyé par l'OCR et en extrait les champs RH
 * structurés (nom, CIN, dates, diplôme, compétences...).
 *
 * Conçu pour rester ouvert à l'extension sans modification (principe
 * Open/Closed) : chaque champ est extrait par une classe dédiée
 * (FieldExtractorInterface). Ajouter un nouveau champ = ajouter une
 * classe dans OCR/Extractors et la déclarer dans extractors() ci-dessous.
 * Aucune autre partie du code n'a besoin d'être touchée.
 */
final class DocumentParserService
{
    /**
     * @return array<int, FieldExtractorInterface>
     */
    private function extractors(): array
    {
        return [
            new NameExtractor(),
            new FirstNameExtractor(),
            new CinExtractor(),
            new BirthDateExtractor(),
            new GenderExtractor(),
            new NationalityExtractor(),
            new AddressExtractor(),
            new PhoneExtractor(),
            new EmailExtractor(),
            new DocumentNumberExtractor(),
            new IssueDateExtractor(),
            new ExpiryDateExtractor(),
            new DiplomaExtractor(),
            new UniversityExtractor(),
            new ExperienceExtractor(),
            new SkillsExtractor(),
        ];
    }

    /**
     * Parse le résultat OCR brut et retourne un tableau associatif
     * contenant uniquement les champs effectivement détectés.
     *
     * @return array{
     *     fields: array<string, string|array<int, string>>,
     *     meta: array{provider: string, text_length: int, fields_found: int}
     * }
     */
    public function parse(OcrTextResult $ocrResult): array
    {
        $text = $ocrResult->rawText;
        $fields = [];

        foreach ($this->extractors() as $extractor) {
            $value = $extractor->extract($text);

            if ($value !== null && $value !== '' && $value !== []) {
                $fields[$extractor->fieldKey()] = $value;
            }
        }

        Log::info('DocumentParserService: parsing terminé.', [
            'provider' => $ocrResult->provider,
            'fields_found' => array_keys($fields),
        ]);

        return [
            'fields' => $fields,
            'meta' => [
                'provider' => $ocrResult->provider,
                'text_length' => mb_strlen($text),
                'fields_found' => count($fields),
            ],
        ];
    }
}
