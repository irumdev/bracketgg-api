<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Channel\UpdateRequest as UpdateChannelRequest;

use App\Services\ChannelService;
use App\Helpers\ResponseBuilder;
use App\Models\Channel\Channel;
use Illuminate\Http\JsonResponse;

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

    public function updateChannelInfoWithOutImage(UpdateChannelRequest $request, Channel $channel): JsonResponse
    {
        $updateResult = $this->channelService->updateChannelInfoWithOutImage(
            $channel,
            $request->validated()
        );
        return $this->responseBuilder->ok($updateResult);
    }
}
