<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Is;

use Illuminate\Foundation\Http\FormRequest;

use App\Helpers\ResponseBuilder;
use App\Helpers\ValidMessage;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\CommonFormRequest;

/**
 * 이메일 중복여부 데이터 유효성 검증 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class EmailDuplicateRequest extends FormRequest
{
    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

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
            'email' => 'required|email|unique:App\Models\User,email'
        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail([
                'isDuplicate' => true
            ])
        );
    }
}
