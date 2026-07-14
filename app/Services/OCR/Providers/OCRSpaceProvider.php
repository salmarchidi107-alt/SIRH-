<?php

declare(strict_types=1);

namespace App\Services\OCR\Providers;

use App\Exceptions\OCR\InvalidApiKeyException;
use App\Exceptions\OCR\OcrProviderUnavailableException;
use App\Exceptions\OCR\OcrTimeoutException;
use App\Exceptions\OCR\UnreadableDocumentException;
use App\Services\OCR\DTO\OCRResult;
use App\Services\OCR\OCRProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fournisseur OCR basé sur OCR.Space (https://ocr.space/ocrapi).
 *
 * Contrairement à Azure Document Intelligence (ou Google Document AI),
 * OCR.Space ne renvoie que du texte brut : il n'existe pas de "champ
 * catégorie" ou "champ montant" structuré côté fournisseur. Tout le
 * parsing métier (titre, montant, date, catégorie, devise) est donc fait
 * ici via des expressions régulières et une détection par mots-clés,
 * afin de conserver la même richesse de résultat que l'ancien provider
 * Azure pour ne rien casser côté formulaire.
 */
final class OCRSpaceProvider implements OCRProviderInterface
{
    private const ENDPOINT = 'https://api.ocr.space/parse/image';

    /**
     * Mots-clés → valeur de catégorie attendue par l'application.
     * IMPORTANT : les clés de droite doivent correspondre exactement aux
     * valeurs utilisées dans le <select name="category"> de vos vues
     * (donc aux constantes/enum de votre modèle Expense). Ajustez cette
     * liste si vos valeurs de catégorie diffèrent.
     *
     * @var array<string, array<int, string>>
     */
    private const CATEGORY_KEYWORDS = [
        'transport' => ['taxi', 'uber', 'bolt', 'essence', 'carburant', 'péage', 'parking', 'billet', 'train', 'avion', 'location de voiture'],
        'repas' => ['restaurant', 'café', 'déjeuner', 'diner', 'dîner', 'traiteur', 'brasserie', 'pizzeria'],
        'hebergement' => ['hôtel', 'hotel', 'auberge', 'riad', 'nuitée', 'booking'],
        'fournitures' => ['papeterie', 'fourniture', 'imprimante', 'cartouche', 'bureautique'],
        'formation' => ['formation', 'séminaire', 'conférence', 'inscription', 'certification'],
        'communication' => ['télécom', 'forfait', 'internet', 'recharge', 'orange', 'maroc telecom', 'inwi'],
    ];

    public function __construct(
        private readonly string $apiKey,
        private readonly int $timeoutSeconds = 30,
        private readonly string $language = 'fre',
    ) {
    }

    public function getName(): string
    {
        return 'ocrspace';
    }

    public function analyze(UploadedFile $file): OCRResult
    {
        if ($this->apiKey === '') {
            Log::error('OCRSpaceProvider: clé API manquante (OCR_SPACE_API_KEY).');
            throw new InvalidApiKeyException("La clé API OCR.space n'est pas configurée.");
        }

        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->retry(2, 500, throw: false)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->asMultipart()
                ->post(self::ENDPOINT, [
                    ['name' => 'apikey', 'contents' => $this->apiKey],
                    ['name' => 'language', 'contents' => $this->language],
                    ['name' => 'isTable', 'contents' => 'true'],
                    ['name' => 'scale', 'contents' => 'true'],
                    ['name' => 'OCREngine', 'contents' => '2'],
                ]);
        } catch (ConnectionException $e) {
            Log::error('OCRSpaceProvider: connexion impossible', ['message' => $e->getMessage()]);
            throw new OCRProviderUnavailableException('Connexion à OCR.Space impossible.', previous: $e);
        }

        if ($response->status() === 403) {
            throw new InvalidApiKeyException('Clé API OCR.Space invalide.');
        }

        if ($response->status() === 408 || $response->status() === 0) {
            throw new OCRTimeoutException("Délai d'attente dépassé pour OCR.Space.");
        }

        if ($response->serverError()) {
            throw new OCRProviderUnavailableException('OCR.Space a renvoyé une erreur serveur.');
        }

        $body = $response->json() ?? [];

        if (($body['IsErroredOnProcessing'] ?? false) === true) {
            Log::warning('OCR.Space: erreur de traitement', ['body' => $body]);
            throw new UnreadableDocumentException("OCR.Space n'a pas pu traiter ce document.");
        }

        $text = $body['ParsedResults'][0]['ParsedText'] ?? '';

        if (trim($text) === '') {
            throw new UnreadableDocumentException('Aucun texte détecté dans le document.');
        }

        return $this->extractFromRawText($text, $body);
    }

    /**
     * Parsing "maison" du texte brut renvoyé par OCR.Space. Retourne le même
     * niveau d'information que l'ancien provider Azure (titre, catégorie,
     * date, montant, devise, description) pour que le reste de l'application
     * (ExpenseOCRController, formulaires Blade) n'ait rien à changer.
     */
    private function extractFromRawText(string $text, array $rawResponse): OCRResult
    {
        $merchantName = $this->extractMerchantName($text);
        $amount = $this->extractAmount($text);
        $date = $this->extractDate($text);
        $currency = $this->extractCurrency($text);
        $category = $this->guessCategory($text);

        return new OCRResult(
            title: $merchantName,
            category: $category,
            expenseDate: $date,
            amount: $amount,
            amountExcludingTax: null,
            vatAmount: null,
            currency: $currency,
            description: $merchantName ? "Dépense chez {$merchantName}" : null,
            merchantName: $merchantName,
            // OCR.Space ne fournit pas de score de confiance par champ (contrairement
            // à Azure) : 0.6 reflète un parsing regex "raisonnable mais non garanti",
            // à afficher tel quel côté UI si vous exploitez ce champ.
            confidence: 0.6,
            rawResponse: $rawResponse,
        );
    }

    private function extractAmount(string $text): ?float
    {
        // "TOTAL 24,90" / "TOTAL: 24.90 €" / "Montant total 150,00 MAD"
        if (preg_match('/(?:total|montant)\s*(?:ttc)?\s*[:\-]?\s*(?:€|dh|mad)?\s*(\d+[.,]\d{2})/i', $text, $matches) === 1) {
            return (float) str_replace(',', '.', $matches[1]);
        }

        // Repli : plus grand nombre décimal du document (souvent le total sur un ticket)
        if (preg_match_all('/\d+[.,]\d{2}/', $text, $all) && $all[0] !== []) {
            $values = array_map(static fn (string $v) => (float) str_replace(',', '.', $v), $all[0]);

            return max($values);
        }

        return null;
    }

    private function extractDate(string $text): ?string
    {
        // Formats FR courants : 12/07/2026, 12-07-2026, 12.07.2026
        if (preg_match('/(\d{2})[.\/\-](\d{2})[.\/\-](\d{4})/', $text, $matches) === 1) {
            return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        }

        return null;
    }

    private function extractCurrency(string $text): ?string
    {
        return match (true) {
            (bool) preg_match('/\bMAD\b|DH\b|Dirham/i', $text) => 'MAD',
            (bool) preg_match('/\bMRU\b|Ouguiya/i', $text) => 'MRU',
            (bool) preg_match('/€|EUR\b/i', $text) => 'EUR',
            default => null,
        };
    }

    private function guessCategory(string $text): ?string
    {
        $normalized = mb_strtolower($text);

        foreach (self::CATEGORY_KEYWORDS as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($normalized, $keyword)) {
                    return $category;
                }
            }
        }

        return null;
    }

    private function extractMerchantName(string $text): ?string
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        $firstLine = $lines[array_key_first($lines)] ?? null;

        // Une ligne trop courte (1-2 caractères, souvent un artefact OCR) n'est pas fiable.
        return ($firstLine !== null && mb_strlen($firstLine) >= 3) ? $firstLine : null;
    }
}
