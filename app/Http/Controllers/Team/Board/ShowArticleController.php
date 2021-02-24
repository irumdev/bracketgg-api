<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team\Board;

use Illuminate\Http\JsonResponse;

use App\Http\Requests\Team\Board\ShowArticleRequest;

use App\Models\Team\Team;
use App\Models\ArticleViewLog;
use App\Models\Team\Board\Article as TeamBoardArticle;

use App\Wrappers\Type\ShowArticleByCategory as CategoryWithArticleType;

use App\Services\Team\BoardService;

use App\Http\Controllers\Common\Board\BaseController;
use App\Properties\Paginate;

class ShowArticleController extends BaseController
{
    /**
     * 채널 서비스레이어
     * @var BoardService $teamBoardService
     */
    public BoardService $boardService;

    public function __construct(BoardService $boardService)
    {
        $this->boardService = $boardService;
    }

    public function showArticleByModel(Team $team, string $teamBoardCategory, TeamBoardArticle $article)
    {
        return parent::getArticleByModel(
            $article,
            ArticleViewLog::TEAM_ARTICLE
        );
    }

    public function showArticleListByCategory(ShowArticleRequest $request): JsonResponse
    {
        $findArticlesDependency = new CategoryWithArticleType(
            $request->route('teamSlug'),
            Paginate::TEAM_ARTICLE_COUNT,
            $request->route('teamBoardCategory')
        );

        return parent::getArticlsByCategory($findArticlesDependency);
    }
}
