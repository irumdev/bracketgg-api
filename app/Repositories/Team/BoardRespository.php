<?php

declare(strict_types=1);

namespace App\Repositories\Team;

use App\Factories\BoardFactory;

use App\Models\Team\Team;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoardRespository extends BoardFactory
{
    public function getArticleCategories(Team $team): Collection
    {
        return $this->getCategories($team);
    }

    public function getBoardArticlesByCategory(string $category, Team $team): HasMany
    {
        return $this->getArticlesFromCategory($category, $team);
    }
}
