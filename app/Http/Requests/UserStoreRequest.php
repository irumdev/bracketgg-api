<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;

use App\Helpers\ResponseBuilder;
use App\Helpers\ValidMessage;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public const REQUIRE_EMAIL = 1;
    public const REQUIRE_NICKNAME = 2;
    public const REQUIRE_PASSWORD = 3;
    public const REQUIRE_RE_ENTER_PASSWORD = 4;

    public const NOT_STRING_NICK_NAME = 5;
    public const NOT_STRING_EMAIL = 6;
    public const NOT_STRING_PASSWORD = 7;
    public const NOT_STRING_RE_ENTER_PASSWORD = 8;

    public const EMAIL_PATTERN_NOT_MATCH = 9;
    public const PASSWORD_MIN_LEN_ERROR = 10;
    public const PASSWORD_RE_ENTER_MIN_LEN_ERROR = 11;
    public const PASSWORD_RE_ENTER_NOT_SAME_WITH_PASSWORD = 12;
    public const EMAIL_ALREADY_EXISTS = 13;

    public function __construct(ResponseBuilder $response)
    {
        $this->response = $response;
    }

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
            'nickName' => 'required|string',
            'email' => 'required|string|email|unique:App\Models\User,email',
            'password' => 'required|string|min:8',
            'confirmedPassword' => 'required|string|min:8|same:password'
        ];
    }
    public function messages(): array
    {
        return [
            'nickName.required' => json_encode(['code' => self::REQUIRE_NICKNAME]),
            'email.required' => json_encode(['code' => self::REQUIRE_EMAIL]),
            'password.required' => json_encode(['code' => self::REQUIRE_PASSWORD]),
            'confirmedPassword.required' => json_encode(['code' => self::REQUIRE_RE_ENTER_PASSWORD]),

            'nickName.string' => json_encode(['code' => self::NOT_STRING_NICK_NAME]),
            'email.string' => json_encode(['code' => self::NOT_STRING_EMAIL]),
            'password.string' => json_encode(['code' => self::NOT_STRING_PASSWORD]),
            'confirmedPassword.string' => json_encode(['code' => self::NOT_STRING_RE_ENTER_PASSWORD]),

            'email.email' => json_encode(['code' => self::EMAIL_PATTERN_NOT_MATCH]),
            'email.unique' => json_encode(['code' => self::EMAIL_ALREADY_EXISTS]),
            'password.min' => json_encode(['code' => self::PASSWORD_MIN_LEN_ERROR]),
            'confirmedPassword.min' =>  json_encode(['code' => self::PASSWORD_RE_ENTER_MIN_LEN_ERROR]),
            'confirmedPassword.same' => json_encode(['code' => self::PASSWORD_RE_ENTER_NOT_SAME_WITH_PASSWORD]),
        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        throw new HttpResponseException(
            $this->response->fail(ValidMessage::first($validator))
        );
    }
}
