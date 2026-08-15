<?php

return [
    'api_key'     => env('ARKESEL_API_KEY'),
    'sender_id'   => env('ARKESEL_SENDER_ID', 'Fenroy'),
    'admin_phone' => env('ARKESEL_ADMIN_PHONE'),
    'url'         => 'https://sms.arkesel.com/api/v2/sms/send',
];
