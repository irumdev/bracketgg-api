<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Tests\TestCase;
use Styde\Enlighten\Tests\EnlightenSetup;

class VerifyEmailTest extends TestCase
{
    use EnlightenSetup;

    private const UN_DEFINED_USER = -1;
    private string $testUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();

        $this->testUrl = route('user.create');
        Http::fake([
            'directsend.co.kr/*' => Http::response(['status' => '0'])
        ]);
    }

    public function generateEmailVerifyUrl(User $user): string
    {
        $reflection = new \ReflectionClass($user);

        $testMehtod = $reflection->getMethod('verificationUrl');
        $testMehtod->setAccessible(true);
        $verifyUrl = $testMehtod->invokeArgs($user, [$user]);
        $parseVerifyUrl = parse_url($verifyUrl);

        return sprintf('%s/api/v1%s?%s', config('app.url'), $parseVerifyUrl['path'], $parseVerifyUrl['query']);
    }

    private function createUser(): array
    {
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' =>  $randEmail =  Str::random(12) . '@' . Str::random(12) . '.com',
            'nick_name' => $nickName = Str::random(12),
            'password' => $password = Str::random(30),
            'confirmedPassword' => $password,
            'is_policy_agree' => 1,
            'is_privacy_agree' => 1,
        ])->assertCreated();

        $user = User::where([
            ['email', '=', $randEmail],
            ['nick_name','=', $nickName],
        ])->first();

        $this->assertEquals($tryCreateUser['messages']['id'], $user->id);
        $this->assertEquals($tryCreateUser['messages']['email'], $user->email);
        $this->assertEquals($tryCreateUser['messages']['nick_name'], $user->nick_name);
        $this->assertEquals($tryCreateUser['messages']['is_policy_agree'], $user->is_policy_agree);
        $this->assertEquals($tryCreateUser['messages']['is_privacy_agree'], $user->is_privacy_agree);
        $this->assertNull($user->email_verified_at);

        return [
            'response' => $tryCreateUser,
            'email' => $randEmail,
            'nickName' => $nickName,
            'password' => $password,
            'user' => $user,
        ];
    }

    /**
     * @test
     * @enlighten
     */
    public function successVerifyEmail(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->createUser();
        $user = $tryCreateUser['user'];

        $tryVerifyEmail = $this->getJson($this->generateEmailVerifyUrl($user))
                               ->assertOk();

        $this->assertTrue($tryVerifyEmail['ok']);
        $this->assertTrue($tryVerifyEmail['isValid']);
        $this->assertTrue($tryVerifyEmail['messages']['markEmailAsVerified']);

        $verifiedUser = User::find($user->id);
        $this->assertNotNull($verifiedUser->email_verified_at);
    }

    /**
     * @test
     * @enlighten
     */
    public function failVerifyEmailWhenTimeOut(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->createUser();
        $user = $tryCreateUser['user'];

        $verifyUrl = $this->generateEmailVerifyUrl($user);
        $this->travel(config('auth.verification.expire') + 1)->minutes();

        $tryVerifyEmail = $this->getJson($verifyUrl)
                               ->assertForbidden();

        $this->assertFalse($tryVerifyEmail['ok']);
        $this->assertFalse($tryVerifyEmail['isValid']);
        $this->assertEquals(403, $tryVerifyEmail['messages']['code']);

        $failVeryfiedUser = User::find($user->id);
        $this->assertNull($failVeryfiedUser->email_verified_at);
    }

    /**
     * @test
     * @enlighten
     */
    public function failVerifyEmailWhenUserIsNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->createUser();
        $user = $tryCreateUser['user'];

        $verifyUrl = $this->generateEmailVerifyUrl($user);
        $parseUrl = parse_url($verifyUrl);

        $splitUrlBySlug = array_merge(array_filter(explode('/', $parseUrl['path'])));
        $splitUrlBySlug[4] = self::UN_DEFINED_USER;

        $callableUrl = config('app.url') . '/' . join('/', $splitUrlBySlug) . '?' . $parseUrl['query'];


        $tryVerifyEmail = $this->getJson($callableUrl)
                               ->assertForbidden();

        $this->assertFalse($tryVerifyEmail['ok']);
        $this->assertFalse($tryVerifyEmail['isValid']);
        $this->assertEquals(403, $tryVerifyEmail['messages']['code']);

        $failVeryfiedUser = User::find($user->id);
        $this->assertNull($failVeryfiedUser->email_verified_at);
    }

    /**
     * @test
     * @enlighten
     */
    public function failVerifyEmailWhenUserAlreadyVerifyEmail(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->createUser();
        $user = $tryCreateUser['user'];


        $this->assertNull($user->email_verified_at);
        $user->email_verified_at = now();
        $user->save();

        $this->assertNotNull($user->email_verified_at);

        $verifyUrl = $this->generateEmailVerifyUrl($user);
        $parseUrl = parse_url($verifyUrl);

        $splitUrlBySlug = array_merge(array_filter(explode('/', $parseUrl['path'])));
        $splitUrlBySlug[4] = self::UN_DEFINED_USER;

        $callableUrl = config('app.url') . '/' . join('/', $splitUrlBySlug) . '?' . $parseUrl['query'];


        $tryVerifyEmail = $this->getJson($callableUrl)
                                ->assertForbidden();

        $this->assertFalse($tryVerifyEmail['ok']);
        $this->assertFalse($tryVerifyEmail['isValid']);
        $this->assertEquals(403, $tryVerifyEmail['messages']['code']);

        $failVeryfiedUser = User::find($user->id);
        $this->assertNotNull($failVeryfiedUser->email_verified_at);
    }
}
