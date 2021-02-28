<?php

namespace Tests\Feature\Channel\Update;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Tests\TestCase;
use App\Models\Channel\Broadcast as ChannelBoradcast;
use App\Http\Requests\Rules\Broadcast as BroadcastRules;

use App\Models\Channel\Channel;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class BroadcastTest extends TestCase
{
    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenUrlIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addBroadcasts'])->create(['owner' => $activeUser->id, ]);
        $requestUrl = route('updateChannelInfo', [
            'slug' => $channel->slug,
        ]);

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'platform' => Arr::random(array_keys(ChannelBoradcast::$platforms)),
                ]
            ],
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateChannelBroadCast['ok']);
        $this->assertFalse($tryUpdateChannelBroadCast['isValid']);
        $this->assertEquals(['code' => BroadcastRules::BROADCAST_ADDRESS_HAS_NOT_PLATFORM], $tryUpdateChannelBroadCast['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenBroadCastIsNotArray(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addBroadcasts'])->create(['owner' => $activeUser->id, ]);

        $requestUrl = route('updateChannelInfo', [
            'slug' => $channel->slug,
        ]);

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => 'asdf',
        ])->assertStatus(422);


        $this->assertFalse($tryUpdateChannelBroadCast['ok']);
        $this->assertFalse($tryUpdateChannelBroadCast['isValid']);
        $this->assertEquals(['code' => BroadcastRules::BROADCAST_IS_NOT_ARRAY], $tryUpdateChannelBroadCast['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenPlatformIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addBroadcasts'])->create(['owner' => $activeUser->id, ]);


        $requestUrl = route('updateChannelInfo', [
            'slug' => $channel->slug,
        ]);

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => 'https://' . Str::random(20) . '.com',
                ]
            ],
        ]);



        $this->assertFalse($tryUpdateChannelBroadCast['ok']);
        $this->assertFalse($tryUpdateChannelBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_ADDRESS_HAS_NOT_URL],
            $tryUpdateChannelBroadCast['messages']
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

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addBroadcasts'])->create(['owner' => $activeUser->id, ]);


        $requestUrl = route('updateChannelInfo', [
            'slug' => $channel->slug,
        ]);

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => false,
                    'platform' => Arr::random(array_keys(ChannelBoradcast::$platforms)),
                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateChannelBroadCast['ok']);
        $this->assertFalse($tryUpdateChannelBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_URL_IS_NOT_STRING],
            $tryUpdateChannelBroadCast['messages']
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

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addBroadcasts'])->create(['owner' => $activeUser->id, ]);

        $requestUrl = route('updateChannelInfo', [
            'slug' => $channel->slug,
        ]);

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => $channel->broadcastAddress->first()->broadcast_address,
                    'platform' => Arr::random(array_keys(ChannelBoradcast::$platforms)),
                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateChannelBroadCast['ok']);
        $this->assertFalse($tryUpdateChannelBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_URL_IS_NOT_UNIQUE],
            $tryUpdateChannelBroadCast['messages']
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

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addBroadcasts'])->create(['owner' => $activeUser->id, ]);

        $requestUrl = route('updateChannelInfo', [
            'slug' => $channel->slug,
        ]);

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => 'https://' . Str::random(20) . '.com',
                    'platform' => 'asdf',
                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateChannelBroadCast['ok']);
        $this->assertFalse($tryUpdateChannelBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_PLATFORM_IS_INVALID],
            $tryUpdateChannelBroadCast['messages']
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

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addBroadcasts'])->create(['owner' => $activeUser->id, ]);

        $requestUrl = route('updateChannelInfo', [
            'slug' => $channel->slug,
        ]);

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => 'https://' . Str::random(20) . '.com',
                    'platform' => -3,
                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateChannelBroadCast['ok']);
        $this->assertFalse($tryUpdateChannelBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_PLATFORM_IS_INVALID],
            $tryUpdateChannelBroadCast['messages']
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

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addBroadcasts'])->create(['owner' => $activeUser->id, ]);

        $requestUrl = route('updateChannelInfo', [
            'slug' => $channel->slug,
        ]);

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => $randUrl = 'https://' . Str::random(20) . '.com',
                    'platform' => $randPlatform = Arr::random(array_keys(ChannelBoradcast::$platforms)),
                ]
            ],
        ])->assertOk();
        $this->assertTrue($tryUpdateChannelBroadCast['ok']);
        $this->assertTrue($tryUpdateChannelBroadCast['isValid']);

        $channelBroadCast = Channel::find($channel->id)->broadcastAddress;

        $channelBroadcastUrls = $channelBroadCast->map(fn (ChannelBoradcast $broadcast): string => $broadcast->broadcast_address);
        $channelBroadcastPlatforms = $channelBroadCast->map(fn (ChannelBoradcast $broadcast): int => $broadcast->platform);

        $this->assertTrue($channelBroadcastUrls->contains($randUrl));
        $this->assertTrue($channelBroadcastPlatforms->contains($randPlatform));
    }

    /**
     * @test
     * @enlighten
     */
    public function successDeleteBroadcast(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states(['addSlug', 'addTenBroadcasts'])->create(['owner' => $activeUser->id, ]);

        $requestUrl = route('updateChannelInfo', [
            'slug' => $channel->slug,
        ]);

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [],
        ])->assertOk();
        $this->assertTrue($tryUpdateChannelBroadCast['ok']);
        $this->assertTrue($tryUpdateChannelBroadCast['isValid']);

        $channelBroadCast = Channel::find($channel->id)->broadcastAddress;

        $this->assertEquals(0, $channelBroadCast->count());
    }

    /**
     * @test
     * @enlighten
     */
    public function successUpdateBroadcast(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addBroadcasts'])->create(['owner' => $activeUser->id, ]);

        $requestUrl = route('updateChannelInfo', [
           'slug' => $channel->slug,
        ]);

        $targetChannelRandKey = Arr::random(array_keys($channel->broadcastAddress->keys()->toArray()));

        $targetRandChannelBroadCast = $channel->broadcastAddress->get($targetChannelRandKey);

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
           'broadcasts' => [
               [
                   'url' => $randUrl = 'https://' . Str::random(20) . '.com',
                   'platform' => $randPlatform = Arr::random(array_keys(ChannelBoradcast::$platforms)),
                   'id' => $targetRandChannelBroadCast->id,
               ]
           ],
       ])->assertOk();

        $this->assertTrue($tryUpdateChannelBroadCast['ok']);
        $this->assertTrue($tryUpdateChannelBroadCast['isValid']);

        $changedChannelBroadcastAddress = Channel::find($channel->id)->broadcastAddress;

        $channelBroadcastUrls = $changedChannelBroadcastAddress->map(fn (ChannelBoradcast $broadcast): string => $broadcast->broadcast_address);
        $channelBroadcastPlatforms = $changedChannelBroadcastAddress->map(fn (ChannelBoradcast $broadcast): int => $broadcast->platform);

        $this->assertTrue($channelBroadcastUrls->contains($randUrl));
        $this->assertTrue($channelBroadcastPlatforms->contains($randPlatform));
    }

    /**
     * @test
     * @enlighten
     */
    public function successUpdateAndCreateBroadcast(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addBroadcasts'])->create(['owner' => $activeUser->id, ]);

        $requestUrl = route('updateChannelInfo', [
           'slug' => $channel->slug,
        ]);

        $targetChannelRandKey = Arr::random(array_keys($channel->broadcastAddress->keys()->toArray()));

        $targetRandChannelBroadCast = $channel->broadcastAddress->get($targetChannelRandKey);

        $tmp = Channel::find($channel->id)->broadcastAddress;

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
           'broadcasts' => [
                [
                   'url' => $updateRandUrl = 'https://' . Str::random(20) . '.update.com',
                   'platform' => $updateRandPlatform = Arr::random(array_keys(ChannelBoradcast::$platforms)),
                   'id' => $targetRandChannelBroadCast->id,
                ],
                [
                    'url' => $createRandUrl = 'https://' . Str::random(20) . '.create.com',
                    'platform' => $createRandPlatform = Arr::random(array_keys(ChannelBoradcast::$platforms)),
                ]
           ],
       ])->assertOk();

        $this->assertTrue($tryUpdateChannelBroadCast['ok']);
        $this->assertTrue($tryUpdateChannelBroadCast['isValid']);

        $changedChannelBroadcastAddress = Channel::find($channel->id)->broadcastAddress;

        $channelBroadcastUrls = $changedChannelBroadcastAddress->map(fn (ChannelBoradcast $broadcast): string => $broadcast->broadcast_address);
        $channelBroadcastPlatforms = $changedChannelBroadcastAddress->map(fn (ChannelBoradcast $broadcast): int => $broadcast->platform);

        $this->assertTrue($channelBroadcastUrls->contains($createRandUrl));
        $this->assertTrue($channelBroadcastPlatforms->contains($createRandPlatform));

        $this->assertEquals(
            Channel::find($channel->id)->broadcastAddress()
                                 ->where('id', '=', $targetRandChannelBroadCast->id)
                                 ->first()
                                 ->broadcast_address,
            $updateRandUrl
        );

        $this->assertEquals(
            Channel::find($channel->id)->broadcastAddress()
                                 ->where('id', '=', $targetRandChannelBroadCast->id)
                                 ->first()
                                 ->platform,
            $updateRandPlatform
        );
    }


    /**
     * @test
     * @enlighten
     */
    public function failUpdateBroadcastWhenTryUpdateAnotherChannelPlatform(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addBroadcasts'])->create(['owner' => $activeUser->id, ]);
        $anotherChannel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addBroadcasts'])->create(['owner' => factory(User::class)->create()->id, ]);

        $requestUrl = route('updateChannelInfo', [
           'slug' => $channel->slug,
        ]);

        $targetChannelRandKey = Arr::random(array_keys($channel->broadcastAddress->keys()->toArray()));
        $anotherChannelRandKey = Arr::random(array_keys($anotherChannel->broadcastAddress->keys()->toArray()));

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
           'broadcasts' => [
               [
                   'url' => 'https://' . Str::random(20) . '.com',
                   'platform' => $randPlatform = Arr::random(array_keys(ChannelBoradcast::$platforms)),
                   'id' => $anotherChannel->broadcastAddress->get($anotherChannelRandKey)->id,
               ]
           ],
       ])->assertStatus(422);
        $this->assertFalse($tryUpdateChannelBroadCast['ok']);
        $this->assertFalse($tryUpdateChannelBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_ID_IS_NOT_BELONGS_TO_MY_TEAM],
            $tryUpdateChannelBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successCreateBroadcastKeepAlreadyExistsBroadcastUrl(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addTenBroadcasts'])->create(['owner' => $activeUser->id, ]);

        $requestUrl = route('updateChannelInfo', [
            'slug' => $channel->slug,
        ]);

        $broadcastInfos = $channel->broadcastAddress->toArray();

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
                    'platform' => Arr::random(array_keys(ChannelBoradcast::$platforms)),
                ],
                [
                    'url' => 'https://' . Str::random(10) . 'create.second.com',
                    'platform' => Arr::random(array_keys(ChannelBoradcast::$platforms)),
                ]
            ],
        ])->assertOk();
        $dbBoradCast = Channel::find($channel->id)->broadcastAddress;

        $this->assertTrue($tryUpdateChannelBroadCast['ok']);
        $this->assertTrue($tryUpdateChannelBroadCast['isValid']);
        $this->assertEquals(count($postData), $dbBoradCast->count());

        collect($postData)->each(function (array $broadCastInfo): void {
            if (isset($broadCast['id'])) {
                $broadCastInstance = ChannelBoradcast::find($broadCastInfo['id']);
            } else {
                $broadCastInstance = ChannelBoradcast::where([
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
    public function successUpdateBroadcastPlatform(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addTenBroadcasts'])->create(['owner' => $activeUser->id, ]);

        $requestUrl = route('updateChannelInfo', [
            'slug' => $channel->slug,
        ]);


        $tmp = ChannelBoradcast::find($tmpId = $channel->broadcastAddress->first()->id);
        $tmp->platform = array_keys(ChannelBoradcast::$platforms)[0];

        $tmp->save();

        $this->assertEquals(array_keys(ChannelBoradcast::$platforms)[0], ChannelBoradcast::find($tmpId)->platform);

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => $requestBroadCast = [
                [
                    'url' => $channel->broadcastAddress->first()->broadcast_address,
                    'id' => $channel->broadcastAddress->first()->id,
                    'platform' => Arr::random(array_keys(ChannelBoradcast::$platforms)),
                ]
            ],
        ])->assertOk();

        $this->assertTrue($tryUpdateChannelBroadCast['ok']);
        $this->assertTrue($tryUpdateChannelBroadCast['isValid']);

        $channelBroadCast = Channel::find($channel->id)->broadcastAddress;

        $this->assertEquals(count($requestBroadCast), $channelBroadCast->count());

        collect($requestBroadCast)->each(function (array $info): void {
            $instance = ChannelBoradcast::find($info['id']);

            $this->assertEquals($info['id'], $instance->id);
            $this->assertEquals($info['url'], $instance->broadcast_address);
            $this->assertEquals($info['platform'], $instance->platform);
        });
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateBroadcastWhenPlatformIdIsNotNumeric(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states(['addSlug','addBannerImage', 'addBroadcasts'])->create(['owner' => $activeUser->id, ]);

        $requestUrl = route('updateChannelInfo', [
           'slug' => $channel->slug,
       ]);

        $randKey = Arr::random(array_keys($channel->broadcastAddress->keys()->toArray()));

        $randChannelBroadCast = $channel->broadcastAddress->get($randKey);

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
           'broadcasts' => [
               [
                   'url' => 'https://' . Str::random(20) . '.com',
                   'platform' => $randPlatform = Arr::random(array_keys(ChannelBoradcast::$platforms)),
                   'id' => 'asdf',
               ]
           ],
       ])->assertStatus(422);
        $this->assertFalse($tryUpdateChannelBroadCast['ok']);
        $this->assertFalse($tryUpdateChannelBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_ID_IS_NOT_NUMERIC],
            $tryUpdateChannelBroadCast['messages']
        );
    }
}
