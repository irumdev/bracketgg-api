<?php

use Illuminate\Database\Seeder;
use App\Http\Models\User;
use App\Models\Channel\Channel;
use App\Models\Team\Team;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        factory(Team::class, 20)->states([
            'addSmallTeamArticlesWithSavedImagesAndComments',
            'addSignedMembers',
            'addSlug',
            'addBannerImage',
            'addBroadcasts',
            'addOperateGame',
            'addRandInvitationCards'
        ])->create();

        factory(Channel::class, 20)->states([
            'addSmallChannelArticlesWithSavedImagesAndComments',
            'addSlug',
            'addBannerImage','hasFollower',
            'addBroadcasts', 'hasLike'
        ])->create();
    }
}
