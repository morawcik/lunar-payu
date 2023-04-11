<?php

namespace Morawcik\LunarPayu\Controllers;

use Illuminate\Http\Request;
use Lunar\Facades\Payments;
use Lunar\Models\Transaction;

class PayuWebhookController
{

    public function webhook(Transaction $transaction)
    {
        if (!$transaction->reference) {
            return null;
        }

        Payments::driver('payu')->withData([
            'orderId' => $transaction->reference,
        ])->authorize();

        return response('OK', 200);
    }

}