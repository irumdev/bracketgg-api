<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelBoardArticlesTable extends Migration
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
            $table->integer('see_count')->comment('조회수');
            $table->integer('like_count')->comment('좋아요 수');
            $table->integer('unlike_count')->comment('싫어요 수');
            $table->integer('comment_count')->comment('댓글 수');

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
