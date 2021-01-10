<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Channel\Channel;

class CreateChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up(): void
    {
        // 채널 테이블
        Schema::create('channels', function (Blueprint $table) {
            $table->id()->comment('인덱스');
            $table->string('logo_image')->nullable(true)->comment('로고이미지');

            $table->bigInteger('follwer_count')->comment('팔로워 카운트');
            $table->bigInteger('like_count')->comment('좋아요 수');

            $table->foreignId('owner')->comment('채널장');
            $table->text('description')->nullable(true)->comment('소개');

            $table->string('name')->comment('채널이름');

            $table->tinyInteger('board_category_count_limit')
                  ->default(Channel::DEFAULT_BOARD_CATEGORY_COUNT)
                  ->comment('채널 게시판 생성 시 카테고리 최대 개수');

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('owner')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
}
