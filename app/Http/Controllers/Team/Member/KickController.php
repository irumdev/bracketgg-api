<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseBuilder;
use App\Models\Team\Team;
use App\Models\User;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Team\Member\KickRequest;

class KickController extends Controller
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

    public function kickByUserId(KickRequest $request, Team $team, User $willKickUser): JsonResponse
    {
        return $this->responseBuilder->ok(
            ['makeTeamMemberKicked' => $this->teamService->kickByUserModel($team, $willKickUser)]
        );
    }
}
