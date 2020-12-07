<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Storage;

/**
 * 프로필 이미지를 조회하는 컨트롤러 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ProfileImageController extends Controller
{
    /**
     * 이미지 이름에 해당하는 파일을 리턴하는 메소드 입니다.
     *
     * @param   string $profileImage 이미지 이름
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 성공 리스폰스
     */
    public function getProfileImage(string $profileImage): BinaryFileResponse
    {
        $path = sprintf("app/profileImages/%s", $profileImage);
        abort_if(Storage::missing(sprintf("profileImages/%s", $profileImage)), 404);
        return response()->file(storage_path($path));
    }
}
