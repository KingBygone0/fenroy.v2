<?php

return [
    'public_key'     => env('PAYSTACK_PUBLIC_KEY'),
    'secret_key'     => env('PAYSTACK_SECRET_KEY'),
    'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
    'payment_url'    => 'https://api.paystack.co',
    'currency'       => 'GHS',
];
