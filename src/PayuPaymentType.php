<?php

namespace Morawcik\LunarPayu;

use Carbon\Carbon;
use Exception;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;
use Morawcik\LunarPayu\Facades\PayuFacade;

class PayuPaymentType extends AbstractPayment
{

    public function initiatePayment()
    {
        if (!$this->order) {
            if (!$this->order = $this->cart->order) {
                $this->order = $this->cart->createOrder();
            }
        }

        if ($this->order->placed_at) {
            throw new Exception('Order has already been placed');
        }

        $transaction = Transaction::create([
            'success' => false,
            'driver' => 'payu',
            'order_id' => $this->order->id,
            'type' => 'intent',
            'amount' => $this->order->total,
            'reference' => '',
            'status' => '',
            'card_type' => '',
        ]);

        $description = str_replace(':order_reference', $this->order->reference, $this->data['description']);

        $orderPayu = PayuFacade::createOrder([
            "notifyUrl" => route($this->data['webhookUrl'], $transaction->id),
            "continueUrl" => route($this->data['redirectRoute'],
                ['order' => $this->order->id, 'transaction' => $transaction->id]),
            "customerIp" => request()->ip(),
            "description" => $description,
            'currencyCode' => $this->cart->currency->code,
            'totalAmount' => $this->order->total->value,
            'extOrderId' => $this->order->id.'-'.uniqid(),
        ], $this->order->productLines, $this->order->shippingAddress, $transaction);

        if (!$orderPayu) {
            throw new Exception('Order cannot be created');
        }

        $transaction->update([
            'reference' => $orderPayu->orderId,
            'status' => 'PENDING',
            'notes' => $description,
        ]);

        return $orderPayu->redirectUri;
    }

    public function authorize(): PaymentAuthorize
    {
        if (!array_key_exists('orderId', $this->data)) {
            return new PaymentAuthorize(
                success: false,
                message: json_encode(['status' => 'not_found', 'message' => 'No order ID provided']),
            );
        }

        $orderPayu = PayuFacade::fetchOrder($this->data['orderId']);

        $orderId = $orderPayu->extOrderId;

        $transaction = Transaction
            ::where('reference', $this->data['orderId'])
            ->where('order_id', $orderId)
            ->where('driver', 'payu')
            ->first();

        $this->order = Order::find($orderId);

        if (!$transaction || !$orderPayu || !$this->order) {
            return new PaymentAuthorize(
                success: false,
                message: json_encode([
                    'status' => 'not_found', 'message' => 'No transaction found for order ID '.$this->data['orderId']
                ]),
            );
        }

        if ($this->order->placed_at) {
            return new PaymentAuthorize(
                success: true,
                message: json_encode(['status' => 'duplicate', 'message' => 'This order has already been placed']),
            );
        }

        $transaction->update([
            'success' => $orderPayu->status == 'COMPLETED',
            'status' => $orderPayu->status,
            'notes' => $orderPayu->description,
            'type' => 'capture',
            'card_type' => '',
        ]);

        if ($orderPayu->status == 'COMPLETED') {
            $date = Carbon::createFromFormat('Y-m-d\TH:i:s.vP', $orderPayu->orderCreateDate);

            $this->order->placed_at = $date->format('Y-m-d H:i:s');
        }

        $this->order->status = config('lunar.payu.payment_status_mappings.'.$orderPayu->status) ?: $orderPayu->status;
        $this->order->save();

        return new PaymentAuthorize(success: $orderPayu->status === 'COMPLETED',
            message: json_encode(['status' => $orderPayu->status]));
    }

    public function refund(Transaction $transaction, int $amount, $notes = null): PaymentRefund
    {
        // TODO: Implement refund() method.
    }

    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        return new PaymentCapture(true);
    }

}
