<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // 👈 on importe Log

class BotTriggerController extends Controller
{

    public function start(Request $request)
    {
        $request->validate([
            'group_link' => 'required|url',
            'tags' => 'array',
            'countries' => 'array',
        ]);

        Log::channel('prospection_bot')->info("📤 Tentative de démarrage du bot", $request->all());

        $response = Http::post(env('PYTHON_BOT_URL') . '/start-prospection', [
            'group_link' => $request->group_link,
            'keywords' => $request->tags,
            'countries' => $request->countries,
        ]);

        Log::channel('prospection_bot')->info("📬 Réponse du bot", [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        if ($response->successful()) {
            return response()->json(['message' => '✅ Prospection lancée avec succès'], 200);
        }

        return response()->json(['message' => '❌ Échec du déclenchement du bot'], 500);
    }

}
