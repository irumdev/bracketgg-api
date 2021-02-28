<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Models\Common\Board\BaseArticle;
use App\Repositories\Common\BoardRespository as BaseBoardRespository;
use App\Helpers\Image;
use App\Models\Common\Board\BaseCategory;
use App\Properties\Paginate;
use Illuminate\Database\Eloquent\Model;
use App\Wrappers\Type\ShowArticleByCategory as CategoryWithArticleType;

abstract class BoardService
{
    public BaseBoardRespository $boardRespository;

    public function getArticleByModel(BaseArticle $article): array
    {
        return $this->articleInfo(
            $this->boardRepository->getByModel($article)
        );
    }

    public function articleInfo(BaseArticle $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'content' => $article->content,
            'category' => $article->category_id,
            'writerInfo' => [
                'id' => $article->writer->id,
                'nickName' => $article->writer->nick_name,
                'profileImage' => empty($article->writer->profile_image) ? null : Image::toStaticUrl('profileImage', [
                    'profileImage' => $article->writer->profile_image
                ]),
            ],
            'seeCount' => $article->see_count,
            'likeCount' => $article->like_count,
            'unlikeCount' => $article->unlike_count,
            'commentCount' => $article->comment_count,
        ];
    }

    public function getBoardArticlesByCategory(CategoryWithArticleType $articlesInfo): array
    {
        $categories = $this->boardRepository->getArticleCategories($articlesInfo->model);
        $articles = $this->boardRepository->getBoardArticlesByCategory($articlesInfo->category, $articlesInfo->model);
        return [
            'categories' => $categories->map(fn (BaseCategory $category): array => $this->categoryInfo($category)),
            'articles' => $articles->simplePaginate($articlesInfo->perPage),
        ];
    }

    private function categoryInfo(BaseCategory $category): array
    {
        return [
            'name' => $category->name,
            'showOrder' => $category->show_order,
            'articleCount' => $category->article_count,
            'isPublic' => $category->is_public,
        ];
    }
}
