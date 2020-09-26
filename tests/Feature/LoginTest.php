<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    public function test_이메일_입력_안함(): void
    {
        $testUrl = route('verify');
        $response = $this->post($testUrl, [
            'password' => 'password',
        ])->assertStatus(422);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(1, $response['messages']['code']);
    }

    public function test_비밀번호_입력_안함(): void
    {
        $testUrl = route('verify');
        $user = factory(User::class)->create();
        $response = $this->post($testUrl, [
            'email' => $user->email
        ])->assertStatus(422);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(4, $response['messages']['code']);
    }


    public function test_올바르지_않은_이메일_입력(): void
    {
        $testUrl = route('verify');
        $user = factory(User::class)->create();
        $response = $this->post($testUrl, [
            'email' => 'asdfasdfsdfasdfasdfasdf',
            'password' => 'password',
        ])->assertStatus(422);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(2, $response['messages']['code']);
    }

    public function test_인증_실패(): void
    {
        $testUrl = route('verify');
        $user = factory(User::class)->create();
        $response = $this->post($testUrl, [
            'email' => $user->email,
            'password' => 'dldf',
        ])->assertStatus(401);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(401, $response['messages']['code']);
    }


    public function test_존재하지_않는_유저_입력(): void
    {
        $testUrl = route('verify');
        $user = factory(User::class)->create();
        $response = $this->post($testUrl, [
            'email' => 'asdfasdfsdfasdfasdfasdf@asdf.com',
            'password' => 'password',
        ])->assertStatus(422);
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(3, $response['messages']['code']);
    }

    public function test_유저_로그인_성공(): void
    {
        $testUrl = route('verify');
        $user = factory(User::class)->create();

        $response = $this->post($testUrl, [
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
        ], $tmpMessages);
        $this->assertIsString($response['messages']['token']);
    }
}
