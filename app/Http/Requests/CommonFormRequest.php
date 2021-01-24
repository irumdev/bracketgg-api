<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\ResponseBuilder;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;

class CommonFormRequest extends FormRequest
{
    protected ResponseBuilder $responseBuilder;

    private function toErrorStructure(int $message): array
    {
        return [
            'code' => $message
        ];
    }

    private function throwResponseException($message, int $httpStatus = Response::HTTP_UNPROCESSABLE_ENTITY): void
    {
        throw new HttpResponseException(
            (new ResponseBuilder())->fail($message, $httpStatus)
        );
    }

    public function throwUnProcessableEntityException(int $message): void
    {
        $this->throwResponseException(
            $this->toErrorStructure($message)
        );
    }

    protected function throwUnAuthorizedException(int $message): void
    {
        $this->throwResponseException(
            $this->toErrorStructure($message),
            Response::HTTP_UNAUTHORIZED
        );
    }

    protected function throwForbidenException(int $message): void
    {
        $this->throwResponseException(
            $this->toErrorStructure($message),
            Response::HTTP_FORBIDDEN
        );
    }
}
