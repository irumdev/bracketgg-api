<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team\Board;

use App\Properties\Paginate;
use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;

use App\Services\Team\BoardService;
use App\Helpers\ResponseBuilder;

use App\Http\Requests\Team\Board\ShowArticleRequest;
use App\Models\Team\Board\Article as TeamArticle;

// use App\Services\Tea\BoardService as ChannelBoardServices;

class ShowArticleController extends Controller
{
    /**
     * 채널 서비스레이어
     * @var BoardService $teamBoardService
     */
    private BoardService $teamBoardService;

    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    public function __construct(BoardService $teamBoardService, ResponseBuilder $responseBuilder)
    {
        $this->teamBoardService = $teamBoardService;
        $this->responseBuilder = $responseBuilder;
    }

    public function showArticleListByCategory(ShowArticleRequest $request): JsonResponse
    {
        $category = $request->validated()['category'];

        $articlesAndCategories = $this->teamBoardService->getBoardArticlesByCategory($category, $request->route('teamSlug'));
        $articles = $articlesAndCategories['articles']->simplePaginate(Paginate::TEAM_ARTICLE_COUNT);

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
