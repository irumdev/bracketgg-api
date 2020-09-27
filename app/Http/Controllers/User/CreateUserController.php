<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Validation\UnauthorizedException;
use App\Helpers\ResponseBuilder;
use App\Services\UserService;
use App\Http\Requests\UserStoreRequest;
use Illuminate\Http\JsonResponse;

/**
 * 회원가입 컨트롤러 클래스 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class CreateUserController extends Controller
{
    private ResponseBuilder $response;
    private UserService $userService;
    public function __construct(ResponseBuilder $responseBuilder, UserService $userService)
    {
        $this->response = $responseBuilder;
        $this->userService = $userService;
    }

    /**
     * 회원가입 컨트롤러 메인 메소드 입니다
     *
     * @param   App\Http\Requests\UserVerifyRequest $request 로그인 입력 검증 클래스 인스턴스
     * @throws  UnauthorizedException 로그인 실패 시 throw 됩니다.
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return
     */
    public function createUser(UserStoreRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        $this->userService->createUser($requestData);
        return $this->response->ok([]);
    }
}
