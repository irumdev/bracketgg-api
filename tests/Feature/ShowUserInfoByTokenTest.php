<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class ShowUserInfoByTokenTest extends TestCase
{
    /** @test */
    public function 토큰으로_유저의_정보를_조회하라(): void
    {
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
            ],
            $response['messages'],
        );
    }

    /** @test */
    public function 로그인_안한_유저의_정보조회에_실패하라(): void
    {
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
