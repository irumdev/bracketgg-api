<?php

declare(strict_types=1);

namespace App\Services\Channel;

use App\Helpers\Image;
use App\Repositories\Channel\BoardRespository;

use App\Services\Common\BoardService as CommonBoardService;
use App\Wrappers\Type\ShowArticleByCategory as CategoryWithArticleType;
use App\Models\Common\Board\BaseArticle;
use Illuminate\Support\Collection;

class BoardService extends CommonBoardService
{
    public function __construct(BoardRespository $boardRepository)
    {
        $this->boardRepository = $boardRepository;
    }

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
     * 채널 게시판에 게시글 이미지를 업로드 하는 서비스 메소드 입니다.
     *
     * @param Collection $uploadImageInfo 업로드 할 이미지 정보
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return string 게시글 저장후 랜딩 url
     */
    public function uploadBoardArticleImage(Collection $uploadImageInfo): string
    {
        return Image::toStaticUrl('channel.article.image', [
            'channelArticleImage' => parent::uploadArticleImage('channelBoardArticleImages', $uploadImageInfo),
        ]);
    }

    public function articleInfo(BaseArticle $article): array
    {
        return parent::articleInfo($article);
    }
}
