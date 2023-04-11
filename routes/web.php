<?php

use Morawcik\LunarPayu\Controllers\PayuRedirectController;
use Morawcik\LunarPayu\Controllers\PayuWebhookController;

Route::middleware('web')->group(function () {
    Route::get('payu/redirect/{order}/{transaction?}',
        [PayuRedirectController::class, 'redirect'])->name('payu.redirect');
});

Route::post('payu/webhook/{transaction}', [PayuWebhookController::class, 'webhook'])->name('payu.webhook');