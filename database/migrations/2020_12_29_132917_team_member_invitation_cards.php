<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Team\InvitationCard;

class TeamMemberInvitationCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('team_member_invitation_cards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->comment('가입신청한 유저');
            $table->foreignId('team_id')->comment('가입신청 결정할 팀');
            $table->tinyInteger('status')->default(InvitationCard::PENDING)->comment('수락 상태');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
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
    }
}
