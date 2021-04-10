<?php

declare(strict_types=1);

namespace App\Contracts\Board;

use App\Models\Common\Board\BaseArticle;
use Illuminate\Database\Eloquent\Model;
use App\Wrappers\Type\ShowArticleByCategory as CategoryWithArticleType;
use Illuminate\Support\Collection;
use App\Wrappers\Article\Article as ArticleWrapper;
use App\Wrappers\Article\Comment as ArticleCommentWrapper;

interface Service
{
    public function uploadArticleImage(string $uploadPath, Collection $uploadImageInfo): string;
    public function uploadArticle(ArticleWrapper $article): void;
    public function uploadComment(ArticleCommentWrapper $comment): void;

    public function getArticleByModel(BaseArticle $article): array;
    public function articleInfo(BaseArticle $article): array;
    public function getBoardArticlesByCategory(CategoryWithArticleType $articlesInfo): array;

    public function updateCategory(Model $teamOrChannel, Collection $willUpdateItem): void;
}
