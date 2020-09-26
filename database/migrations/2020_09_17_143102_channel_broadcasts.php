<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChannelBroadcasts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->comment('방송국 하는 채널');
            $table->string('broadcast_address')->comment('방송국 주소');
            $table->integer('platform')->comment('방송국 플랫폼');

            $table->timestamps();

            $table->foreign('channel_id')->on('channels')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('channel_broadcasts');
    }
}
