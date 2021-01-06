<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NotificationHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('notification_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('type')->comment('이벤트 종류');
            $table->json('message')->comment('알림 할 정보');
            $table->foreignId('user_id')->comment('알림 받는사람');
            $table->tinyInteger('is_read')->comment('알람 확인여부');
            $table->tinyInteger('is_receive')->comment('fc 성공여부');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_infos');
    }
}
