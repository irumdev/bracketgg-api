<?php

declare(strict_types=1);

namespace App\Factories;

use App\Contracts\BoardFactoryContract;
use App\Exceptions\DBtransActionFail;
use App\Models\Common\Board\BaseArticle;
use App\Models\ArticleViewLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Models\Common\Board\BaseCategory;

class BoardFactory implements BoardFactoryContract
{
    public function getCategories(Model $model): Collection
    {
        return $model->boardCategories;
    }

    public function getArticlesFromCategory(BaseCategory $category, Model $model): HasMany
    {
        return $category->articles();
    }

    public function getByModel(BaseArticle $article): BaseArticle
    {
        return $article->with($article->eagerRelation)
                       ->where('id', $article->id)
                       ->firstOr(fn () => (new ModelNotFoundException())->setModel(get_class($article)));
    }
}
