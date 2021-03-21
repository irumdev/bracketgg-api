<?php

declare(strict_types=1);

namespace Tests\Feature\Team\Board\Upload;

use App\Models\Team\Board\Category;
use App\Models\Team\Member;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Styde\Enlighten\Tests\EnlightenSetup;
use App\Wrappers\BoardWritePermission\Team as TeamArticleWritePermissions;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Rules\UploadBoardArticleImage;

class Imagetest extends TestCase
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
    public function failUploadTeamBoardArticleImageWhenTeamImageIsLarge(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $team->boardCategories->random();

        $randomBoardCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            TeamArticleWritePermissions::ONLY_OWNER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('team.article.upload.image', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randomBoardCategory->name
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
    public function failUploadTeamBoardArticleImageWhenTeamImageMimeIsNotValid(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $team->boardCategories->random();

        $randomBoardCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            TeamArticleWritePermissions::ONLY_OWNER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('team.article.upload.image', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randomBoardCategory->name
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
    public function failUploadTeamBoardArticleImageWhenTeamImageIsNotImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $team->boardCategories->random();

        $randomBoardCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            TeamArticleWritePermissions::ONLY_OWNER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('team.article.upload.image', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randomBoardCategory->name
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
    public function failUploadTeamBoardArticleImageWhenTeamImageIsNotAttached(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $team->boardCategories->random();

        $randomBoardCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            TeamArticleWritePermissions::ONLY_OWNER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('team.article.upload.image', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randomBoardCategory->name
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
    public function failUploadTeamBoardArticleImageWhenUserNotLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = factory(User::class)->create();
        $owner = factory(User::class)->create();

        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $team->boardCategories->random();

        $randomBoardCategory->write_permission = TeamArticleWritePermissions::ALL_USER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            TeamArticleWritePermissions::ALL_USER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('team.article.upload.image', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randomBoardCategory->name
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
    public function failUploadTeamBoardArticleImageWhenRequestUserIsMemberButCategoryPermissionIsOnlyOwner(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        Member::factory()->create([
            'team_id' => $team->id,
            'user_id' => $activeUser->id
        ]);

        $randomBoardCategory = $team->boardCategories->random();

        $randomBoardCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            TeamArticleWritePermissions::ONLY_OWNER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('team.article.upload.image', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadTeamArticleImage = $this->postJson($requestUrl, [
            'article_image' => UploadedFile::fake()->create('test.png', 1000)
        ])->assertUnauthorized();

        $this->assertFalse($tryUploadTeamArticleImage['ok']);
        $this->assertFalse($tryUploadTeamArticleImage['isValid']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUploadTeamBoardArticleImageWhenRequestUserUserIsAnotherUser(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $team->boardCategories->random();

        $randomBoardCategory->write_permission = TeamArticleWritePermissions::OWNER_AND_MEMBER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            TeamArticleWritePermissions::OWNER_AND_MEMBER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('team.article.upload.image', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadTeamArticleImage = $this->postJson($requestUrl, [
            'article_image' => UploadedFile::fake()->create('test.png', 1000)
        ])->assertUnauthorized();

        $this->assertFalse($tryUploadTeamArticleImage['ok']);
        $this->assertFalse($tryUploadTeamArticleImage['isValid']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUploadTeamBoardArticleImageWhenRequestUserIsAnotherUserButCategoryPermissionIsOnlyOwner(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $randomBoardCategory = $team->boardCategories->random();

        $randomBoardCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            TeamArticleWritePermissions::ONLY_OWNER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('team.article.upload.image', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadTeamArticleImage = $this->postJson($requestUrl, [
            'article_image' => UploadedFile::fake()->create('test.png', 1000)
        ])->assertUnauthorized();

        $this->assertFalse($tryUploadTeamArticleImage['ok']);
        $this->assertFalse($tryUploadTeamArticleImage['isValid']);
    }

    ##############################################################
    ######################## success case ########################
    ##############################################################

    /**
     * @test
     * @enlighten
     */
    public function successUploadWhenTeamMemberUploadImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        Member::factory()->create([
            'team_id' => $team->id,
            'user_id' => $activeUser->id
        ]);

        $randomBoardCategory = $team->boardCategories->random();

        $randomBoardCategory->write_permission = TeamArticleWritePermissions::OWNER_AND_MEMBER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            TeamArticleWritePermissions::OWNER_AND_MEMBER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('team.article.upload.image', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadTeamArticleImage = $this->postJson($requestUrl, [
            'article_image' => UploadedFile::fake()->create('test.png', 1000)
        ])->assertOk();

        $this->assertTrue($tryUploadTeamArticleImage['ok']);
        $this->assertTrue($tryUploadTeamArticleImage['isValid']);
        $this->get($tryUploadTeamArticleImage['messages']['imageUrl'])->assertOk();
    }

    /**
     * @test
     * @enlighten
     */
    public function successUploadWhenTeamOwnerUploadImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);
        $randomBoardCategory = $team->boardCategories->random();

        $randomBoardCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            TeamArticleWritePermissions::ONLY_OWNER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('team.article.upload.image', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadTeamArticleImage = $this->postJson($requestUrl, [
            'article_image' => UploadedFile::fake()->create('test.png', 1000)
        ])->assertOk();

        $this->assertTrue($tryUploadTeamArticleImage['ok']);
        $this->assertTrue($tryUploadTeamArticleImage['isValid']);
        $this->get($tryUploadTeamArticleImage['messages']['imageUrl'])->assertOk();
    }

    /**
     * @test
     * @enlighten
     */
    public function successUploadWhenAnyUserUploadImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);
        $randomBoardCategory = $team->boardCategories->random();

        $randomBoardCategory->write_permission = TeamArticleWritePermissions::ALL_USER;

        $this->assertTrue($randomBoardCategory->save());

        $randomBoardCategory = Category::find($randomBoardCategory->id);

        $this->assertEquals(
            TeamArticleWritePermissions::ALL_USER,
            $randomBoardCategory->write_permission
        );

        $requestUrl = route('team.article.upload.image', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randomBoardCategory->name
        ]);

        $tryUploadTeamArticleImage = $this->postJson($requestUrl, [
            'article_image' => UploadedFile::fake()->create('test.png', 1000)
        ])->assertOk();

        $this->assertTrue($tryUploadTeamArticleImage['ok']);
        $this->assertTrue($tryUploadTeamArticleImage['isValid']);
        $this->get($tryUploadTeamArticleImage['messages']['imageUrl'])->assertOk();
    }


    ##############################################################
    ######################## success case ########################
    ##############################################################
}
