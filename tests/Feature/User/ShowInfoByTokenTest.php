<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Styde\Enlighten\Tests\EnlightenSetup;

class ShowInfoByTokenTest extends TestCase
{
    use EnlightenSetup;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookUpUserInfoWithBearerToken(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->state('addProfileImage')->create());
        $response = $this->getJson(route('currentUser'))->assertOk();

        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $this->assertEquals(
            [
                'id' => $activeUser->id,
                'nickName' => $activeUser->nick_name,
                'email' => $activeUser->email,
                'profileImage' => $userProfileImageUrl = route('profileImage', [
                    'profileImage' => $activeUser->profile_image
                ]),
                'createdAt' => $activeUser->created_at,
            ],
            $response['messages'],
        );
        if (config('app.test.useRealImage')) {
            $this->get($userProfileImageUrl)->assertOk();
        }
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookUpDonthaveProfileImageUserInfoWithBearerToken(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $response = $this->getJson(route('currentUser'))
                         ->assertOk();

        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $this->assertEquals(
            [
                'id' => $activeUser->id,
                'nickName' => $activeUser->nick_name,
                'email' => $activeUser->email,
                'profileImage' => null,
                'createdAt' => $activeUser->created_at,
            ],
            $response['messages'],
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookUpUserInfoWithOutLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $response = $this->getJson(route('currentUser'))
                         ->assertUnauthorized();

        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);

        $this->assertEquals(
            ['code' => 401],
            $response['messages']
        );
    }
}
