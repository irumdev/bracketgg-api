<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel;

use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;

use App\Services\ChannelService;

use App\Http\Requests\Channel\CreateRequest as CreateChannelRequest;
use App\Models\Channel\Channel;
use App\Events\Dispatchrs\Channel\Create as CreateChannelDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * 채널을 생성하는 컨트롤러 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class CreateChannelController extends Controller
{
    /**
     * 채널 서비스레이어
     * @var ChannelService $channelService
     */
    private ChannelService $channelService;

    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    public function __construct(ChannelService $channelService, ResponseBuilder $responseBuilder)
    {
        $this->channelService = $channelService;
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * 채널을 생성하는 컨트롤러 메인 메소드 입니다.
     *
     * @param CreateRequest $request 컨트롤러로 들어온 요청 객체
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 채널 성공 리스폰스
     */
    public function createChannel(CreateChannelRequest $request): JsonResponse
    {
        $createdChannel = $this->channelService->createChannel(array_merge($request->validated(), [
            'logo_image' => null,
            'follwer_count' => 0,
            'like_count' => 0,
            'owner' => Auth::id(),
        ]));
        event(new CreateChannelDispatcher(Channel::find($createdChannel['id'])));
        return $this->responseBuilder->ok($createdChannel);
    }
}
