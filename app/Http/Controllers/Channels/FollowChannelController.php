<?php

namespace App\Http\Controllers\Channels;

use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FollowChannelController extends Controller
{
    private UserService $userService;
    private ResponseBuilder $responseBuilder;

    public function __construct(UserService $userService, ResponseBuilder $responseBuilder)
    {
        $this->userService = $userService;
        $this->responseBuilder = $responseBuilder;
    }

    public function followChannel(Channel $channel): JsonResponse
    {
        $followChannelResult = $this->userService->followChannel(Auth::user(), $channel);
        if ($followChannelResult['ok']) {
            return $this->responseBuilder->ok($followChannelResult, Response::HTTP_CREATED);
        }
        return $this->responseBuilder->fail($followChannelResult);
    }

    public function unFollowChannel(Channel $channel): JsonResponse
    {
        $unFollowChannelResult = $this->userService->unFollowChannel(Auth::user(), $channel);
        if ($unFollowChannelResult['ok']) {
            return $this->responseBuilder->ok($unFollowChannelResult);
        }
        return $this->responseBuilder->fail($unFollowChannelResult);
    }
}
