<?php

declare(strict_types=1);

namespace Tests\Feature\Channel\Board\Upload;

use App\Models\Channel\Board\Category;
use App\Models\Channel\Channel;
use App\Models\User;

use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use App\Http\Requests\Rules\UploadBoardArticleComment;
use App\Models\Channel\Board\Reply;

class CommentTest extends TestCase
{

    /**
     * @test
     * @enlighten
     */
    public function failUploadCommentToPublicCategoryWhenParentIsNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create(['owner' => $activeUser->id]);

        $randArticle = $channel->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = true;

        $this->assertTrue($randCategory->save());

        $requestUrl = route('channel.article.upload.comment', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randArticle->category->name,
            'channelArticle' => $randArticle->id,
        ]);

        $tryUploadComment = $this->postJson($requestUrl, [
            'content' => Str::random(30),
            'parent_id' => -3,
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUploadComment['ok']);
        $this->assertFalse($tryUploadComment['isValid']);
        $this->assertEquals(['code' => UploadBoardArticleComment::PARENT_IS_NOT_EXISTS], $tryUploadComment['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUploadCommentToPublicCategoryWhenParentIdIsNotNumeric(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create(['owner' => $activeUser->id]);

        $randArticle = $channel->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = true;

        $this->assertTrue($randCategory->save());

        $requestUrl = route('channel.article.upload.comment', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randArticle->category->name,
            'channelArticle' => $randArticle->id,
        ]);

        $tryUploadComment = $this->postJson($requestUrl, [
            'content' => Str::random(30),
            'parent_id' => 'asd',
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUploadComment['ok']);
        $this->assertFalse($tryUploadComment['isValid']);
        $this->assertEquals(['code' => UploadBoardArticleComment::PARENT_ID_IS_NOT_NUMERIC], $tryUploadComment['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUploadCommentToPublicCategoryWhenContentIsNotString(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create(['owner' => $activeUser->id]);

        $randArticle = $channel->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = true;

        $this->assertTrue($randCategory->save());

        $requestUrl = route('channel.article.upload.comment', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randArticle->category->name,
            'channelArticle' => $randArticle->id,
        ]);

        $tryUploadComment = $this->postJson($requestUrl, [
            'content' => ['asdf'],
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUploadComment['ok']);
        $this->assertFalse($tryUploadComment['isValid']);
        $this->assertEquals(['code' => UploadBoardArticleComment::COMMENT_IS_NOT_STRING], $tryUploadComment['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUploadCommentToPublicCategoryWhenContentIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create(['owner' => $activeUser->id]);

        $randArticle = $channel->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = true;

        $this->assertTrue($randCategory->save());

        $requestUrl = route('channel.article.upload.comment', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randArticle->category->name,
            'channelArticle' => $randArticle->id,
        ]);

        $tryUploadComment = $this->postJson($requestUrl, [
            'content' => ''
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUploadComment['ok']);
        $this->assertFalse($tryUploadComment['isValid']);
        $this->assertEquals(['code' => UploadBoardArticleComment::COMMENT_IS_NOT_ATTACHED], $tryUploadComment['messages']);
    }



    /**
     * @test
     * @enlighten
     */
    public function anotherUserFailUploadCommentToPrivateCategory(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();

        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create(['owner' => $owner->id]);

        $randArticle = $channel->articles->random();


        $randCategory = $randArticle->category;
        $randCategory->is_public = false;
        $this->assertTrue($randCategory->save());
        $randCategory = Category::find($randCategory->id);


        $requestUrl = route('channel.article.upload.comment', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randCategory->name,
            'channelArticle' => $randArticle->id,
        ]);

        $tryUploadComment = $this->postJson($requestUrl, $requestData = [
            'content' => Str::random(30)
        ])->assertUnauthorized();

        $this->assertFalse($tryUploadComment['ok']);
        $this->assertFalse($tryUploadComment['isValid']);

        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $tryUploadComment['messages']);
    }


    ######### success case #########
    /**
     * @test
     * @enlighten
     */
    public function successUploadCommentToPublicCategory(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create(['owner' => $activeUser->id]);

        $randArticle = $channel->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = true;

        $this->assertTrue($randCategory->save());


        $requestUrl = route('channel.article.upload.comment', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randArticle->category->name,
            'channelArticle' => $randArticle->id,
        ]);

        $tryUploadComment = $this->postJson($requestUrl, $requestData = [
            'content' => Str::random(30)
        ])->assertOk();

        $this->assertTrue($tryUploadComment['ok']);
        $this->assertTrue($tryUploadComment['isValid']);

        $this->assertEquals(['isSuccess' => true], $tryUploadComment['messages']);

        $this->assertTrue(
            Reply::where('content', $requestData['content'])->exists()
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successUploadChildCommentToPublicCategory(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create(['owner' => $activeUser->id]);

        $randArticle = $channel->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = true;

        $this->assertTrue($randCategory->save());


        $requestUrl = route('channel.article.upload.comment', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randArticle->category->name,
            'channelArticle' => $randArticle->id,
        ]);

        $tryUploadComment = $this->postJson($requestUrl, $requestData = [
            'content' => Str::random(30)
        ])->assertOk();

        $this->assertTrue($tryUploadComment['ok']);
        $this->assertTrue($tryUploadComment['isValid']);

        $this->assertEquals(['isSuccess' => true], $tryUploadComment['messages']);

        $comment = Reply::where('content', $requestData['content']);
        $this->assertTrue($comment->exists());
        $this->assertEquals(1, $comment->count());

        $tryUploadComment = $this->postJson($requestUrl, $childComment = [
            'content' => Str::random(30),
            'parent_id' => $comment->first()->id,
        ])->assertOk();

        $reply = Reply::where([
            ['content', '=', $childComment['content']],
            ['parent_id', '=', $childComment['parent_id']],
        ]);

        $this->assertTrue($reply->exists());
        $this->assertEquals(1, $reply->count());
    }

    /**
     * @test
     * @enlighten
     */
    public function normalUserSuccessUploadCommentToPublicCategory(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create(['owner' => $owner->id]);

        $randArticle = $channel->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = true;

        $this->assertTrue($randCategory->save());


        $requestUrl = route('channel.article.upload.comment', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randArticle->category->name,
            'channelArticle' => $randArticle->id,
        ]);

        $tryUploadComment = $this->postJson($requestUrl, $requestData =  [
            'content' => Str::random(30)
        ])->assertOk();

        $this->assertTrue($tryUploadComment['ok']);
        $this->assertTrue($tryUploadComment['isValid']);

        $this->assertEquals(['isSuccess' => true], $tryUploadComment['messages']);


        $this->assertTrue(
            Reply::where('content', $requestData['content'])->exists()
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successUploadCommentToPrivateCategory(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create(['owner' => $activeUser->id]);

        $randArticle = $channel->articles->random();


        $randCategory = $randArticle->category;
        $randCategory->is_public = false;
        $this->assertTrue($randCategory->save());
        $randCategory = Category::find($randCategory->id);


        $requestUrl = route('channel.article.upload.comment', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randCategory->name,
            'channelArticle' => $randArticle->id,
        ]);

        $tryUploadComment = $this->postJson($requestUrl, $requestData = [
            'content' => Str::random(30)
        ])->assertOk();

        $this->assertTrue($tryUploadComment['ok']);
        $this->assertTrue($tryUploadComment['isValid']);

        $this->assertEquals(['isSuccess' => true], $tryUploadComment['messages']);

        $this->assertTrue(
            Reply::where('content', $requestData['content'])->exists()
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function normalUserSuccessUploadCommentAndReplyToPublicCategory(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();

        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create(['owner' => $owner->id]);

        $randArticle = $channel->articles->random();


        $randCategory = $randArticle->category;
        $randCategory->is_public = true;
        $this->assertTrue($randCategory->save());
        $randCategory = Category::find($randCategory->id);


        $requestUrl = route('channel.article.upload.comment', [
            'slug' => $channel->slug,
            'channelBoardCategory' => $randCategory->name,
            'channelArticle' => $randArticle->id,
        ]);

        $tryUploadComment = $this->postJson($requestUrl, $requestData = [
            'content' => Str::random(30)
        ])->assertOk();

        $this->assertTrue($tryUploadComment['ok']);
        $this->assertTrue($tryUploadComment['isValid']);

        $this->assertEquals(['isSuccess' => true], $tryUploadComment['messages']);

        $this->assertTrue(
            Reply::where('content', $requestData['content'])->exists()
        );
    }
    ######### success case #########
}
