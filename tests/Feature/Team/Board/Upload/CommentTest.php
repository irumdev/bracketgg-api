<?php

declare(strict_types=1);

namespace Tests\Feature\Team\Board\Upload;

use App\Models\Team\Board\Reply;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Rules\UploadBoardArticleComment;
use App\Models\Team\Member;

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
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create(['owner' => $activeUser->id]);

        $randArticle = $team->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = true;

        $this->assertTrue($randCategory->save());

        $requestUrl = route('team.article.upload.comment', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randArticle->category->name,
            'teamArticle' => $randArticle->id,
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
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create(['owner' => $activeUser->id]);

        $randArticle = $team->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = true;

        $this->assertTrue($randCategory->save());

        $requestUrl = route('team.article.upload.comment', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randArticle->category->name,
            'teamArticle' => $randArticle->id,
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
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create(['owner' => $activeUser->id]);

        $randArticle = $team->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = true;

        $this->assertTrue($randCategory->save());

        $requestUrl = route('team.article.upload.comment', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randArticle->category->name,
            'teamArticle' => $randArticle->id,
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
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create(['owner' => $activeUser->id]);

        $randArticle = $team->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = true;

        $this->assertTrue($randCategory->save());

        $requestUrl = route('team.article.upload.comment', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randArticle->category->name,
            'teamArticle' => $randArticle->id,
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
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create(['owner' => $owner->id]);

        $randArticle = $team->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = false;

        $this->assertTrue($randCategory->save());

        $requestUrl = route('team.article.upload.comment', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randArticle->category->name,
            'teamArticle' => $randArticle->id,
        ]);

        $tryUploadComment = $this->postJson($requestUrl, $requestData = [
            'content' => Str::random(30)
        ])->assertUnauthorized();

        $this->assertFalse($tryUploadComment['ok']);
        $this->assertFalse($tryUploadComment['isValid']);

        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $tryUploadComment['messages']);
    }

    ############# success case #############

    /**
     * @test
     * @enlighten
     */
    public function successUploadCommentToPublicCategory(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create(['owner' => $owner->id]);

        $randArticle = $team->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = true;

        $this->assertTrue($randCategory->save());

        $requestUrl = route('team.article.upload.comment', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randArticle->category->name,
            'teamArticle' => $randArticle->id,
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
    }


    /**
     * @test
     * @enlighten
     */
    public function teamMemberSuccessUploadCommentToPrivateCategory(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create(['owner' => $owner->id]);

        Member::factory()->create([
            'user_id' => $requestUser->id,
            'team_id' => $team->id,
        ]);

        $randArticle = $team->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = false;

        $this->assertTrue($randCategory->save());

        $requestUrl = route('team.article.upload.comment', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randArticle->category->name,
            'teamArticle' => $randArticle->id,
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
    }

    /**
     * @test
     * @enlighten
     */
    public function successUploadChildCommentToPublicCategory(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create(['owner' => $activeUser->id]);

        $randArticle = $team->articles->random();

        $randCategory = $randArticle->category;

        $randCategory->is_public = true;

        $this->assertTrue($randCategory->save());


        $requestUrl = route('team.article.upload.comment', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randArticle->category->name,
            'teamArticle' => $randArticle->id,
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
    ############# success case #############
}
