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

use Illuminate\Support\Str;

class ShowArticleTest extends TestCase
{
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
