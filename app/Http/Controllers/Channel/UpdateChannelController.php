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

class UpdateChannelController extends Controller
{
    private ChannelService $channelService;
    private ResponseBuilder $responseBuilder;
    public function __construct(ChannelService $channelService, ResponseBuilder $responseBuilder)
    {
        $this->channelService = $channelService;
        $this->responseBuilder = $responseBuilder;
    }

    public function updateChannelInfoWithOutImage(UpdateChannelRequest $request, Channel $channel): JsonResponse
    {
        $updateResult = $this->channelService->updateChannelInfoWithOutImage(
            $channel,
            $request->validated()
        );
        return $this->successResponse($updateResult);
    }

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

    public function updateLogoImage(UpdateLogoImageRequest $request, Channel $channel)
    {
        $result = $this->channelService->updateLogoImage($request->validated(), $channel);
        return $this->successResponse($result);
    }

    private function successResponse(bool $isSuccess): JsonResponse
    {
        return $this->responseBuilder->ok([
            'isSuccess' => $isSuccess
        ]);
    }
}
