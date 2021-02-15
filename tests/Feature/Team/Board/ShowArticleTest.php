<?php

declare(strict_types=1);

namespace Tests\Feature\Team\Board;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Team\Board\ShowArticleRequest;

use App\Models\Team\Board\Category as TeamBoardCategory;
use App\Models\Team\Board\Article as TeamBoardArticle;
use App\Models\Team\Team;
use App\Models\User;
use App\Helpers\Image;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class ShowArticleTest extends TestCase
{
    /**
     * @todo is_public이 false리서 권한없어서 실패 -> required login, team_member
     */

    /**
     * @test
     * @enlighten
     */
    public function successLookupPublicArticleAndNotIncreaseSeeCount(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $team = factory(Team::class)->states([
            'addSlug',
            'addSmallTeamArticlesWithSavedImages'
        ])->create();

        $boardCategory = $team->boardCategories;

        $randCategoey = $boardCategory->get(
            Arr::random(
                $boardCategory->keys()->toArray()
            )
        );

        $randArticle = $randCategoey->articles->get(
            Arr::random(
                $randCategoey->articles->keys()->toArray()
            )
        );

        $this->assertEquals(0, $randArticle->see_count);

        $requestUrl = route('getTeamArticle', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategoey->name,
            'teamArticle' => $randArticle->id
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

        $team = factory(Team::class)->states([
            'addSlug',
            'addSmallTeamArticlesWithSavedImages'
        ])->create();
        $boardCategory = $team->boardCategories;

        $randCategoey = $boardCategory->get(
            Arr::random(
                $boardCategory->keys()->toArray()
            )
        );

        $requestUrl = route('getTeamArticle', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategoey->name,
            'teamArticle' => -1,
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
        $team = factory(Team::class)->states(['addSlug'])->create();

        $requestUrl = route('getTeamArticlesByCategory', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => Str::random(10),
        ]);

        $tryLookUpArticle = $this->getJson($requestUrl)->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryLookUpArticle['ok']);
        $this->assertFalse($tryLookUpArticle['isValid']);

        $this->assertEquals(['code' => ShowArticleRequest::CATEGORY_IS_NOT_EXISTS], $tryLookUpArticle['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookupPublicArticle(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $team = factory(Team::class)->states([
            'addSlug',
            'addSmallTeamArticlesWithSavedImages'
        ])->create();
        $boardCategory = $team->boardCategories;

        $randCategoey = $boardCategory->get(
            Arr::random(
                $boardCategory->keys()->toArray()
            )
        );

        $randArticle = $randCategoey->articles->get(
            Arr::random(
                $randCategoey->articles->keys()->toArray()
            )
        );

        $this->assertEquals(0, $randArticle->see_count);

        $requestUrl = route('getTeamArticle', [
            'teamSlug' => $team->slug,
            'teamBoardCategory' => $randCategoey->name,
            'teamArticle' => $randArticle->id
        ]);

        $tryLookUpArticle = $this->getJson($requestUrl)->assertOk();

        $messages = $tryLookUpArticle['messages'];
        $writerInfo = $messages['writerInfo'];

        $randArticle = TeamBoardArticle::find($randArticle->id);

        $this->assertTrue($tryLookUpArticle['ok']);
        $this->assertTrue($tryLookUpArticle['isValid']);

        $this->assertEquals($randArticle->id, $messages['id']);
        $this->assertEquals($randArticle->title, $messages['title']);
        $this->assertEquals($randArticle->content, $messages['content']);
        $this->assertEquals($randCategoey->id, $messages['category']);

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
        $team = factory(Team::class)->states(['addSlug', 'addSmallTeamArticlesWithSavedImages'])->create();

        $categories = $team->boardCategories->map(fn (TeamBoardCategory $category) => $category->name)->toArray();

        collect($categories)->each(function (string $category) use ($requestUser, $team) {
            $current = 1;

            do {
                $requestUrl = route('getTeamArticlesByCategory', [
                    'teamSlug' => $team->slug,
                    'teamBoardCategory' => $category,
                ]);

                $tryLookUpArticle = $this->getJson($requestUrl)->assertOk();
                $this->assertTrue($tryLookUpArticle['ok']);
                $this->assertTrue($tryLookUpArticle['isValid']);

                $articleInfo = $tryLookUpArticle['messages'];

                $this->assertEquals($category, $articleInfo['currentCategory']);


                collect($articleInfo['articles'])->each(function (array $article) use ($team, $articleInfo) {
                    $dbCatgory = TeamBoardCategory::where([
                        ['team_id', '=', $team->id],
                        ['name', '=', $articleInfo['currentCategory']],
                    ])->first();

                    $this->assertNotNull($dbCatgory);

                    $dbArticle = TeamBoardArticle::find($article['id']);

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
