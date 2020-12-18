<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    /** @test */
    public function failLoginWithoutEmail(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('verify');
        $response = $this->postJson($testUrl, [
            'password' => 'password',
        ])->assertStatus(422);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(1, $response['messages']['code']);
    }

    /** @test */
    public function failLoginWithoutPassword(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('verify');
        $user = factory(User::class)->create();
        $response = $this->postJson($testUrl, [
            'email' => $user->email
        ])->assertStatus(422);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(4, $response['messages']['code']);
    }

    /** @test */
    public function failLoginWithInvalidEmail(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('verify');
        $user = factory(User::class)->create();
        $response = $this->postJson($testUrl, [
            'email' => 'asdfasdfsdfasdfasdfasdf',
            'password' => 'password',
        ])->assertStatus(422);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(2, $response['messages']['code']);
    }

    /** @test */
    public function failLoginWithWrongPassword(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('verify');
        $user = factory(User::class)->create();
        $response = $this->postJson($testUrl, [
            'email' => $user->email,
            'password' => 'dldf',
        ])->assertStatus(401);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(401, $response['messages']['code']);
    }

    /** @test */
    public function failLoginWithNotExistsEmail(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('verify');
        $user = factory(User::class)->create();
        $response = $this->postJson($testUrl, [
            'email' => 'asdfasdfsdfasdfasdfasdf@asdf.com',
            'password' => 'password',
        ])->assertStatus(422);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(3, $response['messages']['code']);
    }

    /** @test */
    public function successLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('verify');
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

    /** @test */
    public function successLoginWithUndefinedProfileUser(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testUrl = route('verify');
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
