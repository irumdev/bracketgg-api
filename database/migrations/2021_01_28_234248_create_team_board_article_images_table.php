<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamBoardArticleImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 팀 게시글에 확정으로 올린 이미지
        Schema::create('team_board_article_images', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('article_id')->comment('게시글 아이디');
            $table->string('article_image')->comment('게시글 이미지 파일 이름');

            $table->foreign('article_id')->on('team_board_articles')->references('id')->cascadeOnDelete();

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
        Schema::dropIfExists('team_board_article_images');
    }
}
