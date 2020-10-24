<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Illuminate\Support\Facades\Http;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

class ReSendVerifyEmail extends TestCase
{
    private string $testUrl;
    public function setUp(): void
    {
        parent::setUp();
        $this->testUrl = route('resendVerifyEmail');
        Http::fake([
            'directsend.co.kr/*' => Http::response(['status' => '0'])
        ]);
    }

    /** @test */
    public function 이메일_재발송에_성공하라(): void
    {
        $user = factory(User::class)->create();
        $user->email_verified_at = null;
        $user->save();
        $this->assertNull($user->email_verified_at);

        Sanctum::actingAs($user);

        $tryResendEmailVerification = $this->postJson($this->testUrl)
                                           ->assertOk();

        $this->assertTrue($tryResendEmailVerification['ok']);
        $this->assertTrue($tryResendEmailVerification['isValid']);
        $this->assertTrue($tryResendEmailVerification['messages']['sendEmailVerification']);
    }

    /** @test */
    public function 이미_인증한_유저가_이메일_요청에_실패하라(): void
    {
        $user = factory(User::class)->create();
        $this->assertNotNull($user->email_verified_at);

        Sanctum::actingAs($user);
        $tryResendEmailVerification = $this->postJson($this->testUrl)
                                           ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->assertFalse($tryResendEmailVerification['ok']);
        $this->assertFalse($tryResendEmailVerification['isValid']);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $tryResendEmailVerification['messages']['code']);
    }
}
