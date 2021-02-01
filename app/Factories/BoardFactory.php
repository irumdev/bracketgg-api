<?php

declare(strict_types=1);

namespace App\Factories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoardFactory
{
    public function getCategories(Model $model): Collection
    {
        return $model->boardCategories;
    }

    public function getArticlesFromCategory(string $category, Model $model): HasMany
    {
        return $model->boardCategories()->where('name', $category)->firstOr(function () {
            throw (new ModelNotFoundException())->setModel(ChannelBoardCategory::class);
        })->articles();
    }
}
