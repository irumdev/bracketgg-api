<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserVerifyRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use App\Helpers\ResponseBuilder;
use Symfony\Component\HttpFoundation\Response;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

/**
 * 로그인 컨트롤러 클래스 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UserVerifyController extends Controller
{
    private ResponseBuilder $response;
    private UserService $userService;
    public function __construct(ResponseBuilder $responseBuilder, UserService $userService)
    {
        $this->response = $responseBuilder;
        $this->userService = $userService;
    }
    /**
     * 로그인 컨트롤러 메인 메소드 입니다
     *
     * @param   App\Http\Requests\UserVerifyRequest $request 로그인 입력 검증 클래스 인스턴스
     * @throws  UnauthorizedException 로그인 실패 시 throw 됩니다.
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return
     */
    public function verifyUser(UserVerifyRequest $request): JsonResponse
    {
        $requestDatas = $request->validated();

        $canLogin = Auth::attempt([
            'email' => $requestDatas['email'],
            'password' => $requestDatas['password'],
        ]);

        if ($canLogin === false) {
            throw new UnauthorizedException(null, Response::HTTP_UNAUTHORIZED);
        }

        return $this->response->ok(
            $this->userService->createToken(Auth::user())
        );
    }
}
