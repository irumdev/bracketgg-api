<?php

declare(strict_types=1);

namespace App\Factories;

use App\Contracts\BoardFactoryContract;
use App\Exceptions\DBtransActionFail;
use App\Models\Common\Board\BaseArticle;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Illuminate\Support\Collection as DataCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Models\Common\Board\BaseCategory;

class BoardFactory implements BoardFactoryContract
{
    public function getCategories(Model $model): DbCollection
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
                       ->firstOr(function () use ($article): void {
                           throw (new ModelNotFoundException())->setModel(get_class($article));
                       });
    }

    public function updateCategory(Model $teamOrChannel, DataCollection $willUpdateItems): void
    {
        $boardCategories = $teamOrChannel->boardCategories;

        $boardCategoryIds = $boardCategories->map(fn (BaseCategory $category): int => $category->id);

        $willDeleteItems = $boardCategoryIds->diff(
            $willUpdateItems->map(fn (array $willUpdateItem): int => $willUpdateItem['id'] ?? -1)
        );

        DB::transaction(function () use ($willDeleteItems, $willUpdateItems, $teamOrChannel): void {
            $deleteResult = $teamOrChannel->boardCategories()
                                          ->whereIn('id', $willDeleteItems->values())
                                          ->delete();

            throw_if($deleteResult !== $willDeleteItems->count(), new DBtransActionFail());

            $boardCategories = $teamOrChannel->boardCategories;

            $willUpdateItems->each(function (array $willUpdateItem) use ($boardCategories, $teamOrChannel): void {
                if (isset($willUpdateItem['id'])) {
                    $updateInstacne = $boardCategories->where('id', $willUpdateItem['id'])->first();

                    throw_if(
                        $updateInstacne->update([
                            'name' => $willUpdateItem['name'],
                            'is_public' => $willUpdateItem['is_public'],
                            'write_permission' => $willUpdateItem['write_permission'],
                            'show_order' => $willUpdateItem['show_order'],
                        ]) === false,
                        new DBtransActionFail()
                    );
                } else {
                    $relatedInstance = $boardCategories->first();
                    $model = get_class($relatedInstance);

                    $createdCategory = $model::create([
                        'article_count' => 0,
                        $relatedInstance->relatedKey => $teamOrChannel->id,
                        'name' => $willUpdateItem['name'],
                        'is_public' => $willUpdateItem['is_public'],
                        'write_permission' => $willUpdateItem['write_permission'],
                        'show_order' => $willUpdateItem['show_order'],
                    ]);
                    throw_if(is_null($createdCategory), new DBtransActionFail());
                }
            });
        });
    }
}
