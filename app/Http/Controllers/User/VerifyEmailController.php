<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\EmailVerificationRequest;
use App\Http\Requests\User\ResendEmailVerificationRequest;

use App\Services\UserService;
use App\Helpers\ResponseBuilder;

class VerifyEmailController extends Controller
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

    /**
     * 유저 인스턴스
     * @var User $user;
     */
    private User $user;

    public function __construct(ResponseBuilder $responseBuilder, UserService $userService)
    {
        $this->responseBuilder = $responseBuilder;
        $this->userService = $userService;
    }

    /**
     * 이메일 유효성을 업데이트 해주고 그 결과를 알려주는 매소드 입니다.
     * @param EmailVerificationRequest $request 이메일 유효성 검증 요청 객체
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 이메일 인증 결과
     */
    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        $markEmailAsVerifiedResult = $this->userService->markEmailAsVerified($request->route('id'));
        return $this->responseBuilder->ok([
            'markEmailAsVerified' => $markEmailAsVerifiedResult,
        ]);
    }

    /**
     * 인증 이메일 재발송 컨트롤러 메소드 입니다.
     * @param ResendEmailVerificationRequest $request 이메일 재전송 요청 객체
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse
     */
    public function resendEmail(ResendEmailVerificationRequest $request): JsonResponse
    {
        $this->user = Auth::user();
        $this->user->sendEmailVerificationNotification();
        return $this->responseBuilder->ok([
            'sendEmailVerification' => true,
        ]);
    }
}
