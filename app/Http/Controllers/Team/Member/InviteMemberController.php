<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseBuilder;
use App\Services\TeamService;
use App\Models\Team\Team;
use App\Models\User;
use App\Models\Team\InvitationCard;
use App\Wrappers\Type\TeamInviteCard as TeamInviteCardType;
use App\Http\Requests\Team\Invite\InviteRequest;
use App\Http\Requests\Team\Invite\NormalUserJoinRequest;

use App\Http\Requests\Team\Invite\AcceptRequest;
use App\Http\Requests\Team\Invite\RejectRequest;
use App\Models\NotificationMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

use function App\Events\teamInviteResolver;

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
     * @var ResponseBuilder $responseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    /**
     * 팀 서비스 레이어
     * @var TeamService $teamService 팀 서비스 레이어
     */
    private TeamService $teamService;

    public function __construct(TeamService $teamService, ResponseBuilder $responseBuilder)
    {
        $this->teamService = $teamService;
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * 팀장이 일반 유저에게 초대장 보내는 메소드 입니다.
     *
     * @param Team $team 일반유저 초대 요청하는 팀
     * @param User $user 초대 받는 일반 유저
     * @param InviteRequest $request 벨러데이션 객체
     * @throws
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 초대장 결과
     */
    public function sendInviteCardFromTeamOwner(Team $team, User $user, InviteRequest $request): JsonResponse
    {
        return $this->responseBuilder->ok([
            'sendInviteCard' => $this->teamService->sendInviteCard(
                new TeamInviteCardType(
                    team: $team,
                    user: $user,
                    fromType: InvitationCard::FROM_TEAM_OWNER,
                )
            ),
        ]);
    }

    /**
     * 팀장이 가입신청한 유저를
     * 팀원으로 수락하는 메소드
     *
     * @param Team $team
     * @param User $user
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse
     */
    public function acceptJoin(Team $team, User $user, AcceptRequest $request): JsonResponse
    {
        $teamInviteResult = $this->responseBuilder->ok([
            'markTeamInvited' => $this->teamService->acceptInviteCard($team, $user),
        ]);

        return $teamInviteResult;
    }

    /**
     * 팀장이 가입신청한 유저를
     * 팀원으로 수락하는 메소드
     *
     * @param Team $team
     * @param User $user
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse
     */
    public function rejectJoin(Team $team, User $user, RejectRequest $request): JsonResponse
    {
        $rejectTeamJoinRequestReject = $this->responseBuilder->ok([
            'markTeamJoinRequestRejected' => $this->teamService->rejectInviteCard($team, $user),
        ]);

        return $rejectTeamJoinRequestReject;
    }


    /**
     * 일반 유저가 팀에 팀원 가입 요청하는 메소드 입니다.
     *
     * @param Team $team 일반유저가 가입하고 싶어하는 팀
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 초대장 결과
     */
    public function sendInviteCardFromNormalUser(Team $team, NormalUserJoinRequest $request)
    {
        return $this->responseBuilder->ok([
            'sendInviteCard' => $this->teamService->sendInviteCard(
                new TeamInviteCardType(
                    team: $team,
                    user: Auth::user(),
                    fromType: InvitationCard::FROM_NORMAL_USER,
                )
            ),
        ]);
    }

    /**
     * 현재 로그인 한 유저가 팀원으로 들어오라는
     * 제안을 수락하는 메소드
     *
     * @param Team $team
     * @param AcceptRequest $request
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 수락 성공 결과
     */
    public function acceptInviteCard(Team $team, AcceptRequest $request): JsonResponse
    {
        $teamInviteResule = $this->responseBuilder->ok([
            'markTeamInvited' => $this->teamService->acceptInviteCard($team, $currentUser = Auth::user()),
        ]);
        event(teamInviteResolver(
            $team,
            $currentUser->id,
            NotificationMessage::ACCEPT_INVITE_TEAM
        ));

        return $teamInviteResule;
    }

    /**
     * 현재 로그인 한 유저가 팀원으로 들어오라는
     * 제안을 거절하는 메소드
     *
     * @param Team $team
     * @param RejectRequest $request
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 거절 성공 결과
     */
    public function rejectInviteCard(Team $team, RejectRequest $request): JsonResponse
    {
        $rejectTeamOperResult = $this->responseBuilder->ok([
            'markTeamOperRejected' => $this->teamService->rejectInviteCard($team, $currentUser = Auth::user()),
        ]);

        event(teamInviteResolver(
            $team,
            $currentUser->id,
            NotificationMessage::REJECT_INVITE_TEAM
        ));

        return $rejectTeamOperResult;
    }
}
