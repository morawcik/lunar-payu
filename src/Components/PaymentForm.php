<?php

namespace Morawcik\LunarPayu\Components;

use Livewire\Component;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;

class PaymentForm extends Component
{
    public Cart $cart;

    public function handleSubmit()
    {
        $paymentUrl = Payments::driver('payu')->cart($this->cart)->withData([
            'description' => trans('lunar::payu.payment_description'),
            'redirectRoute' => config('lunar.payu.redirect_route'),
            'webhookUrl' => config('lunar.payu.webhook_route'),
        ])->initiatePayment();

        $this->redirect($paymentUrl);
    }

    public function render()
    {
        return view('lunar::payu.components.payment-form');
    }
}