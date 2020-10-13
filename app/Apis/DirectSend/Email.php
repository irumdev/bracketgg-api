<?php
namespace App\Apis\DirectSend;

use InvalidArgumentException;
use Illuminate\Support\Facades\Http;

class Email
{
    private const NOT_EXISTS = 0;


    private static function buildInfo(array $sendData)
    {
        $baseInfo = [
            'username' => config('apis.directSend.userId', null),
            'key' => config('apis.directSend.password', null),

            'sender' => config('apis.directSend.from', null),
        ];


        $hasInvalidBaseInfo = array_filter($baseInfo, fn($item) => $item === null);
        $hasNotReceiver = isset($sendData['receivers']) === false || count($sendData['receivers']) <= 0;

        throw_if(count($hasInvalidBaseInfo) > 0, new InvalidArgumentException(__('apis.directSend.hasNotApiKeys')));
        throw_if($hasNotReceiver, new InvalidArgumentException('must attach receiver'));
        throw_if(isset($sendData['subject']) === false, new InvalidArgumentException('must attach subject'));
        throw_if(isset($sendData['view']) === false, new InvalidArgumentException('must attach view'));

        $baseInfo['receiver'] = array_map(function($receiver) {

            // throw_if(isset($receiver['name']) === false, self::getReceiverErrorMessage('name'));
            throw_if(isset($receiver['email']) === false, self::getReceiverErrorMessage('email'));

            return array_merge($receiver, ['note' => '', 'mobile' => '']);
        }, $sendData['receivers']);


        $baseInfo['body'] = trim($sendData['view']);
        $baseInfo['subject'] = trim($sendData['subject']);

        return $baseInfo;
    }


    private static function getReceiverErrorMessage(string $key): InvalidArgumentException
    {
        return new InvalidArgumentException(__('apis.directSend.receiver', [
            'attribute' => $key
        ]));
    }

    public static function send(array $sendData)
    {
        $sendInfo = self::buildInfo([
            'receivers' => [
                ['email' => 'dhtmdgkr123@naver.com', 'name' => 'asdf'],
                ['email' => 'me@haodoo.io', 'name' => 'asdf'],
            ],
            'subject' => '제목입니다.',
            'view' => view('email.verify', [
                'userName' => 'dhtmdgkr123',
                'verifyUrl' => 'http://asdasdfasdf.com'
            ])->render(),

        ]);


        return self::postRequest($sendInfo);


    }


    private static function postRequest(array $requestData)
    {
        return Http::withHeaders([
            'Cache-control' => 'no-cache',
            'Content-type' => 'application/json; charset=utf-8',
        ])->retry(10)->post('https://directsend.co.kr/index.php/api_v2/mail_change_word', $requestData)->json();
    }






}

