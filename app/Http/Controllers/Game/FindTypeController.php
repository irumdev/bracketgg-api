<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\FindTypeRequest;
use App\Services\GameTypeService;
use App\Models\GameType;
use Illuminate\Http\JsonResponse;

class FindTypeController extends Controller
{
    /**
     * @var ResponseBuilder $responseBuilder
     */
    private ResponseBuilder $responseBuilder;
    private GameTypeService $gameTypeService;

    public function __construct(ResponseBuilder $responseBuilder, GameTypeService $gameTypeService)
    {
        $this->responseBuilder = $responseBuilder;
        $this->gameTypeService = $gameTypeService;
    }

    public function getTypesByKeyword(FindTypeRequest $request): JsonResponse
    {
        $keyword = $request->validated()['query'];
        $gameTypes = $this->gameTypeService->findByKeyword($keyword);

        return $this->responseBuilder->ok(
            $this->responseBuilder->paginateMeta($gameTypes)->merge([
                'types' => array_map(fn (GameType $gameType) => $this->gameTypeService->info($gameType), $gameTypes->items())
            ])
        );
    }
}
