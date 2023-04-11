<?php

namespace Morawcik\LunarPayu\Managers;

use Lunar\Models\Transaction;
use OpenPayU_Configuration;
use OpenPayU_Order;

class PayuManager
{

    public function __construct()
    {
        OpenPayU_Configuration::setEnvironment();

        OpenPayU_Configuration::setMerchantPosId(config('lunar.payu.merchant_pos_id'));
        OpenPayU_Configuration::setSignatureKey(config('lunar.payu.signature_key'));

        OpenPayU_Configuration::setOauthClientId(config('lunar.payu.oauth_client_id'));
        OpenPayU_Configuration::setOauthClientSecret(config('lunar.payu.oauth_client_secret'));
    }

    public function createOrder($orderP, $orderLines, $shippingAddress, Transaction $transaction)
    {
        $orderP['merchantPosId'] = OpenPayU_Configuration::getMerchantPosId();

        foreach ($orderLines as $i => $orderLine) {
            $orderP['products'][$i]['name'] = $orderLine->description;
            $orderP['products'][$i]['unitPrice'] = $orderLine->total->value / $orderLine->quantity;
            $orderP['products'][$i]['quantity'] = $orderLine->quantity;
        }

        $orderP['buyer']['email'] = $shippingAddress->contact_email;
        $orderP['buyer']['phone'] = $shippingAddress->contact_phone;
        $orderP['buyer']['firstName'] = $shippingAddress->first_name;
        $orderP['buyer']['lastName'] = $shippingAddress->last_name;
        $orderP['buyer']['language'] = strtolower($shippingAddress->country->iso2);

        $response = OpenPayU_Order::create($orderP);

        if ($response->getStatus() == 'SUCCESS') {
            return $response->getResponse();
        }

        return null;
    }

    public function fetchOrder($orderId)
    {
        $response = OpenPayU_Order::retrieve(stripslashes($orderId));

        if (!$response || !$response->getResponse()->orders || !isset($response->getResponse()->orders[0])) {
            return null;
        }

        return $response->getResponse()->orders[0];
    }

    public function fetchTransaction($orderId)
    {
        $response = OpenPayU_Order::retrieveTransaction(stripslashes($orderId));

        if (!$response || !$response->getResponse()->transactions || !isset($response->getResponse()->transactions[0])) {
            return null;
        }

        return $response->getResponse()->transactions[0];
    }

}