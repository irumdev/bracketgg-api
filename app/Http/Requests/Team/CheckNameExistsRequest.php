<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use App\Models\User;
use App\Helpers\ValidMessage;
use App\Helpers\ResponseBuilder;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use App\Http\Requests\CommonFormRequest;

/**
 * 팀 이름 존재여부 유효성 검증 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class CheckNameExistsRequest extends CommonFormRequest
{
    /**
     * @var int 팀 생성 권한 없음
     */
    public const CAN_NOT_CREATE_TEAM = 1;

    /**
     * @var int 인증되지 않은 이메일을 가지고 있음
     */
    public const HAS_NOT_VERIFY_EMAIL = 2;

    /**
     * @var User 유저 인스턴스
     */
    private User $user;

    /**
     * @var bool 팀 생성가능 여부
     */
    private bool $canCreateTeam = false;

    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $response;

    public function __construct(ResponseBuilder $response)
    {
        $this->response = $response;
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
        return [
            'name' => 'unique:App\Models\Team\Team'
        ];
    }

    protected function failedAuthorization(): void
    {
        $this->throwUnAuthorizedException(
            $this->buildAuthorizeErrorMessage($this->user)
        );
    }

    protected function failedValidation(ValidContract $validator): void
    {
        throw new HttpResponseException(
            $this->response->fail([
                'isDuplicate' => true,
            ])
        );
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

    /**
     * @override
     * @see Illuminate\Http\Concerns\InteractsWithInput
     */
    public function all($keys = null): array
    {
        return array_merge(request()->all(), [
            'name' => $this->route('teamName')
        ]);
    }
}
