<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Illuminate\Support\Facades\Http;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

use Styde\Enlighten\Tests\EnlightenSetup;

class ReSendVerifyEmailTest extends TestCase
{
    use EnlightenSetup;

    private string $testUrl;
    public function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();

        $this->testUrl = route('user.resendVerifyEmail');
        Http::fake([
            'directsend.co.kr/*' => Http::response(['status' => '0'])
        ]);
    }

    /**
     * @test
     * @enlighten
     */
    public function successSendVerifyEmail(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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

    /**
     * @test
     * @enlighten
     */
    public function failSendVerifyEmailWhenAlreadyUserVerifyEmail(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = factory(User::class)->create();
        $this->assertNotNull($user->email_verified_at);

        Sanctum::actingAs($user);
        $tryResendEmailVerification = $this->postJson($this->testUrl)
                                           ->assertUnauthorized();

        $this->assertFalse($tryResendEmailVerification['ok']);
        $this->assertFalse($tryResendEmailVerification['isValid']);
        $this->assertUnauthorizedMessages($tryResendEmailVerification['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failSendVerifyEmailWhenUserIsNotLogined(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = factory(User::class)->create();
        $this->assertNotNull($user->email_verified_at);

        $tryResendEmailVerification = $this->postJson($this->testUrl)->assertUnauthorized();

        $this->assertFalse($tryResendEmailVerification['ok']);
        $this->assertFalse($tryResendEmailVerification['isValid']);
        $this->assertUnauthorizedMessages($tryResendEmailVerification['messages']);
    }
}
