<?php

declare(strict_types=1);

namespace App\Services\Channel;

use App\Models\Channel\Channel;
use App\Models\Channel\Board\Category as ChannelBoardCategory;
use App\Models\Channel\Board\Article as ChannelBoardArticle;
use App\Helpers\ResponseBuilder;
use App\Properties\Paginate;
use App\Repositories\Channel\BoardRespository;

use App\Services\Common\BoardService as CommonBoardService;
use App\Wrappers\Type\ShowArticleByCategory as CategoryWithArticleType;
use App\Models\Common\Board\BaseArticle;

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

    public function articleInfo(BaseArticle $article): array
    {
        return parent::articleInfo($article);
    }
}
