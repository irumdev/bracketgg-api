<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTeamEventsTableName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::rename('team_events', 'operate_games');

        Schema::table('operate_games', function (Blueprint $table) {
            $table->dropForeign('team_events_game_event_id_foreign');
            $table->renameColumn('game_event_id', 'game_type_id');
            $table->foreign('game_type_id')->on('game_types')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::rename('operate_games', 'team_events');
        Schema::table('team_events', function (Blueprint $table) {
            $table->dropForeign('operate_games_game_type_id_foreign');
            $table->renameColumn('game_type_id', 'game_event_id');

            // $table->foreign('game_event_id')->on('game_events')->references('id')->cascadeOnDelete();
        });
    }
}
