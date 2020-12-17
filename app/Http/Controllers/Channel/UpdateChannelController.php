<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Channel\UpdateRequest as UpdateChannelRequest;
use App\Http\Requests\Channel\UpdateBannerImageRequest;
use App\Http\Requests\Channel\UpdateLogoImageRequest;

use App\Services\ChannelService;
use App\Helpers\ResponseBuilder;
use App\Models\Channel\Channel;
use Illuminate\Http\JsonResponse;

/**
 * 채널정보를 업데이트 하는 컨트롤러 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UpdateChannelController extends Controller
{
    /**
     * 채널 서비스 레이어
     * @var ChannelService $channelService
     */
    private ChannelService $channelService;

    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    public function __construct(ChannelService $channelService, ResponseBuilder $responseBuilder)
    {
        $this->channelService = $channelService;
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * 이미지를 뺀 채널정보를 업데이트 하는 메소드 입니다.
     *
     * @param UpdateChannelRequest $request 업데이트 요청 객체
     * @param Channel $channel 업데이트 할 채널
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 채널정보 업데이트 성공여부
     */
    public function updateChannelInfoWithOutImage(UpdateChannelRequest $request, Channel $channel): JsonResponse
    {
        $updateResult = $this->channelService->updateChannelInfoWithOutImage(
            $channel,
            $request->validated()
        );
        return $this->successResponse($updateResult);
    }

    /**
     * 배너이미지를 업데이트 또는 생성하는 컨트롤러 메소드 입니다.
     *
     * @param UpdateBannerImageRequest $request 배너이미지 업데이트 요청객체
     * @param Channel $channel 업데이트 또는 생성 해야할 채널
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 배너이미지 업데이트 성공 여부
     */
    public function updateBannerImage(UpdateBannerImageRequest $request, Channel $channel): JsonResponse
    {
        $bannerInfo = $request->validated();
        if ($request->has('banner_image_id')) {
            $result = $this->channelService->updateBannerImage($bannerInfo, $channel);
        } else {
            $result = $this->channelService->createBannerImage($bannerInfo, $channel);
        }
        return $this->successResponse($result);
    }

    /**
     * 채널 로고이미지를 생성 또는 업데이트 하는 컨트롤러 메소드 입니다.
     *
     * @param UpdateLogoImageRequest $request 로고이미지 업데이트 요청객체
     * @param Channel $channel 업데이트 또는 생성 해야할 채널
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 로고이미지 업데이트 성공 여부
     */
    public function updateLogoImage(UpdateLogoImageRequest $request, Channel $channel)
    {
        $result = $this->channelService->updateLogoImage($request->validated(), $channel);
        return $this->successResponse($result);
    }

    /**
     * 성공여부를 json response하기위한 공통 메소드 입니다.
     *
     * @param bool $isSuccess 성공여부
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 업데이트 성공 여부
     */
    private function successResponse(bool $isSuccess): JsonResponse
    {
        return $this->responseBuilder->ok([
            'isSuccess' => $isSuccess
        ]);
    }
}
