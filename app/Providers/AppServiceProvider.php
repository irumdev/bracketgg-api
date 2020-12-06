<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;
use App\Helpers\Macros\ArrayMixin;
use Carbon\Carbon;
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
        Arr::mixin(new ArrayMixin());
    }

    private function canUpdateBanner(string $slugType): bool
    {
        $request = request();
        if ($request->has('banner_image_id') === false) {
            return $request->route($slugType)->bannerImages->count() === 0;
        }
        return true;
    }
}
