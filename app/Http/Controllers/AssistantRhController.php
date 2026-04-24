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
            // prompt() retourne maintenant ['success' => bool, 'text' => string]
            $result = $agent->prompt($request->input('message'));

            if (!$result['success']) {
                return response()->json([
                    'reply' => $result['text'],
                ], 422);
            }

            return response()->json([
                'reply' => $result['text'],
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('AssistantRH ConnectionException', ['message' => $e->getMessage()]);

            return response()->json([
                'reply' => 'Impossible de contacter le serveur IA. Vérifiez votre connexion.',
            ], 503);

        } catch (\Throwable $e) {
            \Log::error('AssistantRH Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'reply' => config('app.debug') ? $e->getMessage() : 'Erreur serveur. Réessayez dans quelques instants.',
            ], 500);
        }
    }
}
