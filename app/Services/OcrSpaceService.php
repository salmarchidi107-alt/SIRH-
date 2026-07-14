<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\OCR\InvalidApiKeyException;
use App\Exceptions\OCR\OcrProviderUnavailableException;
use App\Exceptions\OCR\OcrTimeoutException;
use App\Exceptions\OCR\UnreadableDocumentException;
use App\Services\OCR\Contracts\OcrProviderInterface;
use App\Services\OCR\DTO\OcrTextResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Intégration avec l'API OCR.space (https://ocr.space/ocrapi).
 *
 * Remplace Google Document AI : plus aucune dépendance à un SDK Google,
 * à des identifiants de service account ou à un projet GCP. Une seule
 * clé API (OCR_SPACE_API_KEY) suffit.
 *
 * Cette classe ne fait QUE parler au réseau et normaliser la réponse.
 * Elle ne contient aucune logique métier RH (pas de regex CIN/CV ici) :
 * cette responsabilité appartient à DocumentParserService, conformément
 * au principe de responsabilité unique (SRP).
 */
final class OcrSpaceService implements OcrProviderInterface
{
    private const ENDPOINT = 'https://api.ocr.space/parse/image';

    /**
     * OCR Engine 2 gère mieux les documents structurés (CIN, passeport,
     * diplômes) que le moteur 1, et supporte le français.
     */
    private const OCR_ENGINE = '2';

    public function __construct(
        private readonly string $apiKey,
        private readonly int $timeoutSeconds = 30,
        private readonly string $language = 'fre',
    ) {
    }

    public function getProviderName(): string
    {
        return 'ocrspace';
    }

    public function extractText(UploadedFile $file): OcrTextResult
    {
        if ($this->apiKey === '' || $this->apiKey === null) {
            Log::error('OcrSpaceService: clé API manquante (OCR_SPACE_API_KEY non définie).');
            throw new InvalidApiKeyException("La clé API OCR.space n'est pas configurée.");
        }

        $startedAt = microtime(true);

        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->retry(2, 500, throw: false)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->asMultipart()
                ->post(self::ENDPOINT, [
                    ['name' => 'apikey', 'contents' => $this->apiKey],
                    ['name' => 'language', 'contents' => $this->language],
                    ['name' => 'isTable', 'contents' => 'true'],
                    ['name' => 'scale', 'contents' => 'true'],
                    ['name' => 'OCREngine', 'contents' => self::OCR_ENGINE],
                    ['name' => 'detectOrientation', 'contents' => 'true'],
                    ['name' => 'filetype', 'contents' => $this->mapFileType($file)],
                ]);
        } catch (ConnectionException $e) {
            Log::error('OcrSpaceService: connexion impossible', [
                'message' => $e->getMessage(),
            ]);
            throw new OcrProviderUnavailableException(
                'Connexion à OCR.space impossible. Vérifiez la connectivité réseau.',
                previous: $e
            );
        }

        $processingTimeMs = round((microtime(true) - $startedAt) * 1000, 2);

        if ($response->status() === 403) {
            Log::error('OcrSpaceService: clé API rejetée (403).');
            throw new InvalidApiKeyException('Clé API OCR.space invalide ou expirée.');
        }

        if ($response->status() === 408 || $response->status() === 0) {
            Log::warning('OcrSpaceService: délai dépassé.', ['status' => $response->status()]);
            throw new OcrTimeoutException("Délai d'attente dépassé lors de l'appel à OCR.space.");
        }

        if ($response->serverError()) {
            Log::error('OcrSpaceService: erreur serveur OCR.space.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new OcrProviderUnavailableException('OCR.space a renvoyé une erreur serveur (5xx).');
        }

        if ($response->clientError()) {
            Log::warning('OcrSpaceService: erreur client.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new OcrProviderUnavailableException(
                "La requête envoyée à OCR.space a été rejetée (HTTP {$response->status()})."
            );
        }

        $body = $response->json() ?? [];

        if (($body['IsErroredOnProcessing'] ?? false) === true) {
            $errorMessage = is_array($body['ErrorMessage'] ?? null)
                ? implode(' | ', $body['ErrorMessage'])
                : (string) ($body['ErrorMessage'] ?? 'Erreur inconnue');

            Log::warning('OcrSpaceService: traitement échoué côté fournisseur.', [
                'error' => $errorMessage,
            ]);
            throw new UnreadableDocumentException(
                "OCR.space n'a pas pu traiter ce document : {$errorMessage}"
            );
        }

        $text = $body['ParsedResults'][0]['ParsedText'] ?? '';

        if (trim($text) === '') {
            Log::warning('OcrSpaceService: aucun texte détecté.', ['file' => $file->getClientOriginalName()]);
            throw new UnreadableDocumentException(
                'Aucun texte lisible n\'a été détecté dans le document. '
                . 'Vérifiez la qualité du scan/photo (netteté, luminosité, cadrage).'
            );
        }

        Log::info('OcrSpaceService: extraction réussie.', [
            'file' => $file->getClientOriginalName(),
            'processing_time_ms' => $processingTimeMs,
            'text_length' => mb_strlen($text),
        ]);

        return new OcrTextResult(
            rawText: $this->cleanText($text),
            provider: $this->getProviderName(),
            processingTimeMs: $processingTimeMs,
            detectedLanguage: $this->language,
            rawResponse: $body,
        );
    }

    /**
     * OCR.space attend un type de fichier explicite plutôt que de le
     * déduire lui-même : on le déduit depuis l'extension réelle du fichier.
     */
    private function mapFileType(UploadedFile $file): string
    {
        return match (strtolower($file->getClientOriginalExtension())) {
            'pdf' => 'PDF',
            'png' => 'PNG',
            'jpg', 'jpeg' => 'JPG',
            default => 'PDF',
        };
    }

    /**
     * Nettoyage générique du texte brut renvoyé par OCR.space :
     * espaces multiples, retours à la ligne parasites, artefacts "\r".
     * Ce nettoyage est délibérément neutre (aucune règle métier RH ici) :
     * il rend le texte plus facile à parser par les Extractors ensuite.
     */
    private function cleanText(string $text): string
    {
        $text = str_replace("\r\n", "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
}
