<?php

use App\Http\Controllers\ProspectController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScrapingController;
use App\Http\Controllers\BotTriggerController;
use App\Http\Controllers\ScraperController;
use App\Http\Controllers\AuthTelegramController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/run', [ScraperController::class, 'run']);

Route::get('/telegram/login', [AuthTelegramController::class, 'showPhoneForm']);
Route::post('/telegram/send-code', [AuthTelegramController::class, 'sendCode']);
Route::get('/telegram/verify', [AuthTelegramController::class, 'showCodeForm']);
Route::post('/telegram/verify', [AuthTelegramController::class, 'verifyCode']);
Route::get('/sessions', [AuthTelegramController::class, 'listAccounts']);
Route::post('/send-messages', [ScraperController::class, 'sendMessages']);


Route::get('/scraper-form', [ScraperController::class, 'showForm']);
Route::post('/scraperform', [ScraperController::class, 'submitForm']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('prospects', ProspectController::class);
    Route::apiResource('campaigns', CampaignController::class);
});
Route::middleware('auth:sanctum')->post('/scrape', [ScrapingController::class, 'start']);
Route::middleware('auth:sanctum')->post('/start-prospection', [BotTriggerController::class, 'start']);
