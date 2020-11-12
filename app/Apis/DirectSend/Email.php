<?php

declare(strict_types=1);

namespace App\Apis\DirectSend;

use InvalidArgumentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

use App\Exceptions\InvalidEmailArgumentException;

/**
 * direct send 이메일을 보내는 api입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class Email
{
    /**
     * @var string
     */
    private const OK = '0';
    /**
     * @var int
     */
    private const RETRY_COUNT = 10;

    /**
     * send direct api에 보낼 배열 안
     * 내용을 빌드하는 메소드 입니다.
     *
     * @throws App\Exceptions\InvalidEmailArgumentException 이메일 api보낼때 올바르지 않은 데이터가 들어올때 throw합니다.
     * @throws InvalidArgumentException 이메일 api보낼때 올바르지 않은 데이터가 들어올때 throw합니다.
     *
     * @param array 보낼 데이터
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return array 키값들을 포함하여 보내야 하는 데이터
     */
    private static function buildInfo(array $sendData): array
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

    /**
     * exception instance를 리턴합니다.
     *
     * @param string lang의 키 값
     * @param string 빠진 속성
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return InvalidArgumentException 익셉션 객체
     */
    private static function setArgException(string $key, string $missingAttribute): InvalidArgumentException
    {
        return new InvalidArgumentException(__('apis.directSend.' . $key, [
            'attribute' => $missingAttribute
        ]));
    }

    /**
     * 이메일 api에 request합니다.
     *
     * @param array email api에 보낼 데이터
     * @throws App\Exceptions\InvalidEmailArgumentException 이메일 발송 실패 시
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return void
     */
    public static function send(array $sendData): void
    {
        $sendEmailResult = self::postRequest(self::buildInfo($sendData))->json();
        throw_if(data_get($sendEmailResult, 'status') !== self::OK, new InvalidEmailArgumentException(json_encode($sendEmailResult)));
    }

    /**
     * request 요청을 합니다.
     *
     * @param array 보낼 데이터
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return Illuminate\Http\Client\Response requets 결과
     */
    private static function postRequest(array $requestData): Response
    {
        return Http::withHeaders([
            'Cache-control' => 'no-cache',
            'Content-type' => 'application/json; charset=utf-8',
        ])->retry(self::RETRY_COUNT)->post(config('apis.directSend.email'), $requestData);
    }
}
