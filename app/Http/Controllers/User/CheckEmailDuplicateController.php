<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\User\Is\EmailDuplicateRequest as UserEmailDuplicateCheckRequest;
use App\Helpers\ResponseBuilder;
use Illuminate\Http\JsonResponse;

/**
 * 이메일 중복여부를 알려주는 컨트롤러 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class CheckEmailDuplicateController extends Controller
{
    /**
     * @var ResponseBuilder $responseBuilder
     */
    private ResponseBuilder $responseBuilder;

    public function __construct(ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * 이메일 중복 여부를 나타내주는 메소드 입니다.
     *
     * @param   App\Http\Requests\UserEmailDuplicateCheckRequest json리스폰스
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 이메일 중복여부
     */
    public function getUserEmailDuplicate(UserEmailDuplicateCheckRequest $request): JsonResponse
    {
        return $this->responseBuilder->ok([
            'isDuplicate' => false,
        ]);
    }
}
