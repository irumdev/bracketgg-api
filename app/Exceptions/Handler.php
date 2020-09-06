<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpFoundation\Response;

use App\Helpers\ResponseBuilder;



class Handler extends ExceptionHandler
{
    private ResponseBuilder $response;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
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
    public function report(Throwable $exception)
    {
        parent::report($exception);
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
    public function render($request, Throwable $exception)
    {
        $this->response = new ResponseBuilder();
        $message = null;
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        switch (get_class($exception)) {
            case UnauthorizedException::class:
                $message = $this->buildMessage('codes.http.' . Response::HTTP_UNAUTHORIZED);
                $status = $exception->getCode();
            break;

            case MethodNotAllowedHttpException::class:
                $message = $this->buildMessage('codes.http.' . Response::HTTP_METHOD_NOT_ALLOWED);
                $status = Response::HTTP_METHOD_NOT_ALLOWED;
            break;

        }
        if ($message) {
            return $this->response->fail($message, $status);
        }
        return parent::render($request, $exception);
    }

    private function buildMessage(string $langKey): array
    {
        return [
            'code' => (int)__($langKey),
        ];
    }
}
