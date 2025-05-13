<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // 👈 on importe Log

class BotTriggerController extends Controller
{
    public function start(Request $request)
    {
        // 🔍 Étape 1 : Valider les données reçues
        $validated = $request->validate([
            'group_link' => 'required|url',
            'tags' => 'array',
            'countries' => 'array',
        ]);

        // 📋 Log des données envoyées
        Log::info('📤 Tentative de déclenchement du bot', [
            'group_link' => $validated['group_link'],
            'tags' => $validated['tags'],
            'countries' => $validated['countries'],
        ]);

        try {
            // 📡 Envoi vers le bot Python via Flask
            $response = Http::post(env('PYTHON_BOT_URL') . '/start-prospection', [
                'group_link' => $validated['group_link'],
                'keywords' => $validated['tags'], // côté bot c'est 'keywords'
                'countries' => $validated['countries'],
            ]);

            // ✅ Log de la réponse
            Log::info('📬 Réponse du bot', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                return response()->json(['message' => '✅ Prospection lancée avec succès'], 200);
            }

            // ❌ Log si le bot a répondu avec une erreur
            Log::error('🚨 Échec : Réponse du bot invalide', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

        } catch (\Exception $e) {
            // ❌ Log si une exception s'est produite
            Log::error('💥 Exception lors de la tentative de prospection', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json(['message' => '❌ Échec du déclenchement du bot'], 500);
    }
}
