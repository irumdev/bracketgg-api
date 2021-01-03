<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\Rules\CreateTeam as CreateTeamRules;
use App\Helpers\ValidMessage;
use App\Http\Requests\Rules\CreateChannel as CreateChannelRules;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use App\Http\Requests\CommonFormRequest;

/**
 * 팀 생성 전 데이터 유효성 검증 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class CreateRequest extends CommonFormRequest
{
    /**
     * @var User 유저 인스턴스
     */
    private User $user;

    /**
     * @var bool 팀 생성가능 여부
     */
    private bool $canCreateTeam = false;

    /**
     * @var int 팀 생성 권한 없음
     */
    public const CAN_NOT_CREATE_TEAM = 1;

    /**
     * @var int 인증되지 않은 이메일을 가지고 있음
     */
    public const HAS_NOT_VERIFY_EMAIL = 2;

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
        $this->canCreateTeam = $this->user->can('createTeam');
        return $this->canCreateTeam;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return CreateTeamRules::rules();
    }

    public function messages(): array
    {
        return CreateChannelRules::messages();
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
    public function buildAuthorizeErrorMessage(User $user): int
    {
        switch ($user) {
            case $user->hasVerifiedEmail() === false:
                $message = self::HAS_NOT_VERIFY_EMAIL;
                break;

            case $this->canCreateTeam === false:
                $message = self::CAN_NOT_CREATE_TEAM;
                break;

            default:
                $message = self::CAN_NOT_CREATE_TEAM;
                break;
        }
        return $message;
    }
}
