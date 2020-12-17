<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeGameEventTableName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('game_events', 'game_types');
        Schema::table('game_types', function (Blueprint $table) {
            $table->string('name')->comment('게임 종목 이름')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('game_types', 'game_events');

        Schema::table('game_types', function (Blueprint $table) {
            $table->dropUnique('game_types_name_unique');
        });
    }
}
