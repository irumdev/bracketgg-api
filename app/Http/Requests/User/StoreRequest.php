<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;

use App\Helpers\ResponseBuilder;
use App\Helpers\ValidMessage;

/**
 * 유저 회원가입 전 데이터 유효성 검증 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class StoreRequest extends FormRequest
{
    /**
     * @var int 이메일을 작성안함
     */
    public const REQUIRE_EMAIL = 1;

    /**
     * @var int 닉네임을 작성 안함
     */
    public const REQUIRE_NICKNAME = 2;

    /**
     * @var int 비밀번호를 작성 안함
     */
    public const REQUIRE_PASSWORD = 3;

    /**
     * @var int 비밀번호 재입력란을 작성 안함
     */
    public const REQUIRE_RE_ENTER_PASSWORD = 4;

    /**
     * @var int 닉네임이 스트링이 아님
     */
    public const NOT_STRING_NICK_NAME = 5;

    /**
     * @var int 이메일이 스트링이 아님
     */
    public const NOT_STRING_EMAIL = 6;

    /**
     * @var int 비밀번호가 스트링이 아님
     */
    public const NOT_STRING_PASSWORD = 7;

    /**
     * @var int 비밀번호 재입력란이 스트링이 아님
     */
    public const NOT_STRING_RE_ENTER_PASSWORD = 8;

    /**
     * @var int 이메일 패턴이 일치하지 않음
     */
    public const EMAIL_PATTERN_NOT_MATCH = 9;

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
     * @var int 이메일이 이미 존재
     */
    public const EMAIL_ALREADY_EXISTS = 13;

    /**
     * @var int 약관동의란에 값을 넣지 않음
     */
    public const REQUIRE_POLICY_AGREE = 14;

    /**
     * @var int 개인정보 처리방침에 아무 값을 넣지 않음
     */
    public const REQUIRE_PRIVACY_AGREE = 15;

    /**
     * @var int 약괸동의 값에 1이 아님
     */
    public const NOT_EQUAL_ONE_POLICY_AGREE = 16;

    /**
     * @var int 개인정보 처리방침에 값이 1이 아님
     */
    public const NOT_EQUAL_ONE_PRIVACT_AGREE = 17;

    /**
     * @var int 닉네임 최소 자리수 미달
     */
    public const NICKNAME_MIN_LENGTH = 18;

    /**
     * @var int 닉네임 최대 자리수 초과
     */
    public const NICKNAME_MAX_LENGTH = 19;

    /**
     * @var int 비밀번호 최대 자리수 초과
     */
    public const PASSWORD_MAX_LENGTH = 20;

    /**
     * @var int 비밀번호 재입력란 최대 자리수 초과
     */
    public const PASSWORD_RE_ENTER_MAX_LENGTH = 21;

    /**
     * @var int 프로필이미자 란이 이미지가 아님
     */
    public const PROFILE_IMAGE_NOT_IMAGE = 22;

    /**
     * @var int 프로필 이미지 최대용량 초과
     */
    public const PROFILE_IMAGE_MAX_SIZE = 23;

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
            // 'email' => 'required|string|email|unique:App\Models\User,email',
            'email' => [
                'required',
                'string',
                'regex:/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD',
                // 'regex:/^(.+)@((?:\w+)(?:\.(?:\w+))+)/',
                'unique:App\Models\User,email'
            ],
            'nick_name' => 'required|string|min:1|max:12',
            'password' => 'required|string|min:8|max:30',
            'confirmedPassword' => 'required|string|min:8|max:30|same:password',
            'is_policy_agree' =>  'required|in:1',
            'is_privacy_agree' => 'required|in:1',
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
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

            'email.regex' => json_encode(['code' => self::EMAIL_PATTERN_NOT_MATCH]),
            'email.unique' => json_encode(['code' => self::EMAIL_ALREADY_EXISTS]),
            'password.min' => json_encode(['code' => self::PASSWORD_MIN_LENGTH]),
            'confirmedPassword.min' =>  json_encode(['code' => self::PASSWORD_RE_ENTER_MIN_LEN_ERROR]),
            'confirmedPassword.same' => json_encode(['code' => self::PASSWORD_RE_ENTER_NOT_SAME_WITH_PASSWORD]),

            'is_policy_agree.required'  => json_encode(['code' => self::REQUIRE_POLICY_AGREE]),
            'is_privacy_agree.required' => json_encode(['code' => self::REQUIRE_PRIVACY_AGREE]),

            'is_policy_agree.in'  => json_encode(['code' => self::NOT_EQUAL_ONE_POLICY_AGREE]),
            'is_privacy_agree.in' => json_encode(['code' => self::NOT_EQUAL_ONE_PRIVACT_AGREE]),

            'nick_name.min' => json_encode(['code' => self::NICKNAME_MIN_LENGTH]),
            'nick_name.max' => json_encode(['code' => self::NICKNAME_MAX_LENGTH]),

            'password.max'          => json_encode(['code' => self::PASSWORD_MAX_LENGTH]),
            'confirmedPassword.max' => json_encode(['code' => self::PASSWORD_RE_ENTER_MAX_LENGTH]),

            'profile_image.mimes' => json_encode(['code' => self::PROFILE_IMAGE_NOT_IMAGE]),
            'profile_image.max'   => json_encode(['code' => self::PROFILE_IMAGE_MAX_SIZE]),

        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        throw new HttpResponseException(
            $this->response->fail(ValidMessage::first($validator))
        );
    }
}
