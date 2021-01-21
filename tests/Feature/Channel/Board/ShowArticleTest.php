<?php

declare(strict_types=1);

namespace Tests\Feature\Channel\Board;

use App\Models\Channel\Channel;
use App\Models\User;
use App\Models\Channel\Board\Category as ChannelBoardCategory;
use App\Models\Channel\Board\Article as ChannelBoardArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Channel\Board\ShowArticleRequest;

class ShowArticleTest extends TestCase
{
    /**
     * @test
     * @enlighten
     */
    public function failLookupArticleWhenCategoryIsNotAttached(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = factory(User::class)->create();
        $channel = factory(Channel::class)->states(['addSlug'])->create();

        $requestUrl = route('getChannelArticlesByCategory', [
            'slug' => $channel->slug,
        ]);

        $tryLookUpArticle = $this->getJson($requestUrl)->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryLookUpArticle['ok']);
        $this->assertFalse($tryLookUpArticle['isValid']);

        $this->assertEquals(['code' => ShowArticleRequest::CATEGORY_IS_REQUIRED], $tryLookUpArticle['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookupArticleWhenCategoryIsNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = factory(User::class)->create();
        $channel = factory(Channel::class)->states(['addSlug'])->create();

        $requestUrl = route('getChannelArticlesByCategory', [
            'slug' => $channel->slug,
        ]) . '?' . \http_build_query([
            'category' => Str::random(10)
        ]);

        $tryLookUpArticle = $this->getJson($requestUrl)->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryLookUpArticle['ok']);
        $this->assertFalse($tryLookUpArticle['isValid']);

        $this->assertEquals(['code' => ShowArticleRequest::CATEGORY_IS_NOT_EXISTS], $tryLookUpArticle['messages']);
    }


    /**
     * @enlighten
     * @test
     */
    public function successLookupChannelArticlesByCategory(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = factory(User::class)->create();
        $channel = factory(Channel::class)->states(['addSlug', 'addArticles'])->create();

        $categories = $channel->boardCategories->map(fn (ChannelBoardCategory $category) => $category->name)->toArray();

        collect($categories)->each(function (string $category) use ($requestUser, $channel) {
            $current = 1;

            do {
                $requestUrl = route('getChannelArticlesByCategory', [
                    'slug' => $channel->slug,
                ]) . '?' . http_build_query([
                    'page' => $current,
                    'category' => $category
                ]);

                $tryLookUpArticle = $this->getJson($requestUrl)->assertOk();

                $this->assertTrue($tryLookUpArticle['ok']);
                $this->assertTrue($tryLookUpArticle['isValid']);

                $articleInfo = $tryLookUpArticle['messages'];

                $this->assertEquals($category, $articleInfo['currentCategory']);


                collect($articleInfo['articles'])->each(function (array $article) use ($channel, $articleInfo) {
                    $dbCatgory = ChannelBoardCategory::where([
                        ['channel_id', '=', $channel->id],
                        ['name', '=', $articleInfo['currentCategory']],
                    ])->first();

                    $this->assertNotNull($dbCatgory);

                    $dbArticle = ChannelBoardArticle::find($article['id']);

                    $this->assertNotNull($dbArticle);
                    $this->assertEquals($dbArticle->title, $article['title']);
                    $this->assertEquals($dbArticle->content, $article['content']);
                    $this->assertEquals($dbArticle->see_count, $article['seeCount']);
                    $this->assertEquals($dbArticle->like_count, $article['likeCount']);
                    $this->assertEquals($dbArticle->unlike_count, $article['unlikeCount']);
                    $this->assertEquals($dbArticle->comment_count, $article['commentCount']);

                    $this->assertNotNull(User::find($article['writerInfo']['id']));
                });

                $current += 1;
            } while ($tryLookUpArticle['messages']['meta']['hasMorePage']);
        });
    }
}
