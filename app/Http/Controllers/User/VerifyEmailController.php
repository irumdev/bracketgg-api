<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailVerificationRequest;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseBuilder;

class VerifyEmailController extends Controller
{
    private UserService $userService;
    private ResponseBuilder $responseBuilder;

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
}
