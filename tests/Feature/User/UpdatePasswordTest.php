<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Str;
use App\Http\Requests\User\Is\PasswordUpdateRequest;
use Styde\Enlighten\Tests\EnlightenSetup;

class UpdatePasswordTest extends TestCase
{
    use EnlightenSetup;

    private string $testUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
        $this->testUrl = route('updateUserPassword');
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWhenUserIsNotLogined(): void
    {
        $tryUpdateUserPassword = $this->postJson($this->testUrl)->assertUnauthorized()->assertStatus(401);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(401, $tryUpdateUserPassword['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWithoutPassword(): void
    {
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson($this->testUrl)->assertStatus(422);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(PasswordUpdateRequest::REQUIRE_PASSWORD,
                            $tryUpdateUserPassword['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWithoutConfirmedPassword(): void
    {
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson($this->testUrl, [
            'password' => $randPassword = 'password' . Str::random(5)
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(PasswordUpdateRequest::REQUIRE_RE_ENTER_PASSWORD,
                            $tryUpdateUserPassword['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWhenPasswordEnterIsNotString(): void
    {
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson($this->testUrl, [
            'password' => $randPassword = (Int) Str::random(9),
            'confirmedPassword' => $randPassword
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(PasswordUpdateRequest::NOT_STRING_PASSWORD,
                            $tryUpdateUserPassword['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWhenPasswordEnterIsToShort(): void
    {
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson($this->testUrl, [
            'password' => $randPassword = 'pw' . Str::random(2)
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(PasswordUpdateRequest::PASSWORD_MIN_LENGTH,
                            $tryUpdateUserPassword['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWhenPasswordReEnterIsToShort(): void
    {
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson($this->testUrl, [
            'password' => $randPassword = 'password' . Str::random(5),
            'confirmedPassword' => $randPassword = 'pw' . Str::random(2)
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(PasswordUpdateRequest::PASSWORD_RE_ENTER_MIN_LEN_ERROR,
                            $tryUpdateUserPassword['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWhenPasswordReEnterIsNotEqualsPassword(): void
    {
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson($this->testUrl, [
            'password' => $randPassword = 'password1' . Str::random(5),
            'confirmedPassword' => $randPassword = 'password2' . Str::random(5)
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(PasswordUpdateRequest::PASSWORD_RE_ENTER_NOT_SAME_WITH_PASSWORD,
                            $tryUpdateUserPassword['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdatePasswordWhenPasswordEnterIsToLong(): void
    {
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserPassword = $this->postJson($this->testUrl, [
            'password' => $randPassword = 'thisistolongpassword' . Str::random(11),
            'confirmedPassword' => $randPassword,
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateUserPassword['ok']);
        $this->assertFalse($tryUpdateUserPassword['isValid']);
        $this->assertEquals(PasswordUpdateRequest::PASSWORD_MAX_LENGTH,
                            $tryUpdateUserPassword['messages']['code']);
    }
}
