<?php

return [
    'merchant_pos_id' => env('PAYU_MERCHANT_POS_ID'),
    'signature_key' => env('PAYU_SIGNATURE_KEY'),
    'oauth_client_id' => env('PAYU_OAUTH_CLIENT_ID'),
    'oauth_client_secret' => env('PAYU_OAUTH_CLIENT_SECRET'),

    'payment_status_mappings' => [
        'CANCELED' => 'canceled',
        'PENDING' => 'payment-pending',
        'WAITING_FOR_CONFIRMATION' => 'payment-pending',
        'COMPLETED' => 'payment-received',
    ],

    'webhook_route' => 'payu.webhook',
    'redirect_route' => 'payu.redirect',
    'payment_paid_route' => 'checkout-success.view',
    'payment_failed_route' => 'checkout-failed.view',
    //'account.order'
];