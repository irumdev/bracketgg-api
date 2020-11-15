<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateChannelRequest;

use App\Services\ChannelService;
use App\Helpers\ResponseBuilder;
use App\Models\Channel\Channel;

class UpdateChannelController extends Controller
{
    /**
     * @var ChannelService $channelService
     * @var ResponseBuilder $responseBuilder
     */
    private ChannelService $channelService;
    private ResponseBuilder $responseBuilder;
    public function __construct(ChannelService $channelService, ResponseBuilder $responseBuilder)
    {
        $this->channelService = $channelService;
        $this->responseBuilder = $responseBuilder;
    }

    public function updateChannelInfo(UpdateChannelRequest $request, Channel $channel)
    {
        $updateResult = $this->channelService->updateChannelInfo(
            $channel,
            $request->validated()
        );
        return $this->responseBuilder->ok($updateResult);
    }
}
