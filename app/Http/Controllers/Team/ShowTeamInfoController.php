<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team\Team;
use App\Services\TeamService;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseBuilder;

class ShowTeamInfoController extends Controller
{
    private TeamService $teamService;
    public function __construct(TeamService $teamService, ResponseBuilder $responseBuilder)
    {
        $this->teamService = $teamService;
        $this->responseBuilder = $responseBuilder;
    }

    // public function getTeam(Team $team)
    // {
    //     return
    // }
}
