<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ScrapingController extends Controller
{
    public function start(Request $request)
    {
        // 🔐 Auth via Sanctum déjà en place
        $payload = $request->only(['group_link', 'keywords', 'countries']);

        // 👇 Ici, ton bot doit écouter ce webhook via HTTP, ou polling
        $response = Http::post(env('BOT_SCRAPE_URL', 'http://127.0.0.1:5000/scrape'), $payload);

        return $response->successful()
            ? response()->json(['message' => 'Scraping lancé !'], 202)
            : response()->json(['message' => 'Erreur côté bot'], 500);
    }
}
