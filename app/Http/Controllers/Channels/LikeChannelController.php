<?php

namespace App\Http\Controllers\Channels;

use App\Http\Controllers\Controller;
use App\Http\Requests\LikeChannelRequest;

use App\Models\Channel;


use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseBuilder;
use Symfony\Component\HttpFoundation\Response;

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
        return $this->responseBuilder->ok([
            'code' => $this->userService->likeChannel(Auth::user(), $channel)
        ], Response::HTTP_CREATED);
    }

    public function unLikeChannel(Channel $channel): JsonResponse
    {
        return $this->responseBuilder->ok(
            $this->userService->unLikeChannel(Auth::user(), $channel)
        );
    }
}
