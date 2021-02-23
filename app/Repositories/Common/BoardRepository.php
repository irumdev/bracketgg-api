<?php

declare(strict_types=1);

namespace App\Repositories\Common;

use App\Factories\BoardFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class BoardRespository extends BoardFactory
{
    public function getArticleCategories(Model $model): Collection
    {
        return $this->getCategories($model);
    }

    public function getBoardArticlesByCategory(string $category, Model $model): HasMany
    {
        return $this->getArticlesFromCategory($category, $model);
    }

    public function latestArticles(Model $model): HasMany
    {
        return $model->articles()->whereBetween(Model::CREATED_AT, [
            Carbon::now()->format('Y-m-d 00:00:00'),
            Carbon::now()->format('Y-m-d 23:59:59'),
        ]);
    }

    public function latestArticlesCount(Model $model): int
    {
        return $this->latestArticles($model)->count();
    }
}
