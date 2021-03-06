<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel\Board;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseBuilder;
use Illuminate\Http\JsonResponse;
use App\Models\Channel\Board\Article as ChannelArticle;

use App\Services\Channel\BoardService as ChannelBoardServices;

use App\Http\Requests\Channel\Board\ShowArticleRequest;
use App\Properties\Paginate;

use App\Http\Controllers\Common\Board\BaseController;
use App\Models\ArticleViewLog;
use App\Models\Channel\Channel;
use App\Wrappers\Type\ShowArticleByCategory as CategoryWithArticleType;
use App\Contracts\Board\Service as BoardServiceContract;

class ShowArticleController extends BaseController
{
    public function __construct(public BoardServiceContract $boardService)
    {
        $this->boardService = $boardService;
    }

    public function showArticleByModel(Channel $team, string $channelBoardCategoey, ChannelArticle $article): JsonResponse
    {
        return parent::getArticleByModel(
            $article,
            ArticleViewLog::CHANNEL_ARTICLE
        );
    }

    public function showArticleListByCategory(ShowArticleRequest $request): JsonResponse
    {
        $findArticlesDependency = new CategoryWithArticleType(
            $request->route('slug'),
            Paginate::CHANNEL_ARTICLE_COUNT,
            $request->route('channelBoardCategory')
        );

        return parent::getArticlsByCategory($findArticlesDependency);
    }
}
