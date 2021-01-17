<?php

declare(strict_types=1);

namespace App\Services\Channel;

use App\Models\Channel\Channel;
use App\Models\Channel\Board\Category as ChannelBoardCategory;
use App\Models\Channel\Board\Article as ChannelBoardArticle;
use App\Helpers\ResponseBuilder;
use App\Repositories\ChannelRepository;

class BoardService
{
    private ChannelRepository $channelRepostiroy;

    public function __construct(ChannelRepository $channelRepostiroy)
    {
        $this->channelRepostiroy = $channelRepostiroy;
    }


    public function getBoardArticlesByCategory(string $category, Channel $channel): array
    {
        $categories = $this->channelRepostiroy->getArticleCategories($channel);
        $articles = $this->channelRepostiroy->getBoardArticlesByCategory($category, $channel);
        return [
            'categories' => $categories->map(fn (ChannelBoardCategory $category) => $this->categoryInfo($category)),
            'articles' => $articles,
        ];
    }

    private function categoryInfo(ChannelBoardCategory $category): array
    {
        return [
            'name' => $category->name,
            'showOrder' => $category->show_order,
            'articleCount' => $category->article_count,
            'isPublic' => $category->is_public,
        ];
    }


    public function articleInfo(ChannelBoardArticle $article): array
    {
        return [

            'id' => $article->id,
            'title' => $article->title,
            'content' => $article->content,
            'category' => $article->category_id,
            'writerInfo' => [
                'id' => $article->user_id,
            ],
            'seeCount' => $article->see_count,
            'likeCount' => $article->like_count,
            'unlikeCount' => $article->unlike_count,
            'commentCount' => $article->comment_count,
        ];
    }
}
