<?php

declare(strict_types=1);

namespace Tests\Feature\Channel\Board\Upload;

use App\Models\Channel\Board\Article;
use App\Models\Channel\Board\Category;
use App\Models\Channel\Channel;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Styde\Enlighten\Tests\EnlightenSetup;
use Tests\TestCase;
use Illuminate\Support\Str;
use App\Wrappers\BoardWritePermission\Channel as ChannelArticleWritePermissions;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Rules\UploadBoardArticle;
use Illuminate\Http\UploadedFile;

class ArticleTest extends TestCase
{
    use EnlightenSetup;

    private string $baseStubPath = '';
    private string $successMarkup = '';
    private string $failMarkup = '';

    private function getSuccessMarkup(): string
    {
        if (empty($this->successMarkup)) {
            $this->successMarkup = file_get_contents(
                realpath($this->baseStubPath . '/success.html')
            );
        }
        return $this->successMarkup;
    }

    private function getFailMarkup(): string
    {
        if (empty($this->failMarkup)) {
            $this->failMarkup = file_get_contents(
                realpath($this->baseStubPath . '/fail.html')
            );
        }
        return $this->failMarkup;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseStubPath = realpath(__DIR__ . '/../../../../../stubs/uploadArticleTest');
        $this->setUpEnlighten();
    }

    /**
     * @test
     * @enlighten
     */
    public function failWhenChannelOwnerTryUploadArticleButTitleIsNotString(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);

        $testHtml = $this->getSuccessMarkup();

        $randCategory = $channel->boardCategories->random();

        $randCategory->write_permission = ChannelArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('channel.article.upload.article', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randCategory->name
        ]);

        $tryUploadArticle = $this->postJson($requestUrl, $requestData = [
            'article' => $testHtml,
            'title' => UploadedFile::fake()->create('test.html', 1)
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUploadArticle['ok']);
        $this->assertFalse($tryUploadArticle['isValid']);
        $this->assertEquals(['code' => UploadBoardArticle::ARTICLE_IS_NOT_STRING], $tryUploadArticle['messages']);
    }


    /**
     * @test
     * @enlighten
     */
    public function failWhenChannelOwnerTryUploadArticleButArticleIsNotString(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);

        $testHtml = $this->getSuccessMarkup();

        $randCategory = $channel->boardCategories->random();

        $randCategory->write_permission = ChannelArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('channel.article.upload.article', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randCategory->name
        ]);

        $tryUploadArticle = $this->postJson($requestUrl, $requestData = [
            'article' => UploadedFile::fake()->create('test.html', 1),
            'title' => Str::random(10)
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUploadArticle['ok']);
        $this->assertFalse($tryUploadArticle['isValid']);
        $this->assertEquals(['code' => UploadBoardArticle::ARTICLE_IS_NOT_STRING], $tryUploadArticle['messages']);
    }


    /**
     * @test
     * @enlighten
     */
    public function failWhenChannelOwnerTryUploadArticleButTitleIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);

        $testHtml = $this->getSuccessMarkup();

        $randCategory = $channel->boardCategories->random();

        $randCategory->write_permission = ChannelArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('channel.article.upload.article', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randCategory->name
        ]);

        $tryUploadArticle = $this->postJson($requestUrl, $requestData = [
            'article' => $testHtml,
            // 'title' => Str::random(10)
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUploadArticle['ok']);
        $this->assertFalse($tryUploadArticle['isValid']);
        $this->assertEquals(['code' => UploadBoardArticle::ARTICLE_TITLE_IS_NOT_ATTACHED], $tryUploadArticle['messages']);
    }


    /**
     * @test
     * @enlighten
     */
    public function failWhenChannelOwnerTryUploadArticleButArticleIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);


        $randCategory = $channel->boardCategories->random();

        $randCategory->write_permission = ChannelArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('channel.article.upload.article', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randCategory->name
        ]);

        $tryUploadArticle = $this->postJson($requestUrl, $requestData = [

            'title' => Str::random(10)
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);


        $this->assertFalse($tryUploadArticle['ok']);
        $this->assertFalse($tryUploadArticle['isValid']);
        $this->assertEquals(['code' => UploadBoardArticle::ARTICLE_IS_NOT_ATTACHED], $tryUploadArticle['messages']);
    }


    /**
     * @test
     * @enlighten
     */
    public function failWhenChannelOwnerTryUploadArticleButArticleHasXSSMarkup(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);

        $testHtml = $this->getFailMarkup();
        $randCategory = $channel->boardCategories->random();

        $randCategory->write_permission = ChannelArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('channel.article.upload.article', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randCategory->name
        ]);

        $tryUploadArticle = $this->postJson($requestUrl, $requestData = [
            'article' => $testHtml,
            'title' => Str::random(10)
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);


        $this->assertFalse($tryUploadArticle['ok']);
        $this->assertFalse($tryUploadArticle['isValid']);

        $this->assertEquals(['code' => UploadBoardArticle::ARTICLE_IS_NOT_ATTACHED], $tryUploadArticle['messages']);
    }


    ##################### success case #####################
    /**
     * @test
     * @enlighten
     */
    public function successUploadArticleAnyUser(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = factory(User::class)->create();

        $owner = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $testHtml = $this->getSuccessMarkup();

        $randCategory = $channel->boardCategories->random();

        $randCategory->write_permission = ChannelArticleWritePermissions::ALL_USER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('channel.article.upload.article', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randCategory->name
        ]);

        $tryUploadArticle = $this->postJson($requestUrl, $requestData =  [
            'article' => $testHtml,
            'title' => Str::random(10)
        ])->assertOk();

        $cleanMarkup = uglifyHtml(clean($testHtml));

        $savedArticle = Article::where([
            ['title', '=', $requestData['title']],
            ['content', '=', $cleanMarkup],
            ['category_id', '=', $randCategory->id],
        ])->first();

        $this->assertTrue($tryUploadArticle['ok']);
        $this->assertTrue($tryUploadArticle['isValid']);
        $this->assertTrue($tryUploadArticle['messages']['isSuccess']);
        $this->assertEquals(
            $savedArticle->content,
            $cleanMarkup
        );

        $this->assertEquals(
            $savedArticle->title,
            $requestData['title']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successUploadArticleOnlyChannelOwner(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);

        $testHtml = $this->getSuccessMarkup();

        $randCategory = $channel->boardCategories->random();

        $randCategory->write_permission = ChannelArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('channel.article.upload.article', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randCategory->name
        ]);

        $tryUploadArticle = $this->postJson($requestUrl, $requestData =  [
            'article' => $testHtml,
            'title' => Str::random(10)
        ])->assertOk();

        $cleanMarkup = uglifyHtml(clean($testHtml));

        $savedArticle = Article::where([
            ['title', '=', $requestData['title']],
            ['content', '=', $cleanMarkup],
            ['category_id', '=', $randCategory->id],
        ])->first();

        $this->assertTrue($tryUploadArticle['ok']);
        $this->assertTrue($tryUploadArticle['isValid']);
        $this->assertTrue($tryUploadArticle['messages']['isSuccess']);
        $this->assertEquals(
            $savedArticle->content,
            $cleanMarkup
        );

        $this->assertEquals(
            $savedArticle->title,
            $requestData['title']
        );
    }
    ##################### success case #####################
}
