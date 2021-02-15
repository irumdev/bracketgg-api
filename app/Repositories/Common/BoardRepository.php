<?php

declare(strict_types=1);

namespace App\Repositories\Common;

use App\Factories\BoardFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
