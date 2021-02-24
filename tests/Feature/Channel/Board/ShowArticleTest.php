<?php

declare(strict_types=1);

namespace Tests\Feature\Channel\Board;

use App\Helpers\Image;
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
    public function successLookupPublicArticleAndNotIncreaseSeeCount(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $channel = factory(Channel::class)->states([
            'addSlug',
            'addSmallChannelArticlesWithSavedImages'
        ])->create();

        $boardCategory = $channel->boardCategories;

        $randCategory = $boardCategory->get(
            Arr::random(
                $boardCategory->keys()->toArray()
            )
        );

        $randArticle = $randCategory->articles->get(
            Arr::random(
                $randCategory->articles->keys()->toArray()
            )
        );

        $this->assertEquals(0, $randArticle->see_count);

        $requestUrl = route('getChannelArticle', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randCategory->name,
            'channelArticle' => $randArticle->id
        ]);

        $tryLookUpArticle = $this->getJson($requestUrl)->assertOk();

        $this->assertEquals(1, $tryLookUpArticle['messages']['seeCount']);

        $tryLookUpArticle = $this->getJson($requestUrl)->assertOk();
        $this->assertEquals(1, $tryLookUpArticle['messages']['seeCount']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookupPublicArticleWhenNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $channel = factory(Channel::class)->states([
            'addSlug',
            'addSmallChannelArticlesWithSavedImages'
        ])->create();

        $boardCategory = $channel->boardCategories;

        $randCategory = $boardCategory->get(
            Arr::random(
                $boardCategory->keys()->toArray()
            )
        );

        $requestUrl = route('getChannelArticle', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randCategory->name,
            'channelArticle' => -1,
        ]);

        $tryLookUpArticle = $this->getJson($requestUrl)->assertNotFound();


        $this->assertFalse($tryLookUpArticle['ok']);
        $this->assertFalse($tryLookUpArticle['isValid']);

        $this->assertEquals(['code' => 404], $tryLookUpArticle['messages']);
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
            'channelBoardCategory' => Str::random(10),
        ]);

        $tryLookUpArticle = $this->getJson($requestUrl)->assertNotFound();

        $this->assertFalse($tryLookUpArticle['ok']);
        $this->assertFalse($tryLookUpArticle['isValid']);

        $this->assertEquals(['code' => 404], $tryLookUpArticle['messages']);
    }


    /**
     * @test
     * @enlighten
     */
    public function successLookupPublicArticle(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $channel = factory(Channel::class)->states([
            'addSlug',
            'addSmallChannelArticlesWithSavedImages'
        ])->create();

        $boardCategory = $channel->boardCategories;

        $randCategory = $boardCategory->get(
            Arr::random(
                $boardCategory->keys()->toArray()
            )
        );

        $randArticle = $randCategory->articles->get(
            Arr::random(
                $randCategory->articles->keys()->toArray()
            )
        );

        $this->assertEquals(0, $randArticle->see_count);

        $requestUrl = route('getChannelArticle', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randCategory->name,
            'channelArticle' => $randArticle->id
        ]);

        $tryLookUpArticle = $this->getJson($requestUrl)->assertOk();

        $messages = $tryLookUpArticle['messages'];
        $writerInfo = $messages['writerInfo'];

        $randArticle = ChannelBoardArticle::find($randArticle->id);

        $this->assertTrue($tryLookUpArticle['ok']);
        $this->assertTrue($tryLookUpArticle['isValid']);

        $this->assertEquals($randArticle->id, $messages['id']);
        $this->assertEquals($randArticle->title, $messages['title']);
        $this->assertEquals($randArticle->content, $messages['content']);
        $this->assertEquals($randCategory->id, $messages['category']);

        $profileImage = empty($randArticle->writer->profile_image) ? null : Image::toStaticUrl('profileImage', [
            'profileImage' => $randArticle->writer->profile_image
        ]);

        $this->assertNotNull(User::find($writerInfo['id']));
        $this->assertEquals(User::find($writerInfo['id'])->nick_name, $writerInfo['nickName']);
        $this->assertEquals($profileImage, $writerInfo['profileImage']);

        $this->assertEquals($randArticle->see_count, $messages['seeCount']);
        $this->assertEquals($randArticle->comment_count, $messages['commentCount']);
        $this->assertEquals($randArticle->like_count, $messages['likeCount']);
        $this->assertEquals($randArticle->unlikeCount, $messages['unlikeCount']);
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
                    'channelBoardCategory' => $category,
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
