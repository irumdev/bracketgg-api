<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team\Board;

use App\Services\Common\BoardService as CommonBoardService;
use App\Http\Requests\Team\Board\Article\Upload\ArticleRequest;
use App\Http\Controllers\Common\Board\UploadArticleController as CommonBoardArticleUploadController;
use App\Http\Requests\Team\Board\Article\Upload\ImageRequest as TeamBoardArticleImageUploadRequest;
use App\Http\Requests\Team\Board\Article\Upload\CommentRequest as UploadArticleCommentRequest;
use App\Wrappers\Article\Comment as TeamArticleCommentWrapper;

use Illuminate\Http\JsonResponse;
use App\Wrappers\Article\Article as TeamArticleWrapper;
use Illuminate\Support\Facades\Auth;
use App\Contracts\Board\Service as BoardServiceContract;

/**
 * 팀 게시글 업로드 컨트롤러 입니다.
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
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 이미지 url
     */
    public function uploadArticleImage(TeamBoardArticleImageUploadRequest $request): JsonResponse
    {
        return parent::uploadImage(
            collect([
                'uploadCategory' => $request->route('teamBoardCategory'),
                'uploadImage' => $request->validated()['article_image'],
            ])
        );
    }

    /**
     * 게시글 업로드 컨트롤러 메소드 입니다.
     *
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse
     */
    public function uploadTeamArticle(ArticleRequest $request): JsonResponse
    {
        $validatedArticle = $request->validated();
        $willUploadArticle = new TeamArticleWrapper(
            writer: Auth::user(),
            articleOwnerGroup: $request->route('teamSlug'),
            title: $validatedArticle['title'],
            category: $request->route('teamBoardCategory'),
            content: $validatedArticle['article']
        );

        return parent::uploadArticle($willUploadArticle);
    }

    /**
     * 팀 게시글 댓글 업로드 컨트롤러 입니다.
     *
     * @param UploadArticleCommentRequest $request
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 댓글 업로드 성공결과
     */
    public function uploadTeamArticleComment(UploadArticleCommentRequest $request): JsonResponse
    {
        $validatedComment = $request->validated();
        $comment = new TeamArticleCommentWrapper(
            article: $request->route('teamArticle'),
            writer: Auth::user(),
            articleOwnerGroup: $request->route('teamSlug'),
            content: $validatedComment['content'],
            parent: $validatedComment['parent_id'] ?? null,
        );
        return parent::uploadComment($comment);
    }
}
