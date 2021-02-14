<?php

declare(strict_types=1);

namespace App\Services\Team;

use App\Models\Team\Team;
use App\Models\Team\Board\Category as TeamBoardCategory;
use App\Models\Team\Board\Article as TeamBoardArticle;
use App\Properties\Paginate;
use App\Helpers\Image;
use App\Repositories\Team\BoardRespository;

use App\Services\Common\BoardService as CommonBoardService;

class BoardService extends CommonBoardService
{
    public function __construct(BoardRespository $boardRepository)
    {
        $this->boardRepository = $boardRepository;
    }

    public function getTeamArticleByModel(TeamBoardArticle $article): array
    {
        return $this->getArticleByModel($article);
    }

    public function getTeamBoardArticlesByCategory(string $category, Team $team): array
    {
        $categories = $this->boardRepository->getArticleCategories($team);
        $articles = $this->boardRepository->getBoardArticlesByCategory($category, $team);
        return [
            'categories' => $categories->map(fn (TeamBoardCategory $category) => $this->categoryInfo($category)),
            'articles' => $articles->simplePaginate(Paginate::TEAM_ARTICLE_COUNT),
        ];
    }

    private function categoryInfo(TeamBoardCategory $category): array
    {
        return [
            'name' => $category->name,
            'showOrder' => $category->show_order,
            'articleCount' => $category->article_count,
            'isPublic' => $category->is_public,
        ];
    }

    public function articleInfo(TeamBoardArticle $article): array
    {
        return $this->info($article);
    }
}
