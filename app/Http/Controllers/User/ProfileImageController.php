<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Storage;

class ProfileImageController extends Controller
{
    public function getProfileImage(string $profileImage): BinaryFileResponse
    {
        $path = sprintf("app/profileImages/%s", $profileImage);
        abort_if(Storage::missing(sprintf("profileImages/%s", $profileImage)), 404);
        return response()->file(storage_path($path));
    }
}
