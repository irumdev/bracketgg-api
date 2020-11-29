<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\CreateRequest as CreateTeamRequest;
use App\Services\TeamService;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseBuilder;
use Illuminate\Http\JsonResponse;

class CreateTeamController extends Controller
{
    private TeamService $teamService;
    public function __construct(TeamService $teamService, ResponseBuilder $responseBuilder)
    {
        $this->teamService = $teamService;
        $this->responseBuilder = $responseBuilder;
    }

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
