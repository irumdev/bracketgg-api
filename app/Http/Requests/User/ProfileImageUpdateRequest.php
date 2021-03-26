<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator as ValidContract;
use App\Http\Requests\CommonFormRequest;
use App\Helpers\ValidMessage;

/**
 * 유저 프로필 이미지 변경 전  데이터 유효성 검증 클래스 입니다.
 *
 * @author irumdev <jklsj1252@gmail.com>
 * @version 1.0.0
 */
class ProfileImageUpdateRequest extends CommonFormRequest
{
    /**
     * @var int 프로필 이미지 업로드 허용 최대용량
     */
    public const IMAGE_MAX_SIZE = 2048;

    /**
     * @var int 프로필 이미지란이 이미지가 아님
     */
    public const PROFILE_IMAGE_NOT_IMAGE = 22;

    /**
     * @var int 프로필 이미지 최대용량 초과
     */
    public const PROFILE_IMAGE_MAX_SIZE = 23;

    /**
     * @var int 프로필 이미지를 첨부하지 않음
     */
    public const REQUIRE_PROFILE_IMAGE = 24;

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
            'profile_image' => 'required|image|mimes:jpeg,jpg,png|max:' . self::IMAGE_MAX_SIZE,
        ];
    }

    public function messages(): array
    {
        return [
            'profile_image.required' => self::REQUIRE_PROFILE_IMAGE,
            'profile_image.image' => self::PROFILE_IMAGE_NOT_IMAGE,
            'profile_image.mimes' => self::PROFILE_IMAGE_NOT_IMAGE,
            'profile_image.max' => self::PROFILE_IMAGE_MAX_SIZE,
        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
    }
}
