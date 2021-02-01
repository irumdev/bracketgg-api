<?php

declare(strict_types=1);

namespace App\Services\Team;

use App\Models\Team\Team;
use App\Models\Team\Board\Category as TeamBoardCategory;
use App\Models\Team\Board\Article as TeamBoardArticle;
use App\Helpers\ResponseBuilder;
use App\Repositories\Team\BoardRespository;

class BoardService
{
    private BoardRespository $boardRepository;

    public function __construct(BoardRespository $boardRepository)
    {
        $this->boardRepository = $boardRepository;
    }

    public function getBoardArticlesByCategory(string $category, Team $team): array
    {
        $categories = $this->boardRepository->getArticleCategories($team);
        $articles = $this->boardRepository->getBoardArticlesByCategory($category, $team);
        return [
            'categories' => $categories->map(fn (TeamBoardCategory $category) => $this->categoryInfo($category)),
            'articles' => $articles,
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
        return [

            'id' => $article->id,
            'title' => $article->title,
            'content' => $article->content,
            'category' => $article->category_id,
            'writerInfo' => [
                'id' => $article->user_id,
            ],
            'seeCount' => $article->see_count,
            'likeCount' => $article->like_count,
            'unlikeCount' => $article->unlike_count,
            'commentCount' => $article->comment_count,
        ];
    }
}
