<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team;

use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\CheckNameExistsRequest;
use Illuminate\Http\JsonResponse;

/**
 * 팀 이름 중복여부를 판별하기 위한 컨트롤러 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class CheckTeamNameExistsController extends Controller
{
    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    public function __construct(ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * 팀이름 중복여부를 판단하는 메소드 입니다.
     * 해당 메소드는 validate에서 필터링을 해줍니다.
     *
     * @param CheckNameExistsRequest $request 요청객체
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 중복 여부
     */
    public function nameAlreadyExists(CheckNameExistsRequest $request): JsonResponse
    {
        return $this->responseBuilder->ok([
            'isDuplicate' => false,
        ]);
    }
}
