<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\OCR\InvalidApiKeyException;
use App\Exceptions\OCR\OcrException;
use App\Exceptions\OCR\OcrProviderUnavailableException;
use App\Exceptions\OCR\OcrTimeoutException;
use App\Exceptions\OCR\UnreadableDocumentException;
use App\Http\Requests\ConfirmOcrDataRequest;
use App\Http\Requests\StoreOcrDocumentRequest;
use App\Services\DocumentParserService;
use App\Services\OCR\Contracts\OcrProviderInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/**
 * Contrôleur du module OCR RH.
 *
 * Ne dépend que de l'interface OcrProviderInterface (jamais d'OcrSpaceService
 * directement) : si demain le fournisseur change, ce contrôleur n'a
 * strictement aucune ligne à modifier.
 */
final class OcrController extends Controller
{
    public function __construct(
        private readonly OcrProviderInterface $ocrProvider,
        private readonly DocumentParserService $parser,
    ) {
    }

    /**
     * Affiche le formulaire d'upload.
     */
    public function create(): View
    {
        return view('ocr.upload');
    }

    /**
     * Reçoit le document, l'envoie à l'OCR, parse le texte, et affiche
     * un formulaire pré-rempli que l'utilisateur peut corriger avant
     * enregistrement définitif.
     */
    public function store(StoreOcrDocumentRequest $request): View|RedirectResponse
    {
        $file = $request->file('document');
        $documentType = $request->input('document_type', 'autre');

        try {
            $ocrResult = $this->ocrProvider->extractText($file);
            $parsed = $this->parser->parse($ocrResult);
        } catch (InvalidApiKeyException $e) {
            Log::critical('OcrController: clé API OCR invalide.', ['message' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('ocr_error', "Le service OCR n'est pas correctement configuré. Merci de contacter l'administrateur.");
        } catch (OcrTimeoutException $e) {
            Log::warning('OcrController: timeout OCR.', ['message' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('ocr_error', "Le service OCR met trop de temps à répondre. Merci de réessayer dans quelques instants.");
        } catch (OcrProviderUnavailableException $e) {
            Log::error('OcrController: fournisseur OCR indisponible.', ['message' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('ocr_error', 'Le service OCR est momentanément indisponible. Merci de réessayer plus tard.');
        } catch (UnreadableDocumentException $e) {
            Log::info('OcrController: document illisible.', ['message' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('ocr_error', $e->getMessage());
        } catch (OcrException $e) {
            // Filet de sécurité générique pour toute autre exception du module OCR.
            Log::error('OcrController: erreur OCR non spécifique.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('ocr_error', "Une erreur est survenue lors de l'analyse du document.");
        }

        return view('ocr.result', [
            'documentType' => $documentType,
            'fields' => $parsed['fields'],
            'meta' => $parsed['meta'],
            'rawText' => $ocrResult->rawText,
        ]);
    }

    /**
     * Enregistre définitivement les informations validées/corrigées
     * par l'utilisateur. À brancher sur le modèle métier concerné
     * (Employee, Candidate, Document...) selon le contexte SIRH.
     */
    public function confirm(ConfirmOcrDataRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Log::info('OcrController: données RH confirmées par un utilisateur.', [
            'document_type' => $data['document_type'],
            'fields' => array_keys(array_filter($data)),
        ]);

        // Exemple d'intégration, à adapter au module cible du SIRH
        // (Employee, Candidate, DossierRh...) :
        //
        // Employee::query()->updateOrCreate(
        //     ['cin' => $data['cin'] ?? null],
        //     $data
        // );

        return redirect()
            ->route('ocr.create')
            ->with('status', 'Les informations ont été enregistrées avec succès.');
    }
}
