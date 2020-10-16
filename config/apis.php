<?php

$directSendBaseUri = 'https://directsend.co.kr/index.php/api_v2/';
return [
    'directSend' => [
        'userId' => env('DIRECT_SEND_USER_ID'),
        'password' => env('DIRECT_SEND_USER_PASSWORD'),
        'from' => env('MAIL_FROM_ADDRESS'),
        'email' => $directSendBaseUri . 'mail_change_word',
    ]
];
