<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Http\Requests\User\ProfileImageUpdateRequest;
use Styde\Enlighten\Tests\EnlightenSetup;
use Symfony\Component\HttpFoundation\Response;

class UpdateProfileImageTest extends TestCase
{
    use EnlightenSetup;

    private string $testUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
        $this->testUrl = route('user.updateUserProfileImage');
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateProfileImageWhenUserIsNotLogined(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryUpdateUserProfileImage = $this->postJson($this->testUrl)->assertUnauthorized();

        $this->assertFalse($tryUpdateUserProfileImage['ok']);
        $this->assertFalse($tryUpdateUserProfileImage['isValid']);
        $this->assertUnauthorizedMessages($tryUpdateUserProfileImage['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateProfileImageWithoutImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserProfileImage = $this->postJson($this->testUrl)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUpdateUserProfileImage['ok']);
        $this->assertFalse($tryUpdateUserProfileImage['isValid']);
        $this->assertEquals(
            ProfileImageUpdateRequest::REQUIRE_PROFILE_IMAGE,
            $tryUpdateUserProfileImage['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateProfileImageWhenIsNotImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserProfileImage = $this->postJson(
            $this->testUrl,
            [
                'profile_image' => UploadedFile::fake()->create('test.pdf', 1000)
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUpdateUserProfileImage['ok']);
        $this->assertFalse($tryUpdateUserProfileImage['isValid']);
        $this->assertEquals(
            ProfileImageUpdateRequest::PROFILE_IMAGE_NOT_IMAGE,
            $tryUpdateUserProfileImage['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateProfileImageWhenImageIsLarge(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserProfileImage = $this->postJson(
            $this->testUrl,
            [
                'profile_image' => UploadedFile::fake()->create('test.png', 2049)
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryUpdateUserProfileImage['ok']);
        $this->assertFalse($tryUpdateUserProfileImage['isValid']);
        $this->assertEquals(
            ProfileImageUpdateRequest::PROFILE_IMAGE_MAX_SIZE,
            $tryUpdateUserProfileImage['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successUpdateUserProfileImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $tryUpdateUserProfileImage = $this->postJson(
            $this->testUrl,
            [
                'profile_image' => UploadedFile::fake()->create('test.png', 1000)
            ]
        )->assertOk();

        $this->assertTrue($tryUpdateUserProfileImage['ok']);
        $this->assertTrue($tryUpdateUserProfileImage['isValid']);
        $this->assertTrue($tryUpdateUserProfileImage['messages']['isSuccess']);
        $this->assertEquals(
            $this->getJson(route('user.current'))['messages']['profileImage'],
            $tryUpdateUserProfileImage['messages']['profileImage']
        );
    }
}
