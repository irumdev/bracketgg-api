<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\CommonFormRequest;
use Illuminate\Contracts\Validation\Validator as ValidContract;

use App\Models\User;
use App\Helpers\ValidMessage;
use App\Http\Requests\Rules\CreateChannel as CreateChannelRule;

/**
 * 채널 생성 시 유효성 검증 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class CreateRequest extends CommonFormRequest
{
    /**
     * @var int 채널생성조건에 불충분
     */
    public const CAN_NOT_CREATE_CHANNEL = 1;

    /**
     * @var int 인증되지 않은 이메일로 요청
     */
    public const HAS_NOT_VERIFY_EMAIL = 2;

    /**
     * @var User 유저 모델
     */
    private User $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user->can('createChannel');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return CreateChannelRule::rules();
    }

    public function messages(): array
    {
        return CreateChannelRule::messages();
    }

    protected function failedAuthorization(): void
    {
        $this->throwUnAuthorizedException(
            $this->buildAuthorizeErrorMessage($this->user)
        );
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
    }

    /**
     * 권한 없을 시, 권한에 관한 메세지를 빌드해주는 메소드 입니다.
     *
     * @todo 공통화
     * @param User $user 유저 모델
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return int $message 에러 코드
     */
    private function buildAuthorizeErrorMessage(User $user): int
    {
        switch ($user) {
            case $user->hasVerifiedEmail() === false:
                $message = self::HAS_NOT_VERIFY_EMAIL;
                break;

            case $user->can('createChannel') === false:
                $message = self::CAN_NOT_CREATE_CHANNEL;
                break;

            default:
                $message = self::CAN_NOT_CREATE_CHANNEL;
                break;
        }
        return $message;
    }
}
