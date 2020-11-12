<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel;

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
use App\Http\Requests\CheckUserLikeChannelRequest;

/**
 * 채널을 좋아요 또는 좋아요 취소 하는 컨트롤러 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class LikeChannelController extends Controller
{
    /**
     * @var UserService $userService
     * @var ResponseBuilder $responseBuilder
     */
    public UserService $userService;
    public ResponseBuilder $responseBuilder;

    public function __construct(ResponseBuilder $responseBuilder, UserService $userService)
    {
        $this->responseBuilder = $responseBuilder;
        $this->userService = $userService;
    }

    /**
     * 로그인한 유저가 채널에 좋아요 하는 메소드 입니다.
     *
     * @param   App\Http\Requests\LikeChannelRequest 좋아요 request 인스턴스
     * @param   App\Models\Channel 채널 인스턴스
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 성공 또는 실패에 대한 응답 코드
     */
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

    /**
     * 로그인한 유저가 채널에 좋아요 하는 메소드 입니다.
     *
     * @param   App\Http\Requests\UnLikeChannelRequest 좋아요 취소 request 인스턴스
     * @param   App\Models\Channel 채널 인스턴스
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 성공 또는 실패에 대한 응답 코드
     */
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

    public function isLike(CheckUserLikeChannelRequest $request, Channel $channel): JsonResponse
    {
        $channnelIsLike =  $this->userService->isAlreadyLike(Auth::user(), $channel);
        return $this->responseBuilder->ok([
            'isLike' => $channnelIsLike
        ]);
    }
}
