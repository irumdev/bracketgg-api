<?php

declare(strict_types=1);

namespace App\Repositories\Team;

use App\Models\Team\Team;
use App\Repositories\Common\BoardRespository as BaseBoardRepository;
use Illuminate\Database\Eloquent\Collection;

class BoardRespository extends BaseBoardRepository
{
    public const DEFAULT_ARTICLE_LATEST_COUNT = 10;

    public function latestTenArticles(Team $team): Collection
    {
        return parent::latestArticles($team)->with('category')
                                             ->orderBy((new Team())->getKeyName(), 'desc')
                                             ->limit(self::DEFAULT_ARTICLE_LATEST_COUNT)
                                             ->get();
    }
}
