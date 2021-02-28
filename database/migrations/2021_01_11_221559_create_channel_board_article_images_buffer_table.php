<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelBoardArticleImagesBufferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // 채널 이미지 임시 업로드 버퍼 테이블
        Schema::create('channel_board_article_images_buffer', function (Blueprint $table): void {
            $table->id();
            $table->string('buffer_image_path')->comment('임시저장 이미지 이름');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_board_article_images_buffer');
    }
}
