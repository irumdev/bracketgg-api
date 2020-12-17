<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameType;
use App\Repositories\GameTypeRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GameTypeService
{
    private GameTypeRepository $gameTypeRepository;

    public function __construct(GameTypeRepository $gameTypeRepository)
    {
        $this->gameTypeRepository = $gameTypeRepository;
    }

    public function findByKeyword(string $query)
    {
        $searchResult = $this->gameTypeRepository->findByKeyword($query)->simplePaginate();
        throw_unless($searchResult->isNotEmpty(), (new ModelNotFoundException())->setModel(GameType::class));
        return $searchResult;
    }

    public function info(GameType $gameType): array
    {
        return [
            'id' => $gameType->id,
            'name' => $gameType->name
        ];
    }
}
