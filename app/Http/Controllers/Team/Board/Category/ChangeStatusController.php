<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team\Board\Category;

use App\Http\Requests\Team\Board\Category\ChangeStatusRequest;

use App\Services\Common\BoardService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Common\Board\BaseController;
use App\Contracts\Board\Service as BoardServiceContract;

class ChangeStatusController extends BaseController
{
    public function __construct(public BoardServiceContract $boardService)
    {
        $this->boardService = $boardService;
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
