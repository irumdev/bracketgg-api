<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Is;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use App\Http\Requests\CommonFormRequest;

use App\Helpers\ResponseBuilder;
use App\Helpers\ValidMessage;

/**
 * 유저 비밀번호 변경 전  데이터 유효성 검증 클래스 입니다.
 *
 * @author irumdev <jklsj1252@gmail.com>
 * @version 1.0.0
 */
class PasswordUpdateRequest extends CommonFormRequest
{
    /**
     * @var int 비밀번호 최소 길이
     */
    public const PASSWORD_MIN_LEN = 8;

    /**
     * @var int 비밀번호 최대 길이
     */
    public const PASSWORD_MAX_LEN = 30;

    /**
     * @var int 비밀번호를 작성 안함
     */
    public const REQUIRE_PASSWORD = 3;

    /**
     * @var int 비밀번호 재입력란을 작성 안함
     */
    public const REQUIRE_RE_ENTER_PASSWORD = 4;

    /**
     * @var int 비밀번호가 스트링이 아님
     */
    public const NOT_STRING_PASSWORD = 7;

    /**
     * @var int 비밀번호 재입력란이 스트링이 아님
     */
    public const NOT_STRING_RE_ENTER_PASSWORD = 8;

    /**
     * @var int 비밀번호 최소자리수 미달
     */
    public const PASSWORD_MIN_LENGTH = 10;

    /**
     * @var int 비밀번호 재입력란 최소자리수 미달
     */
    public const PASSWORD_RE_ENTER_MIN_LEN_ERROR = 11;

    /**
     * @var int 비밀번호 재입력란이 비밀번호와 일치하지 않음
     */
    public const PASSWORD_RE_ENTER_NOT_SAME_WITH_PASSWORD = 12;

    /**
     * @var int 비밀번호 최대 자리수 초과
     */
    public const PASSWORD_MAX_LENGTH = 20;

    /**
     * @var int 비밀번호 재입력란 최대 자리수 초과
     */
    public const PASSWORD_RE_ENTER_MAX_LENGTH = 21;

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
        $lengthRuleString = 'min:' . self::PASSWORD_MIN_LEN . '|max:' . self::PASSWORD_MAX_LEN;
        return [
            'password' => 'bail|required|string|' . $lengthRuleString,
            'confirmedPassword' => 'required|string|' . $lengthRuleString . '|same:password'
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => self::REQUIRE_PASSWORD,
            'confirmedPassword.required' => self::REQUIRE_RE_ENTER_PASSWORD,

            'password.string' => self::NOT_STRING_PASSWORD,
            'confirmedPassword.string' => self::NOT_STRING_RE_ENTER_PASSWORD,

            'password.min' => self::PASSWORD_MIN_LENGTH,
            'confirmedPassword.min' => self::PASSWORD_RE_ENTER_MIN_LEN_ERROR,
            'confirmedPassword.same' => self::PASSWORD_RE_ENTER_NOT_SAME_WITH_PASSWORD,

            'password.max' => self::PASSWORD_MAX_LENGTH,
            'confirmedPassword.max' => self::PASSWORD_RE_ENTER_MAX_LENGTH,
        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
    }
}
