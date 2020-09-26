<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    private const REQUIRE_EMAIL = 1;
    private const REQUIRE_NICKNAME = 2;
    private const REQUIRE_PASSWORD = 3;
    private const REQUIRE_RE_ENTER_PASSWORD = 4;

    private const NOT_STRING_NICK_NAME = 5;
    private const NOT_STRING_EMAIL = 6;
    private const NOT_STRING_PASSWORD = 7;
    private const NOT_STRING_RE_ENTER_PASSWORD = 8;

    private const EMAIL_PATTERN_NOT_MATCH = 9;
    private const PASSWORD_MIN_LEN_ERROR = 10;
    private const PASSWORD_RE_ENTER_MIN_LEN_ERROR = 11;
    private const PASSWORD_RE_ENTER_NOT_SAME_WITH_PASSWORD = 12;

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
            'nick_name' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
            'confirmedPassword' => 'required|string|min:8|same:password'
        ];
    }
    public function messages(): array
    {
        return [
            'nick_name.required' => json_encode(['code' => self::REQUIRE_NICKNAME]),
            'email.required' => json_encode(['code' => self::REQUIRE_EMAIL]),
            'password.required' => json_encode(['code' => self::REQUIRE_PASSWORD]),
            'confirmedPassword.required' => json_encode(['code' => self::REQUIRE_RE_ENTER_PASSWORD]),

            'nick_name.string' => json_encode(['code' => self::NOT_STRING_NICK_NAME]),
            'email.string' => json_encode(['code' => self::NOT_STRING_EMAIL]),
            'password.string' => json_encode(['code' => self::NOT_STRING_PASSWORD]),
            'confirmedPassword.string' => json_encode(['code' => self::NOT_STRING_RE_ENTER_PASSWORD]),


            'email.email' => json_encode(['code' => self::EMAIL_PATTERN_NOT_MATCH]),
            'password.min' => json_encode(['code' => self::PASSWORD_MIN_LEN_ERROR]),
            'confirmedPassword.min' =>  json_encode(['code' => self::PASSWORD_RE_ENTER_MIN_LEN_ERROR]),
            'confirmedPassword.same' => json_encode(['code' => self::PASSWORD_RE_ENTER_NOT_SAME_WITH_PASSWORD]),

        ];
    }
}
