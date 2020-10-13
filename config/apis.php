<?php
return [
    'directSend' => [
        'userId' => env('DIRECT_SEND_USER_ID'),
        'password' => env('DIRECT_SEND_USER_PASSWORD'),
        'from' => env('MAIL_FROM_ADDRESS'),
    ]
];
