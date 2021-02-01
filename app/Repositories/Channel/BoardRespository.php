<?php

declare(strict_types=1);

namespace App\Repositories\Channel;

use App\Models\Channel\Channel;
use App\Factories\BoardFactory;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoardRespository extends BoardFactory
{
    public function getArticleCategories(Channel $channel): Collection
    {
        return $this->getCategories($channel);
    }

    public function getBoardArticlesByCategory(string $category, Channel $channel): HasMany
    {
        return $this->getArticlesFromCategory($category, $channel);
    }
}
