<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    /** @test */
    public function 이메일_입력안하고_로그인을_시도하라(): void
    {
        $testUrl = route('verify');
        $response = $this->postJson($testUrl, [
            'password' => 'password',
        ])->assertStatus(422);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(1, $response['messages']['code']);
    }

    /** @test */
    public function 비밀번호_입력안하고_로그인을_시도하라(): void
    {
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
    public function 올바르지_않은_이메일_입력하고_로그인을_시도하라(): void
    {
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
    public function 비밀번호를_틀리고_로그인_시도하라(): void
    {
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
    public function 없는_이메일로_로그인_시도하라(): void
    {
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
    public function 로그인_성공하라(): void
    {
        $testUrl = route('verify');
        $user = factory(User::class)->state('addProfileImage')->create();
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
            ])
        ], $tmpMessages);
        $this->assertIsString($response['messages']['token']);
    }

    /** @test */
    public function 프로필_이미지가_없는_유저의_로그인_성공하라(): void
    {
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
            'profileImage' => null
        ], $tmpMessages);
        $this->assertIsString($response['messages']['token']);
    }
}
