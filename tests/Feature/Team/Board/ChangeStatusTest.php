<?php

declare(strict_types=1);

namespace Tests\Feature\Team\Board;

use App\Models\Team\Board\Category;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Wrappers\BoardWritePermission\Team as TeamCategoryWritePermission;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Team\Board\Category\ChangeStatusRequest as TeamBoardChangeRequest;
use App\Http\Requests\Rules\ChangeCategoryStatus as CommonBoardCategoryChangeStatus;
use Illuminate\Http\UploadedFile;
use Styde\Enlighten\Tests\EnlightenSetup;

class ChangeStatusTest extends TestCase
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
    public function failCreateCategoryWhenActiveUserHasNotPermission(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    // 'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => true,
                    'write_permission' => TeamCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertUnauthorized();

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            TeamBoardChangeRequest::CAN_NOT_UPDATE_CATEGORY,
            $tryChangeStatus['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateCategoryWhenCreateCategoryLimitOver(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    // 'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => true,
                    'write_permission' => TeamCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertUnauthorized();

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            TeamBoardChangeRequest::CAN_NOT_CREATE_CATEGORY,
            $tryChangeStatus['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateCategoryWhenWritePermissionIsNotAllowedPermission(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => true,
                    'write_permission' => -10,
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            CommonBoardCategoryChangeStatus::WRITE_PERMISSION_IS_NOT_ALLOWED_POLICY,
            $tryChangeStatus['messages']['code']
        );
    }


    /**
     * @test
     * @enlighten
     */
    public function failUpdateCategoryWhenWritePermissionIsNotInteger(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => true,
                    'write_permission' => 'asd',
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            CommonBoardCategoryChangeStatus::WRITE_PERMISSION_IS_NOT_INTEGER,
            $tryChangeStatus['messages']['code']
        );
    }


    /**
     * @test
     * @enlighten
     */
    public function failUpdateCategoryWhenWritePermissionIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => true,
                    'write_permission' => '',
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            CommonBoardCategoryChangeStatus::WRITE_PERMISSION_IS_EMPTY,
            $tryChangeStatus['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateCategoryWhenPublicStatusIEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => '',
                    'write_permission' => TeamCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            CommonBoardCategoryChangeStatus::PUBLIC_STATUS_IS_EMPTY,
            $tryChangeStatus['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateCategoryWhenPublicStatusIsNotBoolean(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => 'a',
                    'write_permission' => TeamCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            CommonBoardCategoryChangeStatus::PUBLIC_STATUS_IS_NOT_BOOLEAN,
            $tryChangeStatus['messages']['code']
        );
    }


    /**
     * @test
     * @enlighten
     */
    public function failUpdateCategoryWhenNameIsDuplicate(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                $willChangeName = Category::where([
                    ['team_id', '=', $boardCategory->team_id],
                    ['id', '!=', $boardCategory->id],
                ])->get('name')->random()->name;

                return [
                    'id' => $boardCategory->id,
                    'name' => $willChangeName,
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => TeamCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            CommonBoardCategoryChangeStatus::CATEGORY_NAME_IS_DUPLICATE,
            $tryChangeStatus['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateCategoryWhenNameIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => '',
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => TeamCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            CommonBoardCategoryChangeStatus::CATEGORY_NAME_IS_EMPTY,
            $tryChangeStatus['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateCategoryWhenNameIsNotString(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => -3,
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => TeamCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            CommonBoardCategoryChangeStatus::CATEGORY_NAME_IS_NOT_STRING,
            $tryChangeStatus['messages']['code']
        );
    }


    /**
     * @test
     * @enlighten
     */
    public function failUpdateCategoryWhenIdIsNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => -3,
                    'name' => Str::random(10),
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => TeamCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            CommonBoardCategoryChangeStatus::CATEGORY_ID_IS_NOT_EXISTS,
            $tryChangeStatus['messages']['code']
        );
    }




    /**
     * @test
     * @enlighten
     */
    public function failUpdateCategoryWhenIdIsNotInteger(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => 'asdf',
                    'name' => Str::random(10),
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => TeamCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            CommonBoardCategoryChangeStatus::CATEGORY_ID_IS_NOT_INTEGER,
            $tryChangeStatus['messages']['code']
        );
    }


    private function buildModelAssertData(int $teamId): array
    {
        return array_merge(
            Team::find($teamId)->boardCategories->sortBy(function (Category $boardCategory): int {
                return $boardCategory->show_order;
            })->map(function (Category $boardCategory): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => $boardCategory->name,
                    'is_public' => $boardCategory->is_public,
                    'write_permission' => $boardCategory->write_permission,
                ];
            })->toArray()
        );
    }

    ######################### success case #########################

    /**
     * @test
     * @enlighten
     */
    public function createAndUpdateCategory(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug',
        ])->create([
            'owner' => $user->id
        ]);

        Category::factory()->create([
            'show_order' => 0,
            'team_id' => $team->id,
        ]);

        $team = Team::find($team->id);

        $willCreateCategory = [
            'name' => Str::random(10),
            'is_public' => true,
            'write_permission' => TeamCategoryWritePermission::getAllPermissions()->values()->random(),
        ];


        $willChangeCategories = array_merge(
            $team->boardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => TeamCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->merge([$willCreateCategory])->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)->assertOk();

        $this->assertTrue($tryChangeStatus['ok']);
        $this->assertTrue($tryChangeStatus['isValid']);
        $this->assertTrue($tryChangeStatus['messages']['markCategoryUpdate']);


        $dbCategory = $this->buildModelAssertData($team->id);
        collect($willChangeCategories)->each(function (array $category, int $index) use ($dbCategory): void {
            if (isset($category['id'])) {
                $this->assertEquals(
                    $dbCategory[$index],
                    $category
                );
            } else {
                $dbCategoryWithRemoveId = $dbCategory[$index];
                unset($dbCategoryWithRemoveId['id']);
                $this->assertEquals(
                    $dbCategoryWithRemoveId,
                    $category
                );
            }
        });
    }

    /**
     * @test
     * @enlighten
     */
    public function updateAllStatus(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => TeamCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)->assertOk();

        $this->assertTrue($tryChangeStatus['ok']);
        $this->assertTrue($tryChangeStatus['isValid']);
        $this->assertTrue($tryChangeStatus['messages']['markCategoryUpdate']);

        $this->assertEquals(
            $willChangeCategories,
            $this->buildModelAssertData($team->id)
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function updateCategoryNameWhenUseAnotherTeamCategoryName(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $flag = true;
        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder) use (&$flag): array {
                if ($flag) {
                    $flag = false;
                    $anotherName = Category::where([
                        ['team_id', '!=', $boardCategory->team_id]
                    ])->get('name')->random()->name;
                    return [
                        'id' => $boardCategory->id,
                        'name' => $anotherName,
                        'is_public' => $boardCategory->is_public,
                        'write_permission' => $boardCategory->write_permission,
                    ];
                }

                return [
                    'id' => $boardCategory->id,
                    'name' => $boardCategory->name,
                    'is_public' => $boardCategory->is_public,
                    'write_permission' => $boardCategory->write_permission,
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)->assertOk();

        $this->assertTrue($tryChangeStatus['ok']);
        $this->assertTrue($tryChangeStatus['isValid']);
        $this->assertTrue($tryChangeStatus['messages']['markCategoryUpdate']);

        $this->assertEquals(
            $willChangeCategories,
            $this->buildModelAssertData($team->id)
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function updateWritePermission(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $flag = true;
        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder) use (&$flag): array {
                if ($flag) {
                    $flag = false;
                    return [
                        'id' => $boardCategory->id,
                        'name' => $boardCategory->name,
                        'is_public' => $boardCategory->is_public,
                        'write_permission' => TeamCategoryWritePermission::getAllPermissions()->values()->random(),
                    ];
                }

                return [
                    'id' => $boardCategory->id,
                    'name' => $boardCategory->name,
                    'is_public' => $boardCategory->is_public,
                    'write_permission' => $boardCategory->write_permission,
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)->assertOk();

        $this->assertTrue($tryChangeStatus['ok']);
        $this->assertTrue($tryChangeStatus['isValid']);
        $this->assertTrue($tryChangeStatus['messages']['markCategoryUpdate']);

        $this->assertEquals(
            $willChangeCategories,
            $this->buildModelAssertData($team->id)
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function deleteCategory(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $teamBoardCategories = Team::find($team->id)->boardCategories;

        $willChangeCategories = array_merge(
            $teamBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => $boardCategory->name,
                    'is_public' => $boardCategory->is_public,
                    'write_permission' => $boardCategory->write_permission,
                ];
            })->toArray()
        );

        unset($willChangeCategories[0]);

        $willChangeCategories = array_merge($willChangeCategories);

        $requestUrl = route('team.changeCategory', [
            'teamSlug' => $team->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)->assertOk();

        $this->assertTrue($tryChangeStatus['ok']);
        $this->assertTrue($tryChangeStatus['isValid']);
        $this->assertTrue($tryChangeStatus['messages']['markCategoryUpdate']);

        $this->assertEquals(
            $willChangeCategories,
            $this->buildModelAssertData($team->id)
        );
    }

    ######################### success case #########################
}
