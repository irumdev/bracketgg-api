<?php

declare(strict_types=1);

namespace App\Http\Controllers\Common\Board;

use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use App\Services\Common\BoardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Wrappers\Article\Article as ArticleWrapper;
use App\Wrappers\Article\Comment as ChannelArticleCommentWrapper;

/**
 * 게시글 업로드 공통 컨트롤러 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UploadArticleController extends Controller
{
    /**
     * 게시글 이미지 업로드 컨트롤러 메소드 입니다.
     *
     * @param
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 이미지 url
     */
    public function uploadImage(Collection $uploadImageInformation): JsonResponse
    {
        $uploadedImageUrl = $this->boardService->uploadBoardArticleImage(
            $uploadImageInformation
        );

        return (new ResponseBuilder())->ok([
            'imageUrl' => $uploadedImageUrl,
        ]);
    }

    public function uploadArticle(ArticleWrapper $article): JsonResponse
    {
        $this->boardService->uploadArticle($article);
        return (new ResponseBuilder())->ok([
            'isSuccess' => true,
        ]);
    }

    /**
     * 채널 게시글 댓글 업로드 공통 컨트롤러 입니다.
     *
     * @param UploadArticleCommentRequest $request
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 댓글 업로드 성공결과
     */
    public function uploadComment(ChannelArticleCommentWrapper $comment): JsonResponse
    {
        $this->boardService->uploadComment($comment);

        return (new ResponseBuilder())->ok([
            'isSuccess' => true
        ]);
    }
}
