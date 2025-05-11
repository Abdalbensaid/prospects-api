<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use Illuminate\Http\Request;

class ProspectController extends Controller
{
    public function index() {
        return Prospect::all();
    }

    public function store(Request $request) {
        return Prospect::create($request->all());
    }

    public function show(Prospect $prospect) {
        return $prospect;
    }

    public function update(Request $request, Prospect $prospect) {
        $prospect->update($request->all());
        return $prospect;
    }

    public function destroy(Prospect $prospect) {
        $prospect->delete();
        return response()->noContent();
    }
}

