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

class ShowArticleController extends BaseController
{
    /**
     * 채널 서비스레이어
     * @var ChannelBoardServices $channelService
     */
    public ChannelBoardServices $boardService;

    public function __construct(ChannelBoardServices $boardService)
    {
        $this->boardService = $boardService;
    }

    public function showArticleByModel(Channel $team, string $channelBoardCategoey, ChannelArticle $article)
    {
        return parent::getArticleByModel(
            $article,
            ArticleViewLog::CHANNEL_ARTICLE
        );
    }

    public function showArticleListByCategory(ShowArticleRequest $request): JsonResponse
    {
        $category = $request->validated()['category'];

        $findArticlesDependency = new CategoryWithArticleType(
            $request->route('slug'),
            Paginate::CHANNEL_ARTICLE_COUNT,
            $category
        );

        return parent::getArticlsByCategory($findArticlesDependency);
    }
}
