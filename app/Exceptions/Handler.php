<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Helpers\ResponseBuilder;
use App\Helpers\ErrorFormatter;

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

            case AuthenticationException::class:
            case UnauthorizedException::class:
                $message = $this->buildMessage('codes.http.' . Response::HTTP_UNAUTHORIZED);
                $status = Response::HTTP_UNAUTHORIZED;
            break;

            case MethodNotAllowedHttpException::class:
                $message = $this->buildMessage('codes.http.' . Response::HTTP_METHOD_NOT_ALLOWED);
                $status = Response::HTTP_METHOD_NOT_ALLOWED;
            break;


            case NotFoundHttpException::class:
            case ModelNotFoundException::class:
                $message = $this->buildMessage('codes.http.' . Response::HTTP_NOT_FOUND);
                $status = Response::HTTP_NOT_FOUND;
            break;

        }
        if ($message) {
            return $this->response->fail($message, $status);
        }


        if (config('logging.isUseSlackNoti')) {
            $errorMessage = new ErrorFormatter($exception, $request);

            // $errorInfo = [
            //     'errorLine' => $exception->getLine(),
            //     'errorMessage' => $exception->getMessage(),
            //     'errorFile' => $exception->getFile(),
            //     'routeName' => url()->full(),
            //     'requestParam' => $request->all(),
            //     'errorTrase' => $exception->getTrace(),
            //     'requestMethod' => $request->getMethod(),
            //     'requestIp' => $request->ip(),
            // ];

            // $errorFormat = join("\n", [
            //     "%s 파일에서",
            //     "%s 번째 줄에 에러가 발생 했습니다.",
            //     "에러메세지 :  %s",
            //     "에러 url : %s",
            //     "요청 메소드 : %s",
            //     "요청 ip : %s",
            //     "요청 파라미터 : {\n    %s\n}",
            //     "에러 트레이스 : \n%s",
            // ]);
            Log::critical($errorMessage->errorInfo());
            Log::channel('slack')->critical($errorMessage->toKor());
            // Log::channel('slack')->critical(sprintf(
            //     $errorFormat,
            //     $errorInfo['errorFile'],
            //     $errorInfo['errorLine'],
            //     $errorInfo['errorMessage'],
            //     $errorInfo['routeName'],
            //     $errorInfo['requestMethod'],
            //     $errorInfo['requestIp'],
            //     collect($errorInfo['requestParam'])->map(fn ($key, $value) => $key . " : " . $value)->join("\n"),
            //     $exception->getTraceAsString(),
            // ));

            // ->toKor();
            // Log::critical($errorMessage);
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
