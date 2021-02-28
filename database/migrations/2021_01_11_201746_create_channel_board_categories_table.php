<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelBoardCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // 채널 게시판 종류
        Schema::create('channel_board_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->comment('채널 게시판 종류');
            $table->tinyInteger('show_order')->comment('보여지는 순서');
            $table->integer('article_count')->comment('게시글 갯수');
            $table->boolean('is_public')->comment('공개 여부');
            $table->foreignId('channel_id')->comment('소유한 채널장');

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
    public function down(): void
    {
        Schema::dropIfExists('channel_board_categories');
    }
}
