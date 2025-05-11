<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index() {
        return Campaign::all();
    }

    public function store(Request $request) {
        return Campaign::create($request->all());
    }

    public function show(Campaign $campaign) {
        return $campaign;
    }

    public function update(Request $request, Campaign $campaign) {
        $campaign->update($request->all());
        return $campaign;
    }

    public function destroy(Campaign $campaign) {
        $campaign->delete();
        return response()->noContent();
    }
}

