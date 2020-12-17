<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserCreateTeamAndChannelCountLimit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('create_team_limit')->after('password')->default(3)->comment('유저당 팀 생성 수');
            $table->tinyInteger('create_channel_limit')->after('password')->default(5)->comment('유저당 체널 생성 수');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('create_team_limit');
            $table->tinyInteger('create_channel_limit');
        });
    }
}
