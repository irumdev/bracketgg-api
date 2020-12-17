<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Channels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up(): void
    {
        //
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('logo_image');
            $table->bigInteger('follwer_count')->comment('팔로워 카운트');
            $table->bigInteger('like_count')->comment('좋아요 수');
            $table->foreignId('owner')->comment('채널장');
            $table->text('description')->comment('소개');
            $table->string('name')->comment('채널이름');
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
    public function down()
    {
        Schema::dropIfExists('channels');
    }
}
