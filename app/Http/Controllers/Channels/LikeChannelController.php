<?php

namespace App\Http\Controllers\Channels;

use App\Http\Controllers\Controller;
use App\Http\Requests\LikeChannelRequest;
use App\Http\Requests\UnLikeChannelRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

use Symfony\Component\HttpFoundation\Response;

use App\Services\UserService;
use App\Helpers\ResponseBuilder;
use App\Models\ChannelFan;
use App\Models\Channel;

class LikeChannelController extends Controller
{
    public ResponseBuilder $responseBuilder;
    public UserService $userService;
    public function __construct(ResponseBuilder $responseBuilder, UserService $userService)
    {
        $this->responseBuilder = $responseBuilder;
        $this->userService = $userService;
    }

    public function likeChannel(LikeChannelRequest $request, Channel $channel): JsonResponse
    {
        $likeChannelResult = $this->userService->likeChannel(Auth::user(), $channel);

        switch ($likeChannelResult) {
            case ChannelFan::ALREADY_LIKE:
                return $this->responseBuilder->fail([
                    'code' => $likeChannelResult
                ], Response::HTTP_FORBIDDEN);

            case ChannelFan::LIKE_OK:
                return $this->responseBuilder->ok([
                    'code' => $likeChannelResult
                ], Response::HTTP_CREATED);
        }
    }

    public function unLikeChannel(UnLikeChannelRequest $request, Channel $channel): JsonResponse
    {
        $unLikeChannelResult = $this->userService->unLikeChannel(Auth::user(), $channel);
        switch ($unLikeChannelResult) {

            case ChannelFan::ALREADY_UNLIKE:
                return $this->responseBuilder->fail([
                    'code' => $unLikeChannelResult
                ], Response::HTTP_FORBIDDEN);

            case ChannelFan::UNLIKE_OK:
                return $this->responseBuilder->ok([
                    'code' => $unLikeChannelResult
                ]);
        }
    }
}
