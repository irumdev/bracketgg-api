<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Http\Requests\UserStoreRequest;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class CreateUserTest extends TestCase
{
    private string $testUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testUrl = route('createUser');
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

    /** @test */
    public function 아무것도_안넣은채로_회원가입에_실패하라(): void
    {
        $tryCreateUser = $this->postJson($this->testUrl)
                              ->assertStatus(422);


        $this->assertResponseError(
            UserStoreRequest::REQUIRE_EMAIL,
            $tryCreateUser->original
        );
    }

    /** @test */
    public function 올바른_이메일을_입력안한채_회원가입에_실패하라(): void
    {
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => 'asdf',
        ])->assertStatus(422);

        $this->assertResponseError(
            UserStoreRequest::EMAIL_PATTERN_NOT_MATCH,
            $tryCreateUser->original
        );
    }

    /** @test */
    public function 중복된_이메일로_회원기입_시도후_회원가입에_실패하라(): void
    {
        $user = factory(User::class)->create();
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $user->email,
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::EMAIL_ALREADY_EXISTS,
            $tryCreateUser->original
        );
    }

    /** @test */
    public function 이메일은_입력했지만_닉네임을_입력_안한채로_회원가입에_실패하라(): void
    {
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::REQUIRE_NICKNAME,
            $tryCreateUser->original
        );
    }

    /** @test */
    public function 닉네임이_한글자도_없는채로_회원가입에_실패하라(): void
    {
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => ''
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::REQUIRE_NICKNAME,
            $tryCreateUser->original
        );
    }

    /** @test */
    public function 닉네임이_12글자_이상_입력후_회원가입에_실패하라(): void
    {
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(13)
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::NICKNAME_MAX_LENGTH,
            $tryCreateUser->original
        );
    }

    /** @test */
    public function 닉네임과_이메일을_입력했지만_비밀번호를_없이_회원가입에_실패하라(): void
    {
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
            'nick_name' => Str::random(12)
        ])->assertStatus(422);
        $this->assertResponseError(
            UserStoreRequest::REQUIRE_PASSWORD,
            $tryCreateUser->original
        );
    }

    /** @test */
    public function 닉네임과_이메일_비밀번호를을_입력했지만_비밀번호를_8자리_미만입력하여_회원가입에_실패하라(): void
    {
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

    /** @test */
    public function 닉네임과_이메일_비밀번호를을_입력했지만_비밀번호를_30자리_초괴입력하여_회원가입에_실패하라(): void
    {
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

    /** @test */
    public function 비밀번호_재입력을_입력하지않아서_회원가입에_실패하라(): void
    {
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

    /** @test */
    public function 비밀번호_재입력이_8자리_미만으로_입력하여_회원가입에_실패하라(): void
    {
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

    /** @test */
    public function 비밀번호_재입력이_30자리_초과로_입력하여_회원가입에_실패하라(): void
    {
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

    /** @test */
    public function 비밀번호_재입력이_입력한_비밀번호와_달라서_회원가입에_실패하라(): void
    {
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

    /** @test */
    public function 약관동의를_안해서_회원가입에_실패하라(): void
    {
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

    /** @test */
    public function 약관동의에_이상한_값을_넣어서_회원가입에_실패하라(): void
    {
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

    /** @test */
    public function 개인정보_처리방침에_동의하지_않아서_회원가입에_실패하라(): void
    {
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

    /** @test */
    public function 개인정보_처리방침에_이상한_값을_넣어서_회원가입에_실패하라(): void
    {
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


    /** @test */
    public function 프로필사진에_사진_아닌거_올려서_회원가입에_실패하라(): void
    {
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


    /** @test */
    public function 프로필사진에_사진_2048kb보다_큰거_올려서_회원가입에_실패하라(): void
    {
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



    /** @test */
    public function 프로필이미지_없이_회원가입에_성공하라(): void
    {
        $tryCreateUser = $this->postJson($this->testUrl, [
            'email' => $randEmail = Str::random(5) . '@' . 'asdf.com',
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

    /** @test */
    public function 회원가입에_성공하라(): void
    {
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
