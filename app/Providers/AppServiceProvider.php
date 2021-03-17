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

use App\Models\Channel\Board\Category;

use Illuminate\Support\Facades\Validator;
use App\Http\Requests\CommonFormRequest;
use App\Http\Requests\Rules\Broadcast;

use Illuminate\Database\Eloquent\Collection;
use App\Http\Controllers\Team\Board\Category\ChangeStatusController as TeamBoardCategoryStatusChangeController;
use App\Http\Controllers\Channel\Board\Category\ChangeStatusController as ChannelBoardCategoryStatusChangeController;

use App\Services\Channel\BoardService as ChannelBoardService;
use App\Repositories\Channel\BoardRespository as ChannelBoardRepository;

use App\Services\Team\BoardService as TeamBoardService;
use App\Repositories\Team\BoardRespository as TeamBoardRepository;

use App\Services\Common\BoardService as CommonBoardService;
use App\Repositories\Common\BoardRespository as CommonBoardRepository;
use Closure;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->conditionBind([TeamBoardService::class], CommonBoardRepository::class, function (): TeamBoardRepository {
            return new TeamBoardRepository();
        });

        $this->conditionBind([TeamBoardCategoryStatusChangeController::class], CommonBoardService::class, function (): TeamBoardService {
            return new TeamBoardService(new TeamBoardRepository());
        });

        $this->conditionBind([ChannelBoardService::class], CommonBoardRepository::class, function (): ChannelBoardRepository {
            return new ChannelBoardRepository();
        });

        $this->conditionBind([ChannelBoardCategoryStatusChangeController::class], CommonBoardService::class, function (): ChannelBoardService {
            return new ChannelBoardService(new ChannelBoardRepository());
        });
    }


    private function conditionBind(array $condition, string $abstract, Closure $bind)
    {
        $this->app->when($condition)->needs($abstract)->give($bind);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        /**
         * @todo 해당 콜백 클래스로 리턴
         */
        Validator::extend('channelHasOnlyOneBanner', fn (): bool => $this->canUpdateBanner('slug'));
        Validator::extend('teamHasOnlyOneBanner', fn (): bool => $this->canUpdateBanner('teamSlug'));
        Validator::extend('isMyTeamBroadcast', fn ($_, string $param): bool => $this->canUpdateBroadCast('teamSlug', (int)$param));
        Validator::extend('isMyChannelBroadcast', fn ($_, string $param): bool => $this->canUpdateBroadCast('slug', (int)$param));
        Validator::extend('alreadyInvite', fn (): bool => $this->alreadyInvite());
        Validator::extend('isNotTeamMember', fn (): bool => $this->isNotTeamMember());
        Validator::extend('categoryNameIsNotUnique', fn (string $validateIndex, string $categoryName, array $boardCategories): bool => $this->categoryNameIsNotUnique(
            (int)explode('.', $validateIndex)[1],
            $categoryName,
            unserialize(implode('', $boardCategories))
        ));
        Validator::extend('isBroadcastUrlUnique', fn (string $attribute, string $param, array $value): bool => $this->uniqueExists($attribute, $param, $value));

        Arr::mixin(new ArrayMixin());
        Str::mixin(new StringMixin());
    }

    public function categoryNameIsNotUnique(int $validateIndex, string $categoryName, Collection $boardCategories): bool
    {
        $willValidateItem = request()->all()['needValidateItems'][$validateIndex];

        $hasId = isset($willValidateItem['id']);

        $alreadyHasName = $boardCategories->where('name', $willValidateItem['name'])->count() === 0;
        if ($hasId === false) {
            return $alreadyHasName;
        }


        $isSameWillChangeIdAndAlreadyUsedName = $boardCategories->where('name', $willValidateItem['name'])
                                                                ->where('id', $willValidateItem['id'])
                                                                ->count() === 1;

        if ($hasId && $isSameWillChangeIdAndAlreadyUsedName) {
            return true;
        }

        return $alreadyHasName;
    }

    public function uniqueExists(string $attribute, string $param, array $value): bool
    {
        $modelName = 1;

        $requestData = request()->all();
        $requestBroadCastId = data_get($requestData, str_replace('url', 'id', $attribute));
        $requestBroadCastUrl = data_get($requestData, $attribute);


        $hasNotBroadcastId = null === $requestBroadCastId;

        $isExistsBroadcastAddress = $value[$modelName]::where('broadcast_address', $requestBroadCastUrl)->exists();

        if ($hasNotBroadcastId) {
            return $isExistsBroadcastAddress === false;
        }

        executeUnless(is_numeric($requestBroadCastId), function (): void {
            (new CommonFormRequest())->throwUnProcessableEntityException(Broadcast::BROADCAST_ID_IS_NOT_NUMERIC);
        });

        $requestBroadCast = $value[$modelName]::find($requestBroadCastId);
        $isSameDbBroadCastUrlAndRequestBroadcastUrl = $requestBroadCast->broadcast_address === $requestBroadCastUrl;

        if ($isSameDbBroadCastUrlAndRequestBroadcastUrl) {
            return true;
        }
        return $isExistsBroadcastAddress === false;
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

    private function teamRelatedAnotherIsNotExists(string $model, array $otherCondition = []): bool
    {
        $request = request();
        $inviteUser = $request->route('userIdx');
        $team = $request->route('teamSlug');

        $searchConditions = collect(array_merge($otherCondition, [
            ['team_id', '=', $team->id],
            ['user_id', '=', $inviteUser->id],
        ]))->filter(fn (array $searchCondition): bool => count($searchCondition) >= 1)->toArray();
        return $model::where($searchConditions)->exists() === false;
    }


    private function alreadyInvite(): bool
    {
        return $this->teamRelatedAnotherIsNotExists(InvitationCard::class, [
            ['status', '=', InvitationCard::PENDING]
        ]);
    }

    private function isNotTeamMember(): bool
    {
        return $this->teamRelatedAnotherIsNotExists(Member::class);
    }
}
