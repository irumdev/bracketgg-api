<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TeamEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // 팀이 어떤 게임 종목을 운영하는지 매핑 태이블
        Schema::create('team_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->comment('팀 인덱스');
            $table->foreignId('game_event_id')->comment('게임종목 인덱스');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('team_id')->on('teams')->references('id')->cascadeOnDelete();
            $table->foreign('game_event_id')->on('game_events')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('team_events');
    }
}
