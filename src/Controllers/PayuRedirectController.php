<?php

namespace Morawcik\LunarPayu\Controllers;

use Lunar\Facades\Payments;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

class PayuRedirectController
{

    public function redirect(Order $order, Transaction $transaction = null)
    {
        if (!$transaction || !$transaction->reference) {
            return redirect()->route(config('lunar.payu.payment_failed_route'), [$order->id, 'status' => 'failed']);
        }

        $paymentAuthorize = Payments::driver('payu')->withData([
            'orderId' => $transaction->reference,
        ])->authorize();

        return redirect()->route(config('lunar.payu.payment_paid_route'), $order->id);
    }

}