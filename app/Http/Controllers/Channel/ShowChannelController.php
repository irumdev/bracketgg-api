<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseBuilder;
use App\Services\ChannelService;
use App\Models\Channel\Channel;
use Illuminate\Http\JsonResponse;

/**
 * 채널 아이디로 채널의 정보들을 보여주는 컨트롤러 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ShowChannelController extends Controller
{
    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $response;

    /**
     * 채널 서비스 레이어
     * @var ChannelService
     */
    private ChannelService $channelService;

    public function __construct(ResponseBuilder $responseBuilder, ChannelService $channelService)
    {
        $this->response = $responseBuilder;
        $this->channelService = $channelService;
    }

    /**
     * 채널 아이디로 채널의 정보를 조회하는 메소드 입니다.
     *
     * @param   App\Models\Channel $channel
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 채널정보들
     */
    public function getChannelById(Channel $channel): JsonResponse
    {
        return $this->response->ok(
            $this->channelService->findChannelById($channel->id)
        );
    }
}
