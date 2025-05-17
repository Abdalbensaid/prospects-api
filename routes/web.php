<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthTelegramController;

use App\Http\Controllers\ScraperController;

Route::get('/scraper', [ScraperController::class, 'run']);
Route::get('/scraper-form', [ScraperController::class, 'showForm']);
Route::post('/scraperform', [ScraperController::class, 'submitForm']);

Route::get('/telegram/login', [AuthTelegramController::class, 'showPhoneForm']);
Route::post('/telegram/send-code', [AuthTelegramController::class, 'sendCode']);
Route::get('/telegram/verify', [AuthTelegramController::class, 'showCodeForm']);
Route::post('/telegram/verify', [AuthTelegramController::class, 'verifyCode']);
Route::get('/sessions', [AuthTelegramController::class, 'listAccounts']);
Route::post('/scrape-now', [ScraperController::class, 'scrapeFromSession']);
Route::post('/send-messages', [ScraperController::class, 'sendMessages']);

