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
use App\Http\Controllers\Team\Board\UploadArticleController as TeamBoardImageUploadController;

use App\Http\Controllers\Channel\Board\Category\ChangeStatusController as ChannelBoardCategoryStatusChangeController;
use App\Http\Controllers\Channel\Board\UploadArticleController as ChannelBoardArticleUploadController;
use App\Models\User;
use App\Services\Channel\BoardService as ChannelBoardService;
use App\Repositories\Channel\BoardRespository as ChannelBoardRepository;

use App\Services\Team\BoardService as TeamBoardService;
use App\Repositories\Team\BoardRespository as TeamBoardRepository;

use App\Services\Common\BoardService as CommonBoardService;
use App\Repositories\Common\BoardRespository as CommonBoardRepository;
use Closure;

use App\Contracts\Board\Service as BoardServiceContract;

use App\Http\Controllers\Channel\Board\ShowArticleController as ShowChannelBoardArticleController;
use App\Http\Controllers\Team\Board\ShowArticleController as ShowTeamBoardArticleController;

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

        $this->conditionBind([
            TeamBoardImageUploadController::class,
            TeamBoardCategoryStatusChangeController::class,
            ShowTeamBoardArticleController::class,
        ], BoardServiceContract::class, function (): TeamBoardService {
            return new TeamBoardService(new TeamBoardRepository());
        });

        $this->conditionBind([ChannelBoardService::class], CommonBoardRepository::class, function (): ChannelBoardRepository {
            return new ChannelBoardRepository();
        });

        $this->conditionBind([
            ChannelBoardArticleUploadController::class,
            ChannelBoardCategoryStatusChangeController::class,
            ShowChannelBoardArticleController::class,
        ], BoardServiceContract::class, function (): ChannelBoardService {
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
         * @todo ?????? ?????? ???????????? ??????
         */
        Validator::extend('channelHasOnlyOneBanner', fn (): bool => $this->canUpdateBanner('slug'));
        Validator::extend('teamHasOnlyOneBanner', fn (): bool => $this->canUpdateBanner('teamSlug'));
        Validator::extend('isMyTeamBroadcast', fn ($_, string $param): bool => $this->canUpdateBroadCast('teamSlug', (int)$param));
        Validator::extend('isMyChannelBroadcast', fn ($_, string $param): bool => $this->canUpdateBroadCast('slug', (int)$param));
        Validator::extend('alreadyInvite', fn (string $_, User $requestUser, array $__): bool => $this->alreadyInvite($_, $requestUser, $__));
        Validator::extend('isNotTeamMember', fn (string $_, User $requestUser, array $__): bool => $this->isNotTeamMember($_, $requestUser, $__));
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
     * ????????? ?????? ???????????? ???????????? ?????? ????????? ?????????.
     * @param string $slugType ??????????????? ?????? ????????? ??????
     * @param int $boradCastId ???????????? ??? ????????? ?????????
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return bool ????????? ?????? ???????????? ????????????
     */
    private function canUpdateBroadCast(string $slugType, int $boradCastId): bool
    {
        $request = request();
        $requestSlug = $request->route($slugType);

        return $requestSlug->broadcastAddress()->where('id', '=', $boradCastId)->exists();
    }

    /**
     * ????????? ???????????? ?????? ????????? ???????????? ????????? ?????????.
     * ?????? ???????????? ?????????????????? ????????? ?????? ???????????????.
     * ????????? ?????? ???????????? ??????????????? ???????????? ?????? ??? ?????? ?????? (banner_image_id ?????? ?????? ???)
     * false??? ???????????? ???????????? true??? ???????????????.
     *
     * @param string $slugType ??????????????? ?????? ????????? ??????
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return bool ??????????????? ???????????? ?????? ?????? ????????????
     */
    private function canUpdateBanner(string $slugType): bool
    {
        $request = request();
        if ($request->has('banner_image_id') === false) {
            return $request->route($slugType)->bannerImages->count() === 0;
        }
        return true;
    }

    private function teamRelatedAnotherIsNotExists(User $requestUser, string $model, array $otherCondition = []): bool
    {
        $request = request();
        $team = $request->route('teamSlug');

        $searchConditions = collect(array_merge($otherCondition, [
            ['team_id', '=', $team->id],
            ['user_id', '=', $requestUser->id],
        ]))->filter(fn (array $searchCondition): bool => count($searchCondition) >= 1)->toArray();
        return $model::where($searchConditions)->exists() === false;
    }


    private function alreadyInvite(string $_, User $requestUser, array $__): bool
    {
        return $this->teamRelatedAnotherIsNotExists($requestUser, InvitationCard::class, [
            ['status', '=', InvitationCard::PENDING]
        ]);
    }

    private function isNotTeamMember(string $_, User $requestUser, array $__): bool
    {
        return $this->teamRelatedAnotherIsNotExists($requestUser, Member::class);
    }
}
