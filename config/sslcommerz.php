<?php

$sandbox = (bool)env('SSLCOMMERZ_SANDBOX', true);
$apiDomain = $sandbox
    ? 'https://sandbox.sslcommerz.com'
    : 'https://securepay.sslcommerz.com';

return [
    'store_id' => env('SSLCOMMERZ_STORE_ID'),
    'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),

    'sandbox' => $sandbox,
    'connect_from_localhost' => $sandbox,

    'currency' => env('SSLCOMMERZ_CURRENCY', 'BDT'),

    'init_url' => $apiDomain . '/gwprocess/v4/api.php',
    'validation_url' => $apiDomain . '/validator/api/validationserverAPI.php',

    'success_url' => env('APP_URL') . '/user/payments/success',
    'failed_url' => env('APP_URL') . '/user/payments/fail',
    'cancel_url' => env('APP_URL') . '/user/payments/cancel',
    'ipn_url' => env('APP_URL') . '/payments/ipn',

    'apiDomain' => $apiDomain,
    'apiCredentials' => [
        'store_id' => env('SSLCOMMERZ_STORE_ID'),
        'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
    ],
    'apiUrl' => [
        'make_payment' => '/gwprocess/v4/api.php',
        'transaction_status' => '/validator/api/merchantTransIDvalidationAPI.php',
        'order_validate' => '/validator/api/validationserverAPI.php',
        'refund_payment' => '/validator/api/merchantTransIDvalidationAPI.php',
        'refund_status' => '/validator/api/merchantTransIDvalidationAPI.php',
    ],
];