<?php

namespace App\Http\Controllers\Channel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseBuilder;
use App\Services\ChannelService;

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
    public function __construct(ResponseBuilder $responseBuilder, ChannelService $channelService)
    {
        $this->response = $responseBuilder;
        $this->channelService = $channelService;
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
    public function getChannelsByUserId(string $userId)
    {
        return $this->response->ok(
            $this->channelService->findChannelsByUserId($userId)
        );
    }
}
