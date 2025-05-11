<?php

use App\Http\Controllers\ProspectController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('prospects', ProspectController::class);
    Route::apiResource('campaigns', CampaignController::class);
});
