<?php

namespace App\Http\Controllers;

use App\Ai\Agents\AssistantRH;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssistantRhController extends Controller
{
    public function __invoke(Request $request, AssistantRH $agent): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        try {
            $result = $agent->prompt($request->input('message'));

            if (!$result['success']) {
                return response()->json(['reply' => $result['text'], 'pdfs' => []], 422);
            }

            $text = $result['text'];
            $pdfs = [];

            // ── Extraire les tags PDF_DOWNLOAD:: côté PHP ─────────────────
            preg_match_all(
                '/PDF_DOWNLOAD::(\S+?)::([^:\s]+\.pdf)::([^\n]+)/',
                $text,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $m) {
                // Corriger 127.0.0.1 → vrai domaine Laravel
                $url = preg_replace('/https?:\/\/127\.0\.0\.1(:\d+)?/', url('/'), trim($m[1]));
                $url = preg_replace('/https?:\/\/localhost(:\d+)?/',     url('/'), $url);

                $pdfs[] = [
                    'url'      => $url,
                    'filename' => trim($m[2]),
                    'label'    => trim($m[3]),
                ];
            }

            // ── Nettoyer le texte (supprimer les tags PDF) ────────────────
            $cleanText = preg_replace(
                '/PDF_DOWNLOAD::\S+?::[^:\s]+\.pdf::[^\n]*\n?/',
                '',
                $text
            );
            $cleanText = trim($cleanText);

            return response()->json([
                'reply' => $cleanText,
                'pdfs'  => $pdfs,
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('AssistantRH ConnectionException', ['message' => $e->getMessage()]);
            return response()->json(['reply' => 'Impossible de contacter le serveur IA.', 'pdfs' => []], 503);

        } catch (\Throwable $e) {
            \Log::error('AssistantRH Error', ['message' => $e->getMessage()]);
            return response()->json([
                'reply' => config('app.debug') ? $e->getMessage() : 'Erreur serveur.',
                'pdfs'  => [],
            ], 500);
        }
    }
}
