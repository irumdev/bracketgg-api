<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelSlugsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // 채널 슬러그 테이블
        Schema::create('channel_slugs', function (Blueprint $table) {
            $table->id()->comment('채널 슬러그 아이디');
            $table->string('slug')->unique()->comment('채널 슬러그');
            $table->foreignId('channel_id')->comment('슬러그 소유 채널 아이디');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('channel_id')->on('channels')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channel_slugs');
    }
}
