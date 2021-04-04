<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamArticleRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('team_article_replies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->comment('게시글 아이디');
            $table->foreignId('parent_id')->nullable()->comment('부모 아이디');
            $table->foreignId('user_id')->comment('게시자');
            $table->foreignId('team_id')->comment('댓글 소속');
            $table->text('content')->comment('댓글 내용');
            $table->string('delete_reason')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('team_id')->on('teams')->references('id')->cascadeOnDelete();
            $table->foreign('article_id')->on('team_board_articles')->references('id')->cascadeOnDelete();
            $table->foreign('user_id')->on('users')->references('id')->cascadeOnDelete();
            $table->foreign('parent_id')->on('team_article_replies')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('team_article_replies');
    }
}
