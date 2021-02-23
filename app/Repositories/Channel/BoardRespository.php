<?php

declare(strict_types=1);

namespace App\Repositories\Channel;

use App\Repositories\Common\BoardRespository as BaseBoardRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Channel\Channel;

class BoardRespository extends BaseBoardRepository
{
    public const DEFAULT_ARTICLE_LATEST_COUNT = 10;

    public function latestTenArticles(Channel $model): Collection
    {
        return parent::latestArticles($model)->with('category')
                                             ->orderBy((new Channel())->getKeyName(), 'desc')
                                             ->limit(self::DEFAULT_ARTICLE_LATEST_COUNT)
                                             ->get();
    }
}
