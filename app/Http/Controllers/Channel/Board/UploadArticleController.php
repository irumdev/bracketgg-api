<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel\Board;

use App\Services\Common\BoardService as CommonBoardService;
use App\Http\Controllers\Common\Board\UploadArticleController as CommonBoardArticleUploadController;
use App\Http\Requests\Channel\Board\Article\Upload\ImageRequest as ChannelBoardArticleImageUploadRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Channel\Board\Article\Upload\ArticleRequest as UploadArticleRequest;
use App\Http\Requests\Channel\Board\Article\Upload\CommentRequest as UploadArticleCommentRequest;
use App\Wrappers\Article\Article as ChannelArticleWrapper;
use App\Wrappers\Article\Comment as ChannelArticleCommentWrapper;
use Illuminate\Support\Facades\Auth;
use App\Contracts\Board\Service as BoardServiceContract;

/**
 * 채널 게시글 업로드 컨트롤러 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UploadArticleController extends CommonBoardArticleUploadController
{
    public function __construct(public BoardServiceContract $boardService)
    {
        $this->boardService = $boardService;
    }

    /**
     * 게시글 이미지 업로드 컨트롤러 입니다.
     *
     * @param ChannelBoardArticleImageUploadRequest $request 이미지 업로드 벨러데이션 요청 객체
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

    /**
     * 채널 게시글 업로드 컨트롤러 입니다.
     *
     * @param
     * @throws
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse
     */
    public function uploadChannelArticle(UploadArticleRequest $request): JsonResponse
    {
        $validatedArticle = $request->validated();
        $willUploadArticle = new ChannelArticleWrapper(
            writer: Auth::user(),
            articleOwnerGroup: $request->route('slug'),
            title: $validatedArticle['title'],
            category: $request->route('channelBoardCategory'),
            content: $validatedArticle['article']
        );

        return parent::uploadArticle($willUploadArticle);
    }

    /**
     * 채널 게시글 댓글 업로드 컨트롤러 입니다.
     *
     * @param UploadArticleCommentRequest $request
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 댓글 업로드 성공결과
     */
    public function uploadChannelArticleComment(UploadArticleCommentRequest $request): JsonResponse
    {
        $validatedComment = $request->validated();

        $comment = new ChannelArticleCommentWrapper(
            article: $request->route('channelArticle'),
            writer: Auth::user(),
            articleOwnerGroup: $request->route('slug'),
            content: $validatedComment['content'],
            parent: $validatedComment['parent_id'] ?? null,
        );

        return parent::uploadComment($comment);
    }
}
