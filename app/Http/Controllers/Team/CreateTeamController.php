<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\CreateRequest as CreateTeamRequest;
use App\Services\TeamService;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseBuilder;
use Illuminate\Http\JsonResponse;

/**
 * 팀을 생성하는 컨트롤러 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class CreateTeamController extends Controller
{
    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    /**
     * 팀 서비스 레이어
     * @var TeamService 팀 서비스 레이어
     */
    private TeamService $teamService;

    public function __construct(TeamService $teamService, ResponseBuilder $responseBuilder)
    {
        $this->teamService = $teamService;
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * 팀을 생성하는 메소드 입니다.
     *
     * @param CreateTeamRequest $request 팀 생성 요청 객체
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 팀 생성 후 생성된 팀 정보
     */
    public function createTeam(CreateTeamRequest $request): JsonResponse
    {
        $createTeam = $this->teamService->createTeam(array_merge($request->validated(), [
            'is_public' => 0,
            'logo_image' => null,
            'owner' => Auth::id(),
        ]));
        return $this->responseBuilder->ok($createTeam);
    }
}
