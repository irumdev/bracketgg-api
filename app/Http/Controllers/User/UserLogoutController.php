<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class UserLogoutController extends Controller
{
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->where(
            'id',
            $user->currentAccessToken()->id,
        )->delete();
        return response()->noContent();
    }
}
