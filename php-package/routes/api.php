<?php

use Illuminate\Support\Facades\Route;
use Tonso\TrelloTracker\Http\Controllers\MessagingController;
use Tonso\TrelloTracker\Http\Controllers\TranscriptController;
use Tonso\TrelloTracker\Http\Middleware\ValidateBearerToken;

Route::group(['prefix' => 'webhooks'], function () {

    Route::group(['prefix' => 'messaging'], function () {
        Route::get('whatsapp', [MessagingController::class, 'whatsappAuth'])->name('messaging.whatsapp.auth');
        Route::post('whatsapp', [MessagingController::class, 'whatsapp'])->name('messaging.whatsapp');
    });

    Route::options('transcribe/{meetingId}', function () {
        return response('', 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    });
    Route::post('transcribe/{meetingId}', [TranscriptController::class, 'transcribe'])->name('transcribe')->middleware(ValidateBearerToken::class);
});