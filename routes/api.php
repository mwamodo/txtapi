<?php

use App\Http\Controllers\TextMessageController;
use Illuminate\Support\Facades\Route;

Route::post('text', [TextMessageController::class, 'send'])->name('text.send');

Route::get('status/{textId}', [TextMessageController::class, 'status'])->name('text.status');

Route::group(['prefix' => 'external/sms'], function () {
    Route::webhooks('status', 'sms_status');
});
