<?php

namespace App\Http\Controllers\Channel;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Services\ChannelService;
use App\Helpers\ResponseBuilder;
use App\Models\Channel;

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

    public function updateChannelInfo(Channel $channel)
    {

    }
}
