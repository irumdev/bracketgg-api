<?php

namespace App\Helpers;

use Throwable;
use Illuminate\Http\Request;
class ErrorFormatter
{
    private Throwable $exception;
    private Request $request;
    public function __construct(Throwable $exception, Request $request)
    {
        $this->exception = $exception;
        $this->request = $request;
    }

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
            'errorTrase' => $this->exception->getTrace(),
            'errorTraseAsString' => $this->exception->getTraceAsString(),
        ];
    }

    public function format(): string
    {
        return join("\n", [
            "%s 파일에서",
            "%s 번째 줄에 에러가 발생 했습니다.",
            "에러메세지 :  %s",
            "에러 url : %s",
            "요청 메소드 : %s",
            "요청 ip : %s",
            "요청 파라미터 : {\n    %s\n}",
            "에러 트레이스 : \n%s",
        ]);
    }
    public function toKor(): string
    {
        $errorInfo = $this->errorInfo();
        return sprintf(
            $this->format(),
            $errorInfo['errorFile'],
            $errorInfo['errorLine'],
            $errorInfo['errorMessage'],
            $errorInfo['routeName'],
            $errorInfo['requestMethod'],
            $errorInfo['requestIp'],
            collect($errorInfo['requestParam'])->map(fn ($key, $value) => $key . " : " . $value)->join("\n"),
            $errorInfo['errorTraseAsString']
        );
    }
}
