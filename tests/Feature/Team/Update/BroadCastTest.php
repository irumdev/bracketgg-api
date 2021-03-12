<?php

declare(strict_types=1);

namespace Tests\Feature\Team\Update;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Team\Team;
use App\Models\Team\Broadcast as TeamBroadCast;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Http\Requests\Team\UpdateInfoWithOutBannerRequest;
use App\Http\Requests\Team\UpdateBannerImageRequest;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Team\UpdateLogoImageRequest;
use Styde\Enlighten\Tests\EnlightenSetup;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Requests\Rules\Broadcast as BroadcastRules;
use Illuminate\Support\Collection;

class BroadCastTest extends TestCase
{
    use EnlightenSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
    }

    // public function baseBroadCastStructure(Collection $addresses): Collection
    // {
    //     return $addresses->map(function ($broadcast) {
    //         return [
    //             'url' => $broadcast->broadcast_address,
    //             'platform' => $broadcast->platform,
    //             'id' => $broadcast->id,
    //         ];
    //     });
    // }

    // public function appendTestItem(Team $team, array $testItem): Collection
    // {
    //     return $this->baseBroadCastStructure($team->broadcastAddress)->merge(
    //         is_numeric(array_keys($testItem)[0]) ? $testItem : [$testItem]
    //     );
    // }


    /** @test @deprecate */
    // public function failUpdateLogoImageIsNotImage(): void
    // {
    //     $this->setName($this->getCurrentCaseKoreanName());
    //     $activeUser = Sanctum::actingAs(factory(User::class)->create());
    //     $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
    //     $requestUrl = route('team.updateLogo', [
    //         'teamSlug' => $team->slug,
    //     ]);
    //     $tryUpdateTeamLogo = $this->postJson($requestUrl, [
    //         'logo_image' => UploadedFile::fake()->create('test.asdf', 2048),
    //     ])->assertStatus(422);
    //     $this->assertFalse($tryUpdateTeamLogo['ok']);
    //     $this->assertFalse($tryUpdateTeamLogo['isValid']);
    //     $this->assertEquals(['code' => UpdateLogoImageRequest::LOGO_IS_NOT_IMAGE], $tryUpdateTeamLogo['messages']);
    // }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenUrlIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('team.updateInfo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'platform' => Arr::random(array_keys(TeamBroadCast::$platforms)),
                ]
            ],
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(['code' => BroadcastRules::BROADCAST_ADDRESS_HAS_NOT_PLATFORM], $tryUpdateTeamBroadCast['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenBroadCastIsNotArray(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);


        $requestUrl = route('team.updateInfo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => 'asdf',
        ])->assertStatus(422);


        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(['code' => BroadcastRules::BROADCAST_IS_NOT_ARRAY], $tryUpdateTeamBroadCast['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenPlatformIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('team.updateInfo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => 'https://' . Str::random(20) . '.com',
                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_ADDRESS_HAS_NOT_URL],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenUrlIsNotString(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('team.updateInfo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => false,
                    'platform' => Arr::random(array_keys(TeamBroadCast::$platforms)),
                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_URL_IS_NOT_STRING],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenUrlIsNotUnique(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('team.updateInfo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => $team->broadcastAddress->first()->broadcast_address,
                    'platform' => Arr::random(array_keys(TeamBroadCast::$platforms)),
                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_URL_IS_NOT_UNIQUE],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenPlatformIsNotNumeric(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('team.updateInfo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => 'https://' . Str::random(20) . '.com',
                    'platform' => 'asdf',
                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_PLATFORM_IS_INVALID],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenPlatformIsInvalid(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('team.updateInfo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => 'https://' . Str::random(20) . '.com',
                    'platform' => -3,
                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_PLATFORM_IS_INVALID],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successCreateBroadcast(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('team.updateInfo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => $randUrl = 'https://' . Str::random(20) . '.com',
                    'platform' => $randPlatform = Arr::random(array_keys(TeamBroadCast::$platforms)),
                ]
            ],
        ])->assertOk();
        $this->assertTrue($tryUpdateTeamBroadCast['ok']);
        $this->assertTrue($tryUpdateTeamBroadCast['isValid']);

        $teamBroadCast = Team::find($team->id)->broadcastAddress;

        $teamBroadcastUrls = $teamBroadCast->map(fn (TeamBroadCast $broadcast): string => $broadcast->broadcast_address);
        $teamBroadcastPlatforms = $teamBroadCast->map(fn (TeamBroadCast $broadcast): int => $broadcast->platform);

        $this->assertTrue($teamBroadcastUrls->contains($randUrl));
        $this->assertTrue($teamBroadcastPlatforms->contains($randPlatform));
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateBroadcastWhenPlatformIdIsNotNumeric(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('team.updateInfo', [
           'teamSlug' => $team->slug,
       ]);

        $randKey = Arr::random(array_keys($team->broadcastAddress->keys()->toArray()));

        $randTeamBroadCast = $team->broadcastAddress->get($randKey);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
           'broadcasts' => [
               [
                   'url' => 'https://' . Str::random(20) . '.com',
                   'platform' => $randPlatform = Arr::random(array_keys(TeamBroadCast::$platforms)),
                   'id' => 'asdf',
               ]
           ],
       ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_ID_IS_NOT_NUMERIC],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateBroadcastWhenTryUpdateAnotherTeamPlatform(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $anotherTeam = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => factory(User::class)->create()->id, 'is_public' => false]);

        $requestUrl = route('team.updateInfo', [
           'teamSlug' => $team->slug,
        ]);

        $targetTeamRandKey = Arr::random(array_keys($team->broadcastAddress->keys()->toArray()));
        $anotherTeamRandKey = Arr::random(array_keys($anotherTeam->broadcastAddress->keys()->toArray()));

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
           'broadcasts' => [
               [
                   'url' => 'https://' . Str::random(20) . '.com',
                   'platform' => $randPlatform = Arr::random(array_keys(TeamBroadCast::$platforms)),
                   'id' => $anotherTeam->broadcastAddress->get($anotherTeamRandKey)->id,
               ]
           ],
       ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_ID_IS_NOT_BELONGS_TO_MY_TEAM],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successUpdateBroadcast(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('team.updateInfo', [
           'teamSlug' => $team->slug,
        ]);

        $targetTeamRandKey = Arr::random(array_keys($team->broadcastAddress->keys()->toArray()));

        $targetRandTeamBroadCast = $team->broadcastAddress->get($targetTeamRandKey);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
           'broadcasts' => [
               [
                   'url' => $randUrl = 'https://' . Str::random(20) . '.com',
                   'platform' => $randPlatform = Arr::random(array_keys(TeamBroadCast::$platforms)),
                   'id' => $targetRandTeamBroadCast->id,
               ]
           ],
       ])->assertOk();

        $this->assertTrue($tryUpdateTeamBroadCast['ok']);
        $this->assertTrue($tryUpdateTeamBroadCast['isValid']);

        $changedTeamBroadcastAddress = Team::find($team->id)->broadcastAddress;

        $teamBroadcastUrls = $changedTeamBroadcastAddress->map(fn (TeamBroadCast $broadcast): string => $broadcast->broadcast_address);
        $teamBroadcastPlatforms = $changedTeamBroadcastAddress->map(fn (TeamBroadCast $broadcast): int => $broadcast->platform);

        $this->assertTrue($teamBroadcastUrls->contains($randUrl));
        $this->assertTrue($teamBroadcastPlatforms->contains($randPlatform));
    }

    /**
     * @test
     * @enlighten
     */
    public function successCreateBroadcastKeepAlreadyExistsBroadcastUrl(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage', 'addTenBroadcasts'])->create(['owner' => $activeUser->id, ]);

        $requestUrl = route('team.updateInfo', [
            'teamSlug' => $team->slug,
        ]);

        $broadcastInfos = $team->broadcastAddress->toArray();

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => $postData = [
                [
                    'id' => $broadcastInfos[0]['id'],
                    'url' => $broadcastInfos[0]['broadcast_address'],
                    'platform' => $broadcastInfos[0]['platform'],
                ],
                [
                    'id' => $broadcastInfos[1]['id'],
                    'url' => $broadcastInfos[1]['broadcast_address'],
                    'platform' => $broadcastInfos[1]['platform'],
                ],
                // [
                //     'url' => $channel->broadcastAddress[1]['broadcast_address'],
                //     'platform' => Arr::random(array_keys(ChannelBoradcast::$platforms)),
                // ],
                [
                    'url' => 'http://' . Str::random(10) . 'create.first.com',
                    'platform' => Arr::random(array_keys(TeamBroadCast::$platforms)),
                ],
                [
                    'url' => 'https://' . Str::random(10) . 'create.second.com',
                    'platform' => Arr::random(array_keys(TeamBroadCast::$platforms)),
                ]
            ],
        ])->assertOk();
        $dbBoradCast = Team::find($team->id)->broadcastAddress;

        $this->assertTrue($tryUpdateChannelBroadCast['ok']);
        $this->assertTrue($tryUpdateChannelBroadCast['isValid']);
        $this->assertEquals(count($postData), $dbBoradCast->count());

        collect($postData)->each(function (array $broadCastInfo): void {
            if (isset($broadCast['id'])) {
                $broadCastInstance = TeamBroadCast::find($broadCastInfo['id']);
            } else {
                $broadCastInstance = TeamBroadCast::where([
                    ['broadcast_address', '=', $broadCastInfo['url']],
                    ['platform', '=', $broadCastInfo['platform']],
                ]);

                $this->assertEquals(1, $broadCastInstance->get()->count());

                $broadCastInstance = $broadCastInstance->first();
            }

            $this->assertNotNull($broadCastInstance);
            $this->assertEquals($broadCastInfo['url'], $broadCastInstance->broadcast_address);
            $this->assertEquals($broadCastInfo['platform'], $broadCastInstance->platform);
        });
    }


    /**
     * @test
     * @enlighten
     */
    public function successUpdateAndCreateBroadcast(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('team.updateInfo', [
           'teamSlug' => $team->slug,
        ]);

        $targetTeamRandKey = Arr::random(array_keys($team->broadcastAddress->keys()->toArray()));

        $targetRandTeamBroadCast = $team->broadcastAddress->get($targetTeamRandKey);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
           'broadcasts' => [
                [
                   'url' => $updateRandUrl = 'https://' . Str::random(20) . '.com',
                   'platform' => $updateRandPlatform = Arr::random(array_keys(TeamBroadCast::$platforms)),
                   'id' => $targetRandTeamBroadCast->id,
                ],
                [
                    'url' => $createRandUrl = 'https://' . Str::random(20) . '.com',
                    'platform' => $createRandPlatform = Arr::random(array_keys(TeamBroadCast::$platforms)),
                ]
           ],
       ])->assertOk();

        $this->assertTrue($tryUpdateTeamBroadCast['ok']);
        $this->assertTrue($tryUpdateTeamBroadCast['isValid']);

        $changedTeamBroadcastAddress = Team::find($team->id)->broadcastAddress;

        $teamBroadcastUrls = $changedTeamBroadcastAddress->map(fn (TeamBroadCast $broadcast): string => $broadcast->broadcast_address);
        $teamBroadcastPlatforms = $changedTeamBroadcastAddress->map(fn (TeamBroadCast $broadcast): int => $broadcast->platform);

        $this->assertTrue($teamBroadcastUrls->contains($createRandUrl));
        $this->assertTrue($teamBroadcastPlatforms->contains($createRandPlatform));

        $this->assertEquals(
            Team::find($team->id)->broadcastAddress()
                                 ->where('id', '=', $targetRandTeamBroadCast->id)
                                 ->first()
                                 ->broadcast_address,
            $updateRandUrl
        );

        $this->assertEquals(
            Team::find($team->id)->broadcastAddress()
                                 ->where('id', '=', $targetRandTeamBroadCast->id)
                                 ->first()
                                 ->platform,
            $updateRandPlatform
        );
    }
}
