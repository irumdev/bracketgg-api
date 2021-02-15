<?php

declare(strict_types=1);

namespace App\Services\Team;

use App\Models\Common\Board\BaseArticle;
use App\Repositories\Team\BoardRespository;
use App\Wrappers\Type\ShowArticleByCategory as CategoryWithArticleType;

use App\Services\Common\BoardService as CommonBoardService;

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
