<?php

namespace App\Http\Controllers\Channels;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseBuilder;
use App\Services\ChannelService;
use App\Models\Channel;

class ShowChannelController extends Controller
{
    private ResponseBuilder $response;
    private ChannelService $channelService;
    public function __construct(ResponseBuilder $responseBuilder, ChannelService $channelService)
    {
        $this->response = $responseBuilder;
        $this->channelService = $channelService;
    }

    public function getChannelById(Channel $channel)
    {
        return $this->response->ok(
            $this->channelService->findChannelById($channel->id)
        );
    }
    //
}
