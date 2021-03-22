<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel\Board;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Common\BoardService as CommonBoardService;
use App\Http\Controllers\Common\Board\UploadArticleController as CommonBoardArticleUploadController;
use App\Http\Requests\Channel\Board\Article\Upload\ImageRequest as ChannelBoardArticleImageUploadRequest;
use Illuminate\Http\JsonResponse;

/**
 * 채널 게시글 업로드 컨트롤러 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UploadArticleController extends CommonBoardArticleUploadController
{
    /**
     * @var CommonBoardService $boardService
     */
    public CommonBoardService $boardService;

    public function __construct(CommonBoardService $boardService)
    {
        $this->boardService = $boardService;
    }

    /**
     * 게시글 이미지 업로드 컨트롤러 입니다.
     *
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 이미지 url
     */
    public function uploadArticleImage(ChannelBoardArticleImageUploadRequest $request): JsonResponse
    {
        return parent::uploadImage(
            collect([
                'uploadCategory' => $request->route('channelBoardCategory'),
                'uploadImage' => $request->validated()['article_image'],
            ])
        );
    }
}
