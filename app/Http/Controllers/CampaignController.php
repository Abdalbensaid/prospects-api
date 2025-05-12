<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Notifications\CampaignMessageNotification;

class CampaignController extends Controller
{
    // 📄 Affiche toutes les campagnes
    public function index()
    {
        return Campaign::latest()->get();
    }

    // 💾 Crée une nouvelle campagne
    public function store(Request $request)
    {
        // ✅ Validation
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'tag'     => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 📦 Enregistrement de la campagne
        $campaign = Campaign::create([
            'message' => $request->message,
            'tag'     => $request->tag,
        ]);
            foreach ($prospects as $prospect) {
                // 🧪 Envoi d’une notification ou log de test
                if ($prospect->email) {
                    $prospect->notify(new CampaignMessageNotification($campaign->message));
                } else {
                    Log::info("📬 Message prêt pour {$prospect->username} (pas d’email)");
                }
            }
        Log::info("🎯 Campagne créée", ['id' => $campaign->id, 'tag' => $campaign->tag]);

        // 🔎 Recherche des prospects concernés
        $prospects = Prospect::where('tags', 'LIKE', "%{$request->tag}%")->get();

        Log::info("👥 {$prospects->count()} prospects ciblés");

        // 📨 (Optionnel) Lancer une action par prospect ici ou via un Job
        foreach ($prospects as $prospect) {
            Log::info("📬 Message prêt pour {$prospect->username}");
            // Exemple : envoyer à une queue ou lister à envoyer par le bot
        }

        return response()->json([
            'message'    => 'Campagne enregistrée',
            'campaign'   => $campaign,
            'recipients' => $prospects->count(),
        ], 201);
    }

    // 📍 Affiche une campagne
    public function show(Campaign $campaign)
    {
        return $campaign;
    }

    // ✏️ Met à jour une campagne
    public function update(Request $request, Campaign $campaign)
    {
        $campaign->update($request->only(['message', 'tag']));
        return response()->json(['message' => 'Campagne mise à jour', 'campaign' => $campaign]);
    }

    // ❌ Supprime une campagne
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return response()->json(['message' => 'Campagne supprimée'], 204);
    }
}
