<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseBuilder;
use App\Services\ChannelService;
use App\Models\Channel\Channel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Storage;

/**
 * 채널 아이디로 채널의 정보들을 보여주는 컨트롤러 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ShowChannelController extends Controller
{
    /**
     * @var ResponseBuilder $responseBuilder
     * @var ChannelService $channelService
     */
    private ResponseBuilder $response;
    private ChannelService $channelService;

    public function __construct(ResponseBuilder $responseBuilder, ChannelService $channelService)
    {
        $this->response = $responseBuilder;
        $this->channelService = $channelService;
    }

    /**
     * 채널 아이디로 채널의 정보를 조회하는 메소드 입니다.
     *
     * @param   App\Models\Channel $channel
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 채널정보들
     */
    public function getChannelById(Channel $channel)
    {
        return $this->response->ok(
            $this->channelService->findChannelById($channel->id)
        );
    }

    /**
     * 이미지 이름에 해당하는 파일을 리턴하는 메소드 입니다.
     *
     * @param  string $logoImage 이미지 이름
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 성공 리스폰스
     */
    public function getChannelLogoImage(string $logoImage): BinaryFileResponse
    {
        $path = sprintf("app/profileImages/%s", $logoImage);
        abort_if(Storage::missing(sprintf("profileImages/%s", $logoImage)), 404);
        return response()->file(storage_path($path));
    }
}
