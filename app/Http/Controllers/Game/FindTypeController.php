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
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    /**
     * 게임타입 레이어
     * @var ChannelService $channelService
     */
    private GameTypeService $gameTypeService;

    public function __construct(ResponseBuilder $responseBuilder, GameTypeService $gameTypeService)
    {
        $this->responseBuilder = $responseBuilder;
        $this->gameTypeService = $gameTypeService;
    }

    /**
     * 키워드로 게임 종류를 찾아주는 메소드 입니다.
     *
     * @param FindTypeRequest $request 검색 요청 객체
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return JsonResponse 검색결괴
     */
    public function getTypesByKeyword(FindTypeRequest $request): JsonResponse
    {
        $keyword = $request->validated()['query'];
        $gameTypes = $this->gameTypeService->findByKeyword($keyword);

        return $this->responseBuilder->ok(
            $this->responseBuilder->paginateMeta($gameTypes)->merge([
                'types' => array_map(fn (GameType $gameType): array => $this->gameTypeService->info($gameType), $gameTypes->items())
            ])
        );
    }
}
