<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\ResponseBuilder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;


use App\Helpers\ValidMessage;

class FindTypeRequest extends FormRequest
{
    private ResponseBuilder $responseBuilder;
    public const KEYWORD_IS_EMPTY = 1;

    public function __construct(ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'query' => 'required'
        ];
    }


    protected function failedValidation(ValidContract $validator): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail(ValidMessage::first($validator))
        );
    }

    public function messages(): array
    {
        return [
            'query.required' => json_encode(['code' => self::KEYWORD_IS_EMPTY]),
        ];
    }
}
