<?php

namespace App\Helpers;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

/**
 * 응답 형태를 정형화하는 메소드 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ResponseBuilder
{
    /**
     * 값의 유효성 여부
     * @var bool
     */
    private bool $isValid = false;

    /**
     * 리스폰스 성공여부
     * @var bool
     */
    private bool $ok = false;

    /**
     * 실제로 리턴 할 메세지
     * @var mixed
     */
    private $message = null;

    /**
     * http 응답 코드
     * @var int
     */
    private int $status = Response::HTTP_OK;

    /**
     * 클라이언트에게 성공 리스폰스를 리턴하는 메소드 입니다.
     *
     * @param   type 변수이름 변수 설명
     * @throws  throwClass throw 타입 설명
     * @used    \사용된\클래스\메소드
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 성공 리스폰스
     */
    public function ok($message, int $httpStatus = Response::HTTP_OK): JsonResponse
    {
        return $this->setOk(true)
                    ->setHttpStatus($httpStatus)
                    ->setIsValid(true)
                    ->setMessage($message)
                    ->response();
    }

    public function fail($message, int $httpStatus = Response::HTTP_UNPROCESSABLE_ENTITY): JsonResponse
    {
        return $this->setOk(false)
                    ->setHttpStatus($httpStatus)
                    ->setIsValid(false)
                    ->setMessage($message)
                    ->response();
    }

    public function setOk(bool $ok = true): ResponseBuilder
    {
        $this->ok = $ok;
        return $this;
    }

    public function setHttpStatus(int $httpStatus = Response::HTTP_OK): ResponseBuilder
    {
        $this->status = $httpStatus;
        return $this;
    }

    public function setIsValid(bool $isValid = true): ResponseBuilder
    {
        $this->isValid = $isValid;
        return $this;
    }

    public function setMessage($message): ResponseBuilder
    {
        $this->message = $message;
        return $this;
    }


    private function build(): array
    {
        return [
            'ok' => $this->ok,
            'isValid' => $this->isValid,
            'messages' => $this->message
        ];
    }

    public function response(): JsonResponse
    {
        return new JsonResponse($this->build(), $this->status);
    }
}
