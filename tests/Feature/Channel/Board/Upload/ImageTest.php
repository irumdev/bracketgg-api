<?php

declare(strict_types=1);

namespace Tests\Feature\Channel\Board\Upload;

use App\Models\Channel\Board\Category;
use App\Models\Channel\Channel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Styde\Enlighten\Tests\EnlightenSetup;
use App\Wrappers\BoardWritePermission\Channel as ChannelArticleWritePermissions;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Rules\UploadBoardArticleImage;

use Tests\TestCase;

class ImageTest extends TestCase
{
    use EnlightenSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
    }

    /**
     * @test
     * @enlighten
     */
    public function failUploadChannelBoardArticleImageWhenChannelImageIsLarge(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $channel->boardCategories->random();

        $randomBoardCategory->write_permission = ChannelArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            ChannelArticleWritePermissions::ONLY_OWNER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('channel.article.upload.image', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadChannelBoardArticleImage = $this->postJson($requestUrl, [
            'article_image' => UploadedFile::fake()->create('asdf.png', 2049),
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUploadChannelBoardArticleImage['ok']);
        $this->assertFalse($tryUploadChannelBoardArticleImage['isValid']);
        $this->assertEquals(['code' => UploadBoardArticleImage::ARTICLE_IMAGE_IS_LARGE], $tryUploadChannelBoardArticleImage['messages']);
    }



    /**
     * @test
     * @enlighten
     */
    public function failUploadChannelBoardArticleImageWhenChannelImageMimeIsNotValid(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $channel->boardCategories->random();

        $randomBoardCategory->write_permission = ChannelArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            ChannelArticleWritePermissions::ONLY_OWNER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('channel.article.upload.image', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadChannelBoardArticleImage = $this->postJson($requestUrl, [
            'article_image' => UploadedFile::fake()->create('asdf.pdf', 1000),
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUploadChannelBoardArticleImage['ok']);
        $this->assertFalse($tryUploadChannelBoardArticleImage['isValid']);
        $this->assertEquals(['code' => UploadBoardArticleImage::ARTICLE_IMAGE_IS_NOT_IMAGE], $tryUploadChannelBoardArticleImage['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUploadChannelBoardArticleImageWhenChannelImageIsNotImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $channel->boardCategories->random();

        $randomBoardCategory->write_permission = ChannelArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            ChannelArticleWritePermissions::ONLY_OWNER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('channel.article.upload.image', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadChannelBoardArticleImage = $this->postJson($requestUrl, [
            'article_image' => 'errorParam',
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUploadChannelBoardArticleImage['ok']);
        $this->assertFalse($tryUploadChannelBoardArticleImage['isValid']);
        $this->assertEquals(['code' => UploadBoardArticleImage::ARTICLE_IMAGE_IS_NOT_FILE], $tryUploadChannelBoardArticleImage['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUploadChannelBoardArticleImageWhenChannelImageIsNotAttached(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $channel->boardCategories->random();

        $randomBoardCategory->write_permission = ChannelArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            ChannelArticleWritePermissions::ONLY_OWNER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('channel.article.upload.image', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadChannelBoardArticleImage = $this->postJson($requestUrl, [

        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUploadChannelBoardArticleImage['ok']);
        $this->assertFalse($tryUploadChannelBoardArticleImage['isValid']);
        $this->assertEquals(['code' => UploadBoardArticleImage::ARTICLE_IMAGE_IS_NOT_ATTACHED], $tryUploadChannelBoardArticleImage['messages']);
    }


    /**
     * @test
     * @enlighten
     */
    public function failUploadChannelBoardArticleImageWhenUserNotLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = factory(User::class)->create();
        $owner = factory(User::class)->create();

        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $channel->boardCategories->random();

        $randomBoardCategory->write_permission = ChannelArticleWritePermissions::ALL_USER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            ChannelArticleWritePermissions::ALL_USER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('channel.article.upload.image', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadChannelBoardArticleImage = $this->postJson($requestUrl, [
            'article_image' => UploadedFile::fake()->create('test_image.png', 1000)
        ])->assertUnauthorized();

        $this->assertFalse($tryUploadChannelBoardArticleImage['ok']);
        $this->assertFalse($tryUploadChannelBoardArticleImage['isValid']);
        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $tryUploadChannelBoardArticleImage['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUploadChannelBoardArticleImageWhenChannelCategoryAllowOnlyOnwer(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();

        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $channel->boardCategories->random();

        $randomBoardCategory->write_permission = ChannelArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            ChannelArticleWritePermissions::ONLY_OWNER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('channel.article.upload.image', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadChannelBoardArticleImage = $this->postJson($requestUrl, [
            'article_image' => UploadedFile::fake()->create('test_image.png', 1000)
        ])->assertUnauthorized();

        $this->assertFalse($tryUploadChannelBoardArticleImage['ok']);
        $this->assertFalse($tryUploadChannelBoardArticleImage['isValid']);
        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $tryUploadChannelBoardArticleImage['messages']);
    }


    ##############################################################
    ######################## success case ########################
    ##############################################################

    /**
     * @test
     * @enlighten
     */
    public function successUploadWhenChannelOwnerUploadImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);

        $randomBoardCategory = $channel->boardCategories->random();

        $randomBoardCategory->write_permission = ChannelArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            ChannelArticleWritePermissions::ONLY_OWNER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('channel.article.upload.image', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadChannelBoardArticleImage = $this->postJson($requestUrl, [
            'article_image' => UploadedFile::fake()->create('test_image.png', 1000)
        ])->assertOk();

        $this->assertTrue($tryUploadChannelBoardArticleImage['ok']);
        $this->assertTrue($tryUploadChannelBoardArticleImage['isValid']);
        $this->get($tryUploadChannelBoardArticleImage['messages']['imageUrl'])->assertOk();
    }

    /**
     * @test
     * @enlighten
     */
    public function successUploadWhenChannelAnyUserUploadImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();

        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $channel->boardCategories->random();

        $randomBoardCategory->write_permission = ChannelArticleWritePermissions::ALL_USER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            ChannelArticleWritePermissions::ALL_USER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('channel.article.upload.image', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadChannelBoardArticleImage = $this->postJson($requestUrl, [
            'article_image' => UploadedFile::fake()->create('test_image.png', 1000)
        ])->assertOk();

        $this->assertTrue($tryUploadChannelBoardArticleImage['ok']);
        $this->assertTrue($tryUploadChannelBoardArticleImage['isValid']);
        $this->get($tryUploadChannelBoardArticleImage['messages']['imageUrl'])->assertOk();
    }

    ##############################################################
    ######################## success case ########################
    ##############################################################
}
