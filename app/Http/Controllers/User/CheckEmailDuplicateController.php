<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UserEmailDuplicateCheckRequest;
use App\Helpers\ResponseBuilder;

class CheckEmailDuplicateController extends Controller
{
    private ResponseBuilder $responseBuilder;
    public function __construct(ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
    }
    public function getUserEmailDuplicate(UserEmailDuplicateCheckRequest $request)
    {
        return $this->responseBuilder->ok([
            'isDuplicate' => false,
        ]);
    }
}
