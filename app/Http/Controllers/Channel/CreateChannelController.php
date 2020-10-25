<?php

namespace App\Http\Controllers\Channel;

use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;

use App\Services\ChannelService;

use App\Http\Requests\CreateChannelRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CreateChannelController extends Controller
{
    private ChannelService $channelService;
    private ResponseBuilder $responseBuilder;
    public function __construct(ChannelService $channelService, ResponseBuilder $responseBuilder)
    {
        $this->channelService = $channelService;
        $this->responseBuilder = $responseBuilder;
    }

    public function createChannel(CreateChannelRequest $request): JsonResponse
    {
        $createdChannel = $this->channelService->createChannel(array_merge($request->validated(), [
            'logo_image' => null,
            'follwer_count' => 0,
            'like_count' => 0,
            'owner' => Auth::id(),
        ]));
        return $this->responseBuilder->ok($createdChannel);
    }
}
