<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CommonFormRequest;
use App\Models\Channel\Follower as ChannelFollower;

/**
 * 채널 팔로우 요청을 검증하는 클래스 입니다
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class FollowRequest extends CommonFormRequest
{
    /**
     * @var User 유저 모델
     */
    private User $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function authorize(): bool
    {
        $user = $this->user;
        return $user &&
               $user->can('followChannel') &&
               $user->id !== $this->route('slug')->owner;
    }

    protected function failedAuthorization(): void
    {
        $this->throwUnAuthorizedException(
            $this->buildAuthorizeErrorMessage($this->user)
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
    private function buildAuthorizeErrorMessage(User $user): int
    {
        $message = ChannelFollower::AUTORIZE_FAIL;
        switch ($user) {
            case $user->can('followChannel') === false:
                $message = ChannelFollower::AUTORIZE_FAIL;
                break;

            case $user->id === $this->route('slug')->owner:
                $message = ChannelFollower::OWNER_FOLLOW_OWNER;
                break;

            default:
                $message = ChannelFollower::AUTORIZE_FAIL;
                break;
        }
        return $message;
    }

    public function rules(): array
    {
        return [];
    }
}
