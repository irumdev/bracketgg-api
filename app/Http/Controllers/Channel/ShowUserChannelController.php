<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseBuilder;
use App\Services\ChannelService;
use App\Services\UserService;
use App\Models\Channel\Channel;
use App\Models\User;
use App\Properties\Paginate;
use Illuminate\Http\JsonResponse;

/**
 * 유저가 가진 채널들의 정보를 보여주는 컨트롤러 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ShowUserChannelController extends Controller
{
    /**
     * 채널 서비스 레이어
     * @var ChannelService $channelService
     */
    private ChannelService $channelService;

    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $response;

    /**
     * 유저 서비스 레이어
     * @var ChannelService $channelService
     */
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
     * @return JsonResponse 채널정보
     */
    public function getChannelsByUserId(string $userId): JsonResponse
    {
        return $this->response->ok(
            $this->channelService->findChannelsByUserId($userId)
        );
    }

    /**
     * 채널에 해당하는 팔로워를 페이징 하여 조회하는 메소드 입니다.
     *
     * @param Channel $channel 조회 할 채널 객체
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 팔로워 유저 리스트
     */
    public function getFollower(Channel $channel): JsonResponse
    {
        $channelFollowers = $this->channelService->followers($channel)->simplePaginate(Paginate::PER);
        return $this->response->ok(
            $this->response->paginateMeta($channelFollowers)->merge([
                'followers' => array_map(fn (User $fan): array => array_merge($this->userService->info($fan), ['followedAt' => $fan->followedAt]), $channelFollowers->items())
            ])
        );
    }
}
