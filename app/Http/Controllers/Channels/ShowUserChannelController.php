<?php

namespace App\Http\Controllers\Channels;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseBuilder;
use App\Services\ChannelService;

class ShowUserChannelController extends Controller
{
    //

    private ResponseBuilder $response;
    private ChannelService $channelService;
    public function __construct(ResponseBuilder $responseBuilder, ChannelService $channelService)
    {
        $this->response = $responseBuilder;
        $this->channelService = $channelService;
    }


    public function getChannelsByUserId(string $userId)
    {
        return $this->response->ok(
            $this->channelService->findChannelsByUserId($userId)
        );
    }
}
