<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelBannerImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // 채널 배너 이미지 테이블
        Schema::create('channel_banner_images', function (Blueprint $table): void {
            $table->id();
            $table->string('banner_image')->comment('채널 배너 이미지');
            $table->foreignId('channel_id')->comment('채널 아이디');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('channel_id')->references('id')->on('channels')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_banner_images');
    }
}
