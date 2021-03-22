<?php

declare(strict_types=1);

namespace App\Repositories\Common;

use App\Exceptions\FileSaveFailException;
use App\Factories\BoardFactory;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

use App\Models\Common\Board\BaseCategory;

class BoardRespository extends BoardFactory
{
    public function getArticleCategories(Model $model): Collection
    {
        return $this->getCategories($model);
    }

    public function getBoardArticlesByCategory(BaseCategory $category, Model $model): HasMany
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

    public function uploadArticleImage(string $storagePath, Collection $uploadInfo): string
    {
        throw_unless(
            $uploadInfo['uploadImage']->store($storagePath),
            new FileSaveFailException()
        );
        return $uploadInfo['uploadImage']->hashName();
    }

    public function latestArticlesCount(Model $model): int
    {
        return $this->latestArticles($model)->count();
    }

    public function updateCategory(Model $teamOrChannel, Collection $willUpdateItem): void
    {
        parent::updateCategory($teamOrChannel, $willUpdateItem);
    }
}
