<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamBoardCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_board_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('팀 게시판 종류');
            $table->tinyInteger('show_order')->comment('보여지는 순서');
            $table->integer('article_count')->comment('게시글 갯수');
            $table->boolean('is_public')->comment('공개 여부');
            $table->foreignId('team_id')->comment('소유한 팀장');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('team_id')->on('teams')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_board_categories');
    }
}
