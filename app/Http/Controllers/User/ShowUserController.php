<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Models\User;
use App\Helpers\ResponseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/**
 * 유저의 정보를 조회하는 컨트롤러 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ShowUserController extends Controller
{
    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    /**
     * 유저 서비스 레이어
     * @var UserService $userService
     */
    private UserService $userService;

    public function __construct(UserService $userService, ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
        $this->userService = $userService;
    }

    /**
     * 현재 로그인 한 유저의 정보를 가져오는 메소드 입니다.
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 현재 로그인 한 유저의 정보
     */
    public function getCurrent(): JsonResponse
    {
        return $this->responseBuilder->ok(
            $this->userService->info(Auth::user())
        );
    }


    /**
     * 유저 인덱스를 가지고 유저 정보를 조회하는 메소드 입니다.
     * @deprecated
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @param User $user
     */
    public function getById(User $user): JsonResponse
    {
        return $this->responseBuilder->ok(
            $this->userService->info($user)
        );
    }
}
