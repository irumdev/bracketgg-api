<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team\Board\Category;

use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\Board\Category\ChangeStatusRequest;

use App\Services\Common\BoardService;
use Illuminate\Http\JsonResponse;

class ChangeStatusController extends Controller
{
    public BoardService $boardService;
    public ResponseBuilder $responseBuilder;

    public function __construct(BoardService $teamBoardService, ResponseBuilder $responseBuilder)
    {
        $this->boardService = $teamBoardService;
        $this->responseBuilder = $responseBuilder;
    }

    public function changeTeamCategory(ChangeStatusRequest $request): JsonResponse
    {
        $willUpdateItems = collect(array_merge($request->validated(), $request->only('doNotNeedValidate')))->collapse();

        $this->boardService->updateCategory(
            $request->route('teamSlug'),
            $willUpdateItems
        );
        return $this->responseBuilder->ok([
            'markCategoryUpdate' => true,
        ]);
    }
}
