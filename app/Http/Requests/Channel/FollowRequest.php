<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use App\Models\User;
use App\Helpers\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Channel\Follower as ChannelFollower;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * 채널 팔로우 요청을 검증하는 클래스 입니다
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class FollowRequest extends FormRequest
{
    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    /**
     * @var User 유저 모델
     */
    private User $user;

    public function __construct(ResponseBuilder $responseBuilder)
    {
        $this->user = Auth::user();
        $this->responseBuilder = $responseBuilder;
    }

    public function authorize(): bool
    {
        $user = $this->user;
        return $user &&
               $this->user->can('followChannel') &&
               $user->id !== $this->route('slug')->owner;
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail([
                'code' => $this->buildAuthorizeErrorMessage($this->user),
            ], Response::HTTP_UNAUTHORIZED)
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
