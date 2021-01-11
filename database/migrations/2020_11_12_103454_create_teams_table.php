<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Team\Team;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // 팀 테이블

        Schema::create('teams', function (Blueprint $table) {
            $table->id()->comment('팀 아이디');
            $table->foreignId('owner')->comment('팀장유저 인덱스');
            $table->string('name')->unique()->comment('팀 이름');
            $table->integer('member_count')->comment('팀 멤버 수')->default(1);
            $table->tinyInteger('is_public')->comment('팀 공개 여부');
            $table->string('logo_image')->nullable()->comment('로고 이미지');

            $table->tinyInteger('board_category_count_limit')
                  ->default(Team::DEFAULT_BOARD_CATEGORY_COUNT_LIMIT)
                  ->comment('팀 게시판 생성 시 카테고리 최대 개수');

            $table->softDeletes();
            $table->timestamps();
            $table->foreign('owner')->on('users')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
}
