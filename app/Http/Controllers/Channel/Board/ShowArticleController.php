<?php

declare(strict_types=1);

namespace App\Http\Controllers\Channel\Board;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ChannelService;
use App\Helpers\ResponseBuilder;
use Illuminate\Http\JsonResponse;
use App\Models\Channel\Board\Article as ChannelArticle;

use App\Services\Channel\BoardService as ChannelBoardServices;

use App\Http\Requests\Channel\Board\ShowArticleRequest;
use App\Properties\Paginate;

class ShowArticleController extends Controller
{
    /**
     * 채널 서비스레이어
     * @var ChannelBoardServices $channelService
     */
    private ChannelBoardServices $channelBoardService;

    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    public function __construct(ChannelBoardServices $channelBoardService, ResponseBuilder $responseBuilder)
    {
        $this->channelBoardService = $channelBoardService;
        $this->responseBuilder = $responseBuilder;
    }

    public function showArticleListByCategory(ShowArticleRequest $request): JsonResponse
    {
        $category = $request->validated()['category'];

        $articlesAndCategories = $this->channelBoardService->getBoardArticlesByCategory($category, $request->route('slug'));
        $articles = $articlesAndCategories['articles']->simplePaginate(Paginate::CHANNEL_ARTICLE_COUNT);

        $paginateMetaData = $this->responseBuilder->paginateMeta($articles);
        $articles = collect($articles->items())->map(fn (ChannelArticle $article) => $this->channelBoardService->articleInfo($article));

        return $this->responseBuilder->ok(
            $paginateMetaData->merge([
                'articles' => $articles,
                'categories' => $articlesAndCategories['categories'],
                'currentCategory' => $category,
            ])
        );
    }
}
