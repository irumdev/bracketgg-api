<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Models\User;
use App\Helpers\ResponseBuilder;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ShowUserController extends Controller
{
    private ResponseBuilder $responseBuilder;
    private UserService $userService;

    public function __construct(UserService $userService, ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
        $this->userService = $userService;
    }

    public function getCurrent(): JsonResponse
    {
        return $this->responseBuilder->ok(
            $this->userService->info(Auth::user())
        );
    }

    /**
     * @waiting
     * 인덱스별 유저 정보 조회
     */
    public function getById(User $user)
    {
        return $this->responseBuilder->ok(
            $this->userService->info($user)
        );
    }
}
