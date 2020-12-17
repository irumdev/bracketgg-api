<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeTeamBoradcastPlatformType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_broadcasts', function (Blueprint $table) {
            DB::statement("ALTER TABLE `team_broadcasts` CHANGE `platform` `platform` TINYINT(11) UNSIGNED NOT NULL COMMENT '방송국 플랫폼'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('team_broadcasts', function (Blueprint $table) {
            DB::statement("ALTER TABLE `team_broadcasts` CHANGE `platform` `platform` INT(11) NOT NULL COMMENT '방송국 플랫폼';");
        });
    }
}
