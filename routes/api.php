<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScrapingController;
use App\Http\Controllers\BotTriggerController;
use App\Http\Controllers\ScraperController;
use App\Http\Controllers\AuthTelegramController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json($request->user());
});

Route::middleware('auth:sanctum')->group(function () {


    //Route auth telegram
    Route::get('/telegram/login', [AuthTelegramController::class, 'showPhoneForm']);
    Route::post('/telegram/send-code', [AuthTelegramController::class, 'sendCode']);
    Route::get('/telegram/verify', [AuthTelegramController::class, 'showCodeForm']);
    Route::post('/telegram/verify', [AuthTelegramController::class, 'verifyCode']);
    Route::get('/sessions', [AuthTelegramController::class, 'listAccounts']);
    Route::get('/scraper-form', [ScraperController::class, 'showForm']);

    //Route scraper
    Route::get('/run', [ScraperController::class, 'run']);
    Route::post('/scraperform', [ScraperController::class, 'submitForm']);
    Route::post('/send-messages', [ScraperController::class, 'sendMessages']);

    //Route prospec
    Route::apiResource('prospects', ProspectController::class);
    Route::apiResource('campaigns', CampaignController::class);
});
Route::middleware('auth:sanctum')->post('/scrape', [ScrapingController::class, 'start']);
Route::middleware('auth:sanctum')->post('/start-prospection', [BotTriggerController::class, 'start']);
