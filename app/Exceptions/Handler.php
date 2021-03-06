<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Auth\Access\AuthorizationException as AuthAccessFailException;
use Illuminate\Support\Facades\Log;

use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ResponseBuilder;
use App\Helpers\ErrorFormatter;
use Illuminate\Http\JsonResponse;

/**
 * 모든 exception들을 처리하는 클래스 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class Handler extends ExceptionHandler
{
    /**
     * validate response result
     * @var ResponseBuilder
     */
    private ResponseBuilder $response;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
        AuthAccessFailException::class,
        AuthenticationException::class,
        UnauthorizedException::class,
        MethodNotAllowedHttpException::class,
        RouteNotFoundException::class,
        NotFoundHttpException::class,
        ModelNotFoundException::class,
        InvalidSignatureException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception): void
    {
        $shouldReport = $this->shouldReport($exception);
        if ($shouldReport) {
            parent::report($exception);
        }

        if ($shouldReport && config('logging.isUseSlackNoti')) {
            $errorMessage = new ErrorFormatter($exception, request());
            Log::channel('slack')->critical($errorMessage->toKor());
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception): JsonResponse
    {
        $this->response = new ResponseBuilder();
        $renderObject = $this->response->fail('tryAgain', Response::HTTP_INTERNAL_SERVER_ERROR);
        $response = $this->buildClientResponse($exception);
        if ($response['message'] !== null) {
            $renderObject = $this->response->fail($response['message'], $response['status']);
        } elseif (config('app.debug')) {
            $renderObject = $this->response->fail(
                (new ErrorFormatter($exception, request()))->errorInfo(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        return $renderObject;
    }

    private function buildClientResponse(Throwable $exception): array
    {
        $message = null;
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        switch (get_class($exception)) {

            case AuthAccessFailException::class:
            case AuthenticationException::class:
            case UnauthorizedException::class:
                $message = $this->buildMessage((string)Response::HTTP_UNAUTHORIZED);
                $status = Response::HTTP_UNAUTHORIZED;
            break;

            case MethodNotAllowedHttpException::class:
                $message = $this->buildMessage((string)Response::HTTP_METHOD_NOT_ALLOWED);
                $status = Response::HTTP_METHOD_NOT_ALLOWED;
            break;


            case RouteNotFoundException::class:
            case NotFoundHttpException::class:
            case ModelNotFoundException::class:
                $message = $this->buildMessage((string)Response::HTTP_NOT_FOUND);
                $status = Response::HTTP_NOT_FOUND;
            break;

            case InvalidSignatureException::class:
                $message = $this->buildMessage((string)Response::HTTP_FORBIDDEN);
                $status = Response::HTTP_FORBIDDEN;
            break;

        }
        return [
            'status' => $status,
            'message' => $message
        ];
    }

    private function buildMessage(string $langKey): array
    {
        return [
            'code' => (int)__('codes.http.' . $langKey),
        ];
    }
}
