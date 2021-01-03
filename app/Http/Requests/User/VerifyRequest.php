<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;

use App\Helpers\ResponseBuilder;
use App\Helpers\ValidMessage;
use App\Http\Requests\CommonFormRequest;

/**
 * 로그인 요청 값 검증 클래스 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class VerifyRequest extends CommonFormRequest
{
    /**
     * @var int 이메일 값을 안보냄
     */
    private const EMAIL_REQUIRED = 1;

    /**
     * @var int 유효하지 않은 이메일
     */
    private const EMAIL_NOT_VALID = 2;

    /**
     * @var int 이메일이 존재하지 않음
     */
    private const NOT_EXISTS_EMAIL = 3;

    /**
     * @var int 비밀번호를 입력하지 않음
     */
    private const PASSWORD_REQUIRED = 4;

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
            'email' => 'required|email|exists:users',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => self::EMAIL_REQUIRED,
            'email.exists' => self::NOT_EXISTS_EMAIL,
            'password.required' => self::PASSWORD_REQUIRED,
            'email.email' => self::EMAIL_NOT_VALID,
        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
    }
}
