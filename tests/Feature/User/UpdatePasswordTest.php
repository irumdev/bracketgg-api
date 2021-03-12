<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Str;
use App\Http\Requests\User\PasswordUpdateRequest;
use Styde\Enlighten\Tests\EnlightenSetup;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordTest extends TestCase
{
    use EnlightenSetup;

    private string $testUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
        $this->testUrl = route('user.updateUserPassword');
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWhenUserIsNotLogined(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryUpdateUserPassword = $this->postJson($this->testUrl)->assertUnauthorized();

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertUnauthorizedMessages($tryUpdateUserPassword['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWithoutPassword(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson($this->testUrl)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(
            PasswordUpdateRequest::REQUIRE_PASSWORD,
            $tryUpdateUserPassword['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWithoutConfirmedPassword(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson(
            $this->testUrl,
            [
                'password' => Str::random(PasswordUpdateRequest::PASSWORD_MIN_LENGTH)
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(
            PasswordUpdateRequest::REQUIRE_RE_ENTER_PASSWORD,
            $tryUpdateUserPassword['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWhenPasswordEnterIsNotString(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson(
            $this->testUrl,
            [
                'password' => $randPassword = (int)Str::random(PasswordUpdateRequest::PASSWORD_MIN_LENGTH),
                'confirmedPassword' => $randPassword
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(
            PasswordUpdateRequest::NOT_STRING_PASSWORD,
            $tryUpdateUserPassword['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWhenPasswordEnterIsToShort(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson(
            $this->testUrl,
            [
                'password' => Str::random(PasswordUpdateRequest::PASSWORD_MIN_LENGTH - 1)
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(
            PasswordUpdateRequest::PASSWORD_LENGTH_IS_SHORT,
            $tryUpdateUserPassword['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWhenPasswordReEnterIsToShort(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson(
            $this->testUrl,
            [
                'password' => Str::random(PasswordUpdateRequest::PASSWORD_MIN_LENGTH),
                'confirmedPassword' => Str::random(PasswordUpdateRequest::PASSWORD_MIN_LENGTH - 1)
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(
            PasswordUpdateRequest::PASSWORD_RE_ENTER_MIN_LEN_ERROR,
            $tryUpdateUserPassword['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWhenPasswordReEnterIsNotEqualsPassword(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson(
            $this->testUrl,
            [
                'password' => Str::random(PasswordUpdateRequest::PASSWORD_MIN_LENGTH),
                'confirmedPassword' => Str::random(PasswordUpdateRequest::PASSWORD_MIN_LENGTH + 1)
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(
            PasswordUpdateRequest::PASSWORD_RE_ENTER_NOT_SAME_WITH_PASSWORD,
            $tryUpdateUserPassword['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWhenPasswordEnterIsToLong(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson(
            $this->testUrl,
            [
                'password' => $randPassword = Str::random(PasswordUpdateRequest::PASSWORD_MAX_LENGTH + 1),
                'confirmedPassword' => $randPassword,
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(
            PasswordUpdateRequest::PASSWORD_LENGTH_IS_LONG,
            $tryUpdateUserPassword['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successUpdateUserPassword(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson(
            $this->testUrl,
            [
                'password' => $randPassword = Str::random(PasswordUpdateRequest::PASSWORD_MIN_LENGTH),
                'confirmedPassword' => $randPassword,
            ]
        )->assertOk();

        $user = User::find($requestUser->id);

        $this->assertTrue($tryUpdateUserPassword['ok']);
        $this->assertTrue($tryUpdateUserPassword['isValid']);
        $this->assertTrue($tryUpdateUserPassword['messages']['isSuccess']);

        $this->assertFalse(Hash::check($randPassword . '1', $user->password));
        $this->assertTrue(Hash::check($randPassword, $user->password));
    }
}
