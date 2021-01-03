<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseBuilder;
use App\Services\TeamService;
use App\Models\Team\Team;
use App\Models\User;
use App\Http\Requests\Team\SendInveitationCardRequest;

/**
 * 팀원 초대, 수락, 거절 관련 컨트롤러 클랴스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class InviteMemberController extends Controller
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

    public function sendInviteCard(Team $team, User $user, SendInveitationCardRequest $request)
    {
        return $this->responseBuilder->ok([
            'sendInviteCard' => $this->teamService->sendInviteCard($team, $user),
        ]);
    }
}