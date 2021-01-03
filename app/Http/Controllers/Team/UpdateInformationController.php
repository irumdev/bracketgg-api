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

/**
 * 팀 정보를 업데이트 해주는 컨트롤러 메소드 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UpdateInformationController extends Controller
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
     * 팀의 배너이미지를 업데이트 해주는 메소드 입니다.
     *
     * @param UpdateBannerImageRequest $request 배너업데이트 요청 객체
     * @param Team $team 업데이트 할 팀
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 팀 배너이미지 업데이트 성공 여부
     */
    public function updateBannerImage(UpdateBannerImageRequest $request, Team $team): JsonResponse
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

    /**
     * 팀의 로고이미지를 업데이트 해주는 메소드 입니다.
     *
     * @param UpdateLogoImageRequest $request 로고업데이트 요청 객체
     * @param Team $team 업데이트 할 팀
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 팀 로고이미지 업데이트 성공 여부
     */
    public function updateLogoImage(UpdateLogoImageRequest $request, Team $team): JsonResponse
    {
        $validData = $request->validated();
        $updateResult = $this->teamService->updateLogoImage($team, $validData);
        return $this->responseBuilder->ok([
            'isSuccess' => $updateResult,
        ]);
    }

    /**
     * 이미지를 제외한 팀 정보를 업데이트 해주는 컨트롤러 메소드 입니다.
     * @param Team $team 업데이트 할 팀
     * @param UpdateInfoWithOutBannerRequest $request 업데이트 요청 객체
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 정보 업데이트 성공여부
     */
    public function updateInfo(Team $team, UpdateInfoWithOutBannerRequest $request): JsonResponse
    {
        $validatedResult = $request->validated();
        return $this->responseBuilder->ok($this->teamService->updateInfo($team, $validatedResult));
    }
}
