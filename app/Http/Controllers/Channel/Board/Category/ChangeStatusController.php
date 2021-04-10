<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel\Board\Category;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\Board\BaseController;
use App\Services\Common\BoardService;
use App\Http\Requests\Channel\Board\Category\ChangeStatusRequest;
use Illuminate\Http\JsonResponse;
use App\Contracts\Board\Service as BoardServiceContract;

class ChangeStatusController extends BaseController
{
    public function __construct(public BoardServiceContract $boardService)
    {
        $this->boardService = $boardService;
    }

    public function changeChannelCategory(ChangeStatusRequest $request): JsonResponse
    {
        $willUpdateItems = collect(array_merge($request->validated(), $request->only('doNotNeedValidate')))->collapse();

        return parent::changeCategoryStatus(
            'slug',
            $willUpdateItems
        );
    }
}
