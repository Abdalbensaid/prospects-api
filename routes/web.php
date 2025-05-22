<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthTelegramController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScraperController;

Route::get('/registe', function () {
    return view('register');
});

Route::get('/loginshow', function () {
    return view('login');
})->name('login');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//
Route::middleware('auth:sanctum')->group(function () {

    //Route auth telegram
    Route::get('/telegram/login', [AuthTelegramController::class, 'showPhoneForm']);
    Route::post('/telegram/send-code', [AuthTelegramController::class, 'sendCode']);
    Route::get('/telegram/verify', [AuthTelegramController::class, 'showCodeForm']);
    Route::post('/telegram/verify', [AuthTelegramController::class, 'verifyCode']);
    Route::get('/sessions', [AuthTelegramController::class, 'listAccounts']);
    Route::get('/', [ScraperController::class, 'showForm']);

    //Route scraper
    Route::get('/run', [ScraperController::class, 'run']);
    Route::post('/scraperform', [ScraperController::class, 'submitForm']);
    Route::post('/send-messages', [ScraperController::class, 'sendMessages']);

    //Route prospec
    Route::apiResource('prospects', ProspectController::class);
    Route::apiResource('campaigns', CampaignController::class);
//
});