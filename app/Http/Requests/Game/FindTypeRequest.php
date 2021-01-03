<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\ResponseBuilder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use App\Helpers\ValidMessage;
use App\Http\Requests\CommonFormRequest;

/**
 * 게임타입 키워드 검색 요청 검증 객체 입니다.
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class FindTypeRequest extends CommonFormRequest
{
    /**
     * @var int 검색 키워드가 비어있음
     */
    public const KEYWORD_IS_EMPTY = 1;

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
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
    }

    public function messages(): array
    {
        return [
            'query.required' => self::KEYWORD_IS_EMPTY,
        ];
    }
}
