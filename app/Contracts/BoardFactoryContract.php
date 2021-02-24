<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\Common\Board\BaseArticle;
use App\Models\Common\Board\BaseCategory;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface BoardFactoryContract
{
    public function getCategories(Model $model): Collection;
    public function getByModel(BaseArticle $article): BaseArticle;
    public function getArticlesFromCategory(BaseCategory $category, Model $model): HasMany;
}
