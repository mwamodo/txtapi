<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'external/sms'], function () {
    Route::webhooks('status', 'sms_status');
});
