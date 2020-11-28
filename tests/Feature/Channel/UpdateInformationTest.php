<?php

namespace Tests\Feature\Channel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use Illuminate\Support\Str;
use App\Http\Requests\UpdateChannelRequest;
use App\Models\Channel\Slug as ChannelSlug;
use Illuminate\Http\UploadedFile;
use App\Models\Channel\Channel;
use App\Http\Requests\Rules\CreateChannel as RulesCreateChannel;
use App\Models\Channel\BannerImage as ChannelBannerImage;

class UpdateInformationTest extends TestCase
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
    public function successUpdateChannelSlug(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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

        $getChannelUrl = route('findChannelBySlug', [
            'slug' => $rand
        ]);

        $tryGetChannelInfo = $this->getJson($getChannelUrl)->assertOk();
        $this->assertEquals($channel->id, $tryGetChannelInfo['messages']['id']);
    }


    /** @test */
    public function successUpdateChannelSlugWhenSlugLengthIsBoundaries(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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

        $getChannelUrl = route('findChannelBySlug', [
            'slug' => $rand
        ]);

        $tryGetChannelInfo = $this->getJson($getChannelUrl)->assertOk();
        $this->assertEquals($channel->id, $tryGetChannelInfo['messages']['id']);
    }

    /** @test */
    public function failUpdateChannelSlugWhenSlugIsTooShort(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
    public function failUpdateChannelSlugWhenSlugIsNotUnique(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelInfo', [
            'slug' => $channel->slug
        ]);

        $tryChangeSlug = $this->postJson($testUrl, [
            'slug' => $channel->slug,
        ])->assertStatus(422);

        $this->assertFalse($tryChangeSlug['ok']);
        $this->assertFalse($tryChangeSlug['isValid']);
        $this->assertEquals(UpdateChannelRequest::SLUG_IS_NOT_UNIQUE, $tryChangeSlug['messages']['code']);
    }

    /** @test */
    public function failUpdateChannelSlugWhenSlugIsTooLong(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
    public function failUpdateChannelSlugWhenSlugPatterlIsWrong(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
    public function successUpdateChannelName(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
    public function failUpdateChannelNameWhenChannelNameIsTooLong(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
    public function failUpdateChannelNameWhenChannelNameIsDuplicate(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
    public function successUpdateChannelDescription(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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

        $getChannelUrl = route('findChannelBySlug', [
            'slug' => $channel->slug
        ]);

        $tryGetChannelInfo = $this->getJson($getChannelUrl)->assertOk();
        $this->assertEquals($channel->id, $tryGetChannelInfo['messages']['id']);
    }

    /** @test */
    public function failUpdateLogoImageWhenLogoImageIsNotImageFile(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
    public function failUpdateLogoImageWhenLogoImageIsTooLarge(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
    public function successUpdateLogoImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
    public function failUpdateBannerImageWhenBannerImageIsNotImageFile(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
    public function failUpdateBannerImageWhenBannerImageIsTooLarge(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
    public function failUpdateBannerImageWhenUploadBannerImageButBannerImageIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
    public function failUpdateBannerImageWhenBannerImageIdIsInvalid(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
    public function successUpdateBannerImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
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
