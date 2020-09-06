<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;

use App\Helpers\ResponseBuilder;
use App\Helpers\ValidMessage;

/**
 * 로그인 요청 값 검증 클래스 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UserVerifyRequest extends FormRequest
{
    /**
     * @var ResponseBuilder $response 리스폰스 인스턴스
     */
    private ResponseBuilder $response;

    /**
     * @var int EMAIL_REQUIRED 이메일 값을 안보냄
     * @var int EMAIL_NOT_VALID 유효하지 않은 이메일
     * @var int NOT_EXISTS_EMAIL 이메일이 존재하지 않음
     * @var int PASSWORD_REQUIRED 비밀번호를 입력하지 않음
     */
    private const EMAIL_REQUIRED = 1;
    private const EMAIL_NOT_VALID = 2;
    private const NOT_EXISTS_EMAIL = 3;
    private const PASSWORD_REQUIRED = 4;


    public function __construct(ResponseBuilder $response)
    {
        $this->response = $response;
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
            'email' => 'required|email|exists:users',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => json_encode([
                'code' => self::EMAIL_REQUIRED,
            ]),

            'email.exists' => json_encode([
                'code' => self::NOT_EXISTS_EMAIL,
            ]),

            'password.required' => json_encode([
                'code' => self::PASSWORD_REQUIRED,
            ]),

            'email.email' =>  json_encode([
                'code' => self::EMAIL_NOT_VALID,
            ]),
        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        throw new HttpResponseException(
            $this->response->fail(ValidMessage::first($validator))
        );
    }
}
