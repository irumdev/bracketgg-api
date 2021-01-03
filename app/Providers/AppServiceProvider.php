<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Helpers\Macros\ArrayMixin;
use App\Helpers\Macros\StringMixin;

use App\Models\Team\InvitationCard;
use App\Models\Team\Member;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Validator::extend('channelHasOnlyOneBanner', fn () => $this->canUpdateBanner('slug'));
        Validator::extend('teamHasOnlyOneBanner', fn () => $this->canUpdateBanner('teamSlug'));
        Validator::extend('isMyTeamBroadcast', fn ($attribute, $param, $value) => $this->canUpdateBroadCast('teamSlug', (int)$param));
        Validator::extend('isMyChannelBroadcast', fn ($attribute, $param, $value) => $this->canUpdateBroadCast('slug', (int)$param));
        Validator::extend('alreadyInvite', fn ($attribute, $param, $value) => $this->alreadyInvite());
        Validator::extend('isNotTeamMember', fn ($attribute, $param, $value) => $this->isNotTeamMember());
        Arr::mixin(new ArrayMixin());
        Str::mixin(new StringMixin());
    }

    /**
     * 방송국 주소 업데이트 가능여부 판단 메소드 입니다.
     * @param string $slugType 라우터에서 찾을 슬러그 타입
     * @param int $boradCastId 업데이트 할 방송국 아이디
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return bool 방송국 주소 업데이트 가능여부
     */
    private function canUpdateBroadCast(string $slugType, int $boradCastId): bool
    {
        $request = request();
        $requestSlug = $request->route($slugType);

        return $requestSlug->broadcastAddress()->where('id', '=', $boradCastId)->exists();
    }

    /**
     * 배너를 업데이트 가능 여부를 체크하는 메소드 입니다.
     * 해당 메소드는 밸러데이터에 등록을 하고 사용합니다.
     * 배너를 이미 하나라도 가지고있는 상태에서 한번 더 생성 요청 (banner_image_id 없이 요청 시)
     * false를 리턴하며 아닐때는 true를 리턴합니다.
     *
     * @param string $slugType 라우터에서 찾을 슬러그 타입
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return bool 배너이미지 업데이트 또는 생성 가능여부
     */
    private function canUpdateBanner(string $slugType): bool
    {
        $request = request();
        if ($request->has('banner_image_id') === false) {
            return $request->route($slugType)->bannerImages->count() === 0;
        }
        return true;
    }


    private function alreadyInvite(): bool
    {
        $request = request();
        $inviteUser = $request->route('userIdx');
        $team = $request->route('teamSlug');

        return InvitationCard::where([
            ['team_id', '=', $team->id],
            ['user_id', '=', $inviteUser->id],
        ])->exists() === false;
    }

    private function isNotTeamMember(): bool
    {
        $request = request();
        $inviteUser = $request->route('userIdx');
        $team = $request->route('teamSlug');

        return Member::where([
            ['team_id', '=', $team->id],
            ['user_id', '=', $inviteUser->id],
        ])->exists() === false;
    }
}
