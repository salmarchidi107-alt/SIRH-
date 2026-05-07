<?php

namespace App\Http\Controllers;

use App\Ai\Agents\AssistantRH;
use Illuminate\Http\Request;

class AssistantRhController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        try {
            $agent    = new AssistantRH();
            $response = $agent->prompt($request->input('message'));

            return response()->json([
                'reply' => $response['text'] ?? 'Pas de réponse',
            ], $response['error'] ?? false ? 500 : 200);

        } catch (\Throwable $e) {
            \Log::error('[AssistantRH] Controller error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'reply' => '⚠️ Erreur : ' . (config('app.debug') ? $e->getMessage() : 'contactez l\'administrateur'),
            ], 500);
        }
    }
}