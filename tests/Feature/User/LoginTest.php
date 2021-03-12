<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

use Styde\Enlighten\Tests\EnlightenSetup;

class LoginTest extends TestCase
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
    public function failLoginWithoutEmail(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('user.auth');
        $response = $this->postJson($testUrl, [
            'password' => 'password',
        ])->assertStatus(422);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(1, $response['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLoginWithoutPassword(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('user.auth');
        $user = factory(User::class)->create();
        $response = $this->postJson($testUrl, [
            'email' => $user->email
        ])->assertStatus(422);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(4, $response['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLoginWithInvalidEmail(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('user.auth');
        $user = factory(User::class)->create();
        $response = $this->postJson($testUrl, [
            'email' => 'asdfasdfsdfasdfasdfasdf',
            'password' => 'password',
        ])->assertStatus(422);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(2, $response['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLoginWithWrongPassword(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('user.auth');
        $user = factory(User::class)->create();
        $response = $this->postJson($testUrl, [
            'email' => $user->email,
            'password' => 'dldf',
        ])->assertUnauthorized();
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertUnauthorizedMessages($response['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLoginWithNotExistsEmail(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('user.auth');
        $user = factory(User::class)->create();
        $response = $this->postJson($testUrl, [
            'email' => 'asdfasdfsdfasdfasdfasdf@asdf.com',
            'password' => 'password',
        ])->assertStatus(422);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(3, $response['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('user.auth');
        $user = factory(User::class)->states(['addProfileImage'])->create();
        $response = $this->postJson($testUrl, [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $tmpMessages = $response['messages'];
        unset($tmpMessages['token']);
        $this->assertTrue($response['isValid']);
        $this->assertTrue($response['ok']);
        $this->assertEquals([
            'id' => $user->id,
            'nickName' => $user->nick_name,
            'email' => $user->email,
            'profileImage' => route('profileImage', [
                'profileImage' => $user->profile_image
            ]),
            'createdAt' => $user->created_at,
        ], $tmpMessages);
        $this->assertIsString($response['messages']['token']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successLoginWithUndefinedProfileUser(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('user.auth');
        $user = factory(User::class)->create();

        $response = $this->postJson($testUrl, [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $tmpMessages = $response['messages'];
        unset($tmpMessages['token']);
        $this->assertTrue($response['isValid']);
        $this->assertTrue($response['ok']);
        $this->assertEquals([
            'id' => $user->id,
            'nickName' => $user->nick_name,
            'email' => $user->email,
            'profileImage' => null,
            'createdAt' => $user->created_at,
        ], $tmpMessages);
        $this->assertIsString($response['messages']['token']);
    }
}
