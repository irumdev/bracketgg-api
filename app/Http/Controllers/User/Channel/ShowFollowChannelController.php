<?php

declare(strict_types=1);

namespace App\Http\Controllers\User\Channel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

use App\Helpers\ResponseBuilder;
use App\Models\Channel\Channel;
use App\Services\UserService;
use App\Services\ChannelService;

class ShowFollowChannelController extends Controller
{
    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    /**
     * 유저 서비스 레이어
     * @var UserService 유저 서비스 레이어
     */
    private UserService $userService;

    /**
     * 채널 서비스 레이어
     * @var UserService 유저 서비스 레이어
     */
    private ChannelService $channelService;

    public function __construct(ResponseBuilder $responseBuilder, UserService $userService, ChannelService $channelService)
    {
        $this->responseBuilder = $responseBuilder;
        $this->userService = $userService;
        $this->channelService = $channelService;
    }

    public function getFollowedChannelByUser(): JsonResponse
    {
        $followChannels = $this->userService->getFollowedChannels(Auth::user());

        return $this->responseBuilder->ok(
            $this->responseBuilder->paginateMeta($followChannels)->merge([
                'followedChannels' => collect($followChannels->items())->map(fn (Channel $channel): array => $this->channelService->info($channel))
            ])
        );
    }
}
