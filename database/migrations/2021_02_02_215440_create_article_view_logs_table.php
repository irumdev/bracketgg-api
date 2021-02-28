<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleViewLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_view_logs', function (Blueprint $table): void {
            $table->id();
            $table->ipAddress('lookup_ip')->comment('게시글 조회 한 ip주소');
            $table->tinyInteger('article_type')->comment('조회한 게시글이 채널 게시글인지 팀 게시판 게시글인지');
            $table->foreignId('article_id')->comment('조회 한 게시글 아이디');
            $table->timestamps();

            $table->index('created_at', 'created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('article_view_logs');
    }
}
