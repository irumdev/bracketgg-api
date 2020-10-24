<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeChannelNullColunm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->text('description')->nullable(true)->change();
            $table->string('logo_image')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
            $table->string('logo_image')->nullable(false)->change();
        });
    }
}
