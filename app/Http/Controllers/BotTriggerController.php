<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BotTriggerController extends Controller
{
    public function start(Request $request)
    {
        $request->validate([
            'group_link' => 'required|url',
            'tags' => 'array',
            'countries' => 'array',
        ]);

        $response = Http::post(env('PYTHON_BOT_URL') . '/trigger', [
            'group_link' => $request->group_link,
            'tags' => $request->tags,
            'countries' => $request->countries,
        ]);

        if ($response->successful()) {
            return response()->json(['message' => 'Prospection lancée'], 200);
        }

        return response()->json(['message' => 'Erreur déclenchement bot'], 500);
    }
}

