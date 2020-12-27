<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\User\StoreRequest as UserStoreRequest;

use Styde\Enlighten\Tests\EnlightenSetup;

class CreateTest extends TestCase
{
    use EnlightenSetup;

    private string $testUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
        $this->testUrl = route('createUser');
        Http::fake([
            'directsend.co.kr/*' => Http::response(['status' => '0'])
        ]);
    }

    private function errorResponseSkeleton(int $message): array
    {
        return [
            'ok' => false,
            'isValid' => false,
            'messages' => [
                'code' => $message
            ]
        ];
    }

    private function assertResponseError(int $message, array $responseOrigin): void
    {
        $this->assertEquals(
            $this->errorResponseSkeleton($message),
            $responseOrigin
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWithOutAnyInfo(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl)
                              ->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::REQUIRE_EMAIL,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWithoutEmail(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => 'asdf',
        ])->assertStatus(422);

        $this->assertResponseError(
            UserStoreRequest::EMAIL_PATTERN_NOT_MATCH,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWithDuplicateEmail(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $fixedEmail = 'asdf123@asdf.com';
        if (User::where('email', $fixedEmail)->first() !== null) {
            User::where('email', $fixedEmail)->first()->forceDelete();
        }
        $user = factory(User::class)->create();
        $user->email = $fixedEmail;
        $user->save();

        $user = User::find($user->id);

        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $fixedEmail,
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::EMAIL_ALREADY_EXISTS,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWithoutNickName(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::REQUIRE_NICKNAME,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWithEmptyNickName(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => ''
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::REQUIRE_NICKNAME,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWithLargeNickName(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(13)
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::NICKNAME_MAX_LENGTH,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWithOutPassword(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12)
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::REQUIRE_PASSWORD,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWhenPasswordIsShort(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12),
            'password' => $password = Str::random(5)
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::PASSWORD_MIN_LENGTH,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWhenPasswordIsLong(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12),
            'password' => $password = Str::random(31)
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::PASSWORD_MAX_LENGTH,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWithOutPasswordReEnter(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12),
            'password' => $password = Str::random(30),

        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::REQUIRE_RE_ENTER_PASSWORD,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWhenPasswordeEnterIsToShort(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12),
            'password' => $password = Str::random(30),
            'confirmedPassword' => substr($password, 0, 7),

        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::PASSWORD_RE_ENTER_MIN_LEN_ERROR,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWhenPasswordeEnterIsToLong(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12),
            'password' => $password = Str::random(30),
            'confirmedPassword' => $password . 'a',

        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::PASSWORD_RE_ENTER_MAX_LENGTH,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWhenPassworReEnterIsNotEqualsPassword(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12),
            'password' => $password = Str::random(30),
            'confirmedPassword' => Str::random(30),

        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::PASSWORD_RE_ENTER_NOT_SAME_WITH_PASSWORD,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserUnAgreePolicy(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12),
            'password' => $password = Str::random(30),
            'confirmedPassword' => $password,

        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::REQUIRE_POLICY_AGREE,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWhebAgreePolicyValueIsInValud(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12),
            'password' => $password = Str::random(30),
            'confirmedPassword' => $password,
            'is_policy_agree' => Str::random(30),
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::NOT_EQUAL_ONE_POLICY_AGREE,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWhenPrivacyPolicyNotAgree(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12),
            'password' => $password = Str::random(30),
            'confirmedPassword' => $password,
            'is_policy_agree' => 1,
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::REQUIRE_PRIVACY_AGREE,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserWhenPrivacyPolicyValueInvalid(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12),
            'password' => $password = Str::random(30),
            'confirmedPassword' => $password,
            'is_policy_agree' => 1,
            'is_privacy_agree' => $password,
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::NOT_EQUAL_ONE_PRIVACT_AGREE,
            $tryCreateUser->original
        );
    }


    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserProfileImageIsNotImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12),
            'password' => $password = Str::random(30),
            'confirmedPassword' => $password,
            'is_policy_agree' => 1,
            'is_privacy_agree' => 1,
            'profile_image' => UploadedFile::fake()->create('test.pdf', 1000),
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::PROFILE_IMAGE_NOT_IMAGE,
            $tryCreateUser->original
        );
    }


    /**
     * @test
     * @enlighten
     */
    public function failRegisterUserProfileImageIsLarge(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12),
            'password' => $password = Str::random(30),
            'confirmedPassword' => $password,
            'is_policy_agree' => 1,
            'is_privacy_agree' => 1,
            'profile_image' => UploadedFile::fake()->create('test.png', 2049),
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::PROFILE_IMAGE_MAX_SIZE,
            $tryCreateUser->original
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successRegisterUserWithoutProfileImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' =>  $randEmail = Str::random(12) . '@' . Str::random(12) . '.com',
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
    }

    /**
     * @test
     * @enlighten
     */
    public function successRegisterUserWithoutProfileImageWithSpecificEmail(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $specialEmails = collect([
            'test.test@gmail.com',
            'test-a@gmail.com',
            'test._.-a@gmail.co.kr',
            // 'test..a@gmail.com',
            /**
             * @todo 'test..a@gmail.com' 이메일 케이스 통과시키기 (RFC 5322)
             */
        ]);
        $specialEmails->each(function ($email) {
            if (($user = User::where('email', $email)->first()) !== null) {
                $user->forceDelete();
            }
            $tryCreateUser = $this->postJson($this->testUrl, $param = [
                'email' => $email,
                'nick_name' => $nickName = Str::random(12),
                'password' => $password = Str::random(30),
                'confirmedPassword' => $password,
                'is_policy_agree' => 1,
                'is_privacy_agree' => 1,
            ])->assertCreated();

            $user = User::where([
                ['email', '=', $email],
                ['nick_name','=', $nickName],
            ])->first();

            $this->assertEquals($tryCreateUser['messages']['id'], $user->id);
            $this->assertEquals($tryCreateUser['messages']['email'], $user->email);
            $this->assertEquals($tryCreateUser['messages']['nick_name'], $user->nick_name);
            $this->assertEquals($tryCreateUser['messages']['is_policy_agree'], $user->is_policy_agree);
            $this->assertEquals($tryCreateUser['messages']['is_privacy_agree'], $user->is_privacy_agree);
        });
    }

    /**
     * @test
     * @enlighten
     */
    public function successRegisterUser(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => $nickName = Str::random(12),
            'password' => $password = Str::random(30),
            'confirmedPassword' => $password,
            'is_policy_agree' => 1,
            'is_privacy_agree' => 1,
            'profile_image' => UploadedFile::fake()->create('test.png', 2048),

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
    }
}
