<?php

namespace App\Apis\DirectSend;

// use InvalidArgumentException;

use App\Exceptions\InvalidEmailArgumentException;
use InvalidArgumentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class Email
{
    private const OK = '0';
    private const RETRY_COUNT = 10;

    private static function buildInfo(array $sendData)
    {
        $baseInfo = [
            'username' => config('apis.directSend.userId', null),
            'key' => config('apis.directSend.password', null),

            'sender' => config('apis.directSend.from', null),
        ];


        $hasInvalidBaseInfo = array_filter($baseInfo, fn ($item) => $item === null);
        $hasReceiver = isset($sendData['receivers']) || count($sendData['receivers']) <= 0;


        throw_if(count($hasInvalidBaseInfo) > 0, new InvalidArgumentException(__('apis.directSend.hasNotApiKeys')));
        throw_unless($hasReceiver, self::setArgException('attach', 'receiver'));
        throw_unless(isset($sendData['subject']), self::setArgException('attach', 'subject'));
        throw_unless(isset($sendData['view']), self::setArgException('attach', 'view'));

        $baseInfo['receiver'] = array_map(function ($receiver) {

            // throw_if(isset($receiver['name']) === false, self::getReceiverErrorMessage('name'));
            throw_unless(isset($receiver['email']), self::setArgException('receiver', 'email'));

            return array_merge($receiver, ['note' => '', 'mobile' => '']);
        }, $sendData['receivers']);


        $baseInfo['body'] = trim($sendData['view']);
        $baseInfo['subject'] = trim($sendData['subject']);

        return $baseInfo;
    }


    private static function setArgException(string $key, string $missingAttribute): InvalidArgumentException
    {
        return new InvalidArgumentException(__('apis.directSend.' . $key, [
            'attribute' => $missingAttribute
        ]));
    }

    public static function send(array $sendData): void
    {
        $sendEmailResult = self::postRequest(self::buildInfo($sendData))->json();
        throw_if(data_get($sendEmailResult, 'status') !== self::OK, new InvalidEmailArgumentException(json_encode($sendEmailResult)));
    }

    private static function postRequest(array $requestData): Response
    {
        return Http::withHeaders([
            'Cache-control' => 'no-cache',
            'Content-type' => 'application/json; charset=utf-8',
        ])->retry(self::RETRY_COUNT)->post(config('apis.directSend.email'), $requestData);
    }
}
