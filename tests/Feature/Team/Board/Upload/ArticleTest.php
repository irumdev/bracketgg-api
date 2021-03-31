<?php

declare(strict_types=1);

namespace Tests\Feature\Team\Board\Upload;

use App\Models\Team\Board\Article;
use App\Models\Team\Board\Category;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Styde\Enlighten\Tests\EnlightenSetup;
use App\Wrappers\BoardWritePermission\Team as TeamArticleWritePermissions;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Rules\UploadBoardArticle;
use App\Models\Team\Member;

class ArticleTest extends TestCase
{
    use EnlightenSetup;

    private string $baseStubPath = '';
    private string $successMarkup = '';
    private string $failMarkup = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseStubPath = realpath(__DIR__ . '/../../../../../stubs/uploadArticleTest');
        $this->setUpEnlighten();
    }

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


    /**
     * @test
     * @enlighten
     */
    public function failWhenTeamOwnerTryUploadArticleButTitleIsNotString(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);

        $testHtml = $this->getSuccessMarkup();

        $randCategory = $team->boardCategories->random();

        $randCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('team.article.upload.article', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategory->name
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
    public function failWhenTeamOwnerTryUploadArticleButArticleIsNotString(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);

        $testHtml = $this->getSuccessMarkup();

        $randCategory = $team->boardCategories->random();

        $randCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('team.article.upload.article', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategory->name
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
    public function failWhenTeamOwnerTryUploadArticleButTitleIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);

        $testHtml = $this->getSuccessMarkup();

        $randCategory = $team->boardCategories->random();

        $randCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('team.article.upload.article', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategory->name
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
    public function failWhenTeamOwnerTryUploadArticleButArticleIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);


        $randCategory = $team->boardCategories->random();

        $randCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('team.article.upload.article', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategory->name
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
    public function failWhenTeamOwnerTryUploadArticleButArticleHasXSSMarkup(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);

        $testHtml = $this->getFailMarkup();
        $randCategory = $team->boardCategories->random();

        $randCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('team.article.upload.article', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategory->name
        ]);

        $tryUploadArticle = $this->postJson($requestUrl, $requestData = [
            'article' => $testHtml,
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
    public function failUploadArticleWhenUserIsNotTeamMemberButCategoryPermissionIsTeamMember(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = factory(User::class)->create();

        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $testHtml = $this->getSuccessMarkup();

        $randCategory = $team->boardCategories->random();

        $randCategory->write_permission = TeamArticleWritePermissions::OWNER_AND_MEMBER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('team.article.upload.article', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategory->name
        ]);

        $tryUploadArticle = $this->postJson($requestUrl, $requestData =  [
            'article' => $testHtml,
            'title' => Str::random(10)
        ])->assertUnauthorized();


        $this->assertFalse($tryUploadArticle['ok']);
        $this->assertFalse($tryUploadArticle['isValid']);

        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $tryUploadArticle['messages']);
    }


    /**
     * @test
     * @enlighten
     */
    public function failUploadArticleWhenUserIsNotTeamMemberButCategoryPermissionIsOnlyOwner(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = factory(User::class)->create();

        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        Member::factory()->create([
            'team_id' => $team->id,
            'user_id' => $activeUser->id,
            'role' => Team::NORMAL_USER
        ]);

        $testHtml = $this->getSuccessMarkup();

        $randCategory = $team->boardCategories->random();

        $randCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('team.article.upload.article', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategory->name
        ]);

        $tryUploadArticle = $this->postJson($requestUrl, $requestData =  [
            'article' => $testHtml,
            'title' => Str::random(10)
        ])->assertUnauthorized();


        $this->assertFalse($tryUploadArticle['ok']);
        $this->assertFalse($tryUploadArticle['isValid']);

        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $tryUploadArticle['messages']);
    }



    /**
     * @test
     * @enlighten
     */
    public function failUploadArticleWhenUserIsTeamMemberButCategoryPermissionIsOnlyOwner(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = factory(User::class)->create();

        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $testHtml = $this->getSuccessMarkup();

        $randCategory = $team->boardCategories->random();

        $randCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('team.article.upload.article', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategory->name
        ]);

        $tryUploadArticle = $this->postJson($requestUrl, $requestData =  [
            'article' => $testHtml,
            'title' => Str::random(10)
        ])->assertUnauthorized();


        $this->assertFalse($tryUploadArticle['ok']);
        $this->assertFalse($tryUploadArticle['isValid']);

        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $tryUploadArticle['messages']);
    }


    ##################### success case #####################


    /**
     * @test
     * @enlighten
     */
    public function successUploadArticleTeamMember(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = factory(User::class)->create();

        $owner = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);


        Member::factory()->create([
            'team_id' => $team->id,
            'user_id' => $activeUser->id,
            'role' => Team::NORMAL_USER
        ]);
        $testHtml = $this->getSuccessMarkup();

        $randCategory = $team->boardCategories->random();

        $randCategory->write_permission = TeamArticleWritePermissions::OWNER_AND_MEMBER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('team.article.upload.article', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategory->name
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
    public function successUploadArticleAnyUser(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = factory(User::class)->create();

        $owner = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $testHtml = $this->getSuccessMarkup();

        $randCategory = $team->boardCategories->random();

        $randCategory->write_permission = TeamArticleWritePermissions::ALL_USER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('team.article.upload.article', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategory->name
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
    public function successUploadArticleOnlyTeamOwner(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]);

        $testHtml = $this->getSuccessMarkup();

        $randCategory = $team->boardCategories->random();

        $randCategory->write_permission = TeamArticleWritePermissions::ONLY_OWNER;

        $this->assertTrue($randCategory->save());

        $randCategory = Category::find($randCategory->id);

        $requestUrl = route('team.article.upload.article', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategory->name
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
