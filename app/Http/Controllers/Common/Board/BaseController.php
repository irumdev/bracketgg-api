<?php

declare(strict_types=1);

namespace App\Http\Controllers\Common\Board;

use App\Helpers\ResponseBuilder;

use App\Models\Common\Board\BaseArticle;
use App\Services\Common\BoardService;
use App\Http\Controllers\Controller;

use function App\Events\viewArticleResolver;

use App\Wrappers\Type\ShowArticleByCategory as CategoryWithArticleType;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function getArticleByModel(BaseArticle $article, int $type): JsonResponse
    {
        event(viewArticleResolver($article, $type));
        return (new ResponseBuilder())->ok(
            $this->boardService->getArticleByModel($article)
        );
    }

    public function getArticlsByCategory(CategoryWithArticleType $articlesInfo)
    {
        $responseBuilder = new ResponseBuilder();
        $articlesAndCategories = $this->boardService->getBoardArticlesByCategory($articlesInfo);
        $articles = $articlesAndCategories['articles'];

        $paginateMetaData = $responseBuilder->paginateMeta($articles);

        $articles = collect($articles->items())->map(fn (BaseArticle $article) => $this->boardService->articleInfo($article));

        return $responseBuilder->ok(
            $paginateMetaData->merge([
                'articles' => $articles,
                'categories' => $articlesAndCategories['categories'],
                'currentCategory' => $articlesInfo->category['name'],
            ])
        );
    }
}
