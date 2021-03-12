<?php

declare(strict_types=1);

namespace Tests\Feature\Team\Update;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\Team\Team;
use App\Http\Requests\Team\UpdateInfoWithOutBannerRequest;
use Illuminate\Http\UploadedFile;
use Styde\Enlighten\Tests\EnlightenSetup;
use App\Models\User;

class PublicStatusTest extends TestCase
{
    use EnlightenSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateTeamPublicStatusWhenPublicStatusIsNotBoolean(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create(['owner' => $activeUser->id]);

        $requestUrl = route('team.updateInfo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeam = $this->postJson($requestUrl, [
            'is_public' => 'asdf',
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeam['ok']);
        $this->assertFalse($tryUpdateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::PUBLIC_STATUS_IS_NOT_BOOLEAN, $tryUpdateTeam['messages']['code']);
    }
}
