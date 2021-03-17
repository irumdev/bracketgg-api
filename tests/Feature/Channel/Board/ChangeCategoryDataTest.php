<?php

declare(strict_types=1);

namespace Tests\Feature\Channel\Board;

use App\Models\Channel\Board\Category;
use App\Models\Channel\Channel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

use Tests\TestCase;
use Styde\Enlighten\Tests\EnlightenSetup;
use App\Wrappers\BoardWritePermission\Channel as ChannelCategoryWritePermission;
use App\Http\Requests\Rules\ChangeCategoryStatus as CommonBoardCategoryChangeStatus;
use App\Http\Requests\Channel\Board\Category\ChangeStatusRequest as ChannelBoardChangeRequest;

use Illuminate\Support\Str;

class ChangeCategoryDataTest extends TestCase
{
    use EnlightenSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
    }

    ################################ success case ################################

    /**
     * @test
     * @enlighten
     */
    public function createAndUpdateCategory(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug',
        ])->create([
            'owner' => $user->id
        ]);

        Category::factory()->create([
            'show_order' => 0,
            'channel_id' => $channel->id,
        ]);

        $channel = Channel::find($channel->id);

        $willCreateCategory = [
            'name' => Str::random(10),
            'is_public' => true,
            'write_permission' => ChannelCategoryWritePermission::getAllPermissions()->values()->random(),
        ];


        $willChangeCategories = array_merge(
            $channel->boardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => ChannelCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->merge([$willCreateCategory])->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)->assertOk();

        $this->assertTrue($tryChangeStatus['ok']);
        $this->assertTrue($tryChangeStatus['isValid']);
        $this->assertTrue($tryChangeStatus['messages']['markCategoryUpdate']);


        $dbCategory = $this->buildModelAssertData($channel->id);
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
    public function updateCategoryNameWhenUseAnotherChannelCategoryName(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $flag = true;
        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder) use (&$flag): array {
                if ($flag) {
                    $flag = false;
                    $anotherName = Category::where([
                        ['channel_id', '!=', $boardCategory->channel_id]
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

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)->assertOk();

        $this->assertTrue($tryChangeStatus['ok']);
        $this->assertTrue($tryChangeStatus['isValid']);
        $this->assertTrue($tryChangeStatus['messages']['markCategoryUpdate']);

        $this->assertEquals(
            $willChangeCategories,
            $this->buildModelAssertData($channel->id)
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
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $flag = true;
        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder) use (&$flag): array {
                if ($flag) {
                    $flag = false;
                    return [
                        'id' => $boardCategory->id,
                        'name' => $boardCategory->name,
                        'is_public' => $boardCategory->is_public,
                        'write_permission' => ChannelCategoryWritePermission::getAllPermissions()->values()->random(),
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

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)->assertOk();

        $this->assertTrue($tryChangeStatus['ok']);
        $this->assertTrue($tryChangeStatus['isValid']);
        $this->assertTrue($tryChangeStatus['messages']['markCategoryUpdate']);

        $this->assertEquals(
            $willChangeCategories,
            $this->buildModelAssertData($channel->id)
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
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
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

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)->assertOk();

        $this->assertTrue($tryChangeStatus['ok']);
        $this->assertTrue($tryChangeStatus['isValid']);
        $this->assertTrue($tryChangeStatus['messages']['markCategoryUpdate']);

        $this->assertEquals(
            $willChangeCategories,
            $this->buildModelAssertData($channel->id)
        );
    }


    /**
     * @test
     * @enlighten
     */
    public function updateAllStatus(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;


        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => ChannelCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)->assertOk();

        $this->assertTrue($tryChangeStatus['ok']);
        $this->assertTrue($tryChangeStatus['isValid']);
        $this->assertTrue($tryChangeStatus['messages']['markCategoryUpdate']);

        $this->assertEquals(
            $willChangeCategories,
            $this->buildModelAssertData($channel->id)
        );
    }


    ################################ success case ################################

    /**
     * @test
     * @enlighten
     */
    public function failCreateCategoryWhenActiveUserHasNotPermission(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $owner->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    // 'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => true,
                    'write_permission' => ChannelCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertUnauthorized();

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            ChannelBoardChangeRequest::CAN_NOT_UPDATE_CATEGORY,
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
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    // 'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => true,
                    'write_permission' => ChannelCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
        ]);

        $tryChangeStatus = $this->postJson($requestUrl, $willChangeCategories)
                                ->assertUnauthorized();

        $this->assertFalse($tryChangeStatus['ok']);
        $this->assertFalse($tryChangeStatus['isValid']);
        $this->assertEquals(
            ChannelBoardChangeRequest::CAN_NOT_CREATE_CATEGORY,
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
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => true,
                    'write_permission' => -10,
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
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
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => true,
                    'write_permission' => 'asd',
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
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
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => true,
                    'write_permission' => '',
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
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
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => '',
                    'write_permission' => ChannelCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
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
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => Str::random(10),
                    'is_public' => 'a',
                    'write_permission' => ChannelCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
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
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                $willChangeName = Category::where([
                    ['channel_id', '=', $boardCategory->channel_id],
                    ['id', '!=', $boardCategory->id],
                ])->get('name')->random()->name;

                return [
                    'id' => $boardCategory->id,
                    'name' => $willChangeName,
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => ChannelCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
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
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => '',
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => ChannelCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
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
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => $boardCategory->id,
                    'name' => -3,
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => ChannelCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
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
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => -3,
                    'name' => Str::random(10),
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => ChannelCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
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
        $channel = factory(Channel::class)->states([
            'addSlug', 'addSmallChannelArticlesWithSavedImages'
        ])->create([
            'owner' => $user->id
        ]);

        $channelBoardCategories = Channel::find($channel->id)->boardCategories;

        $willChangeCategories = array_merge(
            $channelBoardCategories->map(function (Category $boardCategory, int $showOrder): array {
                return [
                    'id' => 'asdf',
                    'name' => Str::random(10),
                    'is_public' => ! $boardCategory->is_public,
                    'write_permission' => ChannelCategoryWritePermission::getAllPermissions()->values()->random(),
                ];
            })->shuffle()->toArray()
        );

        $requestUrl = route('channel.changeCategory', [
            'slug' => $channel->slug
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


    private function buildModelAssertData(int $channelId): array
    {
        return array_merge(
            Channel::find($channelId)->boardCategories->sortBy(function (Category $boardCategory): int {
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
}
