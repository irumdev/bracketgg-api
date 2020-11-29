<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team;

use App\Models\Team\Team;
use App\Services\TeamService;
use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ShowTeamInfoController extends Controller
{
    private TeamService $teamService;
    public function __construct(TeamService $teamService, ResponseBuilder $responseBuilder)
    {
        $this->teamService = $teamService;
        $this->responseBuilder = $responseBuilder;
    }

    public function getInfo(Team $team): JsonResponse
    {
        return $this->responseBuilder->ok($this->teamService->get($team));
    }
}
