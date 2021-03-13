<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team\Board\Category;

use App\Http\Requests\Team\Board\Category\ChangeStatusRequest;

use App\Services\Common\BoardService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Common\Board\BaseController;

class ChangeStatusController extends BaseController
{
    public BoardService $boardService;

    public function __construct(BoardService $teamBoardService)
    {
        $this->boardService = $teamBoardService;
    }

    public function changeTeamCategory(ChangeStatusRequest $request): JsonResponse
    {
        $willUpdateItems = collect(array_merge($request->validated(), $request->only('doNotNeedValidate')))->collapse();

        return parent::changeCategoryStatus(
            'teamSlug',
            $willUpdateItems
        );
    }
}
