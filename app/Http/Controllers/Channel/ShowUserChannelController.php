<?php

namespace App\Http\Controllers\Channel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseBuilder;
use App\Services\ChannelService;
use App\Services\UserService;
use App\Models\Channel;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * 유자가 가진 채널들의 정보를 보여주는 컨트롤러 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ShowUserChannelController extends Controller
{
    /**
     * @var ResponseBuilder $responseBuilder
     * @var ChannelService $channelService
     */
    private ResponseBuilder $response;
    private ChannelService $channelService;
    private UserService $userService;
    public function __construct(ResponseBuilder $responseBuilder, ChannelService $channelService, UserService $userService)
    {
        $this->response = $responseBuilder;
        $this->channelService = $channelService;
        $this->userService = $userService;
    }

    /**
     * 유저가 가지고있는 채널들의 정보를 보여주는 메소드 입니다.
     *
     * @param   string 유저인덱스
     * @throws  Illuminate\Database\Eloquent\ModelNotFoundException 유저가 가진 채널이 없을때
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 성공 리스폰스
     */
    public function getChannelsByUserId(string $userId): JsonResponse
    {
        return $this->response->ok(
            $this->channelService->findChannelsByUserId($userId)
        );
    }

    public function getFollower(Channel $channel): JsonResponse
    {
        $channelFollowers = $this->channelService->followers($channel)->simplePaginate();
        return $this->response->ok(
            $this->response->paginateMeta($channelFollowers)->merge([
                'followers' => array_map(fn (User $fan) => $this->userService->info($fan), $channelFollowers->items())
            ])
        );
    }
}
