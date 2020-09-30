<?php

use Illuminate\Database\Seeder;
use App\Http\Models\User;
use App\Models\Channel;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        factory(Channel::class, 20)->states([
            'addBannerImage','hasFollower',
            'addBroadcasts', 'hasLike'
        ])->create();
    }
}
