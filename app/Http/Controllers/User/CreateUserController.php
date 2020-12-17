<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Validation\UnauthorizedException;
use App\Helpers\ResponseBuilder;
use App\Services\UserService;
use App\Http\Requests\User\StoreRequest as UserStoreRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * 회원가입 컨트롤러 클래스 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class CreateUserController extends Controller
{
    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $response;

    /**
     * 유저 서비스 레이어
     * @var UserService $userService
     */
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
     *
     * @version 1.0.0
     * @return JsonResponse 성공 메세지, user attribute
     */
    public function createUser(UserStoreRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        $createdUser = $this->userService->createUser($requestData);
        $createdUser->sendEmailVerificationNotification();
        return $this->response->ok($createdUser, Response::HTTP_CREATED);
    }
}
