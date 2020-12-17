<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * 로그아웃을 하는 컨트롤러 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UserLogoutController extends Controller
{
    /**
     * 로그아웃을 하는 메소드 입니다.
     *
     * @param   Illuminate\Http\Request $request 요청 객체
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return Illuminate\Http\Response 204 status를 클라이언트에게 리턴합니다.
     */
    public function logout(Request $request): Response
    {
        $user = $request->user();
        $user->tokens()->where(
            'id',
            $user->currentAccessToken()->id,
        )->delete();
        return response()->noContent();
    }
}
