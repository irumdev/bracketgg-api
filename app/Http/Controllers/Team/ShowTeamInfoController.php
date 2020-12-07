<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team;

use App\Models\Team\Team;
use App\Services\TeamService;
use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * 팀정보를 조회하는 컨트롤러 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ShowTeamInfoController extends Controller
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
     * 팀 슬러그로 팀 정보를 조회하는 컨트롤러 메소드 입니다
     * @param Team $team 조회 할 팀
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 팀 정보
     */
    public function getInfo(Team $team): JsonResponse
    {
        return $this->responseBuilder->ok($this->teamService->get($team));
    }
}
