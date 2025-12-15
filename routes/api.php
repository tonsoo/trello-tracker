<?php

use Illuminate\Support\Facades\Route;
use Tonso\TrelloTracker\Http\Controllers\MessagingController;

Route::group(['prefix' => 'webhooks/messaging'], function () {
    Route::get('whatsapp', [MessagingController::class, 'whatsappAuth'])->name('messaging.whatsapp.auth');
    Route::post('whatsapp', [MessagingController::class, 'whatsapp'])->name('messaging.whatsapp');
});