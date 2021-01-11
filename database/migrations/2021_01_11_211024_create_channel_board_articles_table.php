<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelBoardsArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('channel_board_articles', function (Blueprint $table) {
            $table->id();

            $table->string('title')->comment('게시글 제목');
            $table->text('content')->comment('게시글 내용 (HTML 태그 통째로 들어옴)');
            $table->foreignId('user_id')->comment('작성자');
            $table->foreignId('category_id')->comment('카테고리 아이디');

            $table->foreign('user_id')->on('users')->references('id')->cascadeOnDelete();
            $table->foreign('category_id')->on('channel_board_categories')->references('id')->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_board_articles');
    }
}
