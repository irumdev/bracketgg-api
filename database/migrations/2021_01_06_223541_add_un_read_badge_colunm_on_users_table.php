<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class AddUnReadBadgeColunmOnUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->tinyInteger('un_read_notification_count')->after('remember_token')->default(User::DEFAULT_UN_READ_BADGE_COUNT)->comment('안읽은 뱃지');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('un_read_notification_count');
        });
    }
}
