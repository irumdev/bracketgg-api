<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team\Board;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;

use App\Helpers\ResponseBuilder;

use App\Http\Requests\Team\Board\ShowArticleRequest;

use App\Models\Team\Team;
use App\Models\ArticleViewLog;
use App\Models\Team\Board\Article as TeamArticle;
use App\Models\Team\Board\Article as TeamBoardArticle;

use App\Services\UserService;
use App\Services\Team\BoardService;

use function App\Events\viewArticleResolver;

class ShowArticleController extends Controller
{
    /**
     * 채널 서비스레이어
     * @var BoardService $teamBoardService
     */
    private BoardService $teamBoardService;

    /**
     * 채널 서비스레이어
     * @var BoardService $teamBoardService
     */
    private UserService $userService;

    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    public function __construct(BoardService $teamBoardService, UserService $userService, ResponseBuilder $responseBuilder)
    {
        $this->teamBoardService = $teamBoardService;
        $this->userService = $userService;
        $this->responseBuilder = $responseBuilder;
    }

    public function showArticleByModel(Team $team, string $teamBoardCategory, TeamBoardArticle $article)
    {
        event(viewArticleResolver($article, ArticleViewLog::TEAM_ARTICLE));
        return $this->responseBuilder->ok(
            $this->teamBoardService->getTeamArticleByModel($article)
        );
    }

    /**
     * @todo 채널 컨트롤러와 함께 공통화
     */
    public function showArticleListByCategory(ShowArticleRequest $request): JsonResponse
    {
        $category = $request->validated()['category'];

        $articlesAndCategories = $this->teamBoardService->getTeamBoardArticlesByCategory($category, $request->route('teamSlug'));
        $articles = $articlesAndCategories['articles'];

        $paginateMetaData = $this->responseBuilder->paginateMeta($articles);

        $articles = collect($articles->items())->map(fn (TeamArticle $article) => $this->teamBoardService->articleInfo($article));

        return $this->responseBuilder->ok(
            $paginateMetaData->merge([
                'articles' => $articles,
                'categories' => $articlesAndCategories['categories'],
                'currentCategory' => $category,
            ])
        );
    }
}
