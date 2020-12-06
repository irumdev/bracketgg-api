<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team\Team;
use App\Services\TeamService;
use App\Helpers\ResponseBuilder;
use Illuminate\Http\JsonResponse;

use App\Http\Requests\Team\UpdateInfoWithOutBannerRequest;
use App\Http\Requests\Team\UpdateBannerImageRequest;
use App\Http\Requests\Team\UpdateLogoImageRequest;

class UpdateInformationController extends Controller
{
    private TeamService $teamService;
    private ResponseBuilder $responseBuilder;

    public function __construct(TeamService $teamService, ResponseBuilder $responseBuilder)
    {
        $this->teamService = $teamService;
        $this->responseBuilder = $responseBuilder;
    }

    public function updateBannerImage(UpdateBannerImageRequest $request, Team $team)
    {
        $validatedResult = $request->validated();
        if ($request->has('banner_image_id')) {
            $updateResult = $this->teamService->updateBannerImage($team, $validatedResult);
        } else {
            $updateResult = $this->teamService->createBannerImage($team, $validatedResult);
        }
        return $this->responseBuilder->ok([
            'isSuccess' => $updateResult
        ]);
    }

    public function updateLogoImage(UpdateLogoImageRequest $request, Team $team)
    {
        $validData = $request->validated();
        $updateResult = $this->teamService->updateLogoImage($team, $validData);
        return $this->responseBuilder->ok([
            'isSuccess' => $updateResult,
        ]);
    }

    public function updateInfo(Team $team, UpdateInfoWithOutBannerRequest $request): JsonResponse
    {
        $validatedResult = $request->validated();
        return $this->responseBuilder->ok($this->teamService->updateInfo($team, $validatedResult));
    }
}
