<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id()->comment('인덱스');
            $table->string('nick_name')->comment('유저 닉네임');
            $table->string('email')->unique()->comment('유저 이메일');
            $table->timestamp('email_verified_at')->nullable()->comment('이메일 인증 시각');
            $table->string('password')->comment('비밀번호');
            $table->tinyInteger('create_team_limit')->default(3)->comment('유저당 팀 생성 수');
            $table->tinyInteger('create_channel_limit')->default(5)->comment('유저당 체널 생성 수');
            $table->string('profile_image')->nullable(true)->comment('프로필 이미지');
            $table->tinyInteger('un_read_notification_count')->default(User::DEFAULT_UN_READ_BADGE_COUNT)->comment('안읽은 뱃지');
            $table->boolean('is_policy_agree')->comment('약관동의 여부');
            $table->boolean('is_privacy_agree')->comment('개인정보 처리방침 동의 여부');


            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
