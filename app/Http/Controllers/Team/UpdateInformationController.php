<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Team\UpdateInfoWithOutBannerRequest;
use App\Models\Team\Team;
use App\Services\TeamService;
use App\Helpers\ResponseBuilder;
use Illuminate\Http\JsonResponse;

class UpdateInformationController extends Controller
{
    private TeamService $teamService;
    private ResponseBuilder $responseBuilder;

    public function __construct(TeamService $teamService, ResponseBuilder $responseBuilder)
    {
        $this->teamService = $teamService;
        $this->responseBuilder = $responseBuilder;
    }

    public function updateBannerImages()
    {
    }

    public function updateInfo(Team $team, UpdateInfoWithOutBannerRequest $request): JsonResponse
    {
        $validatedResult = $request->validated();

        return $this->responseBuilder->ok($this->teamService->updateInfo($team, $validatedResult));
    }
}
