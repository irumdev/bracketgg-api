<?php

namespace App\Http\Controllers\Channels;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseBuilder;
use App\Services\ChannelService;
use App\Models\Channel;

/**
 * 채널 아이디로 채널의 정보들을 보여주는 컨트롤러 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ShowChannelController extends Controller
{
    /**
     * @var ResponseBuilder $responseBuilder
     * @var ChannelService $channelService
     */
    private ResponseBuilder $response;
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
    public function getChannelById(Channel $channel)
    {
        return $this->response->ok(
            $this->channelService->findChannelById($channel->id)
        );
    }
}
