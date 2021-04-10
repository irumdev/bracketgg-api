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
use App\Contracts\Board\Service as BoardServiceContract;

class ShowArticleController extends BaseController
{
    public function __construct(public BoardServiceContract $boardService)
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
