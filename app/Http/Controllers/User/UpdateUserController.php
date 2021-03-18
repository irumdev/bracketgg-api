<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\PasswordUpdateRequest;
use App\Http\Requests\User\ProfileImageUpdateRequest;
use App\Services\UserService;
use App\Helpers\ResponseBuilder;
use App\Helpers\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * 유저의 정보를 업데이트하는 컨트롤러 입니다.
 *
 * @author  irumdev <jklsj1252@gmail.com>
 * @version 1.0.0
 */
class UpdateUserController extends Controller
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
     * 현재 로그인 한 유저의 비밀번호를 변경하는 메소드입니다.
     *
     * @param PasswordUpdateRequest $request 유저 비밀번호 업데이트 요청 객체
     * @author  irumdev <jklsj1252@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 유저 비밀번호 업데이트 성공 여부
     */
    public function updatePassword(PasswordUpdateRequest $request): JsonResponse
    {
        $requestPassword = $request->validated()['password'];
        $result = $this->userService->updateUserPassword(Auth::user(), $requestPassword);

        return $this->responseBuilder->ok([
            'isSuccess' => $result
        ]);
    }

    /**
     * 현재 로그인 한 유저의 프로필 이미지를 변경하는 메소드입니다.
     *
     * @param ProfileImageUpdateRequest $request 유저 프로필 이미지 업데이트 요청 객체
     * @author  irumdev <jklsj1252@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 유저 프로필 이미지 업데이트 성공 여부
     */
    public function updateProfileImage(ProfileImageUpdateRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        $loginUser = Auth::user();
        $result = $this->userService->updateUserProfileImage($loginUser, $requestData);

        return $this->responseBuilder->ok([
            'isSuccess' => $result,
            'profileImage' => empty($loginUser->profile_image) ? null : Image::toStaticUrl('profileImage', [
                'profileImage' => $loginUser->profile_image
            ])
        ]);
    }
}
