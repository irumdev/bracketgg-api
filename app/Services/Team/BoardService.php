<?php

declare(strict_types=1);

namespace App\Services\Team;

use App\Helpers\Image;
use App\Models\Common\Board\BaseArticle;
use App\Repositories\Team\BoardRespository;
use App\Wrappers\Type\ShowArticleByCategory as CategoryWithArticleType;

use App\Services\Common\BoardService as CommonBoardService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Wrappers\Article\Article as ArticleWrapper;
use App\Wrappers\Article\Comment as TeamArticleCommentWrapper;

class BoardService extends CommonBoardService
{
    public function getArticleByModel(BaseArticle $article): array
    {
        return parent::getArticleByModel($article);
    }

    public function getBoardArticlesByCategory(CategoryWithArticleType $articlesInfo): array
    {
        return parent::getBoardArticlesByCategory(
            $articlesInfo
        );
    }

    /**
     * 팀 게시판에 게시글 이미지를 업로드 하는 서비스 메소드 입니다.
     *
     * @param Collection $uploadImageInfo 업로드 할 이미지 정보
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return string 게시글 저장후 랜딩 url
     */
    public function uploadBoardArticleImage(Collection $uploadImageInfo): string
    {
        return Image::toStaticUrl('team.article.image', [
            'teamArticleImage' => parent::uploadArticleImage('teamBoardArticleImages', $uploadImageInfo),
        ]);
    }

    /**
     * 팀 게시글을 업로드하는 서비스레이어 입니다.
     *
     * @param ArticleWrapper $article
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse
     */
    public function uploadArticle(ArticleWrapper $article): void
    {
        parent::uploadArticle($article);
    }

    public function updateCategory(Model $team, Collection $willUpdateItem): void
    {
        parent::updateCategory($team, $willUpdateItem);
    }

    /**
     * 채널 게시글에 댓글을 다는 메소드 입니다.
     *
     * @param ChannelArticleCommentWrapper $comment
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse
     */
    public function uploadComment(TeamArticleCommentWrapper $comment): void
    {
        parent::uploadComment($comment);
    }


    public function articleInfo(BaseArticle $article): array
    {
        return parent::articleInfo($article);
    }
}
