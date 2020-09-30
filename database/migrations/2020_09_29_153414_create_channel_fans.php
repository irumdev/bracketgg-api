<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelFans extends Migration
{
    public function up()
    {
        Schema::create('channel_fans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id');
            $table->foreignId('user_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('channel_id')->on('channels')->references('id')->cascadeOnDelete();
            $table->foreign('user_id')->on('users')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channel_fans');
    }
}
