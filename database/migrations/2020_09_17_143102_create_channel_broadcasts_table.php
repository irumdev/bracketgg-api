<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelBroadcastsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // 채널에서 운영하는 방송국 테이블
        Schema::create('channel_broadcasts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('channel_id')->comment('방송국 운영 하는 채널');
            $table->string('broadcast_address')->comment('방송국 주소');
            $table->tinyInteger('platform')->comment('방송국 플랫폼');

            $table->timestamps();

            $table->foreign('channel_id')->on('channels')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('channel_broadcasts');
    }
}
