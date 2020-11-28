<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\GameType;
use Illuminate\Database\Eloquent\Builder;

class GameTypeRepository
{
    private GameType $gameType;
    public function __construct(GameType $gameType)
    {
        $this->gameType = $gameType;
    }

    public function findByKeyword(string $query): Builder
    {
        return GameType::where([
            ['name', 'LIKE', sprintf('%%%s%%', $query)]
        ]);
    }
}
