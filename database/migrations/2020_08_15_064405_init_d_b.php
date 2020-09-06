<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InitDB extends Migration
{
    private function addTableComment(string $tableName, string $comment): void
    {
        \DB::statement(sprintf("ALTER TABLE `%s` comment '%s'", $tableName, $comment));
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // $this->createChannelScheme();
        // $this->createTeamScheme();
        // $this->createBracket();
    }



    private function hasNotTable(string $tableName): bool
    {
        return Schema::hasTable($tableName) === false;
    }

    // private function createBracket(): void
    // {
    //     if ($this->hasNotTable('brackets')) {
    //         Schema::create('brackets', function (Blueprint $table) {
    //             $table->id();
    //             $table->foreignId('winner')->comment('승자 (팀 또는 개인)');
    //             $table->foreignId('match_id')->comment('경기 인덱스');
    //             $table->integer('type')->comment('1 : 개인 / 2 : 팀');
    //             $table->softDeletes();
    //             $table->timestamps();

    //             $table->foreign('winner', 'bracket_winner_team')->references('id')->on('teams')->onDelete('cascade');
    //             $table->foreign('winner', 'bracket_winner_user')->references('id')->on('users')->onDelete('cascade');

    //         });
    //     }

    //     if ($this->hasNotTable('matches')) {
    //         Schema::create('matches', function (Blueprint $table) {

    //             $table->uuid('id');
    //             $table->foreignId('winner')->nullable()->comment('승자 (팀 또는 개인)');
    //             $table->uuid('child')->nullable()->comment('matches uuid 키');
    //             $table->integer('type')->nullable()->comment('1 : 개인 / 2 : 팀');
    //             $table->softDeletes();
    //             $table->timestamps();

    //             $table->primary('id');

    //             $table->foreign('winner', 'matches_winner_team')->references('id')->on('teams')->onDelete('cascade');
    //             $table->foreign('winner', 'matches_winner_user')->references('id')->on('users')->onDelete('cascade');
    //             $table->foreign('child')->references('id')->on('matches')->onDelete('cascade');

    //         });
    //     }

    //     if ($this->hasNotTable('match_members')) {
    //         Schema::create('match_members', function (Blueprint $table) {

    //             $table->id();
    //             $table->uuid('match_id');
    //             $table->foreignId('member')->comment('경기 참여자 (팀 또는 개인)');

    //             $table->integer('type')->comment('1 : 개인 / 2 : 팀');
    //             $table->softDeletes();
    //             $table->timestamps();

    //             $table->foreign('member', 'matches_member_team')->references('id')->on('teams')->onDelete('cascade');
    //             $table->foreign('member', 'matches_member_user')->references('id')->on('users')->onDelete('cascade');

    //         });
    //     }

    //     if ($this->hasNotTable('match_brackets_suppliers')) {
    //         Schema::create('match_brackets_suppliers', function (Blueprint $table) {
    //             $table->id();
    //             $table->foreignId('bracket_id')->comment('브라켓 아이디');
    //             $table->uuid('matche_id')->comment('경기 아이디');

    //             $table->foreign('bracket_id', 'bracket_foreign_id')->references('id')->on('brackets')->onDelete('cascade');
    //             $table->foreign('matche_id', 'matche_foreign_id')->references('id')->on('matches')->onDelete('cascade');

    //         });
    //     }
    // }

    private function createTeamScheme(): void
    {

        // if ($this->hasNotTable('teams')) {
        //     Schema::create('teams', function (Blueprint $table) {
        //         $table->id();
        //         $table->string('name')->comment('팀 이름');
        //         $table->string('logo_image')->comment('로고 이미지 경로');
        //         $table->softDeletes();
        //         $table->timestamps();
        //     });
        //     $this->addTableComment('teams', '팀 정보');
        // }

        // if ($this->hasNotTable('team_slugs')) {
        //     Schema::create('team_slugs', function (Blueprint $table) {
        //         $table->id();
        //         $table->foreignId('slug_id')->comment('팀 인덱스');
        //         $table->string('slug')->comment('url 슬러그');
        //         $table->softDeletes();
        //         $table->timestamps();

        //         // $table->foreign('slug_id')->references('id')->on('teams')->onDelete('cascade');
        //     });
        //     $this->addTableComment('teams', '팀 슬러그');

        // }

        // if ($this->hasNotTable('team_members')) {
        //     Schema::create('team_members', function (Blueprint $table) {
        //         $table->id();
        //         $table->foreignId('team_id')->comment('팀 인덱스');
        //         $table->foreignId('user_id')->comment('유저 인덱스');
        //         $table->integer('level')->comment('등급')->default(1);
        //         $table->softDeletes();
        //         $table->timestamps();

        //         // $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        //         // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        //     });
        //     $this->addTableComment('teams', '팀 멤버');
        // }
    }

    // private function createChannelScheme(): void
    // {
    //     if ($this->hasNotTable('channels')) {
    //         Schema::create('channels', function (Blueprint $table) {
    //             // 채널 스키마
    //             $table->id();
    //             $table->foreignId('user_id')->comment('채널 소유주 유저 인덱스');
    //             $table->string('logo')->comment('채널 로고 경로');
    //             $table->mediumText('intro')->comment('채널 소개글');
    //             $table->softDeletes();
    //             $table->timestamps();

    //             $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    //         });
    //         $this->addTableComment('channels', '유저가 생성한 채널');
    //     }

    //     // 채널 배너 테이블 생성
    //     if ($this->hasNotTable('channel_banner_images')) {

    //         Schema::create('channel_banner_images', function (Blueprint $table) {
    //             // 채널 배너 이미지 테이블
    //             $table->id();
    //             $table->foreignId('channel_id')->comment('채널 인덱스');
    //             $table->string('banner_image')->comment('배너 이미지 경로');
    //             $table->softDeletes();
    //             $table->timestamps();

    //             $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');

    //         });
    //         $this->addTableComment('channel_banner_images', '채널 배너 이미지 경로');
    //     }

    //     if ($this->hasNotTable('channel_slugs')) {
    //         Schema::create('channel_slugs', function (Blueprint $table) {
    //             // 채널 슬러그 테이블
    //             $table->id();
    //             $table->foreignId('channel_id')->comment('채널 인덱스');
    //             $table->string('slug')->comment('채널 url 슬러그');
    //             $table->integer('status')->comment('slug 상태');
    //             $table->softDeletes();
    //             $table->timestamps();

    //             $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');

    //         });
    //         $this->addTableComment('channel_slugs', '채널 url slug');
    //     }
    // }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
