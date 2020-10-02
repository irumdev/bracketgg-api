<?php

namespace App\Http\Controllers\Channels;

use App\Models\Channel;
use App\Models\ChannelFollower;
use App\Services\UserService;
use App\Helpers\ResponseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\FollowChannelRequest;
use App\Http\Requests\UnFollowRequest;

class FollowChannelController extends Controller
{
    private UserService $userService;
    private ResponseBuilder $responseBuilder;

    public function __construct(UserService $userService, ResponseBuilder $responseBuilder)
    {
        $this->userService = $userService;
        $this->responseBuilder = $responseBuilder;
    }

    public function followChannel(FollowChannelRequest $request, Channel $channel): JsonResponse
    {
        $followChannelResult = $this->userService->followChannel(Auth::user(), $channel);

        switch ($followChannelResult) {
            case ChannelFollower::ALREADY_FOLLOW:
                return $this->responseBuilder->fail([
                    'code' => $followChannelResult
                ], Response::HTTP_FORBIDDEN);


            case ChannelFollower::FOLLOW_OK:
                return $this->responseBuilder->ok([
                    'code' => $followChannelResult
                ], Response::HTTP_CREATED);

        }
    }

    public function unFollowChannel(UnFollowRequest $request, Channel $channel): JsonResponse
    {
        $unFollowChannelResult = $this->userService->unFollowChannel(Auth::user(), $channel);

        switch ($unFollowChannelResult) {

            case ChannelFollower::ALREADY_UNFOLLOW:
                return $this->responseBuilder->fail([
                    'code' => $unFollowChannelResult
                ]);
            case ChannelFollower::UNFOLLOW_OK:
                return $this->responseBuilder->ok([
                    'code' => $unFollowChannelResult
                ]);

        }
    }
}
