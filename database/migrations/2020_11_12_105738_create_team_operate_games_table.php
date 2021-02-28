<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamOperateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        /**
         * @todo operage_games -> team_operate_games
         */
        // 팀이 어떤 게임 종목을 운영하는지 매핑 태이블
        Schema::create('operate_games', function (Blueprint $table): void {
            $table->id()->comment('팀이 운영하는 게임 리스트 아이디');
            $table->foreignId('team_id')->comment('팀 인덱스');
            $table->foreignId('game_type_id')->comment('게임종목 인덱스');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('team_id')->on('teams')->references('id')->cascadeOnDelete();
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
        Schema::dropIfExists('operate_games');
    }
}
