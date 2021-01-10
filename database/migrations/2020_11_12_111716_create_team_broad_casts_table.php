<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamBroadCastsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('team_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->comment('방송국 하는 팀');
            $table->string('broadcast_address')->comment('방송국 주소');
            $table->tinyInteger('platform')->comment('방송국 플랫폼');
            $table->timestamps();
            $table->foreign('team_id')->on('teams')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('team_broadcasts');
    }
}
