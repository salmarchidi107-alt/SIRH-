<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\OCR\AmountNotFoundException;
use App\Exceptions\OCR\DateNotFoundException;
use App\Services\OCR\DTO\OCRResult;
use App\Services\OCR\OCRManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service applicatif orchestrant l'analyse OCR d'un justificatif de dépense.
 *
 * Responsabilité unique : faire le lien entre l'upload utilisateur et le résultat
 * exploitable par le formulaire "Note de frais". Ne contient aucune logique HTTP
 * (c'est le rôle du contrôleur) ni de logique bas niveau d'appel API (rôle des providers).
 */
final class ExpenseOCRService
{
    public function __construct(
        private readonly OCRManager $ocrManager,
    ) {
    }

    /**
     * Analyse un justificatif et retourne un résultat OCR normalisé.
     *
     * Ne bloque PAS si un seul champ critique manque (montant OU date) : dans ce cas,
     * le résultat est retourné tel quel, avec `OCRResult::missingCriticalFields()`
     * renseigné, pour que le front-end pré-remplisse ce qui a été trouvé et affiche
     * un avertissement non bloquant sur le reste (cf. ExpenseOCRController::scan()).
     * Ne lève une exception que si RIEN d'exploitable n'a été détecté du tout.
     *
     * @throws \App\Exceptions\OCR\OCRException
     */
    public function scan(UploadedFile $file): OCRResult
    {
        $provider = $this->ocrManager->driver();

        Log::info('OCR: début analyse justificatif', [
            'provider' => $provider->getName(),
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);

        $result = $provider->analyze($file);

        if (! $result->isUsable()) {
            Log::warning('OCR: résultat totalement inexploitable', ['provider' => $provider->getName()]);

            // On priorise le message le plus pertinent : montant d'abord, car c'est le champ critique.
            if ($result->amount === null) {
                throw new AmountNotFoundException('Montant introuvable dans le document.');
            }

            throw new DateNotFoundException('Date introuvable dans le document.');
        }

        $missingFields = $result->missingCriticalFields();

        if ($missingFields !== []) {
            Log::info('OCR: analyse partielle, champs manquants', [
                'provider' => $provider->getName(),
                'missing' => $missingFields,
            ]);
        } else {
            Log::info('OCR: analyse réussie', [
                'provider' => $provider->getName(),
                'confidence' => $result->confidence,
            ]);
        }

        return $result;
    }

    /**
     * Stocke le justificatif de façon sécurisée et retourne son chemin relatif.
     * Le fichier est renommé (UUID) pour éviter tout écrasement ou path traversal.
     *
     * IMPORTANT : disque "public" + dossier "receipts", pour rester cohérent avec
     * ExpenseController::store()/update() qui stockent déjà les justificatifs ainsi,
     * et avec Storage::url($expense->receipt_path) utilisé dans les vues (qui suppose
     * le disque public). Ne changez pas ce disque sans adapter tout le contrôleur.
     */
    public function storeReceipt(UploadedFile $file): string
    {
        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();

        return $file->storeAs('receipts', $filename, 'public');
    }
}
