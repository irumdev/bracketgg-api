<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailVerificationRequest;
use App\Http\Requests\ResendEmailVerificationRequest;

use App\Services\UserService;
use App\Helpers\ResponseBuilder;

class VerifyEmailController extends Controller
{
    /**
     * @var ResponseBuilder $response
     */
    private ResponseBuilder $responseBuilder;

    /**
     * @var UserService $userService
     */
    private UserService $userService;

    public function __construct(ResponseBuilder $responseBuilder, UserService $userService)
    {
        $this->responseBuilder = $responseBuilder;
        $this->userService = $userService;
    }

    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        $markEmailAsVerifiedResult = $this->userService->markEmailAsVerified($request->route('id'));

        return $this->responseBuilder->ok([
            'markEmailAsVerified' => $markEmailAsVerifiedResult,
        ]);
    }

    public function resendEmail(ResendEmailVerificationRequest $request)
    {
        $user = Auth::user();
        $user->sendEmailVerificationNotification();
        return $this->responseBuilder->ok([
            'sendEmailVerification' => true,
        ]);
    }
}
