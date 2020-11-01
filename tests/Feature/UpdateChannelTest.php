<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use Illuminate\Support\Str;
use App\Http\Requests\UpdateChannelRequest;
use App\Models\ChannelSlug;
use Illuminate\Http\UploadedFile;
use App\Models\Channel;
use App\Http\Requests\Rules\CreateChannel as RulesCreateChannel;
use App\Models\ChannelBannerImage;

class UpdateChannelTest extends TestCase
{
    public function createChannel(): array
    {
        $user = Sanctum::actingAs(
            factory(User::class)->states(['addProfileImage'])->create()
        );

        $tryCreateChannel = $this->postJson(route('createChannel'), [
            'name' => \Illuminate\Support\Str::random(20),
        ])->assertOk();

        $dbChannel = User::find($user->id)->channels->first();

        $channel = $tryCreateChannel['messages'];

        $this->assertTrue($tryCreateChannel['ok']);
        $this->assertTrue($tryCreateChannel['isValid']);

        $this->assertEquals(
            $dbChannel->id,
            $channel['id']
        );
        $this->assertEquals(
            $dbChannel->name,
            $channel['channelName']
        );

        $this->assertEquals(
            $dbChannel->owner,
            $channel['owner']
        );
        $this->assertEquals($channel['bannerImages'], []);
        $this->assertEquals($channel['broadCastAddress'], []);

        $this->assertEquals($channel['likeCount'], 0);
        $this->assertEquals($channel['followerCount'], 0);

        $this->assertEquals($dbChannel->slug, $channel['slug']);


        return [
            'user' => $user,
            'channel' => $dbChannel,
        ];
    }

    /** @test */
    public function 채널_슬러그_업데이트에_성공하라(): void
    {
        $tryCreateChannel = $this->createChannel();

        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeSlug = $this->postJson($testUrl, [
            'slug' => $rand =  'a' . strtolower(Str::random(10))
        ])->assertOk();

        $this->assertTrue($tryChangeSlug['ok']);
        $this->assertTrue($tryChangeSlug['isValid']);
        $this->assertEquals($rand, $tryChangeSlug['messages']['slug']);
        $this->assertEquals($activeUser->id, $tryChangeSlug['messages']['owner']);

        $getChannelUrl = route('findChannelById', [
            'slug' => $rand
        ]);

        $tryGetChannelInfo = $this->getJson($getChannelUrl)->assertOk();
        $this->assertEquals($channel->id, $tryGetChannelInfo['messages']['id']);
    }


    /** @test */
    public function 최소자리로_채널_슬러그_업데이트에_성공하라(): void
    {
        $tryCreateChannel = $this->createChannel();

        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeSlug = $this->postJson($testUrl, [
            'slug' => $rand =  'a' . strtolower(Str::random(ChannelSlug::MIN_SLUG_LENGTH - 1))
        ])->assertOk();

        $this->assertTrue($tryChangeSlug['ok']);
        $this->assertTrue($tryChangeSlug['isValid']);
        $this->assertEquals($rand, $tryChangeSlug['messages']['slug']);
        $this->assertEquals($activeUser->id, $tryChangeSlug['messages']['owner']);

        $getChannelUrl = route('findChannelById', [
            'slug' => $rand
        ]);

        $tryGetChannelInfo = $this->getJson($getChannelUrl)->assertOk();
        $this->assertEquals($channel->id, $tryGetChannelInfo['messages']['id']);
    }

    /** @test */
    public function 채널_슬러그_자리수_미달로_업데이트에_실패하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeSlug = $this->postJson($testUrl, [
            'slug' => $rand = 'a' . strtolower(Str::random(ChannelSlug::MIN_SLUG_LENGTH - 2))
        ])->assertStatus(422);

        $this->assertFalse($tryChangeSlug['ok']);
        $this->assertFalse($tryChangeSlug['isValid']);
        $this->assertEquals(UpdateChannelRequest::SLUG_IS_SHORT, $tryChangeSlug['messages']['code']);
    }

    /** @test */
    public function 채널_슬러그_자리수_초과로_업데이트에_실패하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeSlug = $this->postJson($testUrl, [
            'slug' => $rand = 'a' . strtolower(Str::random(ChannelSlug::MAX_SLUG_LENGTH))
        ])->assertStatus(422);

        $this->assertFalse($tryChangeSlug['ok']);
        $this->assertFalse($tryChangeSlug['isValid']);
        $this->assertEquals(UpdateChannelRequest::SLUG_IS_LONG, $tryChangeSlug['messages']['code']);
    }

    /** @test */
    public function 채널_슬러그_패턴_불일치로_업데이트에_실패하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        collect([
            '1' . strtolower(Str::random(ChannelSlug::MAX_SLUG_LENGTH - 1)),
            '+' . strtolower(Str::random(ChannelSlug::MAX_SLUG_LENGTH - 2)),
            'A' . strtoupper(Str::random(ChannelSlug::MAX_SLUG_LENGTH - 2)),
        ])->each(function ($lilleagleSlug) use ($testUrl) {
            $tryChangeSlug = $this->postJson($testUrl, [
                'slug' => $lilleagleSlug
            ])->assertStatus(422);

            $this->assertFalse($tryChangeSlug['ok']);
            $this->assertFalse($tryChangeSlug['isValid']);
            $this->assertEquals(UpdateChannelRequest::SLUG_PATTERN_IS_WRONG, $tryChangeSlug['messages']['code']);
        });
    }


    /** @test */
    public function 채널_이름_업데이트에_성공하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);


        $tryChangeSlug = $this->postJson($testUrl, [
            'name' => $name = Str::random(20),
        ])->assertOk();

        $this->assertTrue($tryChangeSlug['ok']);
        $this->assertTrue($tryChangeSlug['isValid']);

        $this->assertTrue($tryChangeSlug['ok']);
        $this->assertTrue($tryChangeSlug['isValid']);
        $this->assertEquals($name, $tryChangeSlug['messages']['channelName']);
        $this->assertEquals($activeUser->id, $tryChangeSlug['messages']['owner']);
    }

    /** @test */
    public function 채널_이름_최대자리_초과로_실패하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeName = $this->postJson($testUrl, [
            'name' => $name = Str::random(RulesCreateChannel::NAME_MAX_LENGTH + 1),
        ]);

        $this->assertFalse($tryChangeName['ok']);
        $this->assertFalse($tryChangeName['isValid']);
        $this->assertEquals(RulesCreateChannel::NAME_LENGTH_LONG, $tryChangeName['messages']['code']);
    }

    /** @test */
    public function 채널_이름_중복으로_실패하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeName = $this->postJson($testUrl, [
            'name' => $channel->name,
        ]);

        $this->assertFalse($tryChangeName['ok']);
        $this->assertFalse($tryChangeName['isValid']);
        $this->assertEquals(RulesCreateChannel::NAME_IS_NOT_UNIQUE, $tryChangeName['messages']['code']);
    }

    /** @test */
    public function 채널_설명_업데이트에_성공하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);


        $tryChangeSlug = $this->postJson($testUrl, [
            'description' => $desc = Str::random(20),
        ])->assertOk();

        $this->assertTrue($tryChangeSlug['ok']);
        $this->assertTrue($tryChangeSlug['isValid']);

        $this->assertTrue($tryChangeSlug['ok']);
        $this->assertTrue($tryChangeSlug['isValid']);
        $this->assertEquals($desc, $tryChangeSlug['messages']['description']);
        $this->assertEquals($activeUser->id, $tryChangeSlug['messages']['owner']);

        $getChannelUrl = route('findChannelById', [
            'slug' => $channel->slug
        ]);

        $tryGetChannelInfo = $this->getJson($getChannelUrl)->assertOk();
        $this->assertEquals($channel->id, $tryGetChannelInfo['messages']['id']);
    }

    /** @test */
    public function 로고이미지_이미지_아닌거_올려서_채널정보_업데이트에_실패하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'logo_image' => UploadedFile::fake()->create('test.asdf', 2047),
        ])->assertStatus(422);

        $this->assertFalse($tryChangeLogo['ok']);
        $this->assertFalse($tryChangeLogo['isValid']);

        $this->assertEquals(
            UpdateChannelRequest::PROFILE_UPLOAD_FILE_IS_NOT_IMAGE,
            $tryChangeLogo['messages']['code']
        );
    }

    /** @test */
    public function 로고이미지_이미지_사진큰거_올려서_채널정보_업데이트에_실패하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'logo_image' => UploadedFile::fake()->create('test.png', 2049),
        ])->assertStatus(422);

        $this->assertFalse($tryChangeLogo['ok']);
        $this->assertFalse($tryChangeLogo['isValid']);

        $this->assertEquals(
            UpdateChannelRequest::PROFILE_UPLOAD_FILE_IS_LARGE,
            $tryChangeLogo['messages']['code']
        );
    }

    /** @test */
    public function 로고이미지_이미지_업데이트에_성공하라(): void
    {
        $tryCreateChannel = $this->createChannel();

        $channel = $tryCreateChannel['channel'];

        $channel->logo_image = null;

        $channel->save();

        $this->assertNull(Channel::find($channel->id)->logo_image);

        $activeUser = $tryCreateChannel['user'];
        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'logo_image' => UploadedFile::fake()->create('test.png', 2048),
        ])->assertOk();

        $this->assertTrue($tryChangeLogo['ok']);
        $this->assertTrue($tryChangeLogo['isValid']);
        $this->assertNotNull(Channel::find($channel->id)->logo_image);
        $this->assertIsString($tryChangeLogo['messages']['logoImage']);
    }


    /** @test */
    public function 배너이미지_이미지_아닌거_올려서_채널정보_업데이트에_실패하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];


        $banerImage = ChannelBannerImage::create([
            'banner_image' => 'test',
            'channel_id' => $channel->id
        ]);

        $this->assertEquals(
            'test',
            $channel->bannerImages->first()->banner_image
        );

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'banner_image' => UploadedFile::fake()->create('test.asdf', 2047),
            'banner_image_id' => $banerImage->id,
        ])->assertStatus(422);

        $this->assertFalse($tryChangeLogo['ok']);
        $this->assertFalse($tryChangeLogo['isValid']);

        $this->assertEquals(
            UpdateChannelRequest::BANNER_UPLOAD_FILE_IS_NOT_IMAGE,
            $tryChangeLogo['messages']['code']
        );
    }

    /** @test */
    public function 배너이미지_이미지_사진큰거_올려서_채널정보_업데이트에_실패하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $banerImage = ChannelBannerImage::create([
            'banner_image' => 'test',
            'channel_id' => $channel->id
        ]);

        $this->assertEquals(
            'test',
            $channel->bannerImages->first()->banner_image
        );

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'banner_image' => UploadedFile::fake()->create('test.png', 2050),
            'banner_image_id' => $banerImage->id,
        ])->assertStatus(422);

        $this->assertFalse($tryChangeLogo['ok']);
        $this->assertFalse($tryChangeLogo['isValid']);

        $this->assertEquals(
            UpdateChannelRequest::BANNER_UPLOAD_FILE_IS_LARGE,
            $tryChangeLogo['messages']['code']
        );
    }

    /** @test */
    public function 배너이미지_이미지_올랄때_배너_아이디_안올려서_채널정보_업데이트에_실패하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $banerImage = ChannelBannerImage::create([
            'banner_image' => 'test',
            'channel_id' => $channel->id
        ]);

        $this->assertEquals(
            'test',
            $channel->bannerImages->first()->banner_image
        );

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'banner_image' => UploadedFile::fake()->create('test.png', 1900),
            // 'banner_image_id' => $banerImage->id,
        ])->assertStatus(422);

        $this->assertEquals(
            UpdateChannelRequest::BANNER_ID_IS_EMPTY,
            $tryChangeLogo['messages']['code']
        );
    }


    /** @test */
    public function 배너이미지_이미지_올랄때_배너_아이디_이상한거_올려서_채널정보_업데이트에_실패하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $banerImage = ChannelBannerImage::create([
            'banner_image' => 'test',
            'channel_id' => $channel->id
        ]);

        $this->assertEquals(
            'test',
            $channel->bannerImages->first()->banner_image
        );

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'banner_image' => UploadedFile::fake()->create('test.png', 1900),
            'banner_image_id' => -3,
        ])->assertStatus(422);

        $this->assertEquals(
            UpdateChannelRequest::BANNER_ID_IS_NOT_EXISTS,
            $tryChangeLogo['messages']['code']
        );
    }

    /** @test */
    public function 배너이미지_이미지_업데이트에_성공하라(): void
    {
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];


        $banerImage = ChannelBannerImage::create([
            'banner_image' => 'test',
            'channel_id' => $channel->id
        ]);

        $this->assertEquals(
            'test',
            $channel->bannerImages->first()->banner_image
        );

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'banner_image' => UploadedFile::fake()->create('test.jpg', 2000),
            'banner_image_id' => $banerImage->id,
        ])->assertOk();

        $this->assertTrue($tryChangeLogo['ok']);
        $this->assertTrue($tryChangeLogo['isValid']);
        foreach ($tryChangeLogo['messages']['bannerImages'] as $image) {
            $this->assertFalse('test' === $image);
        }
    }
}
