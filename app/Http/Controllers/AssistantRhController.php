<?php

namespace App\Http\Controllers;

use App\Ai\Agents\AssistantRH;
use Illuminate\Http\Request;

class AssistantRhController extends Controller
{
   public function __invoke(Request $request, AssistantRH $agent)
{
    $request->validate([
        'message' => 'required|string|max:1000',
    ]);

    try {
        $response = $agent->prompt($request->input('message'));

        return response()->json([
            'reply'  => $response->text ?? 'Pas de réponse',
            'thread' => $response->conversationId ?? null,
        ]);

    } catch (\Throwable $e) {
        \Log::error('AssistantRH Error', ['message' => $e->getMessage()]);

        return response()->json([
            'reply' => 'Erreur serveur côté assistant',
            'error' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}

}