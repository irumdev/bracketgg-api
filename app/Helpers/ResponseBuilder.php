<?php

namespace App\Helpers;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * 응답 형태를 정형화하는 클래스 입니다.
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
     * @param   string 보여줄 메세지
     * @param   int 클라이언트 http status
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

    /**
     * 클라이언트에게 실패 리스폰스를 리턴합니다.
     *
     * @param   string 보여줄 메세지
     * @param   int 클라이언트 http status
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 성공 리스폰스
     */
    public function fail($message, int $httpStatus = Response::HTTP_UNPROCESSABLE_ENTITY): JsonResponse
    {
        return $this->setOk(false)
                    ->setHttpStatus($httpStatus)
                    ->setIsValid(false)
                    ->setMessage($message)
                    ->response();
    }

    /**
     * ok 여부를 설정합니다.
     *
     * @param   bool ok 여부
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return ResponseBuilder
     */
    public function setOk(bool $ok = true): ResponseBuilder
    {
        $this->ok = $ok;
        return $this;
    }

    /**
     * http status를 설정합니다.
     *
     * @param   int httpStatus 응답코드
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return ResponseBuilder
     */
    public function setHttpStatus(int $httpStatus = Response::HTTP_OK): ResponseBuilder
    {
        $this->status = $httpStatus;
        return $this;
    }

    /**
     * 값이 유효한지 여부를 설정합니다.
     *
     * @param   bool ok 여부
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return ResponseBuilder
     */
    public function setIsValid(bool $isValid = true): ResponseBuilder
    {
        $this->isValid = $isValid;
        return $this;
    }

    /**
     * 클라이언트한테 리턴할 메세지를 설정합니다.
     *
     * @param   mixed 보여줄 메세지
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return ResponseBuilder
     */
    public function setMessage($message): ResponseBuilder
    {
        $this->message = $message;
        return $this;
    }

    /**
     * 세팅한 값을을 배열로 정형화 합니다.
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return array
     */
    private function build(): array
    {
        return [
            'ok' => $this->ok,
            'isValid' => $this->isValid,
            'messages' => $this->message
        ];
    }

    /**
     * 세팅한 값을을 배열로 정형화 합니다.
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse
     */
    public function response(): JsonResponse
    {
        return new JsonResponse($this->build(), $this->status);
    }

    /**
     * 페이징 시 페이지 메타데이터만 따로 추출하여 리턴합니다.
     * get pageinate metadata from Illuminate\Pagination\Paginator
     *
     * @param   \Illuminate\Pagination\Paginator $metadata 페이징 메타데이터
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return \Illuminate\Support\Collection 해당 값의 컬렉션을 리턴합니다.
     */
    public function paginateMeta(Paginator $metaData): Collection
    {
        return collect([
            'meta' => [
                'next' => $metaData->nextPageUrl(),
                'prev' => $metaData->previousPageUrl(),
                'curr' => $metaData->currentPage(),
                'length' => $metaData->count(),
                'hasMorePage' => $metaData->hasMorePages(),
            ]
        ]);
    }
}
