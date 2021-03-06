<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel;

use App\Models\Channel\Channel;
use App\Models\Channel\Follower as ChannelFollower;
use App\Services\UserService;
use App\Helpers\ResponseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Channel\UnFollowRequest;
use App\Http\Requests\User\Is\FollowChannelRequest as CheckUserFollowChannelRequest;
use App\Http\Requests\Channel\FollowRequest as FollowChannelRequest;

/**
 * 채널을 팔로우 또는 언팔로우 하는 컨트롤러 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class FollowChannelController extends Controller
{
    /**
     * 유저 서비스레이어
     * @var UserService $userService
     */
    private UserService $userService;

    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    public function __construct(UserService $userService, ResponseBuilder $responseBuilder)
    {
        $this->userService = $userService;
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * 채널을 팔로우 하는 메소드 입니다.
     *
     * @param   App\Http\Requests\FollowChannelRequest 채널 request 객체
     * @param   App\Models\Channel 팔로우 요청한 채널 인스턴스
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 성공 리스폰스 또는 팔로우 실패 리스폰스
     */
    public function followChannel(FollowChannelRequest $request, Channel $channel): JsonResponse
    {
        $followChannelResult = $this->userService->followChannel(Auth::user(), $channel);

        switch ($followChannelResult) {
            case ChannelFollower::ALREADY_FOLLOW:
                return $this->responseBuilder->fail([
                    'code' => $followChannelResult
                ], Response::HTTP_FORBIDDEN);


            case ChannelFollower::FOLLOW_OK:
                return $this->responseBuilder->ok([
                    'code' => $followChannelResult
                ], Response::HTTP_CREATED);

        }
    }

    /**
     * 채널을 언팔로우 하는 메소드 입니다.
     *
     * @param   App\Http\Requests\UnFollowRequest 채널 request 객체
     * @param   App\Models\Channel 언팔로우 요청한 채널 인스턴스
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 성공 리스폰스 또는 언팔로우 실패 리스폰스
     */
    public function unFollowChannel(UnFollowRequest $request, Channel $channel): JsonResponse
    {
        $unFollowChannelResult = $this->userService->unFollowChannel(Auth::user(), $channel);

        switch ($unFollowChannelResult) {
            case ChannelFollower::ALREADY_UNFOLLOW:
                return $this->responseBuilder->fail([
                    'code' => $unFollowChannelResult
                ], Response::HTTP_FORBIDDEN);

            case ChannelFollower::UNFOLLOW_OK:
                return $this->responseBuilder->ok([
                    'code' => $unFollowChannelResult
                ]);

        }
    }

    /**
     * 유저가 채널을 팔로우 했는지 안했는지 판단하는 메소드 입니다.
     *
     * @param   App\Http\Requests\UnFollowRequest 채널 request 객체
     * @param   App\Models\Channel 언팔로우 요청한 채널 인스턴스
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 성공 리스폰스 또는 언팔로우 실패 리스폰스
     */
    public function isFollow(CheckUserFollowChannelRequest $request, Channel $channel): JsonResponse
    {
        $isFollowChannel = $this->userService->isFollowChannel(Auth::user(), $channel);
        return $this->responseBuilder->ok([
            'isFollow' => $isFollowChannel,
        ]);
    }
}
