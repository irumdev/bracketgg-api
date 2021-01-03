<?php

declare(strict_types=1);

namespace App\Helpers;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

/**
 * 에러 로그를
 * 포매팅 해주는 헬퍼 클래스 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ErrorFormatter
{
    /**
     * @var Throwable
     */
    private Throwable $exception;

    /**
     * @var Request
     */
    private Request $request;

    public function __construct(Throwable $exception, Request $request)
    {
        $this->exception = $exception;
        $this->request = $request;
    }


    /**
     * 에러정보를 정형화 하여 배열로 리턴합니다.
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return array 에러정보
     */
    public function errorInfo(): array
    {
        return [
            'errorLine' => $this->exception->getLine(),
            'errorMessage' => $this->exception->getMessage(),
            'errorFile' => $this->exception->getFile(),
            'routeName' => url()->full(),
            'requestParam' =>  $this->request->all(),
            'requestMethod' => $this->request->getMethod(),
            'requestIp' =>  $this->request->ip(),
            /**
             * @todo 에러트레이스 제이슨 직렬화
             */
            // 'errorTrase' => $this->exception->getTrace(),
            'errorTraseAsString' => $this->exception->getTraceAsString(),
        ];
    }

    /**
     * 에러 포맷을 한글 형태로 리턴합니다.
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return string 에러 포맷
     */
    public function format(): string
    {
        return join("\n", [
            "장애 발생 시각 : %s",
            "에러파일 : %s",
            "에러 발생 줄번호 : %s",
            "에러 메세지 :  %s",
            "에러 url : %s",
            "요청 메소드 : %s",
            "요청 ip : %s",
            "요청 파라미터(JSON 직렬화 되어있습니다) : %s",
            "에러 트레이스 : \n%s",
        ]);
    }

    /**
     * 에러 내용을 한글화 합니다.
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return string
     */
    public function toKor(): string
    {
        $errorInfo = $this->errorInfo();
        return sprintf(
            $this->format(),
            now()->format('Y-m-d H:i:s'),
            $errorInfo['errorFile'],
            $errorInfo['errorLine'],
            $errorInfo['errorMessage'],
            $errorInfo['routeName'],
            $errorInfo['requestMethod'],
            $errorInfo['requestIp'],
            collect($errorInfo['requestParam'])->toJson(),
            $errorInfo['errorTraseAsString']
        );
    }
}
