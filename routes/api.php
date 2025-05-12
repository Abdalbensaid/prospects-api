<?php

use App\Http\Controllers\ProspectController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScrapingController;
use App\Http\Controllers\BotTriggerController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('prospects', ProspectController::class);
    Route::apiResource('campaigns', CampaignController::class);
});
Route::middleware('auth:sanctum')->post('/scrape', [ScrapingController::class, 'start']);
Route::middleware('auth:sanctum')->post('/start-prospection', [BotTriggerController::class, 'start']);
