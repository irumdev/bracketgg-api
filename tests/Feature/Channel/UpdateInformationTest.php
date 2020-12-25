<?php

namespace Tests\Feature\Channel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use App\Models\Channel\Slug as ChannelSlug;
use Illuminate\Http\UploadedFile;
use App\Models\Channel\Channel;
use App\Models\Channel\BannerImage as ChannelBannerImage;
use App\Http\Requests\Rules\CreateChannel as RulesCreateChannel;
use App\Http\Requests\Channel\UpdateRequest as UpdateChannelRequest;
use App\Http\Requests\Channel\UpdateLogoImageRequest;
use App\Http\Requests\Channel\UpdateBannerImageRequest;
use Styde\Enlighten\Tests\EnlightenSetup;


class UpdateInformationTest extends TestCase
{
    use EnlightenSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
    }

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
            $channel['name']
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

    /**
     * @test
     * @enlighten
     */
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
            'slug' => $rand = Str::bracketGGslug(ChannelSlug::MIN_SLUG_LENGTH, ChannelSlug::MAX_SLUG_LENGTH)
        ])->assertOk();

        $this->assertTrue($tryChangeSlug['ok']);
        $this->assertTrue($tryChangeSlug['isValid']);
        $this->assertTrue($tryChangeSlug['messages']['isSuccess']);

        $getChannelUrl = route('findChannelBySlug', [
            'slug' => $rand
        ]);

        $tryGetChannelInfo = $this->getJson($getChannelUrl)->assertOk();
        $this->assertEquals($channel->id, $tryGetChannelInfo['messages']['id']);
    }


    /**
     * @test
     * @enlighten
     */
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
        $this->assertTrue($tryChangeSlug['messages']['isSuccess']);

        $getChannelUrl = route('findChannelBySlug', [
            'slug' => $rand
        ]);

        $tryGetChannelInfo = $this->getJson($getChannelUrl)->assertOk();
        $this->assertEquals($channel->id, $tryGetChannelInfo['messages']['id']);
    }

    /**
     * @test
     * @enlighten
     */
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

    /**
     * @test
     * @enlighten
     */
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

    /**
     * @test
     * @enlighten
     */
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

    /**
     * @test
     * @enlighten
     */
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
            '1asdfasdfasdf',
            'ㅁㄴㅇㄹㅁㄴㅇㄹ',
            'Asdf1-23',
            // 'sdf1-23sdf1-23sdf1-23sdf1-23sdf1-23sdf1-23sdf1-23sdf1-23', // filter by ChannelSlug::SLUG_IS_LONG
        ])->each(function ($lilleagleSlug) use ($testUrl) {
            $tryChangeSlug = $this->postJson($testUrl, [
                'slug' => $lilleagleSlug
            ])->assertStatus(422);

            $this->assertFalse($tryChangeSlug['ok']);
            $this->assertFalse($tryChangeSlug['isValid']);
            $this->assertEquals(UpdateChannelRequest::SLUG_PATTERN_IS_WRONG, $tryChangeSlug['messages']['code']);
        });
    }


    /**
     * @test
     * @enlighten
     */
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
        $this->assertTrue($tryChangeSlug['messages']['isSuccess']);
        $this->assertEquals(Channel::find($channel->id)->name, $name);
    }

    /**
     * @test
     * @enlighten
     */
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

    /**
     * @test
     * @enlighten
     */
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

    /**
     * @test
     * @enlighten
     */
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

        $this->assertTrue($tryChangeSlug['messages']['isSuccess']);

        $getChannelUrl = route('findChannelBySlug', [
            'slug' => $channel->slug
        ]);

        $tryGetChannelInfo = $this->getJson($getChannelUrl)->assertOk();
        $this->assertEquals($channel->id, $tryGetChannelInfo['messages']['id']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateLogoImageWhenLogoImageIsNotImageFile(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelLogo', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'logo_image' => UploadedFile::fake()->create('test.asdf', 2047),
        ])->assertStatus(422);

        $this->assertFalse($tryChangeLogo['ok']);
        $this->assertFalse($tryChangeLogo['isValid']);

        $this->assertEquals(
            UpdateLogoImageRequest::LOGO_UPLOAD_FILE_IS_NOT_IMAGE,
            $tryChangeLogo['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateLogoImageWhenLogoImageIsTooLarge(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelLogo', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'logo_image' => UploadedFile::fake()->create('test.png', 2049),
        ])->assertStatus(422);

        $this->assertFalse($tryChangeLogo['ok']);
        $this->assertFalse($tryChangeLogo['isValid']);

        $this->assertEquals(
            UpdateLogoImageRequest::LOGO_UPLOAD_FILE_IS_LARGE,
            $tryChangeLogo['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successUpdateLogoImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateChannel = $this->createChannel();

        $channel = $tryCreateChannel['channel'];

        $channel->logo_image = null;

        $channel->save();

        $this->assertNull(Channel::find($channel->id)->logo_image);

        $activeUser = $tryCreateChannel['user'];
        $testUrl = route('updateChannelLogo', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'logo_image' => UploadedFile::fake()->create('test.png', 2048),
        ])->assertOk();

        $this->assertTrue($tryChangeLogo['ok']);
        $this->assertTrue($tryChangeLogo['isValid']);
        $this->assertTrue($tryChangeLogo['messages']['isSuccess']);
        $this->assertNotNull(Channel::find($channel->id)->logo_image);
    }


    /**
     * @test
     * @enlighten
     */
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

        $testUrl = route('updateChannelBanner', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'banner_image' => UploadedFile::fake()->create('test.asdf', 2047),
            'banner_image_id' => $banerImage->id,
        ])->assertStatus(422);

        $this->assertFalse($tryChangeLogo['ok']);
        $this->assertFalse($tryChangeLogo['isValid']);

        $this->assertEquals(
            UpdateBannerImageRequest::BANNER_UPLOAD_FILE_IS_NOT_IMAGE,
            $tryChangeLogo['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
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

        $testUrl = route('updateChannelBanner', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'banner_image' => UploadedFile::fake()->create('test.png', 2050),
            'banner_image_id' => $banerImage->id,
        ])->assertStatus(422);

        $this->assertFalse($tryChangeLogo['ok']);
        $this->assertFalse($tryChangeLogo['isValid']);

        $this->assertEquals(
            UpdateBannerImageRequest::BANNER_UPLOAD_FILE_IS_LARGE,
            $tryChangeLogo['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
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

        $testUrl = route('updateChannelBanner', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'banner_image' => UploadedFile::fake()->create('test.png', 1900),
            'banner_image_id' => -3,
        ])->assertStatus(422);

        $this->assertEquals(
            UpdateBannerImageRequest::BANNER_IMAGE_ID_IS_NOT_EXISTS,
            $tryChangeLogo['messages']['code']
        );
    }

    /**
     * @test
     * @enlighten
     */
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

        $testUrl = route('updateChannelBanner', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'banner_image' => UploadedFile::fake()->create('test.jpg', 2000),
            'banner_image_id' => $banerImage->id,
        ])->assertOk();

        $this->assertTrue($tryChangeLogo['ok']);
        $this->assertTrue($tryChangeLogo['isValid']);
        $this->assertTrue($tryChangeLogo['messages']['isSuccess']);

        $this->assertTrue($channel->bannerImages()->first()->banner_image !== 'test');
    }


    /**
     * @test
     * @enlighten
     */
    public function successCreateBannerImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCreateChannel = $this->createChannel();
        $channel = $tryCreateChannel['channel'];
        $activeUser = $tryCreateChannel['user'];

        $testUrl = route('updateChannelBanner', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'banner_image' => UploadedFile::fake()->create('test.jpg', 2000),
            // 'banner_image_id' => $banerImage->id,
        ])->assertOk();

        $this->assertTrue($tryChangeLogo['ok']);
        $this->assertTrue($tryChangeLogo['isValid']);
        $this->assertTrue($tryChangeLogo['messages']['isSuccess']);

        $this->assertIsString($channel->bannerImages()->first()->banner_image);
    }


    /**
     * @test
     * @enlighten
     */
    public function failCreateBannerImageWhenBannerAlreadyExists(): void
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

        $testUrl = route('updateChannelBanner', [
            'slug' => $channel->slug
        ]);

        $tryChangeLogo = $this->postJson($testUrl, [
            'banner_image' => UploadedFile::fake()->create('test.jpg', 2000),
        ])->assertStatus(422);

        $this->assertFalse($tryChangeLogo['ok']);
        $this->assertFalse($tryChangeLogo['isValid']);

        $this->assertEquals(
            UpdateBannerImageRequest::BANNER_UPLOAD_FILE_HAS_MANY_BANNER,
            $tryChangeLogo['messages']['code']
        );
    }
}
