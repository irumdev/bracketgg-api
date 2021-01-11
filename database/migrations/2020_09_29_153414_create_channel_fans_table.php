<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelFansTable extends Migration
{
    public function up(): void
    {
        // 채널 좋아요 한 사람들 테이블
        Schema::create('channel_fans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->comment('좋아요 한 채널 아이디');
            $table->foreignId('user_id')->comment('좋아요 누른 유저 아이디');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('channel_id')->on('channels')->references('id')->cascadeOnDelete();
            $table->foreign('user_id')->on('users')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channel_fans');
    }
}
