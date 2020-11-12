<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Teams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner')->comment('팀장유저 인덱스');
            $table->string('name')->comment('팀 이름');
            $table->tinyInteger('is_public')->comment('팀 공개 여부');
            $table->string('logo_image');
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
